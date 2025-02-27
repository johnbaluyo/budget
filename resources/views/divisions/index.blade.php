@extends('partials._layout')

@section('header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Divisions</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ URL::to('/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Divisions</li>
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
                            <button class="btn btn-success" onclick="add()">Add Division</button>
                        </div>
                    </div>
                    <table class="table table-bordered table-striped dataTable">
                        <thead>
                            <tr>
                                <th>Division Description</th>
                                <th>Division</th>
                                <th>Location</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($my_data as $data)
                                <tr>
                                    <td>{{ $data->division_name }}</td>
                                    <td>{{ $data->division_acronym }}</td>
                                    <td>{{ $data->location }}</td>
                                    <td>
                                        <button class="btn btn-primary btn-sm me-1"
                                            onclick="edit(`{{ $data->id }}`, `{{ $data->division_name }}`, `{{ $data->division_acronym }}`, `{{ $data->location }}`);">
                                            <span class="fa fa-edit"></span>
                                        </button>
                                        <form method="POST" action="{{ URL::to('/divisions/delete') }}"
                                            class="d-inline-block">
                                            @csrf
                                            <input type="hidden" name="division_id" value="{{ $data->id }}">
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Are you sure you want to delete this Division?');">
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
    <div class="modal fade" id="addDivisionsModal" tabindex="-1" aria-labelledby="addDivisionsModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addDivisionsModalLabel">Add Division</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="addForm" action="{{ URL::to('/divisions/store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Division Description
                                <span class="text-red">*</span>
                            </label>
                            <input type="text" class="form-control" name="division_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Division
                                <span class="text-red">*</span>
                            </label>
                            <input type="text" class="form-control" name="division_acronym" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location
                                <span class="text-red">*</span>
                            </label>
                            <input type="text" class="form-control" name="location" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- edit modal -->
    <div class="modal fade" id="editDivisionsModal" tabindex="-1" aria-labelledby="editDivisionsModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editDivisionsModalLabel">Edit Division</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ URL::to('/divisions/update') }}">
                        @csrf
                        <input type="hidden" id="division_id" name="division_id">
                        <div class="mb-3">
                            <label class="form-label">Division Description
                                <span class="text-red">*</span>
                            </label>
                            <input type="text" class="form-control" id="division_name" name="division_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Division
                                <span class="text-red">*</span>
                            </label>
                            <input type="text" class="form-control" id="division_acronym" name="division_acronym"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location
                                <span class="text-red">*</span>
                            </label>
                            <input type="text" class="form-control" id="location" name="location" required>
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
            $('#addDivisionsModal').modal('toggle');
            $('#addForm')[0].reset();
        }

        function edit(id, division_name, division_acronym, location) {
            $('#division_id').val(id);
            $('#division_name').val(division_name);
            $('#division_acronym').val(division_acronym);
            $('#location').val(location);

            $('#editDivisionsModal').modal('show');
        }
    </script>
@endsection
