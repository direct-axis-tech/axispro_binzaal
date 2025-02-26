$(function () {
    var findClosestGroupWrapper = function(field) {
        return field.$element.closest('div[class^="col"], div[class*=" col"]');
    }

    // Initialise the parsley form
    var pslyForm = $('#update-form').parsley({
        errorClass: 'is-invalid',
        successClass: 'is-valid',
        errorsWrapper: '<ul class="errors-list"></ul>',
        classHandler: findClosestGroupWrapper,
        errorsContainer: findClosestGroupWrapper
    });

    // Initialise the select2s
    $('#edit-employee, #nationality, #bank_id, #supervisor_id').select2();

    // Change handler for nationality
    $('#nationality').on('change', function() {
        var $inp = $('#passport_no');
        var $formGroup = $inp.closest('.form-group');
        if (this.value != this.dataset.homeCountry) {
            $formGroup.addClass('required');
            $inp.prop('required', true);
        } else {
            $formGroup.removeClass('required');
            $inp.prop('required', false);
        }

        if (pslyForm._failedOnce) {
            pslyForm.validate({group: 'nationality'});
        }
    })

    // Change handler for mode of payment
    $('#mode_of_pay').on('change', function() {
        var $inputs = $('#bank_id, #branch_name, #iban_no, #personal_id_no');
        var $formGroups = $inputs.closest('.form-group');
        if (this.value == 'B') {
            $formGroups.addClass('required');
            $inputs.prop('required', true);
        } else {
            $formGroups.removeClass('required');
            $inputs.prop('required', false);
        }

        if (pslyForm._failedOnce) {
            pslyForm.validate({group: 'mode_of_pay'})
        }
    })

    $('#nationality, #mode_of_pay').trigger('change');

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
            if (res.status && res.status == 204) {
                toastr.success("Employee Updated Successfully");
                $('#update-form').hide();
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

    $("#profile_photo").change(function (event) {
        readURL(this);
    });
    
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
    
            reader.onload = function (e) {
                $('#image_preview').attr('src', e.target.result);
            }
    
            reader.readAsDataURL(input.files[0]);
        }
    }
});