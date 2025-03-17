@extends('partials._layout')

@section('header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ strtoupper($project->project_name) }}</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ URL::to('/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Budget Tracking</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-sm-4">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <label class="form-label mb-0" for="year">List of Expenditure</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row form-group">
                <div class="col-sm">
                    <button class="btn btn-success" onclick="addProject()">
                        <span class="fa fa-plus"></span> Add Project
                    </button>
                    <button class="btn btn-ssi float-right" onclick="printTable()">
                        <span class="fa fa-print"></span> Print Table
                    </button>
                    <div class="tooltip"></div>
                </div>
            </div>
            <input id="project_id" name="project_id" type="hidden" value="{{ $project->id }}">
            <div class="row form-group">
                <div class="col-sm">
                    <table class="table table-bordered" id="budgetTable">
                        <thead>
                            <tr>
                                <th>Item of Expenditure</th>
                                <th>Budget</th>
                                @if (!Request::is('gaa*'))
                                    <th>Realign IN</th>
                                    <th>Realign OUT</th>
                                @endif
                                <th>Expenses </th>
                                <th>Balance</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $lastObjectType = null; @endphp
                            @foreach ($categoryTree as $category)
                                @if ($lastObjectType !== $category['object_type'])
                                    @php $lastObjectType = $category['object_type']; @endphp
                                    <tr class="table-info">
                                        <td class="font-weight-bold" colspan="7">
                                            @if ($category['object_type'] === 'MOOE')
                                                MAINTENANCE AND OTHER OPERATING EXPENSES (MOOE)
                                            @elseif($category['object_type'] === 'PS')
                                                PERSONNEL SERVICES (PS)
                                            @elseif($category['object_type'] === 'CO')
                                                CAPITAL OUTLAY (CO)
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                                @include('gaa.row', [
                                    'category' => $category,
                                    'level' => 0,
                                ])
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- add project modal --}}

    <div class="modal fade" id="projectModal" data-bs-backdrop="static" data-bs-keyboard="false"
        aria-labelledby="projectModalLabel" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ URL::to('/categories/saveProject') }}" method="post">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="projectModalLabel">Add Project for {{ $selectedYear }}</h5>
                        <button class="btn-close" data-bs-dismiss="modal" type="button" aria-label="Close"></button>
                    </div>
                    <input id="year" name="year" type="hidden" value="{{ $selectedYear }}">
                    <div class="modal-body">
                        <div class="row form-group">
                            <div class="col-sm">
                                <label>Project Name:</label>
                                <input class="form-control" id="project_name" name="project_name" type="text" required>
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm">
                                <label>Division:</label>
                                <select class="form-select" id="division_id" name="division_id">
                                    <option value="" selected><small>-select division-</small>
                                    </option>
                                    @foreach ($divisions as $division)
                                        <option value="{{ $division->id }}">{{ $division->division_acronym }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success mt-3">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- tracking modal --}}
    <div class="modal fade" id="trackingModal" data-bs-backdrop="static" data-bs-keyboard="false"
        aria-labelledby="trackingModalLabel" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="trackingModalLabel"><span id="expense_name"></span></h5>
                    <button class="btn-close" data-bs-dismiss="modal" type="button" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row form-group">
                        <input id="gaa_project_id" name="gaa_project_id" type="hidden">
                        <div class="col-sm-7">
                            <label>Amount</label>
                            <input class="form-control" id="amount" name="amount" type="number" required>
                        </div>
                        <div class="col-sm">
                            <label>Activity Date</label>
                            <input class="form-control" id="activity_date" name="activity_date" type="date" required>
                        </div>
                    </div>
                    <div class="row form-group">
                        <div class="col-sm">
                            <label>Remarks</label>
                            <input class="form-control" id="remarks" name="remarks" type="text">
                            <div class="form-check mt-2">
                                <input class="form-check-input" id="realignCheckbox" type="checkbox">
                                <label class="form-check-label" for="realignCheckbox">Realign Funds</label>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row form-group realignSection" style="display: none;">
                        <div class="col-sm">
                            <label class="text-secondary"><small>--> realign funds to:</small></label><br>
                            <label>Project:</label>
                            <select class="form-select" id="realign_project_id" name="realign_project_id"
                                onchange="loadGaaFromProject()">
                                <option value="" selected>- Select Project -</option>
                                @php $projects = App\Project::where('approved_budget_id', $approved_budget_id)->get(); @endphp
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row form-group realignSection" style="display: none;">
                        <div class="col-sm">
                            <label>Project Item:</label>
                            <select class="form-select" id="realign_gaa_id" name="realign_gaa_id">
                                <option value="" selected>- Select Item -</option>
                            </select>
                        </div>
                    </div>
                    <div class="row form-group" id="realignButton" style="display: none;">
                        <div class="col-sm">
                            <button class="btn btn-ssi btn-lg" id="btn_realign" onclick="updateTracking('OUT')"><span
                                    class="fa fa-exchange-alt"></span> Realign Fund
                                (OUT)</button>
                        </div>
                    </div>
                    <div class="row form-group" id="inOutButtons">
                        <div class="col-sm">
                            <button class="btn btn-success btn-block btn-lg" id="btn_in"
                                onclick="updateTracking('IN')"><span class="fa fa-plus"></span> IN</button>
                        </div>
                        <div class="col-sm">
                            <button class="btn btn-danger btn-block btn-lg" id="btn_out"
                                onclick="updateTracking('OUT')"><span class="fa fa-minus"></span> OUT</button>
                        </div>
                    </div>
                    <div class="row form-group border-top">
                        <div class="col-sm"><br>
                            <table class="table table-bordered">
                                <thead>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Activity Date</th>
                                    <th>Remarks</th>
                                    {{-- <th></th> --}}
                                </thead>
                                <tbody id="tracking_tbody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    @include('gaa.css')
@endsection

@section('js')
    <script>
        function addProject() {
            $('#projectModal').modal('toggle');
        }
    </script>
    @include('gaa.js')
@endsection
