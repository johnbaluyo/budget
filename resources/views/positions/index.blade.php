@extends('partials._layout')

@section('header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Positions</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ URL::to('/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Positions</li>
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
                            <button class="btn btn-success" onclick="add()">Add Position</button>
                        </div>
                    </div>
                    <table class="table table-bordered table-striped dataTable">
                        <thead>
                            <tr>
                                <th>Position Name</th>
                                <th>Salary Grade</th>
                                <th>Step Increment</th>
                                <th>Plantilla Code</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($my_data as $data)
                                <tr>
                                    <td>{{ $data->position_name }}</td>
                                    <td>{{ $data->salary_grade }}</td>
                                    <td>{{ $data->step_increment }}</td>
                                    <td>{{ $data->plantilla_code }}</td>
                                    <td>
                                        <button class="btn btn-primary btn-sm me-1"
                                            onclick="edit(`{{ $data->id }}`, `{{ $data->position_name }}`, `{{ $data->salary_grade }}`, `{{ $data->step_increment }}`, `{{ $data->plantilla_code }}`);">
                                            <span class="fa fa-edit"></span>
                                        </button>
                                        <form method="POST" action="{{ URL::to('/positions/delete') }}"
                                            class="d-inline-block">
                                            @csrf
                                            <input type="hidden" name="position_id" value="{{ $data->id }}">
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Are you sure you want to delete this Position?');">
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
    <div class="modal fade" id="addPositionsModal" tabindex="-1" aria-labelledby="addPositionsModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPositionsModalLabel">Add Position</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="addForm" action="{{ URL::to('/positions/store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Position Name</label>
                            <input type="text" class="form-control" name="position_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Salary Grade</label>
                            <input type="text" class="form-control" name="salary_grade">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Step Increment</label>
                            <input type="text" class="form-control" name="step_increment">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Plantilla Code </label>
                            <input type="text" class="form-control" name="plantilla_code">
                        </div>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- edit modal -->
    <div class="modal fade" id="editPositionsModal" tabindex="-1" aria-labelledby="editPositionsModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPositionsModalLabel">Edit Position</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ URL::to('/positions/update') }}">
                        @csrf
                        <input type="hidden" id="position_id" name="position_id">
                        <div class="mb-3">
                            <label class="form-label">Position Name</label>
                            <input type="text" class="form-control" id="position_name" name="position_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Salary Grade</label>
                            <input type="text" class="form-control" id="salary_grade" name="salary_grade">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Step Increment</label>
                            <input type="text" class="form-control" id="step_increment" name="step_increment">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Plantilla Code </label>
                            <input type="text" class="form-control" id="plantilla_code" name="plantilla_code">
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
        function add() {
            $('#addPositionsModal').modal('toggle');
            $('#addForm')[0].reset();
        }

        function edit(id, position_name, salary_grade, step_increment, plantilla_code) {
            $('#position_id').val(id);
            $('#position_name').val(position_name);
            $('#salary_grade').val(salary_grade);
            $('#step_increment').val(step_increment);
            $('#plantilla_code').val(plantilla_code);

            $('#editPositionsModal').modal('show');
        }
    </script>
@endsection
