<?php

namespace App\Http\Controllers;

use App\ApprovedBudget;
use App\Division;
use App\GAA;
use App\Project;
use Illuminate\Http\Request;

class GAAController extends Controller
{

    public function index(Request $request)
    {
        $currentYear = date('Y');
        $selectedYear = $request->get('year', $currentYear);
        $divisions = Division::all();

        $approved_budget = ApprovedBudget::with([
            'gaa',
            'gaa.gaaProjects',
            'gaa.gaaProjects.expenses'
        ])
            ->where('year', $selectedYear)
            ->first();
        // return response()->json($approved_budget);

        // Collect parent categories
        $parents = collect();
        foreach ($approved_budget['gaa'] as $gaa_item) {
            $parent = $gaa_item->parentCategory;
            while ($parent) {
                if (!$parents->contains('id', $parent->id)) {
                    $parents->push($parent);
                }
                $parent = $parent->parentCategory;
            }
        }

        $filteredgaa = $approved_budget['gaa']->merge($parents)->unique('id');
        $topLevelgaa = $filteredgaa->where('parent_id', null)
            ->sortByDesc('object_type');
        $sortedgaa = $topLevelgaa->merge(
            $filteredgaa->where('parent_id', '!=', null)
        );

        $categoryTree = $this->buildTree($sortedgaa);
        // $dropdownData = $this->dropdownData($selectedYear);
        // return response()->json($categoryTree);
        return view('gaa.index', compact('categoryTree', 'selectedYear', 'divisions'));
    }


    public function buildTree($gaa, $parentId = null, $level = 0)
    {
        $tree = [];
        foreach ($gaa as $category) {
            if ($category->parent_id === $parentId) {
                $children = $this->buildTree($gaa, $category->id, $level + 1);

                $expenses = [];
                foreach ($category->gaaProjects as $project) {
                    foreach ($project->expenses as $expense) {
                        $expenses[] = [
                            'project' => $project->project->project_name,
                            'type' => $expense->type,
                            'amount' => $expense->amount,
                            'remarks' => $expense->remarks,
                            'date' => $expense->date,
                            'division' => $expense->division->division_acronym ?? null,
                            'realign_from' => $expense->realign_from,
                            'realign_to' => $expense->realign_to,
                        ];
                    }
                }
                $realign_in = []; //get from expenses array
                $realign_out = []; // get from expenses array
                $remaining_balance = 0; //get from expenses array, with type is OUT and IN

                $tree[] = [
                    'id' => $category->id,
                    'item_of_expenditure' => $category->item_of_expenditure,
                    'level' => $level,
                    'object_type' => $category->object_type,
                    'fund_cluster' => $category->fund_cluster,
                    'division' => $category->division->division_acronym ?? null,
                    'allocation' => $category->allocation,
                    'expenses' => $expenses,
                    'realign_in' => $realign_in,
                    'realign_out' => $realign_out,
                    'remaining_balance' => $remaining_balance, // Include remaining_balance only if no children
                    'children' => $children,
                ];
            }
        }
        return $tree;
    }

    public function dropdownData($selectedYear)
    {
        $approved_budget = ApprovedBudget::with([
            'gaa' => function ($query) {
                $query->where('object_type', '<>', 'PS');
            },
            'gaa.gaaProjects',
            'gaa.gaaProjects.expenses'
        ])
            ->where('year', $selectedYear)
            ->first();
        // return response()->json($approved_budget);

        // Collect parent categories
        $parents = collect();
        foreach ($approved_budget['gaa'] as $gaa_item) {
            $parent = $gaa_item->parentCategory;
            while ($parent) {
                if (!$parents->contains('id', $parent->id)) {
                    $parents->push($parent);
                }
                $parent = $parent->parentCategory;
            }
        }

        $filteredgaa = $approved_budget['gaa']->merge($parents)->unique('id');
        $topLevelgaa = $filteredgaa->where('parent_id', null)
            ->sortByDesc('object_type');
        $sortedgaa = $topLevelgaa->merge(
            $filteredgaa->where('parent_id', '!=', null)
        );

        $categoryTree = $this->buildTree($sortedgaa);
        return $this->buildTree($categoryTree);
    }

    public function project($projectId, Request $request)
    {

        $currentYear = date('Y');
        $selectedYear = $request->get('year', $currentYear);
        $divisions = Division::all();
        $approved_budget = ApprovedBudget::whereHas('gaa.gaaProjects', function ($query) use ($projectId) {
            $query->where('project_id', $projectId);
        })
            ->with([
                'gaa' => function ($query) use ($projectId) {
                    $query->whereHas('gaaProjects', function ($query) use ($projectId) {
                        $query->where('project_id', $projectId);
                    });
                },
                'gaa.gaaProjects.expenses'
            ])
            ->first();
        // return response()->json($approved_budget);

        if (!$approved_budget) {
            $msg = Project::find($projectId);
            return redirect()->back()
                ->with('message', 'Please assign an item from GAA to project: ' . $msg->project_name)
                ->with('color', 'warning');
        }
        $parents = collect();
        foreach ($approved_budget['gaa'] as $gaa_item) {
            $parent = $gaa_item->parentCategory;
            while ($parent) {
                if (!$parents->contains('id', $parent->id)) {
                    $parents->push($parent);
                }
                $parent = $parent->parentCategory;
            }
        }

        $filteredgaa = $approved_budget['gaa']->merge($parents)->unique('id');

        // Make parent_id null if the parent_id is not existing in the collection
        foreach ($filteredgaa as $gaa_item) {
            if ($gaa_item->parent_id && !$filteredgaa->contains('id', $gaa_item->parent_id)) {
                $gaa_item->parent_id = null;
            }
        }

        $topLevelgaa = $filteredgaa->where('parent_id', null)
            ->sortByDesc('object_type');
        $sortedgaa = $topLevelgaa->merge(
            $filteredgaa->where('parent_id', '!=', null)
        );

        $categoryTree = $this->buildTree($sortedgaa);
        // return response()->json($categoryTree);
        return view('gaa.project', compact('categoryTree', 'selectedYear', 'divisions'));
    }
}
