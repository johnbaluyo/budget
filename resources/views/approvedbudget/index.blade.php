@extends('partials._layout')

@section('header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Approved Budget</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ URL::to('/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Approved Budget</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
@endsection

@section('content')
    <div class="row form-group">
        <div class="col-md">
            <div class="card">
                <!-- <div class="card-header">Dashboard</div> -->
                <div class="card-body">
                    <div class="row form-group">
                        <div class="col-sm">
                            <button class="btn btn-success" onclick="add()">Add Approved Budget</button>
                        </div>
                    </div>
                    <table id="example" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Year</th>
                                <th>Grand Total</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($my_data as $data)
                                <tr>
                                    <td>{{ $data->year }}</td>
                                    <td>{{ number_format($data->grand_total_amount, 2) }}</td>
                                    <td>
                                        <button class="btn btn-primary btn-sm me-1"
                                            onclick="edit(`{{ $data->id }}`, `{{ $data->year }}`, `{{ $data->grand_total_amount }}`);">
                                            <span class="fa fa-edit"></span>
                                        </button>
                                        <form method="POST" action="{{ URL::to('/approvedbudget/delete') }}"
                                            class="d-inline-block">
                                            @csrf
                                            <input type="hidden" name="approvedbudget_id" value="{{ $data->id }}">
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Are you sure you want to delete this record?');">
                                                <span class="fa fa-trash"></span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- add modal -->
    <div class="modal fade" id="addApprovedBudgetModal" tabindex="-1" aria-labelledby="addApprovedBudgetModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addApprovedBudgetModal">Add Division</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="addForm" action="{{ URL::to('/approvedbudget/store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="year" class="form-label">Approved Budget for year:
                                <span class="text-red">*</span>
                            </label>
                            <select class="form-select" id="year" name="year" required>
                                @for ($y = 2024; $y <= date('Y') + 4; $y++)
                                    <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Grand Total
                                <span class="text-red">*</span>
                            </label>
                            <input type="number" class="form-control" name="grand_total_amount" required min="1"
                                step="0.01">
                        </div>

                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- edit modal -->
    <div class="modal fade" id="editApprovedBudgetModal" tabindex="-1" aria-labelledby="editApprovedBudgetModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editApprovedBudgetModalLabel">Edit Division</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ URL::to('/approvedbudget/update') }}">
                        @csrf
                        <input type="hidden" id="approved_budget_id" name="approved_budget_id">
                        <input type="hidden" id="approved_budget_id" name="approved_budget_id">
                        <div class="mb-3">
                            <label for="year" class="form-label">Approved Budget for year:
                                <span class="text-red">*</span>
                            </label>
                            <select class="form-select" id="year2" name="year" required>
                                @foreach (range(date('Y'), date('Y') + 3) as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Grand Total Amount
                                <span class="text-red">*</span>
                            </label>
                            <input type="text" class="form-control" id="grand_total_amount" name="grand_total_amount"
                                required>
                        </div>

                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <style></style>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            $('#example').DataTable({
                lengthMenu: [5, 10, 25, 50], // Options for entries dropdown
                pageLength: 10 // Default entries displayed
            });
        });

        function add() {
            $('#addApprovedBudgetModal').modal('toggle');
            $('#addForm')[0].reset();

        }

        function edit(id, year, grand_total_amount) {
            $('#approved_budget_id').val(id);
            $('#year2').val(year);
            $('#grand_total_amount').val(grand_total_amount);

            $('#editApprovedBudgetModal').modal('show');
        }
    </script>
@endsection
