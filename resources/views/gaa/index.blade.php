@extends('partials._layout')

@section('header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">General Administration and Support</h1>
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
                            <label class="form-label mb-0" for="year">Summary of Expenditures</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="info-box" style="background-color: #f3f5cb; height: 89px">
                        <span class="info-box-icon bg-success">₱</span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{ $selectedYear }} Budget</span>
                            <span
                                class="info-box-number">{{ number_format($approved_budget->grand_total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box" style="background-color: #f3f5cb">
                        <span class="info-box-icon bg-ssi"><i class="fas fa-file-invoice-dollar"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Allocated</span>
                            <span class="info-box-number">
                                @php
                                    $allocated_percentage =
                                        $approved_budget->grand_total_amount > 0
                                            ? ($allocated_budget / $approved_budget->grand_total_amount) * 100
                                            : 0;
                                @endphp
                                {{ number_format($allocated_budget, 2) }}
                                <span class="float-right">{{ number_format($allocated_percentage, 2) }}%</span>
                                <div class="progress">
                                    <div class="progress-bar bg-ssi" role="progressbar"
                                        aria-valuenow="{{ $allocated_percentage }}" aria-valuemin="0" aria-valuemax="100"
                                        style="width: {{ $allocated_percentage }}%"></div>
                                </div>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box" style="background-color: #f3f5cb">
                        <span class="info-box-icon bg-ssi"><i class="fas fa-chart-line"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Expenditures</span>
                            <span class="info-box-number">
                                @php
                                    $percentage =
                                        $approved_budget->grand_total_amount > 0
                                            ? ($approved_budget->total_out / $approved_budget->grand_total_amount) * 100
                                            : 0;
                                @endphp
                                <span class="progress-description">
                                    {{ number_format($approved_budget->total_out, 2) }}
                                    <span class="float-right">{{ number_format($percentage, 2) }}%</span>
                                </span>
                                <div class="progress">
                                    <div class="progress-bar bg-ssi" role="progressbar" aria-valuenow="{{ $percentage }}"
                                        aria-valuemin="0" aria-valuemax="100" style="width: {{ $percentage }}%"></div>
                                </div>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box" style="background-color: #f3f5cb; height: 89px">
                        <span class="info-box-icon bg-warning"><i class="fas fa-balance-scale"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Remaining Balance</span>
                            <span
                                class="info-box-number">{{ number_format($approved_budget->remaining_balance, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row form-group">
                <div class="col-sm">
                    <button class="btn btn-success" onclick="addGaa()">
                        <span class="fa fa-plus"></span> Add Item
                    </button>
                    <button class="btn btn-ssi float-right" onclick="printTable()">
                        <span class="fa fa-print"></span> Print Table
                    </button>
                    <div class="tooltip"></div>
                </div>
            </div>
            <input id="project_id" name="project_id" type="hidden" value="0">
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
                                @else
                                    <th>Allocated</th>
                                @endif
                                <th>Budget Tracking </th>
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

    {{-- add item to gaa --}}

    <div class="modal fade" id="GAAitemModal" data-bs-backdrop="static" data-bs-keyboard="false"
        aria-labelledby="GAAitemLabel" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ URL::to('/gaa/store') }}" method="post">
                    @csrf
                    <input id="gaa_id" name="gaa_id" type="hidden">
                    <input id="parent_id" name="parent_id" type="hidden">
                    <input id="year" name="year" type="hidden" value="{{ $selectedYear }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="GAAitemLabel">GAA Item Details</h5>
                        <button class="btn-close" data-bs-dismiss="modal" type="button" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row form-group">
                            <div class="col-sm">
                                <label>Item of Expenditure:</label>
                                <input class="form-control" id="item_of_expenditure" name="item_of_expenditure"
                                    type="text" required>
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm">
                                <label>Division:</label>
                                <select class="form-select" id="division_id" name="division_id">
                                    <option value="" selected>-select division-</option>
                                    @foreach ($divisions as $division)
                                        <option value="{{ $division->id }}">{{ $division->division_acronym }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm">
                                <label>Fund Cluster</label>
                                <input class="form-control" name="fund_cluster" type="text" value="RAF-01"
                                    style="flex-grow: 0.5;" placeholder="Fund Cluster" readonly>
                            </div>
                            <div class="col-sm">
                                <label>Object Type</label>
                                <select class="form-select" id="object_type" name="object_type">
                                    <option>MOOE</option>
                                    <option>CO</option>
                                    <option>PS</option>
                                </select>
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm">
                                <label>Remarks:</label>
                                <input class="form-control" id="remarks" name="remarks" type="text">
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

    {{-- add item to project modal --}}

    <div class="modal fade" id="projectModal" data-bs-backdrop="static" data-bs-keyboard="false"
        aria-labelledby="projectModalLabel" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ URL::to('/project/saveItemToProject') }}" method="post">
                    @csrf
                    <input id="year" name="year" type="hidden" value="{{ $selectedYear }}">
                    <input id="item_to_project_gaa_id" name="item_to_project_gaa_id" type="hidden">
                    <div class="modal-header">
                        <h5 class="modal-title">Assign Expenditure: <span id="projectModalLabel"></span></h5>
                        <button class="btn-close" data-bs-dismiss="modal" type="button" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row form-group">
                            <div class="col-sm">
                                <label>Available Fund:</label>
                                <input class="form-control" id="available_fund" name="available_fund" type="text"
                                    readonly>
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm">
                                <label>Project Name:</label>
                                <input class="form-control" id="project_name" name="project_name" type="text"
                                    list="projectList" required>
                                <datalist id="projectList">
                                    @php
                                        $projects = DB::table('projects')
                                            ->where('approved_budget_id', function ($query) use ($selectedYear) {
                                                $query
                                                    ->select('id')
                                                    ->from('approved_budget')
                                                    ->where('year', $selectedYear);
                                            })
                                            ->get();
                                    @endphp
                                    @foreach ($projects as $project)
                                        <option value="{{ $project->project_name }}"></option>
                                    @endforeach
                                </datalist>
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-6">
                                <label>Budget allocation for this project item:</label>
                                <input class="form-control" id="budget" name="budget" type="number" required>
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

    {{-- manage budget/fund modal --}}

    <div class="modal fade" id="budgetModal" data-bs-backdrop="static" data-bs-keyboard="false"
        aria-labelledby="budgetModalLabel" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Manage Budget Allocation: <span id="budgetModalLabel"></span></h5>
                    <button class="btn-close" data-bs-dismiss="modal" type="button" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row form-group">
                        <div class="col-sm">
                            <label>GAA Item Budget:</label>
                            <form id="gaa_budget_form">
                                <div class="input-group mb-3">
                                    <input id="fund_gaa_id" name="fund_gaa_id" type="hidden">
                                    <input class="form-control" id="gaa_budget" name="gaa_budget" type="number"
                                        readonly>
                                    <button class="btn btn-primary" id="editBudgetButton" type="button">
                                        <span class="fa fa-edit"></span> Edit
                                    </button>
                                    <button class="btn btn-success d-none" id="saveBudgetButton" type="button">
                                        <span class="fa fa-check"></span> Save
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="row form-group">
                        <div class="col-sm">
                            <label>Unallocated fund:</label>
                            <input class="form-control" id="unallocated_fund" name="unallocated_fund" type="number"
                                readonly>
                        </div>
                    </div>
                    <hr>
                    <div class="row form-group">
                        <div class="col-sm">
                            <label>Budget Allocation:</label>
                            <table class="table table-bordered" id="projectListTable">
                                <thead>
                                    <tr>
                                        <th>Project Name</th>
                                        <th>Allocation</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="projectListBody">
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
    @include('gaa.css') {{-- blade file --}}
@endsection

@section('js')
    @include('gaa.js') {{-- blade file --}}
@endsection
