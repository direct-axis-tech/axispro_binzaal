$(function () {
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

     // Adds Custom Validator notEquals
     window.Parsley.addValidator('notEquals', {
        messages: {en: 'This value should not be equal to %s'},
        requirementType: 'string',
        validate: function(_value, requirement) {
            var requirementEl = document.querySelector(requirement);

            return requirementEl.value != _value;
        }
    });

    // Initialise the parsley form
    var pslyForm = $('#salary-form').parsley({
        errorClass: 'is-invalid',
        successClass: 'is-valid',
        errorsWrapper: '<ul class="errors-list"></ul>',
        classHandler: findClosestGroupWrapper,
        errorsContainer: findClosestGroupWrapper
    });

    // Initialise the select2s
    $('#employee_id').select2();

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
                document.getElementById('current_salary').value = Number(res.data.monthly_salary).toFixed(2);
                var salaryFromEl = document.getElementById('from');
                salaryFromEl.setAttribute('data-parsley-date-after', res.data.salary_from);

                var salaryFromDate = new Date(res.data.salary_from);
                salaryFromDate.setDate(salaryFromDate.getDate() + 1);
                $(salaryFromEl).datepicker('setStartDate', salaryFromDate);
            } else {
                toastr.error("Something went wrong! Could not retrieve the employee salary");
            }
        }).fail(function () {
            toastr.error("Something went wrong! Please try again later.");
        }).always(unsetBusyState);
    })

    // Sum the total if any of the payElement Changes
    $('[data-pay-element]').on('change', function () {
        var elements = document.querySelectorAll('[data-pay-element]');
        var total = 0;
        for (var i = 0; i < elements.length; i++) {
            var element = elements[i];
            var factor = parseInt(element.dataset.type);
            var val = parseFloat(element.value);
            if (val !== val) val = 0;

            total += (factor * val);
        }

        $('#gross_salary').val(total.toFixed(2)).trigger('change');
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
                toastr.success("Employee Added Successfully");
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
        $('#employee_id').val('').trigger('change.select2');
        $('#from').datepicker('update', '');
        $('#from').datepicker('setStartDate', '');
        $('#from').removeAttr('data-parsley-date-after');
    })
});