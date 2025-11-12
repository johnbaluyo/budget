{{-- filepath: c:\websites\budget\resources\views\projects\index.blade.php --}}
@extends('partials._layout')

@section('header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="display-6">PROJECTS</h1>
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
    @php
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    @endphp

    @if ($projects->count())
        <div class="accordion" id="projectsAccordion">
            @foreach ($projects as $project)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading{{ $project->id }}">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapse{{ $project->id }}" aria-expanded="false"
                            aria-controls="collapse{{ $project->id }}">
                            <h5>{{ $project->project_name }}</h5>
                        </button>
                    </h2>
                    <div id="collapse{{ $project->id }}" class="accordion-collapse collapse"
                        aria-labelledby="heading{{ $project->id }}">
                        <div class="accordion-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th class="align-middle"></th>
                                            @foreach ($months as $month)
                                                <th class="text-center">{{ $month }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>Allocated Budget</strong></td>
                                            @for ($month = 1; $month <= 12; $month++)
                                                <td class="text-right">
                                                    {{ number_format($project->monthly_budgets[$month] ?? 0, 2) }}</td>
                                            @endfor
                                        </tr>
                                        <tr>
                                            <td><strong>Actual Expenses</strong></td>
                                            @for ($month = 1; $month <= 12; $month++)
                                                <td class="text-right">
                                                    {{ number_format($project->monthly_expenses[$month] ?? 0, 2) }}</td>
                                            @endfor
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <p class="text-center mb-0">No projects found for this year.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
