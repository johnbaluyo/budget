<?php

namespace App\Http\Controllers;

use App\ApprovedBudget;
use App\Division;
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
        return response()->json($categoryTree);

        // $dropdownData = $this->dropdownData($selectedYear);
        return view('gaa.index', compact('categoryTree', 'selectedYear', 'divisions', 'dropdownData'));
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
                $realign_in = 'get from expenses array';
                $realign_out = 'get from expenses array';
                $remaining_balance = 'get from expenses array, with type is OUT and IN';

                $tree[] = [
                    'id' => $category->id,
                    'category_name' => $category->category_name,
                    'level' => $level,
                    'object_type' => $category->object_type,
                    'fund_cluster' => $category->fund_cluster,
                    'division' => $category->division->division_acronym ?? null,
                    'allocation' => $category->allocation,
                    'expenses' => json_encode($expenses),
                    'realign_in' => $realign_in,
                    'realign_out' => $realign_out,
                    'remaining_balance' => $remaining_balance, // Include remaining_balance only if no children
                    'children' => $children,
                ];
            }
        }
        return $tree;
    }
}
