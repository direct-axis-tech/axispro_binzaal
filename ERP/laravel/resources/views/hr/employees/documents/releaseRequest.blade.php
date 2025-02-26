@extends('layout.app')

@section('title', 'Request Document Release')
    
@section('page')
    <!--begin:ContentContainer-->
    <div class="container mw-900px" id="emp-document-release-req-form">
        <form action="{{ route('hr.docReleaseRequest.store') }}" method="POST" id="release-req-form">
            @csrf
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Request Document Release</h2>
                    </div>
                </div>
                <div class="card-body p-xxl-15">
                    <div class="form-group row mt-5">
                        <label for="document_type" class="col-form-label col-sm-3">Document Type:</label>
                        <div class="col-lg-6">
                            <label class="col-form-label" id="document_type">Passport</label>
                            <input type="hidden" name="document_type_id" value="{{ \App\Models\DocumentType::EMP_PASSPORT }}">
                        </div>
                    </div>

                    <div class="{{ class_names(['form-group row mt-5', 'd-none' => $canOnlyAccessOwn]) }}">
                        <label for="employee_id" class="col-form-label col-sm-3 required">Employee:</label>
                        <div class="col-lg-6">
                            <select
                                required
                                name="employee_id"
                                data-control="select2"
                                id="employee_id"
                                class="form-select w-100">
                                <option value="">-- Select Employee --</option>
                                @foreach ($authorizedEmployees as $emp)
                                <option {{ $currentEmployeeId == $emp->id ? 'selected' : '' }} value="{{ $emp->id }}">
                                    {{ $emp->formatted_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row mt-5">
                        <label for="requested_from" class="col-form-label col-sm-3 required">Requested From:</label>
                        <div class="col-lg-6">
                            <input
                                type="text"
                                required
                                data-parsley-trigger-after-failure="change"
                                class="form-control"
                                name="requested_from"
                                id="requested_from"
                                data-provide="datepicker"
                                autocomplete="off"
                                data-date-autoclose="true"
                                data-date-orientation="bottom"
                                data-date-format="<?= getDateFormatForBSDatepicker() ?>"
                                data-parsley-date="{{ dateformat('momentJs') }}">
                            <small id="requested_from_help" class="form-text text-muted">The day: the passport is required by</small>
                        </div>
                    </div>

                    <div class="form-group row mt-5">
                        <label for="return_date" class="col-form-label col-sm-3 required">Return Date:</label>
                        <div class="col-lg-6">
                            <input
                                type="text"
                                required
                                class="form-control"
                                name="return_date"
                                id="return_date"
                                data-provide="datepicker"
                                autocomplete="off"
                                data-date-autoclose="true"
                                data-parsley-trigger-after-failure="change"
                                data-date-format="<?= getDateFormatForBSDatepicker() ?>"
                                data-parsley-date="{{ dateformat('momentJs') }}">
                            <small id="return_date_help" class="form-text text-muted">The day: the passport will be returned.</small>
                        </div>
                    </div>

                    <div class="form-group row mt-5">
                        <label for="remarks" class="{{ class_names(['col-form-label col-sm-3', 'required' => $canOnlyAccessOwn]) }}">Reason</label>
                        <div class="col-sm-9">
                            <textarea
                                data-selection-css-class="validate"
                                {!! $canOnlyAccessOwn ? 'required data-parsley-minwords="3"' : '' !!}
                                name="reason"
                                class="form-control"
                                id="reason"></textarea>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button
                        type="reset"
                        id="reset-btn"
                        class="btn btn-label-dark float-start">
                        <span class="la la-refresh mr-2"></span>
                        Reset
                    </button>
                    <button
                        type="submit"
                        id="submit-btn"
                        class="btn btn-primary float-end">
                        <span class="la la-plus mr-2"></span>
                        Apply Passport
                    </button>
                </div>
            </div>
        </form>
    </div>
    <!--end:ContentContainer-->
@endsection

@push('scripts')
<script>
    $(function () {
        // Initialize Parsley on the form
        var pslyForm = $('#release-req-form').parsley();

        pslyForm.$element.on('reset',  function () {
            pslyForm.reset();
            $('#employee_id').val('').trigger('change.select2');
            $('#requested_from, #return_date').datepicker('update', '');
        })

        pslyForm.on('form:submit', function () {
            ajaxRequest({
                method: pslyForm.element.method,
                url: pslyForm.element.action,
                data: new FormData(pslyForm.element),
                processData: false,
                contentType: false
            }).done(function (resp, textStatus, jqXHR) {
                if (jqXHR && jqXHR.status == 201) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Request Submitted Successfully!'
                    });

                    return pslyForm.element.reset();
                }

                defaultErrorHandler();
            }).fail(defaultErrorHandler);

            return false;
        })
    })
</script>
@endpush