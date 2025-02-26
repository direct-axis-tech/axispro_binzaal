$(function () {
    var currentStageIndex = 0;
    var animating = false;
    var findClosestGroupWrapper = function(field) {
        return field.$element.closest('div[class^="col"], div[class*=" col"]');
    }

    // Adds Custom Validator Required if not select
    window.Parsley.addValidator('requiredIfNotSelect', {
        messages: {en: 'This value is required'},
        requirementType: 'string',
        validate: function(_value, requirement) {
            var requirement = requirement.split(',');

            var select = document.getElementById(requirement[0]);
            var notRequiredValue = requirement[1];

            return select.value == notRequiredValue || _value.length != 0;
        }
    });

    // Adds Custom Validator Required if select
    window.Parsley.addValidator('requiredIfSelect', {
        messages: {en: 'This value is required'},
        requirementType: 'string',
        validate: function(_value, requirement) {
            var requirement = requirement.split(',');

            var select = document.getElementById(requirement[0]);
            var requiredValue = requirement[1];

            return select.value != requiredValue || _value.length != 0;
        }
    });

    // Initialise the parsley form
    var pslyForm = $('#reg-form').parsley({
        errorClass: 'is-invalid',
        successClass: 'is-valid',
        errorsWrapper: '<ul class="errors-list"></ul>',
        classHandler: findClosestGroupWrapper,
        errorsContainer: findClosestGroupWrapper
    });

    // Initialise the select2s
    $('#nationality, #bank_id, #department_id, #designation_id, #supervisor_id, #pension_scheme').select2();

    // Change handler for nationality
    $('#nationality').on('change', function() {
        var $el = $('#passport_no').closest('.form-group');
        if (this.value != this.dataset.homeCountry) {
            $el.addClass('required');
        } else {
            $el.removeClass('required');
        }
    })

    // Change handler for mode of payment
    $('#mode_of_pay').on('change', function() {
        var $elements = $('#bank_id, #branch_name, #iban_no, #personal_id_no').closest('.form-group');
        if (this.value == 'B') {
            $elements.addClass('required');
        } else {
            $elements.removeClass('required');
        }
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

    // Handles the next button click
    $('#next').on('click', function() {
        if (animating) return false;

        pslyForm.whenValidate({
            group: 'stage-'+currentStageIndex,
            force: true
        }).then(function() {
            var stages = document.getElementsByClassName('stage');
            var stepsIndicators = {
                ui: document.querySelectorAll('#progressbar li'),
                circles: document.getElementsByClassName('step')
            }
            var $currentStage = $(stages[currentStageIndex]);
            var $nextStage = $(stages[currentStageIndex + 1]);
            if (currentStageIndex == stages.length - 1) {
                // submit the form
                pslyForm.$element.submit();
                return false;
            }
            
            animating = true;
            $(stepsIndicators.ui[currentStageIndex + 1]).addClass('active');
            $(stepsIndicators.circles[currentStageIndex + 1]).addClass('active');
            $(stepsIndicators.circles[currentStageIndex]).addClass('finish');
            $(stepsIndicators.circles[currentStageIndex]).removeClass('active');

            $nextStage.show();
            $currentStage.animate({left: '-50%'}, {
                step: function(now, fx) {
                    $currentStage.css({
                        opacity: 1 + (now / 100)
                    });
                    $nextStage.css({
                        position: 'absolute',
                        left: 100 + (now * 2),
                        opacity: now / 100 * -2
                    })
                },
                complete: function(){
                    $nextStage.css({
                        position: 'relative',
                        left: 0,
                        opacity: 1
                    });
                    $currentStage.hide();
                    $currentStage.css({
                        opacity: 0,
                        left: 0
                    });
                    currentStageIndex++;
                    if (currentStageIndex > 0) {
                        $('#previous').show();
                    }
                    if (currentStageIndex == stages.length - 1) {
                        $('#next').text('Submit ✓');
                    }
                    animating = false;
                },
            });
        }).catch(function () {
            return;
        })
    });

    // Handles the unique validation busy state
    $('#emp_ref, #machine_id').parsley({
        remoteOptions: {
            beforeSend: setBusyState,
            complete: unsetBusyState
        }
    });

    // Handles the previous button click
    $('#previous').on('click', function() {
        if (animating || currentStageIndex == 0) return false;
        animating = true;

        var stages = document.getElementsByClassName('stage');
        var stepsIndicators = {
            ui: document.querySelectorAll('#progressbar li'),
            circles: document.getElementsByClassName('step')
        }
        var $currentStage = $(stages[currentStageIndex]);
        var $previousStage = $(stages[currentStageIndex - 1]);
        
        $(stepsIndicators.ui[currentStageIndex]).removeClass('active');
        $(stepsIndicators.circles[currentStageIndex]).removeClass('active');
        $(stepsIndicators.circles[currentStageIndex - 1]).addClass('active');
        $(stepsIndicators.circles[currentStageIndex - 1]).removeClass('finish');

        $previousStage.show();
        $currentStage.animate({left: '50%'}, {
            step: function(now, fx) {
                $currentStage.css({
                    opacity: 1 - (now * 2 / 100)
                });
                $previousStage.css({
                    position: 'absolute',
                    left: now - 50,
                    opacity: now * 2 / 100
                })
            },
            complete: function(){
                $previousStage.css({
                    position: 'relative',
                    right: 0,
                    opacity: 1
                });
                $currentStage.hide();
                $currentStage.css({
                    opacity: 0,
                    right: 0
                });
                currentStageIndex--;
                if (currentStageIndex == 0) {
                    $('#previous').hide();
                }
                if (currentStageIndex < stages.length - 1) {
                    $('#next').text('Next >>');
                }
                animating = false;
            },
        });
    });

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
                setTimeout(function() {
                    window.location.reload();
                }, 1000);
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

    $('#has_pension').on('click', function() {
        var $element = $('#pension_scheme').closest('.form-group');
        $element.toggleClass('required', this.checked);
        $('#pension_scheme').prop('required', this.checked);
    });
    
});