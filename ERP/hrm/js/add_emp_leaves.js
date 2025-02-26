$(function() {
    var findClosestGroupWrapper = function(field) {
        return field.$element.closest('div[class^="col"], div[class*=" col"]');
    }

    // Initialise the select 2
    $('#reviewed_by, #employee_id').select2();

    // Adds custom validator for leave
    window.Parsley.addValidator('leave', {
        messages: {en: '1/2 day leaves are only allowed for one day'},
        requirementType: 'string',
        validate: function(value, requirement) {
            value = Number(value);
            return !(value > 1 && value % 1 > 0);
        }
    });

    // Add custom validator for checking if the leave is unique
    window.Parsley.addValidator('isLeaveUnique', {
        messages: {en: 'It seems the employee already applied for a leave on the same period'},
        requirementType: 'string',
        validate: function(value, requirement) {
            return new Promise(function (resolve, reject) {
                var employee_id = document.getElementById('employee_id').value;
                var days = Number(document.getElementById('days').value);
                var from = document.getElementById('from').value.trim();

                if (
                    employee_id.length == 0
                    || days == 0
                    || (days > 1 && days % 1 > 0)
                    || from.length == 0
                ) {
                    return resolve();
                }

                setBusyState();
                $.ajax({
                    method: 'POST',
                    url: route('API_Call', {method: 'isLeaveUnique'}),
                    data: {
                        employee_id: employee_id,
                        days: days,
                        from: from
                    },
                }).done(resolve)
                .fail(reject)
                .always(unsetBusyState);
            })
        }
    })

    // update the end date for leave when start date or the number of days changes
    $('#from, #days').on('change', () => {
        const fromEl = $('#from');
        const fromDate = moment(fromEl.datepicker('getDate'));
        const days = parseFloat($('#days').val()) || 0;

        fromDate.isValid() && days
            ? $('#till').val(fromDate.add(days < 1 ? 0 : (Math.floor(days) - 1), 'days').format(fromEl.data('momentJsDateFormat')))
            : $('#till').val('')
    });

    // Exclude sick|unpaid leave from date after now validation
    const dateAfterNow = $('#from').attr('data-parsley-dateafternow');
    $('#leave_type_id').change(function () {
        const excluded = [leaveTypes.SICK, leaveTypes.UNPAID];
        if (excluded.indexOf(parseInt(this.value) || 'ERR') != -1) {
            return $('#from').removeAttr('data-parsley-dateafternow');
        }

        if (!!dateAfterNow) {
            $('#from').attr('data-parsley-dateafternow', dateAfterNow);
        }
    });

    // when changing days or employee, revalidate the unique leave
    $("#employee_id, #leave_type_id, #days, #from").on("change", function () {
        
        var doc = document;
        var employee_id = doc.getElementById("employee_id").value;
        var leave_type_id = doc.getElementById("leave_type_id").value;
        var from_date = doc.getElementById("from").value;
        
        if (employee_id != "" && leave_type_id != "") {
            
            var filterFormElem = doc.getElementById('add-leave-form');

            setBusyState();
            $.ajax({
                url: filterFormElem.action,
                method: 'post',
                data: {
                    employee_id: employee_id,
                    leave_type_id: leave_type_id,
                    from_date: from_date,
                    action: "get_leave_details",
                },
                dataType: 'json'
            }).done(function(res) {
                if (res.status && res.status == 200) {
                    var ddl_leave_type = doc.getElementById("leave_type_id");
                    var txt_leave_taken = doc.getElementById("leave_taken");
                    var txt_leave_remaining = doc.getElementById("leave_remaining");
                    var txt_days = doc.getElementById("days");
                    
                    txt_leave_taken.value = res.data.history;
                    txt_leave_remaining.value = res.data.balance;
                    
                    var gender = res.data.gender;
                    
                    if (leave_type_id == window.leaveTypes.MATERNITY || leave_type_id == window.leaveTypes.PARENTAL) {
                        doc.getElementById("rdbRadioButtons").style.display = 'flex';
                    }
                    else {
                        doc.getElementById("rdbRadioButtons").style.display = 'none';
                    }

                    if (gender == 'M' && leave_type_id == window.leaveTypes.MATERNITY) {
                        toastr.error("Sorry Maternity leave is for Female only...!");
                        ddl_leave_type.selectedIndex = 0;
                    }
                    else if (leave_type_id == window.leaveTypes.HAJJ && parseFloat(txt_leave_taken.value) > 0 && txt_days.value != '' ) {
                        toastr.error("Sorry Hajj leave can be taken once in a life (service period) no matter how many days...!");
                        txt_days.value = '';
                    }
                    else if ( parseFloat(txt_days.value) > parseFloat(txt_leave_remaining.value) ) {
                        if (leave_type_id != window.leaveTypes.PAID && leave_type_id != window.leaveTypes.UNPAID) {
                            toastr.error("Sorry enterd Days must be less or equal to (" + res.data.balance + ") day(s) remaining...!");
                            txt_days.value = '';
                        }
                    }
                } else {
                    toastr.error("Something went wrong!");
                }
            }).fail(function (xhr) {
                toastr.error(
                    "Something went wrong! Please try again or contact the administrator"
                    );
                })
                .always(unsetBusyState);
            }
        });

    var pslyForm = $('#add-leave-form').parsley({
        errorClass: 'is-invalid',
        successClass: 'is-valid',
        errorsWrapper: '<ul class="errors-list"></ul>',
        classHandler: findClosestGroupWrapper,
        errorsContainer: findClosestGroupWrapper
    });

    var parsleyFromDate = $('#from').parsley();

    // when changing days or employee, revalidate the unique leave
    $('#days, #employee_id').on('change', function() {
        if (parsleyFromDate._failedOnce) {
            parsleyFromDate.validate();
        }
    })

    pslyForm.$element.on('reset',  function() {
        pslyForm.reset();
        $('#reviewed_by, #employee_id').val('').trigger('change.select2');
        $('#from, #reviewed_on, #requested_on').datepicker('update', '');
    })

    // Handle the submission
    pslyForm.on('form:submit', function() {
        var form = this.element;
        var formData = new FormData(form);

        Swal.fire({
            title: 'Are you sure?',
            text: "Please make sure that all the information is correct!"
                + " This process is irreversible and non-editable",
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
                }).done(function (res) {
                    if (res.status) {
                        if (res.status == 201) {
                            toastr.success("Leave Added Successfully");
                            form.reset();
                        }
                        
                        else if (typeof res.message == 'string') {
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
})