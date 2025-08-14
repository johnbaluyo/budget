<?php

namespace App\Http\Controllers;

use App\ApprovedBudget;
use App\Project;
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
            ? Project::with(['division', 'gaaProjects.expenses'])->where('approved_budget_id', $approved_budget->id)->get()
            : collect();

        // Calculate totals for each project
        foreach ($projects as $project) {
            $totalBudget = 0;
            $totalExpenses = 0;

            foreach ($project->gaaProjects as $gaaProject) {
                $totalBudget += $gaaProject->budget;

                // Sum OUT and IN separately
                $out = $gaaProject->expenses->where('type', 'OUT')->sum('amount');
                $in = $gaaProject->expenses->where('type', 'IN')->sum('amount');
                $totalExpenses += ($out - $in);
            }

            $project->total_budget = $totalBudget;
            $project->total_expenses = $totalExpenses;
            $project->total_remaining = $totalBudget - $totalExpenses;
        }

        return view('projects.index', compact('projects', 'selectedYear'));
    }
}
