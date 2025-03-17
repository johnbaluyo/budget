<?php

namespace App\Http\Controllers;

use App\ApprovedBudget;
use App\Division;
use App\GAA;
use App\GAAProject;
use App\GAAProjectExpenses;
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
        $approved_budget_id = $approved_budget->id;
        $parents = collect();
        if ($approved_budget) {
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
        } else {
            return redirect('/approvedbudget');
        }
        return view('gaa.index', compact('categoryTree', 'selectedYear', 'divisions', 'approved_budget_id'));
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
        $approved_budget_id = $approved_budget->id;
        // return response()->json($approved_budget);

        $project = Project::find($projectId);
        if (!$approved_budget) {
            return redirect('/gaa')
                ->with('message', 'Please assign an item from GAA to project: ' . $project->project_name)
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
        return view('gaa.project', compact('categoryTree', 'selectedYear', 'divisions', 'project', 'approved_budget_id'));
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

    public function saveItemToProject(Request $request)
    {
        $approved_budget = ApprovedBudget::where('year', $request->year)->first();
        $check_project = Project::where('project_name', $request->project_name)
            ->where('approved_budget_id', $approved_budget->id)
            ->first();
        if (!$check_project) {
            $project = new Project();
            $project->project_name = $request->project_name;
            $project->approved_budget_id = $approved_budget->id;
            $project->save();
        } else {
            $project = $check_project;
        }
        $gaa_project = new GAAProject();
        $gaa_project->project_id = $project->id;
        $gaa_project->gaa_id = $request->item_to_project_gaa_id;
        $gaa_project->save();
        return redirect()->back()
            ->with('message', 'Item successfully assigned to project: ' . $project->project_name)
            ->with('color', 'success');
    }

    public function delete(Request $request)
    {
        try {
            $model = $request->type == 1 ? GAA::find($request->gaa_id) : GAAProject::where('project_id', $request->project_id)->where('gaa_id', $request->gaa_id);
            $model->delete();
            return response()->json('success');
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $check = GAA::where([
                ['item_of_expenditure', $request->item_of_expenditure],
                ['parent_id', $request->parent_id],
                ['object_type', $request->object_type],
                ['fund_cluster', $request->fund_cluster],
            ])->when($request->gaa_id, function ($query) use ($request) {
                return $query->where('id', '<>', $request->gaa_id);
            })->first();

            if ($check) {
                return redirect()->back()->with([
                    'message' => 'Item already exists.',
                    'color' => 'warning'
                ]);
            }

            $approved_budget_id = ApprovedBudget::where('year', date('Y'))->first()->id;
            $model = GAA::findOrNew($request->gaa_id);
            $model->item_of_expenditure = $request->item_of_expenditure;
            $model->object_type = $request->object_type;
            $model->fund_cluster = $request->fund_cluster;
            $model->division_id = $request->division_id;
            $model->budget_allocation = $request->allocation;
            $model->remarks = $request->remarks;
            $model->parent_id = $request->parent_id;
            $model->approved_budget_id = $approved_budget_id;
            $model->save();
            return redirect()->back()->with([
                'message' => 'Record saved successfully.',
                'color' => 'success'
            ]);
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    public function edit(Request $request)
    {
        try {
            return response()->json(GAA::find($request->gaa_id));
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    public function getExpenseId(Request $request)
    {
        try {
            $gaa_project_expenses = GAAProject::with(['gaa', 'expenses'])
                ->where('project_id', $request->project_id)
                ->where('gaa_id', $request->gaa_id)
                ->first();
            return response()->json($gaa_project_expenses);
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    public function updateTracking(Request $request)
    {
        try {
            $data = GAAProjectExpenses::create([
                'gaa_project_id' => $request->gaa_project_id,
                'type' => $request->type,
                'amount' => $request->amount,
                'remarks' => $request->remarks,
                'date' => $request->date,
                'division_id' => $request->division_id,
                'realign_from' => $request->has('realign_from') ? $request->realign_from : null,
                'realign_to' => $request->has('realign_to') ? $request->realign_to : null,
            ]);
            $gaa_id = GAAProject::find($request->gaa_project_id);
            return response()->json(array('message' => 'success', 'gaa_id' => $gaa_id->gaa_id));
        } catch (\Exception $e) {
            return response()->json(array('message' => $e->getMessage()));
        }
    }

    public function getGaaFromProject(Request $request)
    {
        $project_id = $request->project_id;
        $approved_budget = ApprovedBudget::whereHas('gaa.gaaProjects', function ($query) use ($project_id) {
            $query->where('project_id', $project_id);
        })
            ->with([
                'gaa' => function ($query) use ($project_id) {
                    $query->whereHas('gaaProjects', function ($query) use ($project_id) {
                        $query->where('project_id', $project_id);
                    });
                },
                'gaa.gaaProjects.expenses'
            ])
            ->first();

        if (!$approved_budget) {
            return response()->json(array('message' => 'no item assigned to project'));
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

        return response()->json(array('message' => 'success', 'items' => $this->buildTree($sortedgaa)));
    }
}
