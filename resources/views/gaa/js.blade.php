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
                        `<b>${formattedDate}</b><br> - ${remarks || "No details available."}`
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
                $('#realignSection').show();
                $('#realignButton').show();

                // Hide IN and OUT buttons
                $('#inOutButtons').hide();
            } else {
                // Hide realign section and button
                $('#realignSection').hide();
                $('#realignButton').hide();

                // Show IN and OUT buttons
                $('#inOutButtons').show();
                $('#realign_category_id').val('');
            }
        }

        function showmodal() {
            $('#expenseModal').modal('toggle');
        }

        function showTracking(category_id, expense_name) {
            $('#trackingModal').modal('toggle');
            $('#realignCheckbox').prop('checked', false);
            toggleRealignSection($(this).is(':checked'));
            const today = new Date();
            const formattedDate = today.toISOString().split('T')[0];

            // Set the default value of the input
            $('#activity_date').val(formattedDate);
            $('#remarks').val('');
            $('#tracking_category_id').val(category_id);
            $('#expense_name').html(expense_name);
            loadTracking(category_id);
        }

        function loadTracking(category_id) {
            var formData = new FormData();
            formData.append('category_id', category_id);

            $.ajax({
                url: "{{ url('categories/loadTracking') }}",
                method: 'POST',
                data: formData,
                dataType: 'json',
                contentType: false,
                processData: false,
                cache: false,
                success: function(response) {
                    console.log(response); // Debugging output

                    var tbody = $('#tracking_tbody');
                    tbody.empty(); // Clear existing rows
                    $('#remarks').val(''); // Reset remarks input

                    if (response.length === 0 || !response[0].budget_tracking) {
                        tbody.append('<tr><td colspan="4" class="text-center">No records found</td></tr>');
                        return;
                    }

                    let allocation = response[0].allocation || 0; // Ensure allocation is checked safely

                    // Disable or enable the realign checkbox based on allocation
                    if (allocation == 0) {
                        $('#realignCheckbox').prop('disabled', true);
                    } else {
                        $('#realignCheckbox').prop('disabled', false);
                    }

                    response[0].budget_tracking.forEach(function(item) { // Corrected key: budget_tracking
                        let bgColor = '';

                        if (item.type === 'IN' && item.realignment_from_cat_id != 0) {
                            bgColor = 'table-info';
                        } else if (item.type === 'IN') {
                            bgColor = 'table-success';
                        } else if (item.type === 'OUT' && item.realignment_from_cat_id != 0) {
                            bgColor = 'table-warning';
                        } else if (item.type === 'OUT') {
                            bgColor = 'table-danger';
                        }

                        var row = `
                    <tr class="${bgColor}">
                        <td>${item.type ?? '-'}</td>
                        <td>${item.amount ? parseFloat(item.amount).toLocaleString() : '-'}</td>
                        <td>${item.activity_date ?? '-'}</td>
                        <td>${item.remarks ?? '-'}</td>
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




        function updateTracking(type) {
            const trackingCategoryId = $('#tracking_category_id').val();
            const amount = $('#amount').val();
            const activityDate = $('#activity_date').val();
            const remarks = $('#remarks').val();
            const realignCategoryId = $('#realign_category_id').val();

            // Validation check
            if (!amount || amount <= 0) {
                alert('Please enter a valid amount.');
                return;
            }

            if (!activityDate) {
                alert('Please select an activity date.');
                return;
            }

            const data = {
                category_id: trackingCategoryId,
                type: type,
                amount: amount,
                activity_date: activityDate,
                remarks: remarks
            };

            if (type === 'OUT' && $('#realignCheckbox').is(':checked')) {
                if (!realignCategoryId) {
                    alert('Please select a category to realign funds.');
                    return;
                }
                data.realignment_from_cat_id = realignCategoryId;
            }

            // Send the data to the server using AJAX
            $.ajax({
                url: "{{ URL::to('/categories/updateTracking') }}", // Adjust this URL to match your route
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.message === 'success') {
                        if ($('#realignCheckbox').is(':checked')) {
                            location.reload();
                        } else {
                            loadTracking(trackingCategoryId);
                            $('#amount').val('');
                            $('#remarks').val('');
                            $('#realign_category_id').val('');
                        }
                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr) {
                    alert('An error occurred while updating tracking. Please try again.');
                }
            });
        }

        function _edit(category_id) {
            var formData = new FormData();
            formData.append('category_id', category_id);
            $.ajax({
                url: "{{ URL::to('categories/edit') }}",
                method: 'post',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    $('#projectItemModal').modal('show');
                    $('#projectItemModalLabel').html('Edit Details');
                    $('#sub_category_id').val(response.sub_category_id);
                    $('#category_id').val(response.id);
                    $('#category_name').val(response.category_name);
                    $('#division_id').val(response.division_id);
                    $('#object_type').val(response.object_type);
                    $('#allocation').val(response.allocation);
                    $('#fund_cluster').val(response.fund_cluster);
                },
                cache: false,
                contentType: false,
                processData: false
            })
        }

        function _delete(category_id) {
            Swal.fire({
                title: 'Delete this record?',
                text: "This item will be deleted as well as its sub-items.",
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
                    formData.append('category_id', category_id);
                    $.ajax({
                        url: "{{ URL::to('categories/delete') }}",
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

        function addItemToProject() {
            $('#projectItemModal').modal('toggle');
            $('#sub_category_id').val();
            $('#projectItemModalLabel').html('Add Item to List');
            $('#category_id').val('');
            $('#category_name').val('');
            $('#division_id').val('');
            $('#allocation').val('');
        }

        function addSubItem(category_id, category_name) {
            $('#projectItemModal').modal('toggle');
            $('#projectItemModalLabel').html('Sub Item for: ' + category_name);
            $('#sub_category_id').val(category_id);
            $('#category_id').val('');
            $('#category_name').val('');
            $('#division_id').val('');
            $('#allocation').val('');
        }
    </script>
