<?php

namespace App\Http\Controllers;

use App\DeleteLog;
use App\Division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    public function index()
    {
        $my_data = Division::all();
        return view('divisions.index', compact('my_data'));
    }

    public function store(Request $request)
    {
        $insert = new Division;
        $insert->division_name = $request->division_name;
        $insert->division_acronym = $request->division_acronym;
        $insert->location = $request->location;
        $insert->save();

        return redirect()->back()
            ->with('message', 'Record Saved.')
            ->with('color', 'success');
    }

    public function update(Request $request)
    {
        $update = Division::find($request->division_id);
        $update->division_name = $request->division_name;
        $update->division_acronym = $request->division_acronym;
        $update->location = $request->location;
        $update->save();

        return redirect()->back()
            ->with('message', 'Record Updated.')
            ->with('color', 'info');
    }

    public function delete(Request $request)
    {
        $delete = Division::find($request->division_id);
        DeleteLog::delete_log(
            'divisions',
            auth()->user()->id,
            json_encode($delete)
        );
        $delete->delete();

        return redirect()->back()
            ->with('message', 'Record Deleted.')
            ->with('color', 'danger');
    }
}
