@extends('layout.app')

@section('title', 'General Requests')

@section('page')

<div class="container-fluid">
    <div>
        <h1 class="d-inline-block my-10">Manage General Requests</h1>
        <button type="button" data-bs-toggle="modal" data-bs-target="#generalRequestModal" class="btn btn-primary float-end my-10">
            <span class="fa fa-plus mx-2"></span> Add New Request
        </button>
    </div>
    <div class="card mt-10">
        <div class="card-body">
            <div class="w-100 table-responsive">
                <table id="general-request-table" class="table-striped table w-100 table-row-bordered g-3 text-nowrap thead-strong">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Request Type</th>
                            <th>Remarks</th>
                            <th>Requested By</th>
                            <th>Requested Date</th>
                            <th>Request Status</th>
                            <th>Reviewed By</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for adding General Requests Modal -->
    <div class="modal fade" id="generalRequestModal" tabindex="-1" aria-labelledby="generalRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="generalRequestModalLabel">Add General Request</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="generalRequestForm" method="POST">
                    <div class="modal-body p-5 bg-light">

                        <div class="{{ class_names(['form-group row mt-3', 'd-none' => $canOnlyAccessOwn]) }}">
                            <label class="col-sm-3 col-form-label" for="employee_id">Employee:</label>
                            <div class="col-sm-9">
                                <select name="employee_id" id="employee_id" class="form-control" data-control="select2" 
                                data-placeholder=" -- Select --" required >
                                    <option value=""> -- Select Employee -- </option>
                                    @foreach ($authorizedEmployees as $employee)
                                        <option value="{{ $employee->id }}" {{ $currentEmployeeId == $employee->id ? 'selected' : '' }} >{{ $employee->formatted_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row mt-3">
                            <label class="col-sm-3 col-form-label" for="request_type">Request Type:</label>
                            <div class="col-sm-9">
                                <select name="request_type" id="request_type" class="form-control" data-control="select2" 
                                data-placeholder=" -- Select --" required >
                                    <option value=""> -- Select Request Type -- </option>
                                    @foreach ($requestTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->request_type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row mt-3">
                            <label class="col-sm-3 col-form-label" for="request_date">Request Date:</label>
                            <div class="col-sm-9">
                                <input
                                    type="text"
                                    name="request_date"
                                    id="request_date"
                                    class="form-control"
                                    data-parsley-trigger-after-failure="change"
                                    data-parsley-date="{{ dateformat('momentJs') }}"
                                    data-control="bsDatepicker"
                                    data-dateformat="{{ dateformat('bsDatepicker') }}"
                                    data-date-today-btn="linked"
                                    value="{{ date(dateformat()) }}" required >
                            </div>
                        </div>

                        <div class="form-group row mt-3">
                            <label class="col-sm-3 col-form-label" for="remarks">Remarks:</label>
                            <div class="col-sm-9">
                                <textarea required 
                                    data-parsley-minwords="3"
                                    placeholder="Description"
                                    name="remarks"
                                    class="form-control"
                                    id="remarks"></textarea>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <input type="hidden" name="request_id" class="request_id" id="request_id" value="" >
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="addGeneralRequestBtn" class="btn btn-primary">Add Request</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

</div>
@endsection
@push('scripts')

<script>

    $(document).ready(function() {
        
        $('#employee_id, #request_type').select2({dropdownParent: $('#generalRequestModal')});

        route.push('general_requests.store', '{{ rawRoute('general-requests.store') }}');

        const parsleyForm = $('#generalRequestForm').parsley();

        var table = $('#general-request-table').DataTable({
            ajax: ajaxRequest({
                url: '{{ route('api.dataTable.generalRequests') }}',
                method: 'post',
                eject: true,
            }),
            processing: true,
            serverSide: true,
            paging: true,
            ordering: true,
            order: [[ 4, 'desc' ]],
            rowId: 'id',
            columns: [
                {
                    data: 'preferred_name',
                    title: 'Employee',
                    class: 'text-nowrap',
                },
                {
                    data: 'request_type',
                    title: 'Request Type',
                    class: 'text-nowrap',
                },
                {
                    data: 'remarks',
                    title: 'Remarks',
                    class: 'text-nowrap',
                },
                {
                    data: 'initiated_by',
                    title: 'Requested By',
                    class: 'text-nowrap',
                },
                {
                    data: 'formatted_request_date',
                    title: 'Requested Date',
                    class: 'text-nowrap',
                },
                {
                    data: 'request_status',
                    title: 'Request Status',
                    class: 'text-nowrap',
                },
                {
                    data: 'completed_by',
                    title: 'Reviewed By',
                    class: 'text-nowrap',
                }
            ],
        });

        parsleyForm.on('form:submit', function() {
            const data = parsleyForm.$element.serializeArray()
                            .reduce((acc, ob) => {
                                acc[ob.name] = ob.value;
                                return acc;
                            }, {});
            ajaxRequest({
                method: "POST",
                url: route('general_requests.store'),
                data: data
            }).done(function(response) {
                Swal.fire({
                    icon: "success",
                    title: "Success",
                    text: response.message
                });
                $('#generalRequestModal').modal('hide');
                table.ajax.reload();
            }).fail(defaultErrorHandler);
            return false;
        });

        $('#generalRequestModal').on('hidden.bs.modal', function () {
            $('#generalRequestForm')[0].reset();
        });

        parsleyForm.$element.on('reset', function () {
            parsleyForm.reset();
            $('#request_id').val('');
            $('#generalRequestModalLabel, #addGeneralRequestBtn').text('Add General Request'); 
            $('#employee_id, #request_type, #remarks').val('').trigger('change');
            $('#request_date').datepicker('setDate', new Date()).trigger('change');
        })

    });

</script>
@endpush