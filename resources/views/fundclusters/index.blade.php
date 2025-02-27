@extends('partials._layout')

@section('header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Fund Clusters</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ URL::to('/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Fund Clusters</li>
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
                            <button class="btn btn-success" onclick="add()">Add Fund Cluster</button>
                        </div>
                    </div>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($my_data as $data)
                                <tr>
                                    <td>{{ $data->code }}</td>
                                    <td>{{ $data->name }}</td>
                                    <td>
                                        <button class="btn btn-primary btn-sm me-1"
                                            onclick="edit(`{{ $data->id }}`, `{{ $data->code }}`, `{{ $data->name }}`)">
                                            <span class="fa fa-edit"></span>
                                        </button>
                                        <form method="POST" action="{{ URL::to('/fundclusters/delete') }}"
                                            class="d-inline-block">
                                            @csrf
                                            <input type="hidden" name="fundcluster_id" value="{{ $data->id }}">
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Are you sure you want to delete this Fund Cluster?');">
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
    <div class="modal fade" id="addFundClustersModal" tabindex="-1" aria-labelledby="addFundClustersModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addFundClustersModalLabel">Add Users</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ URL::to('/fundclusters/store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Code</label>
                            <input type="text" class="form-control" name="code" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Name </label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- edit modal -->
    <div class="modal fade" id="editFundClustersModal" tabindex="-1" aria-labelledby="editFundClustersModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editFundClustersModal">Edit Fund Cluster</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ URL::to('/fundclusters/update') }}">
                        @csrf
                        <input type="hidden" id="fundcluster_id" name="fundcluster_id">
                        <div class="mb-3">
                            <label class="form-label">Code </label>
                            <input type="text" class="form-control" id="code" name="code">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
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
            $('#addFundClustersModal').modal('toggle');
        }

        function edit(id, code, name) {
            $('#fundcluster_id').val(id);
            $('#code').val(code);
            $('#name').val(name);

            $('#editFundClustersModal').modal('show');
        }
    </script>
@endsection
