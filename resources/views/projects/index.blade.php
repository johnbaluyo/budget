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
                                        <th>Actions</th>
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
                                            <td>
                                                <button class="btn btn-sm btn-primary"
                                                    onclick="openBEDModal({{ $data->id }}, '{{ $data->project_name }}')"
                                                    title="Budget Execution Distribution">
                                                    <i class="fas fa-calendar-alt"></i> BED
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="text-center" colspan="6">No projects found for this year.</td>
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

    {{-- Budget Execution Distribution Modal - Consolidated View --}}
    <div class="modal fade" id="bedModal" data-bs-backdrop="static" data-bs-keyboard="false"
        aria-labelledby="bedModalLabel" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bedModalLabel">Consolidated Budget Execution Distribution (BED)</h5>
                    <button class="btn-close" data-bs-dismiss="modal" type="button" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-sm-12">
                            <strong>Project:</strong> <span id="bed_project_name"></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> This shows the consolidated monthly budget allocation for this project across all GAA items.
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="bed_project_id">
                    <input type="hidden" id="bed_year" value="{{ $selectedYear }}">
                    <div class="row">
                        <div class="col-sm-12">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Month</th>
                                        <th>Total Allocation</th>
                                    </tr>
                                </thead>
                                <tbody id="consolidated_bed_tbody">
                                </tbody>
                                <tfoot>
                                    <tr class="table-secondary">
                                        <th>Grand Total:</th>
                                        <th>₱<span id="consolidated_bed_grand_total">0.00</span></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        function openBEDModal(projectId, projectName) {
            $('#bed_project_id').val(projectId);
            $('#bed_project_name').text(projectName);

            // Load consolidated monthly budgets from all GAA project items
            var formData = new FormData();
            formData.append('project_id', projectId);
            formData.append('year', $('#bed_year').val());
            formData.append('_token', '{{ csrf_token() }}');

            $.ajax({
                url: "{{ URL::to('project/getProjectConsolidatedBED') }}",
                method: 'POST',
                data: formData,
                dataType: 'json',
                contentType: false,
                processData: false,
                cache: false,
                success: function(response) {
                    $('#bedModal').modal('show');

                    const months = ['January', 'February', 'March', 'April', 'May', 'June',
                        'July', 'August', 'September', 'October', 'November', 'December'
                    ];
                    let grandTotal = 0;
                    let tbody = $('#consolidated_bed_tbody');
                    tbody.empty();

                    response.monthly_budgets.forEach(function(item, index) {
                        const amount = parseFloat(item.budget_amount);
                        grandTotal += amount;
                        const row = `
                            <tr>
                                <td>${months[index]}</td>
                                <td>₱${amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            </tr>
                        `;
                        tbody.append(row);
                    });

                    $('#consolidated_bed_grand_total').text(grandTotal.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));
                },
                error: function(xhr, status, error) {
                    console.error('Error loading consolidated BED data:', error);
                    Swal.fire('Error', 'Failed to load consolidated BED data', 'error');
                }
            });
        }
    </script>
@endsection
