// Document ready
$(function () {
    "use strict";
    
    if (!document.getElementById('employee-document-upload-form')) {
        return;
    }

    /** @type {FormData} the static object available in this scope to store the formData */
    let formData = null;

    /** The upload form */
    const form = document.forms.namedItem('upload-form');

    let validatedOnce = false;
    let employeeId = null;

    // Initialize the dropzone
    const dropzone = new Dropzone('#attachment', {
        url: form.action,
        autoProcessQueue: false,
        addRemoveLinks: true,
        maxFiles: 1,
        maxFilesizes: 2,
        uploadMultiple: true,
        acceptedFiles: "application/pdf"
    })

    // Adds Custom Validator files
    window.Parsley.addValidator('files', {
        messages: {en: 'Please select a file'},
        requirementType: 'integer',
        validate: function() {
            return dropzone.files.length > 0;
        }
    });

    // Adds Custom Validator to check if the file is not already there
    window.Parsley.addValidator('unique', {
        messages: {en: 'The selected file is already uploaded'},
        requirementType: 'string',
        validate: function() {
            const employeeId = form.elements.namedItem('entity_id').value;
            const documentType = form.elements.namedItem('document_type').value;
            validatedOnce = true;

            // Make sure the employee & document type is selected
            if (!employeeId || !documentType) {
                return true;
            }

            return new Promise((resolve, reject) => {
                ajaxRequest({
                    url: route('api.employees.documents.exists'),
                    data: {
                        employee_id: employeeId,
                        document_type: documentType
                    }
                })
                .done(() => reject())
                .fail(() => resolve());
            })
        }
    });

    // Initializes the form falidation
    const parsleyForm = $(form).parsley({
        inputs: 'input, textarea, select, #attachment'
    });

    // When there is change in employee revalidate the document type
    $('#employee_id').on('change', function() {
        employeeId = this.value;

        if (!validatedOnce) return;
        const parsleyDocType = parsleyForm.fields.find(field => field.element.name == 'document_type');
        parsleyDocType.validate();
    })

    // Handle the submit
    parsleyForm.on('form:submit', (event) => {
        formData = new FormData(form);

        // Handles if the previous upload was error
        const file = dropzone.files[0];
        if (file.status == Dropzone.ERROR) {
            file.status = Dropzone.QUEUED;
        }

        setBusyState();

        // processing the queue submits the request
        dropzone.processQueue();

        return false;
    })

    // resets the form as well as any validation errors
    parsleyForm.$element.on('reset', () => {
        parsleyForm.reset();
        validatedOnce = false;
        dropzone.removeFile(dropzone.files[0]);
        $('#document_type').val('').trigger('change.select2');
        setTimeout(() => $('#employee_id').val(employeeId).trigger('change'));
    })

    // Intercept the sending file and add the form data
    dropzone.on('sending', (file, xhr, _formData) => {
        for (const [key, value] of formData) {
            _formData.append(key, value);
        }
    })

    // unset the busy state when the request is completed either with error or success
    dropzone.on('complete', unsetBusyState);

    // default error handler
    dropzone.on('error', defaultErrorHandler);

    // when the submit is success
    dropzone.on('success', () => {
        form.reset();
        toastr.success("Success! The document has been successfully uploaded")
    })
});