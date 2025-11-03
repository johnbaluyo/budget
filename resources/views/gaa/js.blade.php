    <script>
        $(document).ready(function() {
            // BED modal - calculate total allocated on input change
            $(document).on('input', '.bed-month-input', function() {
                calculateBEDTotal();
            });

            $('#realignCheckbox').on('change', function() {
                toggleRealignSection($(this).is(':checked'));
            });

            $('#realign_category_id').on('change', function() {
                if ($('#realign_category_id').val() === $('#tracking_category_id').val()) {
                    Swal.fire('Invalid selection', 'Please choose a different category.', 'warning');
                    $('#realign_category_id').val('');
                };
            });

            const tooltip = $('<div class="custom-tooltip"></div>').appendTo('body');

            $(".hover-text").hover(
                function(event) {
                    const $this = $(this);
                    const remarks = $this.data("remarks");
                    const activityDate = $this.data("activity");
                    const type = $this.data("type");

                    let formattedDate = "Invalid date";
                    if (activityDate) {
                        const date = new Date(activityDate);
                        formattedDate = date.toLocaleDateString("en-US", {
                            month: "short",
                            day: "2-digit",
                            year: "numeric",
                        });
                    }

                    const offset = $this.offset();
                    tooltip
                        .css({
                            top: offset.top + $this.outerHeight() / 2 - tooltip.outerHeight() / 2,
                            left: offset.left + $this.outerWidth() + 10,
                        })
                        .fadeIn();

                    tooltip.html(
                        `<b>${formattedDate} (<span style="color: ${type === 'OUT' ? 'red' : type === 'IN' ? 'green' : 'black'};">${type}</span>)</b><br> - ${remarks || " -- "}`
                    );
                },
                function() {
                    tooltip.fadeOut();
                }
            );

            $(".hover-text").mousemove(function(event) {
                tooltip.css({
                    top: event.pageY + 10,
                    left: event.pageX + 10,
                });
            });

            $('#trackingModal').on('hidden.bs.modal', function() {
                location.reload();
            });

            $('#editBudgetButton').on('click', function() {
                // Enable the input field
                $('#gaa_budget').prop('readonly', false);

                // Show the Save button and hide the Edit button
                $('#editBudgetButton').addClass('d-none');
                $('#saveBudgetButton').removeClass('d-none');
            });

            // Handle Save button click
            $('#saveBudgetButton').on('click', function(e) {
                e.preventDefault(); // Prevent the default form submission

                const formData = new FormData($('#gaa_budget_form')[0]);

                $.ajax({
                    url: "{{ URL::to('/gaa/saveGAABudget') }}", // Endpoint for saving the budget
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.message === 'success') {
                            Swal.fire("Success", "Budget saved successfully.", "success");

                            // Disable the input field again
                            $('#gaa_budget').prop('readonly', true);

                            // Show the Edit button and hide the Save button
                            $('#editBudgetButton').removeClass('d-none');
                            $('#saveBudgetButton').addClass('d-none');

                            // Refresh unallocated fund
                            updateUnallocatedFund();
                        } else {
                            Swal.fire("Error", response.message, "error");
                        }
                    },
                    error: function(xhr) {
                        Swal.fire("Error", xhr.responseJSON.message || "An error occurred.",
                            "error");
                    }
                });
            });

            $('#budgetModal').on('hidden.bs.modal', function() {
                location.reload(); // Refresh the page
            });
        });

        function addProject() {
            $('#projectModal').modal('toggle');
        }
        // include CSRF token for POST
        formData.append('_token', '{{ csrf_token() }}');

        function toggleRealignSection(isChecked) {
            if (isChecked) {
                // Show realign section and button
                $('.realignSection').show();
                $('#realignButton').show();

                // populate datalist for typing suggestions
                $('#realign_gaa_list').empty();
                // add empty placeholder option
                $('#realign_gaa_list').append('<option value=""></option>');

                function addDatalistOption(category, level) {
                    if (category.item_of_expenditure) {
                        $('#realign_gaa_list').append('<option value="' + category.item_of_expenditure.replace(/"/g,
                            '&quot;') + '"></option>');
                    }
                    if (category.children && category.children.length) {
                        category.children.forEach(function(child) {
                            addDatalistOption(child, level + 1);
                        });
                    }
                }
                response.items.forEach(function(item) {
                    addDatalistOption(item, 0);
                });
                $('.realignSection').hide();
                $('#realignButton').hide();

                // Show IN and OUT buttons
                $('#inOutButtons').show();
                $('#realign_category_id').val('');
            }
        }

        function loadTracking(gaa_id) {
            var formData = new FormData();
            formData.append('gaa_id', gaa_id);
            formData.append('project_id', '{{ $project->id }}');
            $.ajax({
                url: "{{ URL::to('project/getExpenseId') }}",
                method: 'POST',
                data: formData,
                dataType: 'json',
                contentType: false,
                processData: false,
                cache: false,
                success: function(response) {
                    $('#gaa_project_id').val(response.id);
                    $('#expense_name').html(response.gaa.item_of_expenditure);
                    var tbody = $('#tracking_tbody');
                    tbody.empty();
                    if (!response.expenses || response.expenses.length === 0) {
                        tbody.append('<tr><td colspan="4" class="text-center">No records found</td></tr>');
                        return;
                    }

                    response.expenses.forEach(function(item) {
                        let bgColor = item.type === 'IN' ? 'table-success' : item.type === 'OUT' ?
                            'table-danger' : '';
                        var row = `
                        <tr class="${bgColor}">
                            <td>${item.type ?? '-'}</td>
                            <td>${item.amount ? parseFloat(item.amount).toLocaleString() : '-'}</td>
                            <td>${item.date ?? '-'}</td>
                            <td>
                                ${item.realignFrom ?? ''}
                                ${item.realignTo ?? ''}
                                ${item.remarks ?? ''}
                            </td>
                        </tr>
                    `;
                        tbody.append(row);
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error loading tracking data:', error);
                }
            });
        }

        function showTracking(gaa_id) {
            $('#trackingModal').modal('toggle');
            const today = new Date();
            const formattedDate = today.toISOString().split('T')[0];
            $('#activity_date').val(formattedDate);
            $('#remarks').val('');
            loadTracking(gaa_id);

        }

        function updateTracking(type) {
            const formData = new FormData();
            formData.append('gaa_project_id', $('#gaa_project_id').val());
            formData.append('type', type);
            formData.append('amount', $('#amount').val());
            formData.append('date', $('#activity_date').val());
            formData.append('remarks', $('#remarks').val());
            formData.append('realign_gaa_id', $('#realignCheckbox').is(':checked') ? $('#realign_gaa_id').val() : null);
            formData.append('realign_project_id', $('#realignCheckbox').is(':checked') ? $('#realign_project_id').val() :
                null);

            // Validation check
            if (!formData.get('amount') || formData.get('amount') <= 0) {
                Swal.fire(
                    "Please enter a valid amount.",
                    "Required field(s) missing",
                    "warning"
                )
                return;
            }

            if (!formData.get('date')) {
                Swal.fire(
                    "Activity date is required.",
                    "Required field(s) missing",
                    "warning"
                )
                return;
            }

            if ($('#realignCheckbox').is(':checked') && (!formData.get('realign_gaa_id') || !formData.get(
                    'realign_project_id'))) {
                Swal.fire(
                    "Please select both Project and Project Item for realignment.",
                    "Required field(s) missing",
                    "warning"
                )
                return;
            }

            // include CSRF token
            formData.append('_token', '{{ csrf_token() }}');

            function doAjax() {
                $.ajax({
                    url: "{{ URL::to('/project/updateTracking') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.message === 'success') {
                            loadTracking(response.gaa_id);
                            $('#amount').val('');
                            $('#remarks').val('');
                        } else {
                            Swal.fire(
                                response.message,
                                "DB Error",
                                "danger"
                            )
                        }
                    },
                    error: function(xhr) {
                        Swal.fire(
                            xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message :
                            'Ajax error',
                            "Ajax error",
                            "danger"
                        )
                    }
                });
            }

            // If realigning to a typed (non-numeric) gaa id, prompt for object_type and fund_cluster before creating
            var realignVal = $('#realignCheckbox').is(':checked' ? $('#realign_gaa_id').val() : null);
            // fallback: pull value directly
            realignVal = $('#realignCheckbox').is(':checked') ? $('#realign_gaa_id').val() : null;

            if ($('#realignCheckbox').is(':checked') && realignVal && isNaN(Number(realignVal))) {
                Swal.fire({
                    title: 'New Item — provide details',
                    html: '<label>Object Type</label>' +
                        '<select id="swal_object_type" class="form-control"><option>MOOE</option><option>PS</option><option>CO</option></select>' +
                        '<label class="mt-2">Fund Cluster</label>' +
                        '<input id="swal_fund_cluster" class="form-control" value="RAF-01">',
                    focusConfirm: false,
                    showCancelButton: true,
                    preConfirm: () => {
                        return {
                            object_type: $('#swal_object_type').val(),
                            fund_cluster: $('#swal_fund_cluster').val()
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        formData.append('realign_object_type', result.value.object_type || 'MOOE');
                        formData.append('realign_fund_cluster', result.value.fund_cluster || 'RAF-01');
                        doAjax();
                    }
                });
            } else {
                doAjax();
            }
        }

        function _delete(gaa_id, type) {
            if (type === 1) {
                var message = 'NOTE: This includes sub-item(s) and items assigned into project(s)';
            } else {
                var message = 'NOTE: This item will be removed from this project';
            }
            Swal.fire({
                title: 'Delete this record?',
                text: message,
                input: 'text',
                inputPlaceholder: 'Type "CONFIRM" to proceed',
                inputAttributes: {
                    autocapitalize: 'off'
                },
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Proceed',
                cancelButtonText: 'Close',
                showLoaderOnConfirm: true,
                preConfirm: (confirm) => {
                    if (confirm !== "CONFIRM") {
                        Swal.showValidationMessage(
                            'Type CONFIRM to proceed'
                        );
                    }
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.value) {
                    var formData = new FormData();
                    formData.append('gaa_id', gaa_id);
                    formData.append('type', type);
                    formData.append('project_id', '{{ $project->id }}');
                    $.ajax({
                        url: "{{ URL::to('gaa/delete') }}",
                        method: 'post',
                        data: formData,
                        dataType: 'json',
                        success: function(response) {
                            if (response === "success") {
                                let timerInterval;
                                Swal.fire({
                                    title: 'Request successfully deleted',
                                    html: 'Refreshing page...',
                                    timer: 1000,
                                    didOpen: () => {
                                        Swal.showLoading();
                                        timerInterval = setInterval(() => {
                                            const b = Swal.getHtmlContainer()
                                                .querySelector('b');
                                            if (b) {
                                                b.textContent = Swal.getTimerLeft();
                                            }
                                        }, 100);
                                    },
                                    willClose: () => {
                                        clearInterval(timerInterval);
                                        location.reload();
                                    }
                                });
                            } else {
                                Swal.fire(
                                    "Database Error",
                                    response,
                                    'error'
                                );
                            }
                        },
                        cache: false,
                        contentType: false,
                        processData: false
                    });
                }
            });
        }

        function printTable() {
            $('#budgetTable th:last-child, #budgetTable th:nth-last-child(2)').hide();
            $('#budgetTable td:last-child, #budgetTable td:nth-last-child(2)').hide();
            // Print the table
            var printWindow = window.open("", "_blank");
            printWindow.document.open();
            printWindow.document.write(`
                <html>
                <head>
                    <title>Print Table</title>
                    <style>
                        table {
                            width: 100%;
                            border-collapse: collapse;
                        }
                        th, td {
                            border: 1px solid #ddd;
                            padding: 8px;
                            text-align: left;
                        }
                        th {
                            background-color: #f2f2f2;
                        }
                            @media print {
                            #budgetTable th:last-child, 
                            #budgetTable td:last-child {
                                display: none;
                            }
                        }
                    </style>
                </head>
                <body>
                    <h1>Budget Tracking</h1>
                    ${$("#budgetTable")[0].outerHTML}
                </body>
                </html>
            `);

            $('#budgetTable td:last-child, #budgetTable td:nth-last-child(2)').show();
            $('#budgetTable th:last-child, #budgetTable th:nth-last-child(2)').show();
            printWindow.document.close();
            printWindow.print();
        }

        function addItemToProject(gaa_id, item_of_expenditure, available_fund) {
            $('#projectModal').modal('toggle');
            $('#item_to_project_gaa_id').val(gaa_id);
            $('#available_fund').val(available_fund);
            $('#budget').val('');
            $('#projectModalLabel').html(item_of_expenditure);
        }

        function addGaa() {
            $('#GAAitemModal').modal('toggle');
            $('#GAAitemLabel').html('GAA Item details:');
            $('#gaa_id').val('');
            $('#parent_id').val('');
            $('#item_of_expenditure').val('');
            $('#division_id').val('');
            $('#remarks').val('');
        }

        function editGaaItem(gaa_id) {
            var formData = new FormData();
            formData.append('gaa_id', gaa_id);
            $.ajax({
                url: "{{ URL::to('gaa/edit') }}",
                method: 'post',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    $('#GAAitemModal').modal('toggle');
                    $('#GAAitemLabel').html('GAA Item details:');
                    $('#gaa_id').val(response.id);
                    $('#parent_id').val(response.parent_id);
                    $('#item_of_expenditure').val(response.item_of_expenditure);
                    $('#division_id').val(response.division_id);
                    $('#object_type').val(response.object_type);
                    $('#remarks').val(response.remarks);
                },
                cache: false,
                contentType: false,
                processData: false
            })
        }

        function addSubItem(gaa_id, item_of_expenditure, object_type) {
            $('#GAAitemModal').modal('toggle');
            $('#GAAitemLabel').html('Sub item for: ' + item_of_expenditure);
            $('#gaa_id').val('');
            $('#parent_id').val(gaa_id);
            $('#item_of_expenditure').val('');
            $('#division_id').val('');
            $('#object_type').val(object_type);
            $('#remarks').val('');
        }

        function manageBudget(gaa_id, item_of_expenditure) {
            var formData = new FormData();
            formData.append('gaa_id', gaa_id);
            $.ajax({
                url: "{{ URL::to('gaa/getGaaprojects') }}",
                method: 'post',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    $('#budgetModal').modal('toggle');
                    $('#fund_gaa_id').val(gaa_id);
                    $('#gaa_budget').val(response.gaa_allocation);
                    $('#budgetModalLabel').html(item_of_expenditure);
                    $("#projectListBody").empty();

                    let totalAllocated = 0;

                    if (response.gaa_projects.length > 0) {
                        response.gaa_projects.forEach(function(item) {
                            totalAllocated += parseFloat(item.budget);

                            var row = `
                                <tr>
                                    <td>${item.project.project_name}</td>
                                    <td>
                                        <input type="number" class="form-control" id="budget_${item.project.id}" value="${item.budget}" style="display: inline-block;" readonly>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary btn-sm" id="editBtn_${item.project.id}" onclick="enableEdit(${item.project.id})">Edit</button>
                                        <button class="btn btn-danger btn-sm d-none" id="cancelBtn_${item.project.id}" onclick="cancelEdit(${item.project.id}, ${item.budget})">Cancel</button>
                                        <button class="btn btn-success btn-sm d-none" id="saveBtn_${item.project.id}" onclick="saveProjectAllocation(${item.project.id}, ${gaa_id})">Save</button>
                                    </td>
                                </tr>`;
                            $("#projectListBody").append(row);
                        });
                    } else {
                        $("#projectListBody").append(
                            '<tr><td colspan="3" class="text-center">No records found</td></tr>');
                    }

                    // Calculate unallocated fund
                    const gaaBudget = parseFloat(response.gaa_allocation) || 0;
                    const unallocatedFund = gaaBudget - totalAllocated;

                    // Update the unallocated_fund input field
                    $('#unallocated_fund').val(unallocatedFund.toFixed(2));
                },
                cache: false,
                contentType: false,
                processData: false
            });
        }

        function cancelEdit(projectId, originalBudget) {
            $(`#budget_${projectId}`).val(originalBudget);
            $(`#budget_${projectId}`).prop('readonly', true);
            $(`#editBtn_${projectId}`).removeClass('d-none');
            $(`#saveBtn_${projectId}`).addClass('d-none');
            $(`#cancelBtn_${projectId}`).addClass('d-none');
        }

        function enableEdit(projectId) {
            $(`#budget_${projectId}`).prop('readonly', false);
            $(`#editBtn_${projectId}`).addClass('d-none');
            $(`#saveBtn_${projectId}`).removeClass('d-none');
            $(`#cancelBtn_${projectId}`).removeClass('d-none');
        }

        function saveProjectAllocation(projectId, gaaId) {
            const budget = parseFloat($(`#budget_${projectId}`).val());
            if (!budget || budget <= 0) {
                Swal.fire("Invalid Budget", "Please enter a valid budget amount.", "warning");
                return;
            }

            let totalAllocated = 0;
            $('#projectListBody input[type="number"]').each(function() {
                totalAllocated += parseFloat($(this).val()) || 0;
            });

            const gaaBudget = parseFloat($('#gaa_budget').val()) || 0;

            if (totalAllocated > gaaBudget) {
                Swal.fire("Allocation Exceeded", "The total allocation cannot exceed the GAA budget.", "error");
                return;
            }

            const formData = new FormData();
            formData.append('gaa_project_id', gaaId);
            formData.append('project_id', projectId);
            formData.append('budget', budget);

            $.ajax({
                url: "{{ URL::to('gaa/saveProjectAllocation') }}",
                method: 'post',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.message === 'success') {
                        Swal.fire("Success", "Budget allocation updated successfully.", "success");
                        $(`#budget_${projectId}`).prop('readonly', true);
                        $(`#editBtn_${projectId}`).removeClass('d-none');
                        $(`#saveBtn_${projectId}`).addClass('d-none');
                        updateUnallocatedFund();
                        cancelEdit(projectId, budget);
                    } else {
                        Swal.fire("Error", response.message, "error");
                    }
                },
                error: function(xhr) {
                    Swal.fire("Error", xhr.responseJSON.message, "error");
                }
            });
        }

        function updateUnallocatedFund() {
            let totalAllocated = 0;
            $('#projectListBody input[type="number"]').each(function() {
                totalAllocated += parseFloat($(this).val()) || 0;
            });

            const gaaBudget = parseFloat($('#gaa_budget').val()) || 0;
            const unallocatedFund = gaaBudget - totalAllocated;

            $('#unallocated_fund').val(unallocatedFund.toFixed(2));

        }

        function loadGaaFromProject() {
            var formData = new FormData();
            formData.append('project_id', $('#realign_project_id').val());
            $.ajax({
                url: "{{ URL::to('project/getGaaFromProject') }}",
                method: 'post',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.message === 'success') {
                        $('#realign_gaa_id').empty();
                        $('#realign_gaa_id').append('<option value="">- Select Item -</option>');
                        response.items.forEach(function(item) {
                            appendDropdownOption(item, 0);
                        });
                    } else {
                        Swal.fire(
                            response.message,
                            "System Message",
                            "danger"
                        )
                    }
                },
                cache: false,
                contentType: false,
                processData: false
            });
        }

        function appendDropdownOption(category, level) {
            // Deprecated for datalist approach — kept for backward compatibility if needed
            var option = `<option value="${category.gaa_id}">${category.item_of_expenditure}</option>`;
            // append to datalist if it exists
            if ($('#realign_gaa_list').length) {
                $('#realign_gaa_list').append(
                    `<option value="${category.item_of_expenditure.replace(/"/g, '&quot;')}"></option>`);
            } else if ($('#realign_gaa_id').is('select')) {
                $('#realign_gaa_id').append(option);
            }
            if (category.children && category.children.length > 0) {
                category.children.forEach(function(child) {
                    appendDropdownOption(child, level + 1);
                });
            }
        }

        function moveToOtherProject(gaa_id, project_id) {
            var formData = new FormData();
            formData.append('gaa_id', gaa_id);
            formData.append('project_id', project_id);
            $.ajax({
                url: "{{ URL::to('project/moveToOtherProject') }}",
                method: 'post',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.message === 'success') {
                        Swal.fire(
                            "Success",
                            "Item moved to another project.",
                            "success"
                        )
                        location.reload();
                    } else {
                        Swal.fire(
                            response.message,
                            "System Message",
                            "danger"
                        )
                    }
                },
                cache: false,
                contentType: false,
                processData: false
            });
        }

        // Consolidated BED Function (for GAA index view)
        function showConsolidatedBED(gaa_id, item_name) {
            var formData = new FormData();
            formData.append('gaa_id', gaa_id);
            formData.append('year', '{{ $selectedYear }}');
            formData.append('_token', '{{ csrf_token() }}');
            
            $.ajax({
                url: "{{ URL::to('gaa/getGAAConsolidatedBED') }}",
                method: 'POST',
                data: formData,
                dataType: 'json',
                contentType: false,
                processData: false,
                cache: false,
                success: function(response) {
                    $('#consolidatedBEDModal').modal('show');
                    $('#consolidated_bed_item_name').html(item_name);
                    $('#consolidated_bed_gaa_id').val(gaa_id);
                    
                    const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                                    'July', 'August', 'September', 'October', 'November', 'December'];
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

        // BED (Budget Execution Distribution) Functions
        function showBED(gaa_id) {
            var formData = new FormData();
            formData.append('gaa_id', gaa_id);
            formData.append('project_id', '{{ $project->id }}');
            formData.append('_token', '{{ csrf_token() }}');
            
            // First get the gaa_project_id
            $.ajax({
                url: "{{ URL::to('project/getExpenseId') }}",
                method: 'POST',
                data: formData,
                dataType: 'json',
                contentType: false,
                processData: false,
                cache: false,
                success: function(response) {
                    loadBED(response.id, response.gaa.item_of_expenditure);
                },
                error: function(xhr, status, error) {
                    console.error('Error getting GAA project:', error);
                    Swal.fire('Error', 'Failed to load BED data', 'error');
                }
            });
        }

        function loadBED(gaa_project_id, item_name) {
            var formData = new FormData();
            formData.append('gaa_project_id', gaa_project_id);
            formData.append('year', '{{ $selectedYear }}');
            formData.append('_token', '{{ csrf_token() }}');
            
            $.ajax({
                url: "{{ URL::to('project/getGAAProjectMonthlyBudget') }}",
                method: 'POST',
                data: formData,
                dataType: 'json',
                contentType: false,
                processData: false,
                cache: false,
                success: function(response) {
                    $('#bedModal').modal('show');
                    $('#bed_gaa_project_id').val(gaa_project_id);
                    $('#bed_expense_name').html(response.item_of_expenditure);
                    $('#bed_total_budget').val(parseFloat(response.total_budget).toFixed(2));
                    
                    // Fill in monthly budgets
                    response.monthly_budgets.forEach(function(item) {
                        $(`.bed-month-input[data-month="${item.month}"]`).val(parseFloat(item.budget_amount).toFixed(2));
                    });
                    
                    calculateBEDTotal();
                },
                error: function(xhr, status, error) {
                    console.error('Error loading BED data:', error);
                    Swal.fire('Error', 'Failed to load monthly budget data', 'error');
                }
            });
        }

        function calculateBEDTotal() {
            let total = 0;
            $('.bed-month-input').each(function() {
                const value = parseFloat($(this).val()) || 0;
                total += value;
            });
            
            $('#bed_total_allocated').text(total.toFixed(2));
            
            const totalBudget = parseFloat($('#bed_total_budget').val()) || 0;
            if (total > totalBudget) {
                $('#bed_over_budget_warning').show();
            } else {
                $('#bed_over_budget_warning').hide();
            }
        }

        function saveBED() {
            const gaaProjectId = $('#bed_gaa_project_id').val();
            const totalBudget = parseFloat($('#bed_total_budget').val()) || 0;
            
            // Calculate total allocated directly from input fields
            let totalAllocated = 0;
            $('.bed-month-input').each(function() {
                totalAllocated += parseFloat($(this).val()) || 0;
            });
            
            if (totalAllocated > totalBudget) {
                Swal.fire('Error', 'Total monthly allocation exceeds item budget', 'error');
                return;
            }
            
            // Collect monthly budgets
            const monthlyBudgets = [];
            $('.bed-month-input').each(function() {
                const month = $(this).data('month');
                const amount = parseFloat($(this).val()) || 0;
                monthlyBudgets.push({
                    month: month,
                    budget_amount: amount
                });
            });
            
            const formData = new FormData();
            formData.append('gaa_project_id', gaaProjectId);
            formData.append('year', '{{ $selectedYear }}');
            formData.append('monthly_budgets', JSON.stringify(monthlyBudgets));
            formData.append('_token', '{{ csrf_token() }}');
            
            $.ajax({
                url: "{{ URL::to('project/saveGAAProjectMonthlyBudget') }}",
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success', response.message, 'success');
                        $('#bedModal').modal('hide');
                    } else {
                        Swal.fire('Error', response.error || 'Failed to save', 'error');
                    }
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Failed to save monthly budget';
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        }
    </script>
