$(function() {
    var findClosestGroupWrapper = function(field) {
        return field.$element.closest('div[class^="col"], div[class*=" col"]');
    }

    // Adds Custom Validator Date After
    window.Parsley.addValidator('dateAfter', {
        messages: {en: 'The date should not be less than %s'},
        requirementType: 'string',
        validate: function(_value, requirement, instance) {
            var value = instance.$element.datepicker('getDate');
            var dt = new Date(requirement);
            dt.setHours(0,0,0,0);
            value && value.setHours(0,0,0,0);

            return !value || value.getTime() > dt.getTime();
        }
    });

    // Initialise the parsley form
    var pslyForm = $('#job-update-form').parsley({
        errorClass: 'is-invalid',
        successClass: 'is-valid',
        errorsWrapper: '<ul class="errors-list"></ul>',
        classHandler: findClosestGroupWrapper,
        errorsContainer: findClosestGroupWrapper
    });

    // Initialise the select2s
    $('#employee_id, #department_id, #designation_id, #supervisor_id, #pension_scheme').select2();

    // Change handler for employee_id
    $('#employee_id').on('change', function() {
        setBusyState();
        $.ajax({
            url: route('API_Call', {method: 'getEmployee'}),
            method: 'GET',
            data: {
                id: this.value
            },
            dataType: 'json'
        }).done(function (res) {
            if (res.status && res.status == 200) {
                document.getElementById('work_hours').value = res.data.work_hours;
                
                $('#department_id').val(res.data.department_id).trigger('change');
                $('#designation_id').val(res.data.designation_id).trigger('change');
                $('#default_shift_id').val(res.data.default_shift_id || '').trigger('change');
                $('#supervisor_id').val(JSON.parse(res.data.supervisor_id)).trigger('change');
                $('#working_company_id').val(res.data.working_company_id).trigger('change');
                $('#visa_company_id').val(res.data.visa_company_id).trigger('change');
                $('#attendance_type').val(res.data.attendance_type).trigger('change');
                $('#pension_scheme').val(res.data.pension_scheme).trigger('change');

                document.getElementById('has_commission').checked = res.data.has_commission == '1';
                document.getElementById('has_pension').checked = res.data.has_pension == '1';
                document.getElementById('has_overtime').checked = res.data.has_overtime == '1';
                document.getElementById('require_attendance').checked = res.data.require_attendance == '1';

                var week_offs = JSON.parse(res.data.week_offs);
                Array.from(document.getElementById('week_offs').options).forEach(
                    function(option) {
                        option.selected = (week_offs.indexOf(option.value) > -1)
                    }
                )

                var commenceFromEl = document.getElementById('commence_from');
                commenceFromEl.setAttribute('data-parsley-date-after', res.data.commence_from);

                var commenceFromDate = new Date(res.data.salary_from);
                commenceFromDate.setDate(commenceFromDate.getDate() + 1);
                $(commenceFromEl).datepicker('setStartDate', commenceFromDate);
            } else {
                toastr.error("Something went wrong! Could not retrieve the employee details");
            }
        }).fail(function () {
            toastr.error("Something went wrong! Please try again later.");
        }).always(unsetBusyState);
    })

    // Handle the submission
    pslyForm.on('form:submit', function() {
        var form = this.element;
        var formData = new FormData(form);

        setBusyState();
        $.ajax({
            url: form.action,
            method: form.method,
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json'
        }).done(function (res) {
            if (res.status && res.status == 201) {
                toastr.success("Added Employee's Job Update Successfully");
                pslyForm.element.reset();
            } else {
                toastr.error("Something went wrong!")
            }
        }).fail(function() {
            toastr.error("Somthing went wrong. Please try again or contact the administrator");
        }).always(function() {
            setBusyState(false);
        })
        return false;
    });

    // Handle the form reset
    pslyForm.$element.on('reset',  function() {
        pslyForm.reset();
        $('#employee_id, #department_id, #designation_id, #pension_scheme').val('').trigger('change.select2');
        $('#supervisor_id').val([]).trigger('change.select2');
        $('#commence_from').datepicker('update', '');
        $('#commence_from').datepicker('setStartDate', '');
        $('#commence_from').removeAttr('data-parsley-date-after');
    });

    $('#has_pension').on('click', function() {
        var $element = $('#pension_scheme').closest('.form-group');
        $element.toggleClass('required', this.checked);
        $('#pension_scheme').prop('required', this.checked);
    });
    
})