<?php

namespace App\Http\Controllers;

use App\ApprovedBudget;
use App\DeleteLog;
use App\Division;
use App\GAA;
use App\GAAProject;
use App\GAAProjectExpenses;
use App\Project;
use Illuminate\Http\Request;

class GAAController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request, $year)
    {
        $selectedYear = $request->get('year', $year);
        $divisions = Division::all();
        $approved_budget = ApprovedBudget::with([
            'gaa.gaaProjects.expenses'
        ])->where('year', $selectedYear)->first();

        if (!$approved_budget) {
            return redirect('/approvedbudget');
        }

        $allocated_budget = GAA::where('approved_budget_id', $approved_budget->id)->sum('budget_allocation');

        if ($approved_budget) {
            $expenses = $approved_budget->gaa->flatMap(fn($gaa) => $gaa->gaaProjects->flatMap(fn($project) => $project->expenses));
            $totals = $expenses->filter(fn($expense) => $expense->realign_from === null && $expense->realign_to === null)
                ->groupBy('type')
                ->map(fn($group) => $group->sum('amount'));

            $approved_budget->total_in = $totals['IN'] ?? 0;
            $approved_budget->total_out = $totals['OUT'] ?? 0;
            $approved_budget->remaining_balance = $approved_budget->grand_total_amount + $approved_budget->total_in - $approved_budget->total_out;
        }
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
        // return response()->json($categoryTree);
        return view('gaa.index', compact('categoryTree', 'selectedYear', 'divisions', 'approved_budget', 'allocated_budget'));
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
                        $expenses[] = $expense;
                    }
                }
                $remaining_balance = 0; //get from expenses array, with type is OUT and IN

                $tree[] = [
                    'gaa_id' => $category->id,
                    'item_of_expenditure' => $category->item_of_expenditure,
                    'level' => $level,
                    'object_type' => $category->object_type,
                    'fund_cluster' => $category->fund_cluster,
                    'division' => $category->division->division_acronym ?? null,
                    'allocation' => $category->budget_allocation,
                    'remaining_balance' => $remaining_balance, // Include remaining_balance only if no children
                    'expenses' => $expenses,
                    'children' => $children,
                    'allocated_budget' => $category->gaaProjects->sum('budget'),
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
        return view('gaa.project', compact('categoryTree', 'selectedYear', 'divisions', 'project', 'approved_budget'));
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
        $gaa_project->budget = $request->budget;
        $gaa_project->save();
        return redirect()->back()
            ->with('message', 'Item successfully assigned to project: ' . $project->project_name)
            ->with('color', 'success');
    }

    public function delete(Request $request)
    {
        try {
            $model = $request->type == 1 ? GAA::find($request->gaa_id) : GAAProject::where('project_id', $request->project_id)->where('gaa_id', $request->gaa_id);
            DeleteLog::delete_log(
                $request->type == 1 ? 'gaa' : 'gaa_projects',
                auth()->user()->id,
                json_encode($model)
            );
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

            $approved_budget_id = ApprovedBudget::where('year', $request->year)->first()->id;
            $model = GAA::findOrNew($request->gaa_id);
            $model->item_of_expenditure = $request->item_of_expenditure;
            $model->object_type = $request->object_type;
            $model->fund_cluster = $request->fund_cluster;
            $model->division_id = $request->division_id;
            $model->remarks = $request->remarks;
            $model->parent_id = $request->parent_id;
            $model->approved_budget_id = $approved_budget_id;

            if (isset($request->project)) {
                $model->budget_allocation = $request->allocation;
                $model->save();
                $gaa_project = new GAAProject();
                $gaa_project->project_id = $request->project;
                $gaa_project->gaa_id = $model->id;
                $gaa_project->budget = $request->allocation;
                $gaa_project->save();
            } else {
                $model->save();
            }
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
            $gaa_project_expenses = GAAProject::with([
                'gaa',
                'expenses',
                'expenses.realignFrom',
                'expenses.realignTo',
                'expenses.realignFrom.gaa',
                'expenses.realignTo.gaa',
                'expenses.realignFrom.project',
                'expenses.realignTo.project'
            ])
                ->where('project_id', $request->project_id)
                ->where('gaa_id', $request->gaa_id)
                ->first();

            $expenses = $gaa_project_expenses->expenses->map(function ($expense) {
                return [
                    'type' => $expense->type,
                    'amount' => $expense->amount,
                    'date' => $expense->date,
                    'remarks' => $expense->remarks,
                    'realignFrom' => $expense->realignFrom ? 'realigned from: <b>(' . $expense->realignFrom->project->project_name . ') ' . $expense->realignFrom->gaa->item_of_expenditure . '</b><br>' : null,
                    'realignTo' => $expense->realignTo ? 'realigned to: <b>(' . $expense->realignTo->project->project_name . ') ' . $expense->realignTo->gaa->item_of_expenditure . '</b><br>' : null,
                ];
            });

            return response()->json([
                'id' => $gaa_project_expenses->id,
                'gaa' => $gaa_project_expenses->gaa,
                'expenses' => $expenses
            ]);
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    public function updateTracking(Request $request)
    {
        try {
            $realign_to_gaa_project = null;

            if ($request->realign_project_id && $request->realign_gaa_id) {
                $realign_to_gaa_project = GAAProject::where('project_id', $request->realign_project_id)
                    ->where('gaa_id', $request->realign_gaa_id)
                    ->pluck('id')
                    ->first();

                if ($realign_to_gaa_project) {
                    GAAProjectExpenses::create([
                        'gaa_project_id' => $realign_to_gaa_project,
                        'type' => "IN",
                        'amount' => $request->amount,
                        'remarks' => $request->remarks,
                        'date' => $request->date,
                        'division_id' => $request->division_id,
                        'realign_from' => $request->gaa_project_id,
                    ]);
                }
            }

            GAAProjectExpenses::create([
                'gaa_project_id' => $request->gaa_project_id,
                'type' => $request->type,
                'amount' => $request->amount,
                'remarks' => $request->remarks,
                'date' => $request->date,
                'division_id' => $request->division_id,
                'realign_to' => $realign_to_gaa_project,
            ]);

            $gaa_project = GAAProject::find($request->gaa_project_id);

            return response()->json(['message' => 'success', 'gaa_id' => $gaa_project->gaa_id]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
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

    public function moveToOtherProject(Request $request)
    {
        try {
            $gaa_project = GAAProject::where('gaa_id', $request->gaa_project_id)->where('project_id', $request->project_id)->first();
            if (!$gaa_project) {
                return response()->json(['message' => 'GAA Project not found'], 404);
            }
            $gaa_project->project_id = $request->project_id;
            $gaa_project->save();
            return response()->json(['message' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }


    public function getGaaprojects(Request $request)
    {
        $gaa_allocation = GAA::find($request->gaa_id)->budget_allocation;
        $gaa_projects = GAAProject::with('project')
            ->where('gaa_id', $request->gaa_id)
            ->get();

        return response()->json([
            'gaa_allocation' => $gaa_allocation,
            'gaa_projects' => $gaa_projects,
        ]);
    }

    public function saveProjectAllocation(Request $request)
    {
        try {
            $gaa_project = GAAProject::find($request->gaa_project_id);
            $gaa_project->budget = $request->budget;
            $gaa_project->save();
            return response()->json(['message' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function saveGAABudget(Request $request)
    {
        try {
            $gaa = GAA::find($request->fund_gaa_id);
            $gaa->budget_allocation = $request->gaa_budget;
            $gaa->save();
            return response()->json(['message' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
