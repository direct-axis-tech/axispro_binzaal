$(function() {

    $('#employee_id').select2();

    var findClosestGroupWrapper = function(field) {
        return field.$element.closest('div[class^="col"], div[class*=" col"]');
    }

    $("#employee_id, #adjustment_date, #leave_type_id, #days, #adjustment_type").on("change", function() {

        var doc = document;
        var employee_id = doc.getElementById("employee_id").value;
        var leave_type_id = doc.getElementById("leave_type_id").value;
        var adjustment_date = doc.getElementById("adjustment_date").value;
        var adjustment_type = doc.getElementById("adjustment_type").value;

        if (employee_id != "" && leave_type_id != "") {

            var filterFormElem = doc.getElementById('add-adjustment-form');

            setBusyState();

            $.ajax({
                url: filterFormElem.action,
                method: 'post',
                data: {
                    employee_id: employee_id,
                    leave_type_id: leave_type_id,
                    adjustment_date: adjustment_date,
                    action: "get_leave_details",
                },
                dataType: 'json'
            }).done(function(result) {
                if (result.status && result.status == 200) {

                    var leave_type = doc.getElementById("leave_type_id");
                    var leave_taken = doc.getElementById("leave_taken");
                    var leave_remaining = doc.getElementById("leave_remaining");
                    var days = doc.getElementById("days");
                    var gender = result.data.gender;
                    leave_taken.value = result.data.history;
                    leave_remaining.value = result.data.balance;
                    
                    if (gender == 'M' && leave_type.value == window.leaveTypes.MATERNITY) {
                        toastr.error("Sorry Maternity leave is for Female only...!");
                        leave_type.selectedIndex = 0;
                    } else if (leave_type.value == window.leaveTypes.HAJJ && parseInt(leave_taken.value) > 0 && days.value != '' ) {
                        toastr.error("Sorry Hajj leave can be taken once in a life (service period) no matter how many days...!");
                        txt_days.value = '';
                    }else if (adjustment_type == window.adjustmentTypes.DEBIT && parseInt(days.value) > parseInt(leave_remaining.value)) {
                        if (leave_type.value != window.leaveTypes.PAID && leave_type.value != window.leaveTypes.UNPAID) {
                            toastr.error("Sorry enterd Days must be less or equal to (" + result.data.balance + ") day(s) remaining...!");
                            days.value = '';
                        }
                    }
                    if(leave_type.value == window.leaveTypes.SICK){
                        $('.leave_remaining_div').addClass('d-none');
                    } else {
                        $('.leave_remaining_div').removeClass('d-none');
                    }

                    if (leave_type.value == window.leaveTypes.MATERNITY || leave_type.value == window.leaveTypes.PARENTAL) {
                        $('.leave-continue-div').removeClass('d-none');
                    } else {
                        $('.leave-continue-div').addClass('d-none');
                    }
                } else {
                    toastr.error("Something went wrong!");
                }
            }).fail(function (xhr) {
                toastr.error("Something went wrong! Please try again or contact the administrator");
            }).always(unsetBusyState);
        }

    });

    var pslyForm = $('#add-adjustment-form').parsley({
        errorClass: 'is-invalid',
        successClass: 'is-valid',
        errorsWrapper: '<ul class="errors-list"></ul>',
        classHandler: findClosestGroupWrapper,
        errorsContainer: findClosestGroupWrapper
    });

    // Handle the submission
    pslyForm.on('form:submit', function() {
        var form = this.element;
        var formData = new FormData(form);

        Swal.fire({
            title: 'Are you sure?',
            text: "Please make sure that all the information is correct!",
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
                }).done(function (result) {
                    if (result.status) {
                        if (result.status == 201) {
                            toastr.success("Leave Adjustment Added Successfully");
                            form.reset();
                        } else if (typeof result.message == 'string') {
                            toastr.error(result.message)
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

    var parsleyFromDate = $('#adjustment_date').parsley();

    $('#days, #employee_id').on('change', function() {
        if (parsleyFromDate._failedOnce) {
            parsleyFromDate.validate();
        }
    });

    pslyForm.$element.on('reset',  function() {
        pslyForm.reset();
        $('#employee_id').val('').trigger('change.select2');
        $('#adjustment_date').datepicker('update', '');
    });

    // Add custom validator for checking if the leave is unique
    window.Parsley.addValidator('isLeaveUnique', {
        messages: {en: 'It seems the employee already applied for a leave on the same date'},
        requirementType: 'string',
        validate: function(value, requirement) {
            return new Promise(function (resolve, reject) {
                var employee_id = document.getElementById('employee_id').value;
                var days = Number(document.getElementById('days').value);
                var adjustment_date = document.getElementById('adjustment_date').value.trim();
                var leave_type_id = document.getElementById('leave_type_id').value;
                
                if (
                    employee_id.length == 0
                    || days == 0
                    || adjustment_date.length == 0
                    || leave_type_id == 0
                ) {
                    return resolve();
                }

                setBusyState();
                $.ajax({
                    method: 'POST',
                    url: route('API_Call', {method: 'isAdjustmentLeaveUnique'}),
                    data: {
                        employee_id: employee_id,
                        leave_type_id : leave_type_id,
                        adjustment_date: adjustment_date
                    },
                }).done(resolve)
                .fail(reject)
                .always(unsetBusyState);
            })
        }
    });


});