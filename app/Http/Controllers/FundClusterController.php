<?php

namespace App\Http\Controllers;

use App\DeleteLog;
use App\FundCluster;
use Illuminate\Http\Request;

class FundClusterController extends Controller
{
    public function index()
    {
        $my_data = FundCluster::all();
        return view('fundclusters.index', compact('my_data'));
    }

    public function store(Request $request)
    {
        $insert = new FundCluster;
        $insert->code = $request->code;
        $insert->name = $request->name;
        $insert->save();

        return redirect()->back()
            ->with('message', 'Record Saved.')
            ->with('color', 'success');
    }

    public function update(Request $request)
    {
        $update = FundCluster::find($request->fundcluster_id);
        $update->code = $request->code;
        $update->name = $request->name;
        $update->save();

        return redirect()->back()
            ->with('message', 'Record Updated.')
            ->with('color', 'info');
    }

    public function delete(Request $request)
    {
        $delete = FundCluster::find($request->fundcluster_id);
        DeleteLog::delete_log(
            'fund_clusters',
            auth()->user()->id,
            json_encode($delete)
        );
        $delete->delete();

        return redirect()->back()
            ->with('message', 'Record Deleted.')
            ->with('color', 'danger');
    }
}
