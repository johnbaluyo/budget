<?php

namespace App\Http\Controllers;

use App\DeleteLog;
use App\Division;
use App\Position;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $my_data = User::with('division', 'position')->get();
        $divisions = Division::all();
        $positions = Position::all();

        // return response()->json($my_data);


        return view('users.index', compact('my_data', 'divisions', 'positions'));
    }

    public function store(Request $request)
    {
        $insert = new User;
        $insert->name = $request->name;
        $insert->email = $request->email;
        $insert->password = Hash::make($request->password);
        $insert->position_id = $request->position;
        $insert->division_id = $request->division;
        $insert->user_type = $request->user_type;
        $insert->save();

        return redirect()->back()
            ->with('message', 'Record Saved.')
            ->with('color', 'success');
    }


    public function update(Request $request)
    {
        $update = User::find($request->user_id);

        if ($request->password !== null) {
            $update->password = Hash::make($request->password);
        }

        $update->name = $request->name;
        $update->email = $request->email;
        $update->position_id = $request->position;
        $update->division_id = $request->division;
        $update->user_type = $request->user_type;
        $update->save();

        return redirect()->back()
            ->with('message', 'Record Saved.')
            ->with('color', 'success');
    }

    public function delete(Request $request)
    {
        $delete = User::find($request->user_id);
        DeleteLog::delete_log(
            'users',
            auth()->user()->id,
            json_encode($delete)
        );
        $delete->delete();

        return redirect()->back()
            ->with('message', 'Record Deleted.')
            ->with('color', 'danger');
    }
}
