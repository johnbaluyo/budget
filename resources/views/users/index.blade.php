@extends('partials._layout')

@section('header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Users</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ URL::to('/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Users v1</li>
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
                            <button class="btn btn-success" onclick="add()">Add User</button>
                        </div>
                    </div>
                    <table class="table table-bordered table-striped dataTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Division</th>
                                <th>Position</th>
                                <th>User Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($my_data as $data)
                                <tr>
                                    <td>{{ $data->name }}</td>
                                    <td>{{ $data->email }}</td>
                                    <td>{{ $data->division->division_acronym ?? null }}</td>
                                    <td>{{ $data->position->position_name ?? null }}</td>
                                    @php
                                        $userTypes = [
                                            1 => 'Staff',
                                            2 => 'Division Chief',
                                            3 => 'Executive Director',
                                            4 => 'Admin',
                                        ];
                                    @endphp

                                    <td>{{ $userTypes[$data->user_type] ?? 'Unknown' }}</td>

                                    <td>
                                        <button class="btn btn-primary btn-sm me-1"
                                            onclick="edit(`{{ $data->id }}`, `{{ $data->name }}`, `{{ $data->email }}`, `{{ $data->position_id }}`, `{{ $data->division_id }}`, `{{ $data->user_type }}`)">
                                            <span class="fa fa-edit"></span>
                                        </button>
                                        <form method="POST" action="{{ URL::to('/users/delete') }}"
                                            class="d-inline-block">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $data->id }}">
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Are you sure you want to delete this User?');">
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
    <div class="modal fade" id="addUsersModal" tabindex="-1" aria-labelledby="addUsersModalLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUsersModalLabel">Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="addForm" action="{{ URL::to('/users/store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Name
                                <span class="text-red">*</span>
                            </label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email
                                <span class="text-red">*</span>

                            </label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password
                                <span class="text-red">*</span>
                            </label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Division
                                <span class="text-red">*</span>
                            </label>
                            <select type="text" class="form-control" name="division" required>
                                <option value="">--Select Division--</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}"> {{ $division->division_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Position
                                <span class="text-red">*</span>
                            </label>
                            <select type="text" class="form-control" name="position" required>
                                <option value="">--Select Position--</option>
                                @foreach ($positions as $position)
                                    <option value="{{ $position->id }}"> {{ $position->position_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">User Type
                            </label>
                            <select type="text" class="form-control" name="user_type">
                                <option value="1"> Staff</option>
                                <option value="2"> Division Chief </option>
                                <option value="3"> Executive Director</option>
                                <option value="4"> Admin </option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- edit modal -->
    <div class="modal fade" id="editUsersModal" tabindex="-1" aria-labelledby="editUsersModalLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUsersModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ URL::to('/users/update') }}">
                        @csrf
                        <input type="hidden" id="user_id" name="user_id">
                        <div class="mb-3">
                            <label class="form-label">Name
                                <span class="text-red">*</span>

                            </label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email
                                <span class="text-red">*</span>

                            </label>
                            <input type="text" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="optional">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Division
                                <span class="text-red">*</span>
                            </label>
                            <select type="text" class="form-control" id="division" name="division" required>
                                <option value="">--Select Division--</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}"> {{ $division->division_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Position
                                <span class="text-red">*</span>
                            </label>
                            <select type="text" class="form-control" id="position" name="position" required>
                                <option value="">--Select Position--</option>
                                @foreach ($positions as $position)
                                    <option value="{{ $position->id }}"> {{ $position->position_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">User Type </label>
                            <select type="text" class="form-control" id="user_type" name="user_type">
                                <option value="">--Select User Type--</option>
                                <option value="1"> Staff</option>
                                <option value="2"> Division Chief </option>
                                <option value="3"> Executive Director</option>
                                <option value="4"> Admin </option>
                            </select>
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
            $('#addUsersModal').modal('toggle');
            $('#addForm')[0].reset();
        }

        function edit(id, name, email, position, division, user_type) {
            $('#user_id').val(id);
            $('#name').val(name);
            $('#email').val(email);
            $('#position').val(position);
            $('#division').val(division);
            $('#user_type').val(user_type);

            $('#editUsersModal').modal('show');
        }
    </script>
@endsection
