@extends('layout.app')

@section('title', 'Update Document')
    
@section('page')
    <!--begin:ContentContainer-->
    <div class="container mw-900px" id="employee-document-update-form">
        <form action="{{ route('employeeDocument.update', $document->id) }}" method="POST" id="update-form">
            @csrf
            @method('PUT')          
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Update Document</h2>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success text-center">
                        {{ session('success') }}
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger text-center">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="card-body p-xxl-15">
                    <div class="form-group row mb-3 mb-xxl-7">
                        <label for="employee_id" class="col-form-label col-sm-3 required">Employee</label>
                        <div class="col-lg-4">
                            <select                                
                                name="entity_id"
                                class="form-control"
                                data-placeholder="-- Select Employee --"
                                data-control="select2"
                                id="employee_id">                              

                                @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}" {{ $document->entity_id == $employee->id ? 'selected' : '' }}>
                                {{ $employee->formatted_name }}
                                </option>
                            @endforeach


                            </select>
                        </div>
                    </div>
                    <div class="form-group row mb-3 mb-xxl-7">
                        <label for="document_type" class="col-form-label col-sm-3 required">Document Type</label>
                        <div class="col-lg-4">
                            <select                                
                                name="document_type"
                                class="form-control"
                                data-control="select2"
                                data-parsley-unique="type"
                                data-parsley-trigger-after-failure="change"
                                data-placeholder="-- Select Document Type --"
                                id="document_type">
                                <option value="">-- Select Document Type --</option>
                                @foreach ($docTypes as $docType)
                                    <option value="{{ $docType->id }}" {{ $document->document_type == $docType->id ? 'selected' : '' }}>
                                    {{ $docType->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row mb-3 mb-xxl-7">
                        <label for="issued_on" class="col-form-label col-sm-3 required">Issued on</label>
                        <div class="col-auto">
                            <input
                                type="text"
                                required
                                data-provide="datepicker"
                                data-date-format="d-M-yyyy"
                                data-date-clear-btn="true"
                                data-date-autoclose="true"
                                data-parsley-trigger-after-failure="change"
                                class="form-control"
                                name="issued_on"
                                id="issued_on"
                                placeholder="d-M-yyyy"
                                value="{{ isset($document) ? $document->issued_on->format('d-M-Y') : '' }}"
                                >
                        </div>
                    </div>
                    <div class="form-group row mb-3 mb-xxl-7">
                        <label for="expires_on" class="col-form-label col-sm-3">Expires on</label>
                        <div class="col-auto">
                            <input
                                type="text"
                                data-provide="datepicker"
                                data-date-format="d-M-yyyy"
                                data-date-clear-btn="true"
                                data-date-autoclose="true"
                                data-parsley-trigger-after-failure="change"
                                class="form-control"
                                name="expires_on"
                                id="expires_on"
                                placeholder="d-M-yyyy"
                                value="{{ (($document->expires_on != '') ? $document->expires_on->format('d-M-Y') : '') }}"
                                >
                        </div>
                    </div>
                    <div class="form-group row mb-3 mb-xxl-7">
                        <label for="reference" class="col-form-label col-sm-3">Reference</label>
                        <div class="col-auto">
                            <input
                                type="text"
                                minlength="3"
                                data-parsley-pattern="[a-zA-Z0-9\-_]+"
                                data-parsley-pattern-message="Only alphabets, numbers, dash & underscore are allowed"
                                class="form-control"
                                name="reference"
                                id="reference"
                                placeholder="ABC12345TD"
                                value={{ $document->reference }}
                                >
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col">
                            <!--begin::Dropzone-->
                            <div
                                class="dropzone parsley-indicator"
                                id="attachment"
                                data-parsley-files="1"
                                data-parsley-validate-if-empty="true"
                                data-parsley-trigger-after-failure="mouseleave">
                                <!--begin::Message-->
                                <div class="dz-message justify-content-center py-5 py-xxl-15 needsclick">
                                    <!--begin::Icon-->
                                    <i class="bi bi-file-earmark-arrow-up text-primary fs-3x"></i>
                                    <!--end::Icon-->

                                    <!--begin::Info-->
                                    <div class="ms-4 dropzone-preview">
                                        <h3 class="fs-5 fw-bolder text-gray-900 mb-1">Drop file here or click to upload.</h3>
                                        <span class="fs-7 fw-bold text-gray-400">Only PDF files less than 2MB are allowed</span>
                                    </div>
                                    <!--end::Info-->
                                </div>
                            </div>
                            <!--end::Dropzone-->
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="sumbit" class="btn btn-primary float-end">
                        <span>{!! get_svg_icon('icons/duotune/general/gen043.svg', '') !!}</span>
                        Submit
                    </button>
                </div>
            </div>
        </form>
    </div>
    <!--end:ContentContainer-->
@endsection

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<script>

$(function () {
    "use strict";
    
    if (!document.getElementById('employee-document-update-form')) {
        return;
    }

    let formData = null;

    const form = document.forms.namedItem('update-form');

    let validatedOnce = false;
    let employeeId = null;

    const dropzone = new Dropzone('#attachment', {
        url: form.action,
        autoProcessQueue: false,
        addRemoveLinks: true,
        maxFiles: 1,
        maxFilesizes: 2,
        uploadMultiple: true,
        acceptedFiles: "application/pdf"
    })

    window.Parsley.addValidator('files', {
        messages: {en: 'Please select a file'},
        requirementType: 'integer',
        validate: function() {
            return dropzone.files.length > 0 || dropzone.getQueuedFiles().length === 0;
        }
    });

    const parsleyForm = $(form).parsley({
        inputs: 'input, textarea, select, #attachment'
    });

    $('#employee_id').on('change', function() {
        employeeId = this.value;

        if (!validatedOnce) return;
        const parsleyDocType = parsleyForm.fields.find(field => field.element.name == 'document_type');
        parsleyDocType.validate();
    })

    parsleyForm.on('form:submit', (event) => {
        formData = new FormData(form);

        if (dropzone.getQueuedFiles().length === 0) {
            parsleyForm.removeError('files', {updateClass: true});
        }

        const file = dropzone.files[0];
        if (file && file.status == Dropzone.ERROR) {
            file.status = Dropzone.QUEUED;
        }

        setBusyState();
        dropzone.processQueue();
        return false;
    });

    parsleyForm.$element.on('reset', () => {
        parsleyForm.reset();
        validatedOnce = false;
        dropzone.removeFile(dropzone.files[0]);
        $('#document_type, #reference').val('').trigger('change.select2');
        setTimeout(() => $('#employee_id').val(employeeId).trigger('change'));
    })

    dropzone.on('sending', (file, xhr, _formData) => {
        for (const [key, value] of formData) {
            _formData.append(key, value);
        }
    })

    dropzone.on('complete', unsetBusyState);

    dropzone.on('error', defaultErrorHandler);

    dropzone.on('success', () => {
        form.reset();
        toastr.success("Success! The document has been successfully uploaded");
        window.location.href = '{{ route("employeeDocument.manage") }}';
    })
});

</script>