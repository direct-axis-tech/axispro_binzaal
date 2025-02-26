$(function () {
    // initialize select2
    $('.form-select:not([data-key^="language_"])').select2();

    // Initialize parsley form
    var parsleyForm = $('#create_labour_form').parsley({
        inputs: 'input, textarea, select, #passport_size_photo,#full_body_photo'
    });

    // resets the form as well as any validation errors
    parsleyForm.$element.on('reset', () => {
        parsleyForm.reset();
        $('.form-select').val('').trigger('change.select2');
    })

    // conditional required validator
    window.Parsley.addValidator('requiredIfHasFile', {
        requirementType: 'string',
        validate: function (value, requirement) {
            var file = document.querySelector(`[name="${requirement}"]`).files;

            if (file.length == 0) {
                return true;
            }

            return value.trim().length != 0
        },
        messages: {
            en: 'This value is required'
        }
    });

    // Add custom validator for checking if the leave is unique
    window.Parsley.addValidator('isRefUnique', {
        messages: {en: 'The maid reference is already in use'},
        requirementType: 'string',
        validate: function(value, requirement) {
            return new Promise(function (resolve, reject) {
                const id = document.getElementById('labour_id')?.value || null;
                const reference = value;

                if (!reference.length) {
                    resolve();
                }

                ajaxRequest({
                    method: 'POST',
                    url: route('labour.reference.isUnique'),
                    data: {id, reference},
                }).done(resp => {
                    (resp && resp.result) ? resolve() : reject();
                })
                .fail(reject)
            })
        }
    })

    // conditional required validator
    window.Parsley.addValidator('requiredIfNotEmpty', {
        requirementType: 'string',
        validate: function (value, requirement) {
            var dependant = document.querySelector(`[name="${requirement}"]`);

            return dependant && (!dependant.value || !!value)
        },
        messages: {
            en: 'This value is required'
        }
    });

    parsleyForm.on('form:submit', function (event) {
        var form = parsleyForm.element;
        var formData = new FormData(form);

        ajaxRequest({
            method: "POST",
            url: form.action,
            data: formData,
            processData: false,
            contentType: false
        }).done(function (data) {
            $('.error_message').text('')
            if (data.status == 201) {
                Swal.fire('Success', data.message, 'success')
                    .then((result) => {
                        if (result.isConfirmed) {
                            form.reset();
                        }
                    })
            } else if (data.status == 200) {
                Swal.fire('Success', data.message, 'success')
                window.location = route('labour.index')
            } else {
                defaultErrorHandler()
            }
        }).fail(function (xhr) {
            if (xhr.status == 422 && xhr.responseJSON && xhr.responseJSON.message) {
                $.each(xhr.responseJSON.message, function (key, value) {
                    var input = $('#' + key);
                    var errorMessage = value.join(' ');
                    input.next('.error_message').text(errorMessage);
                });
            } else {
                defaultErrorHandler();
            }
        });

        return false;
    });

    $('#known_languages').on('click','[data-action="addKnownLang"]', function() {
        const inputGroupClone = $(this).closest('[data-parsley-form-group]').clone();
        const proficiency = inputGroupClone.find('[data-key="language_proficiency"]');
        const language = inputGroupClone.find('[data-key="language_id"]');
        const lastIndex = +(language.prop('name').match(/languages\[(.*?)\]/)[1]);
        const nextIndex = lastIndex + 1;
        
        proficiency.find('option:selected').removeAttr('selected');
        language.find('option:selected').removeAttr('selected');
        proficiency.attr('name', `languages[${nextIndex}][proficiency]`);
        proficiency.attr('data-parsley-required-if-not-empty', `languages[${nextIndex}][id]`);
        language.attr('name', `languages[${nextIndex}][id]`);
        inputGroupClone.insertAfter("#known_languages [data-parsley-form-group]:last");
        this.parentElement.removeChild(this);
    })
})
