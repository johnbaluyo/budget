<?php

namespace App\Http\Controllers;

use App\ApprovedBudget;
use App\Project;
use App\ProjectMonthlyBudget;
use Illuminate\Http\Request;

class ProjectController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request, $year)
    {
        $selectedYear = $request->get('year', $year);

        $approved_budget = ApprovedBudget::where('year', $selectedYear)->first();

        if (!$approved_budget) {
            return redirect('/approvedbudget');
        }
        $projects = $approved_budget
            ? Project::with([
                'division',
                'gaaProjects.expenses' => function($query) use ($selectedYear) {
                    $query->whereYear('date', $selectedYear);
                },
                'gaaProjects.monthlyBudgets' => function($query) use ($selectedYear) {
                    $query->where('year', $selectedYear);
                }
            ])->where('approved_budget_id', $approved_budget->id)->get()
            : collect();

        // Calculate totals and monthly data for each project
        foreach ($projects as $project) {
            $totalBudget = 0;
            $totalExpenses = 0;
            
            // Initialize monthly arrays
            $monthlyBudgets = array_fill(1, 12, 0);
            $monthlyExpenses = array_fill(1, 12, 0);

            foreach ($project->gaaProjects as $gaaProject) {
                $totalBudget += $gaaProject->budget;

                // Sum OUT and IN separately
                $out = $gaaProject->expenses->where('type', 'OUT')->sum('amount');
                $in = $gaaProject->expenses->where('type', 'IN')->sum('amount');
                $totalExpenses += ($out - $in);
                
                // Aggregate monthly budgets from all gaa_projects
                foreach ($gaaProject->monthlyBudgets as $monthlyBudget) {
                    $monthlyBudgets[$monthlyBudget->month] += $monthlyBudget->budget_amount;
                }
                
                // Aggregate monthly expenses from all gaa_projects
                // Expenses are already filtered by year in the eager loading
                foreach ($gaaProject->expenses as $expense) {
                    if ($expense->date) {
                        $expenseDate = \Carbon\Carbon::parse($expense->date);
                        $month = $expenseDate->month;
                        
                        if ($expense->type == 'OUT') {
                            $monthlyExpenses[$month] += $expense->amount;
                        } elseif ($expense->type == 'IN') {
                            $monthlyExpenses[$month] -= $expense->amount;
                        }
                    }
                }
            }

            $project->total_budget = $totalBudget;
            $project->total_expenses = $totalExpenses;
            $project->total_remaining = $totalBudget - $totalExpenses;
            $project->monthly_budgets = $monthlyBudgets;
            $project->monthly_expenses = $monthlyExpenses;
        }

        return view('projects.index', compact('projects', 'selectedYear'));
    }

    public function getMonthlyBudget(Request $request)
    {
        $projectId = $request->get('project_id');
        $year = $request->get('year');

        $project = Project::with(['monthlyBudgets' => function ($query) use ($year) {
            $query->where('year', $year);
        }])->find($projectId);

        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        // Calculate total budget for the project
        $project->load(['gaaProjects']);
        $totalBudget = 0;
        foreach ($project->gaaProjects as $gaaProject) {
            $totalBudget += $gaaProject->budget;
        }

        // Prepare monthly data
        $monthlyData = [];
        for ($month = 1; $month <= 12; $month++) {
            $existingBudget = $project->monthlyBudgets->where('month', $month)->first();
            $monthlyData[] = [
                'month' => $month,
                'budget_amount' => $existingBudget ? $existingBudget->budget_amount : 0
            ];
        }

        return response()->json([
            'project_name' => $project->project_name,
            'total_budget' => $totalBudget,
            'monthly_budgets' => $monthlyData
        ]);
    }

    public function saveMonthlyBudget(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'year' => 'required|integer',
            'monthly_budgets' => 'required|array',
            'monthly_budgets.*.month' => 'required|integer|min:1|max:12',
            'monthly_budgets.*.budget_amount' => 'required|numeric|min:0'
        ]);

        $projectId = $request->project_id;
        $year = $request->year;
        $monthlyBudgets = $request->monthly_budgets;

        // Validate total doesn't exceed project budget
        $project = Project::with('gaaProjects')->find($projectId);
        $totalBudget = 0;
        foreach ($project->gaaProjects as $gaaProject) {
            $totalBudget += $gaaProject->budget;
        }

        $totalAllocated = array_sum(array_column($monthlyBudgets, 'budget_amount'));
        if ($totalAllocated > $totalBudget) {
            return response()->json([
                'error' => 'Total monthly allocation exceeds project budget'
            ], 422);
        }

        // Save or update monthly budgets
        foreach ($monthlyBudgets as $data) {
            ProjectMonthlyBudget::updateOrCreate(
                [
                    'project_id' => $projectId,
                    'month' => $data['month'],
                    'year' => $year
                ],
                [
                    'budget_amount' => $data['budget_amount']
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Monthly budget allocation saved successfully'
        ]);
    }
}
