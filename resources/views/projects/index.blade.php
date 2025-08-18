{{-- filepath: c:\websites\budget\resources\views\projects\index.blade.php --}}
@extends('partials._layout')

@section('header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">PROJECTS</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ URL::to('/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Projects</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row form-group">
        <div class="col-md">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Project Name</th>
                                        <th>Division</th>
                                        <th>Total Budget</th>
                                        <th>Total Expenses</th>
                                        <th>Total Remaining</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($projects as $data)
                                        <tr>
                                            <td>{{ $data->project_name }}</td>
                                            <td>{{ $data->division->division_acronym ?? '' }}</td>
                                            <td>{{ number_format($data->total_budget, 2) }}</td>
                                            <td>{{ number_format($data->total_expenses, 2) }}</td>
                                            <td>{{ number_format($data->total_remaining, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="text-center" colspan="3">No projects found for this year.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
