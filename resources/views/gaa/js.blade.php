    <script>
        $(document).ready(function() {
            $('#realignCheckbox').on('change', function() {
                toggleRealignSection($(this).is(':checked'));
            });

            $('#realign_category_id').on('change', function() {
                if ($('#realign_category_id').val() === $('#tracking_category_id').val()) {
                    alert('invalid selection');
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
        });


        function toggleRealignSection(isChecked) {
            if (isChecked) {
                // Show realign section and button
                $('.realignSection').show();
                $('#realignButton').show();

                // Hide IN and OUT buttons
                $('#inOutButtons').hide();
            } else {
                // Hide realign section and button
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
            formData.append('project_id', $('#project_id').val());
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
                        let bgColor = item.type === 'IN' ? 'table-success' : item.type === 'OUT' ? 'table-danger' : '';
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
            formData.append('realign_project_id', $('#realignCheckbox').is(':checked') ? $('#realign_project_id').val() : null);

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
                        xhr.responseJSON.message,
                        "Ajax error",
                        "danger"
                    )
                }
            });
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
                    formData.append('project_id', $('#project_id').val());
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

        function _delete_tracking(tracking_id) {
            var formData = new FormData();
            formData.append('tracking_id', tracking_id);
            $.ajax({
                url: "{{ URL::to('categories/deleteTracking') }}",
                method: 'post',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.message === 'success') {
                        loadTracking($('#tracking_category_id').val());
                    } else {
                        alert(response.message);
                    }
                },
                cache: false,
                contentType: false,
                processData: false
            })
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
            $('#allocation').val('');
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
                    $('#allocation').val(response.budget_allocation);
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
            $('#allocation').val('');
            $('#remarks').val('');
        }

        function showRealign() {

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
            var option = `<option value="${category.gaa_id}"`;
            if (level === 0) {
                option += ` style="background-color: #d3d3d3; font-weight: bold;"`;
            } else if (level === 1) {
                option += ` style="background-color: #f0f0f0; font-weight: 600;"`;
            } else {
                option += ` style="background-color: transparent;"`;
            }
            option += `>${'&nbsp;&nbsp;&nbsp;'.repeat(level)}`;
            if (level > 0) {
                option += `- `;
            }
            option += `${category.item_of_expenditure}</option>`;
            $('#realign_gaa_id').append(option);

            if (category.children && category.children.length > 0) {
                category.children.forEach(function(child) {
                    appendDropdownOption(child, level + 1);
                });
            }
        }
    </script>
