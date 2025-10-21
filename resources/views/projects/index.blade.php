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
                                                <button class="btn btn-sm btn-primary" onclick="openBEDModal({{ $data->id }}, '{{ $data->project_name }}', {{ $data->total_budget }})" title="Budget Execution Distribution">
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

    {{-- Budget Execution Distribution Modal --}}
    <div class="modal fade" id="bedModal" data-bs-backdrop="static" data-bs-keyboard="false"
        aria-labelledby="bedModalLabel" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bedModalLabel">Budget Execution Distribution</h5>
                    <button class="btn-close" data-bs-dismiss="modal" type="button" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>Project:</strong> <span id="bed_project_name"></span>
                        </div>
                        <div class="col-sm-6">
                            <strong>Total Budget:</strong> ₱<span id="bed_total_budget"></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Allocate the project budget across the 12 months. The total allocation cannot exceed the project's total budget.
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
                                        <th>Budget Allocation</th>
                                    </tr>
                                </thead>
                                <tbody id="monthly_budget_table">
                                    @php
                                        $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                                    @endphp
                                    @foreach($months as $index => $month)
                                        <tr>
                                            <td>{{ $month }}</td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm monthly-budget-input" 
                                                       id="budget_month_{{ $index + 1 }}" 
                                                       data-month="{{ $index + 1 }}"
                                                       min="0" step="0.01" value="0">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-secondary">
                                        <th>Total Allocated:</th>
                                        <th>₱<span id="total_allocated">0.00</span></th>
                                    </tr>
                                    <tr id="remaining_row">
                                        <th>Remaining:</th>
                                        <th>₱<span id="remaining_budget">0.00</span></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
                    <button class="btn btn-success" onclick="saveMonthlyBudget()" type="button">
                        <i class="fas fa-save"></i> Save Allocation
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
    let currentProjectBudget = 0;

    function openBEDModal(projectId, projectName, totalBudget) {
        currentProjectBudget = totalBudget;
        
        $('#bed_project_id').val(projectId);
        $('#bed_project_name').text(projectName);
        $('#bed_total_budget').text(parseFloat(totalBudget).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        
        // Load existing monthly budgets
        $.ajax({
            url: '{{ URL::to('/projects/getMonthlyBudget') }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                project_id: projectId,
                year: $('#bed_year').val()
            },
            success: function(response) {
                // Populate the monthly budget inputs
                response.monthly_budgets.forEach(function(item) {
                    $('#budget_month_' + item.month).val(parseFloat(item.budget_amount).toFixed(2));
                });
                
                // Calculate totals
                calculateTotals();
                
                // Show the modal
                $('#bedModal').modal('show');
            },
            error: function(xhr) {
                alert('Error loading monthly budget data: ' + (xhr.responseJSON?.error || 'Unknown error'));
            }
        });
    }

    function calculateTotals() {
        let total = 0;
        $('.monthly-budget-input').each(function() {
            let value = parseFloat($(this).val()) || 0;
            total += value;
        });
        
        $('#total_allocated').text(total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        
        let remaining = currentProjectBudget - total;
        $('#remaining_budget').text(remaining.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        
        // Change color based on whether we're over budget
        if (remaining < 0) {
            $('#remaining_row').addClass('table-danger').removeClass('table-success');
        } else {
            $('#remaining_row').addClass('table-success').removeClass('table-danger');
        }
    }

    function saveMonthlyBudget() {
        let projectId = $('#bed_project_id').val();
        let year = $('#bed_year').val();
        let monthlyBudgets = [];
        
        $('.monthly-budget-input').each(function() {
            let month = $(this).data('month');
            let amount = parseFloat($(this).val()) || 0;
            monthlyBudgets.push({
                month: month,
                budget_amount: amount
            });
        });
        
        // Validate total doesn't exceed budget
        let total = monthlyBudgets.reduce((sum, item) => sum + item.budget_amount, 0);
        if (total > currentProjectBudget) {
            alert('Total allocation (' + total.toFixed(2) + ') exceeds project budget (' + currentProjectBudget.toFixed(2) + ')');
            return;
        }
        
        $.ajax({
            url: '{{ URL::to('/projects/saveMonthlyBudget') }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                project_id: projectId,
                year: year,
                monthly_budgets: monthlyBudgets
            },
            success: function(response) {
                alert(response.message);
                $('#bedModal').modal('hide');
            },
            error: function(xhr) {
                alert('Error saving monthly budget: ' + (xhr.responseJSON?.error || 'Unknown error'));
            }
        });
    }

    // Add event listener to recalculate totals when inputs change
    $(document).ready(function() {
        $(document).on('input', '.monthly-budget-input', function() {
            calculateTotals();
        });
    });
</script>
@endsection
