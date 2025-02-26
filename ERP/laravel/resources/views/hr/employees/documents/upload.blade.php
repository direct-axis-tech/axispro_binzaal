@extends('layout.app')

@section('title', 'Upload Document')
    
@section('page')
    <!--begin:ContentContainer-->
    <div class="container mw-900px" id="employee-document-upload-form">
        <form action="{{ route('employeeDocument.store') }}" method="POST" id="upload-form">
            @csrf
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Upload Document</h2>
                    </div>
                </div>
                <div class="card-body p-xxl-15">
                    <div class="form-group row mb-3 mb-xxl-7">
                        <label for="employee_id" class="col-form-label col-sm-3 required">Employee</label>
                        <div class="col-lg-4">
                            <select
                                required
                                name="entity_id"
                                class="form-control"
                                data-placeholder="-- Select Employee --"
                                data-control="select2"
                                id="employee_id">
                                <option value="">-- Select Employee --</option>
                                <?php foreach ($employees as $employee): ?>
                                <option value="<?= $employee->id ?>"><?= $employee->formatted_name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row mb-3 mb-xxl-7">
                        <label for="document_type" class="col-form-label col-sm-3 required">Document Type</label>
                        <div class="col-lg-4">
                            <select
                                required
                                name="document_type"
                                class="form-control"
                                data-control="select2"
                                data-parsley-unique="type"
                                data-parsley-trigger-after-failure="change"
                                data-placeholder="-- Select Document Type --"
                                id="document_type">
                                <option value="">-- Select Document Type --</option>
                                <?php foreach ($docTypes as $docType): ?>
                                <option value="<?= $docType->id ?>" data-notify-before="<?= $docType->notify_before ?>"><?= $docType->name ?></option>
                                <?php endforeach; ?>
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
                                placeholder="d-M-yyyy">
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
                                placeholder="d-M-yyyy">
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
                                placeholder="ABC12345TD">
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
@push('scripts')
    <script>
    $('#document_type').on('change', function () {
        const notificationRequired = (parseFloat(this.options[this.selectedIndex]?.dataset?.notifyBefore) || 0) != 0;
        
        $("#expires_on").prop('required', notificationRequired);
        $('label[for="expires_on"]')[notificationRequired ? 'addClass' : 'removeClass']('required');
    })
    </script>
@endpush