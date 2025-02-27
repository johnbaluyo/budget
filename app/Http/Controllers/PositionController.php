<?php

namespace App\Http\Controllers;

use App\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index()
    {
        $my_data = Position::all();
        return view('positions.index', compact('my_data'));
    }

    public function store(Request $request)
    {
        $insert = new Position;
        $insert->position_name = $request->position_name;
        $insert->salary_grade = $request->salary_grade;
        $insert->step_increment = $request->step_increment;
        $insert->plantilla_code = $request->plantilla_code;
        $insert->save();

        return redirect()->back()
            ->with('message', 'Record Saved.')
            ->with('color', 'success');
    }
    
    public function update(Request $request)
    {
        $update = Position::find($request->position_id);
        $update->position_name = $request->position_name;
        $update->salary_grade = $request->salary_grade;
        $update->step_increment = $request->step_increment;
        $update->plantilla_code = $request->plantilla_code;
        $update->save();

        return redirect()->back()
            ->with('message', 'Record Updated.')
            ->with('color', 'info');
    }

    public function delete(Request $request)
    {
        $delete = Position::find($request->position_id);
        $delete->delete();

        return redirect()->back()
            ->with('message', 'Record Deleted.')
            ->with('color', 'danger');
    }
}
