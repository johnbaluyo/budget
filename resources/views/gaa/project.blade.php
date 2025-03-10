@extends('partials._layout')

@section('header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">PROJECT NAME HERE</h1>
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
            <input id="project_id" name="project_id" type="hidden" value="{{ $projectId }}">
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
