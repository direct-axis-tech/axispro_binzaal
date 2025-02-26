$(function() {

    $('#employee_id').select2();

    var findClosestGroupWrapper = function(field) {
        return field.$element.closest('div[class^="col"], div[class*=" col"]');
    }

    // Function to calculate timeout_duration and update the field
    function calculateTimeoutDuration() {
        var fromDate = $('#time_out_from').val();
        var toDate = $('#time_out_to').val();
        $('#timeout_duration').val('');

        if (fromDate && toDate) {
            // Get the current date
            var currentDate = new Date().toISOString().split('T')[0];

            // Create Date objects with the current date and selected times
            var fromDateTime = new Date(currentDate + ' ' + fromDate);
            var toDateTime = new Date(currentDate + ' ' + toDate);

            // Validate to date > from date
            if (toDateTime <= fromDateTime) {
                toastr.error("Timeout To must be greater than Timeout From");
                $('#time_out_to').val('');
                return;
            }

            // Calculate duration in minutes
            var durationMinutes = (toDateTime - fromDateTime) / (60 * 1000);

            if (durationMinutes > $('#time_remaining').val()) {
                toastr.error("Sorry enterd Timeout duration must be less or equal to (" + $('#time_remaining').val() + ")  remaining minutes...!");
                $('#time_out_to').val('');
                return;
            }

            // Update the timeout_duration field
            $('#timeout_duration').val(durationMinutes.toFixed(2));
            $('#timeout_duration').parsley().validate();

        }
    }

    $('#time_out_from, #time_out_to').on('change', calculateTimeoutDuration);

    // when changing employee,time_out_date revalidate the unique leave
    $("#employee_id, #time_out_date").on("change", function() {

        var doc = document;
        var employee_id = doc.getElementById("employee_id").value;
        var time_out_date = doc.getElementById("time_out_date").value;
        $('#time_out_from, #time_out_to, #timeout_duration').val('');
        $('#time_remaining').val(0);

        if (employee_id != "") {

            var filterFormElem = doc.getElementById('add-timeout-form');

            setBusyState();

            $.ajax({
                url: filterFormElem.action,
                method: 'post',
                data: {
                    employee_id: employee_id,
                    time_out_date: time_out_date,
                    action: "get_employee_timeouts",
                },
                dataType: 'json'
            }).done(function(result) {
                if (result.status && result.status == 200) {

                    $('#time_remaining').val(result.data.timeoutBalance);

                }
            }).fail(function(xhr) {
                toastr.error(
                    "Something went wrong! Please try again or contact the administrator"
                );
            }).always(unsetBusyState);
        }
    });


    var pslyForm = $('#add-timeout-form').parsley({
        errorClass: 'is-invalid',
        successClass: 'is-valid',
        errorsWrapper: '<ul class="errors-list"></ul>',
        classHandler: findClosestGroupWrapper,
        errorsContainer: findClosestGroupWrapper
    });

    pslyForm.$element.on('reset', function() {
        pslyForm.reset();
        $('#employee_id').val('').trigger('change.select2');
        $('#time_out_date').datepicker('update', '');
    });

    // Handle the submission
    pslyForm.on('form:submit', function() {
        var form = this.element;
        var formData = new FormData(form);

        Swal.fire({
            title: 'Are you sure?',
            text: "Please make sure that all the information is correct!" +
                " This process is irreversible and non-editable",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, I Confirm!'
        }).then(function(result) {
            if (result.value) {
                setBusyState();
                $.ajax({
                    url: form.action,
                    method: form.method,
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json'
                }).done(function(res) {
                    if (res.status) {
                        if (res.status == 201) {
                            toastr.success("Timeout Added Successfully");
                            form.reset();
                            $("#employee_id").trigger('change');
                        } else if (typeof res.message == 'string') {
                            toastr.error(res.message)
                        }
                    } else {
                        toastr.error("Something went wrong!")
                    }
                }).fail(function() {
                    toastr.error("Somthing went wrong. Please try again or contact the administrator");
                }).always(function() {
                    setBusyState(false);
                })
            }
        });
        return false;
    });

    // Add custom validator for checking if the timeout is unique
    window.Parsley.addValidator('isTimeoutUnique', {
        messages: {en: 'It seems the employee already applied for a timeout on the same period'},
        requirementType: 'string',
        validate: function(value, requirement) {
            return new Promise(function (resolve, reject) {
    
                var filterFormElem = document.getElementById('add-timeout-form');
                var employee_id = document.getElementById('employee_id').value;
                var time_out_date = document.getElementById("time_out_date").value;
                var time_out_from = document.getElementById("time_out_from").value;
                var time_out_to = document.getElementById("time_out_to").value;
    
                if (
                    employee_id.length === 0 ||
                    time_out_date.length === 0 ||
                    time_out_from.length === 0 ||
                    time_out_to.length === 0
                ) {
                    return resolve();
                }
    
                setBusyState();
    
                $.ajax({
                    url: filterFormElem.action,
                    method: 'POST',
                    data: {
                        employee_id: employee_id,
                        time_out_date: time_out_date,
                        time_out_from: time_out_from,
                        time_out_to: time_out_to,
                        action: "is_timeout_unique",
                    },
                }).done(resolve)
                  .fail(reject)
                  .always(unsetBusyState);
            });
        }
    });

    $("#employee_id").trigger('change');

});


