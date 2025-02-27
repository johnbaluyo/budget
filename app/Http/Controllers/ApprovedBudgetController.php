<?php

namespace App\Http\Controllers;

use App\ApprovedBudget;
use App\GAA;
use App\GAAProject;
use App\Project;
use Illuminate\Http\Request;

class ApprovedBudgetController extends Controller
{
    public function index()
    {
        $my_data = ApprovedBudget::all();
        return view('approvedbudget.index', compact('my_data'));
    }

    public function store(Request $request)
    {
        if (ApprovedBudget::where('year', $request->year)->exists()) {
            return redirect()->back()
                ->with('message', 'Record for this year already exists.')
                ->with('color', 'danger');
        }

        $insert = ApprovedBudget::create([
            'year' => $request->year,
            'grand_total_amount' => $request->grand_total_amount
        ]);

        $project = Project::create([
            'approved_budget_id' => $insert->id,
            'project_name' => 'PSRTI'
        ]);

        $default_categories = GAA::getDefaultCategories();

        $gaa_ids = collect($default_categories)->map(function ($category) use ($insert) {
            $parentId = $category['parent_category']
            ? GAA::where('item_of_expenditure', $category['parent_category'])->orderBy('id', 'desc')->value('id')
            : null;

            $gaa = GAA::create([
            'object_type' => $category['object_type'],
            'item_of_expenditure' => $category['category_name'],
            'parent_id' => $parentId,
            'fund_cluster' => 'RAF-01',
            'approved_budget_id' => $insert->id
            ]);

            return $gaa->id;
        });

        $gaa_ids->each(function ($gaa_id) use ($project) {
            GAAProject::create([
            'project_id' => $project->id,
            'gaa_id' => $gaa_id
            ]);
        });

        return redirect()->back()
            ->with('message', 'Record Saved.')
            ->with('color', 'success');
    }

    public function update(Request $request)
    {
        if (ApprovedBudget::where('year', $request->year)->exists()) {
            return redirect()->back()
                ->with('message', 'Record for this year already exists.')
                ->with('color', 'danger');
        }

        $update = ApprovedBudget::find($request->approved_budget_id);
        $update->year = $request->year;
        $update->grand_total_amount = $request->grand_total_amount;
        $update->save();

        return redirect()->back()
            ->with('message', 'Record Updated.')
            ->with('color', 'info');
    }

    public function delete(Request $request)
    {
        $delete = ApprovedBudget::find($request->approvedbudget_id);
        $delete->delete();

        return redirect()->back()
            ->with('message', 'Record Deleted.')
            ->with('color', 'danger');
    }
}
