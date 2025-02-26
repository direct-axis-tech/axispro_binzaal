@extends('layout.app')

@section('title', 'Add/Manage Leave Carry Forward Limit')

@section('page')
<div class="container">
    <div>
        <h1 class="d-inline-block my-10">Add/Manage Leave Carry Forward Limits</h1>
        <button type="button" data-bs-toggle="modal" data-bs-target="#addLeaveCarryForwardModal" class="btn btn-primary float-end my-10">
            <span class="fa fa-plus mx-2"></span> Add New Leave Carry Forward Limit
        </button>
    </div>
    <div class="card mt-10">
        <div class="card-body">
            <div class="w-100 table-responsive">
                <table id="limit-table" class="table-striped table w-100 table-row-bordered g-3 text-nowrap thead-strong">
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for adding LIMIT-->
    <div class="modal fade" id="addLeaveCarryForwardModal" tabindex="-1" aria-labelledby="limitModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="addLimitForm" method="POST">
                    <div class="modal-header">
                        <h2 class="modal-title" id="limitModalLabel">Add Leave Carry Forward Limit</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body p-5 bg-light">
                        <div class="form-group row mt-3">
                            <label class="col-sm-3 col-form-label" for="carry_forward_limit">Leave Carry Forward Limit:</label>
                            <div class="col-sm-9">
                                <input
                                    type="number"
                                    name="carry_forward_limit"
                                    id="carry_forward_limit"
                                    class="form-control"
                                    placeholder="Enter limit"
                                    required >
                            </div>
                        </div>

                        <div class="form-group row mt-3">
                            <label class="col-sm-3 col-form-label" for="affected_from_date">Limit Affected From Date:</label>
                            <div class="col-sm-9">
                                <input
                                    type="text"
                                    data-parsley-trigger-after-failure="change"
                                    data-parsley-date="{{ dateformat('momentJs') }}"
                                    data-control="bsDatepicker"
                                    data-dateformat="{{ dateformat('bsDatepicker') }}"
                                    data-date-today-btn="linked"
                                    name="affected_from_date"
                                    id="affected_from_date"
                                    class="form-control"
                                    placeholder="Select date"
                                    required >
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <input type="hidden" name="limit_id" class="limit_id" id="limit_id" value="" >
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="addLimitBtn" class="btn btn-primary">Add Leave Carry Forward Limit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
@push('scripts')

<script>
    $(document).ready(function () {
        route.push('leaveCarryForward.store', '{{ rawRoute('leaveCarryForward.store') }}');
        route.push('leaveCarryForward.update', '{{ rawRoute('leaveCarryForward.update') }}');
        route.push('leaveCarryForward.destroy', '{{ rawRoute('leaveCarryForward.destroy') }}');

        var table = $('#limit-table').DataTable({
            ajax: ajaxRequest({
                url: '{{ route('api.dataTable.leaveCarryForward') }}',
                method: 'post',
                eject: true,
            }),
            processing: true,
            serverSide: true,
            paging: true,
            ordering: false,
            rowId: 'id',
            columns: [
                {
                    data: 'carry_forward_limit',
                    title: 'Leave Carry Forward Limit',
                    class: 'text-nowrap',
                },
                {
                    data: 'affected_from_date',
                    title: 'Affected From Date',
                    class: 'text-nowrap',
                    render: function (data, type, row) {
                        if (type === 'display') {
                            return moment(data).format('DD-MMM-YYYY');
                        }
                        return data;
                    },
                },
                {
                    data: 'leave_type_name',
                    title: 'Leave Type',
                    class: 'text-nowrap',
                },
                {
                    data: null,
                    defaultContent: '',
                    title: '',
                    width: '20px',
                    searchable: false,
                    orderable: false,
                    render: function (data) {
                        var actions = `<span data-action="edit" title="Edit" class="text-warning mx-1 fa fs-1 p-2 cursor-pointer fa-pencil-alt"></span>`;
                        if (!parseInt(data.is_used)) {
                            actions += `<span data-action="delete" title="Delete" class="text-accent mx-1 fa fs-1 p-2 cursor-pointer fa-trash"></span>`;
                        }
                        return actions;
                    },
                },
            ],
        });

        const parsleyForm = $('#addLimitForm').parsley();

        parsleyForm.on('form:submit', function () {
            const data = parsleyForm.$element.serializeArray()
                .reduce((acc, ob) => {
                    acc[ob.name] = ob.value;
                    return acc;
                }, {});

            data._method = data.limit_id ? 'PATCH' : 'POST';
            ajaxRequest({
                method: 'post',
                url: data.limit_id
                    ? route('leaveCarryForward.update', { leaveCarryForward: data.limit_id })
                    : route('leaveCarryForward.store'),
                data: data
            }).done(function (response, status, xhr) {
                Swal.fire({
                    icon: "success",
                    title: "Success",
                    text: response.message
                });

                $('#addLeaveCarryForwardModal').modal('hide');
                table.ajax.reload();
            }).fail(defaultErrorHandler)

            return false;
        })

        // Handle Limit Update
        $('#limit-table').on('click', 'span[data-action="edit"]', function() {
            var data = table.row($(this).closest('tr')).data();

            parsleyForm.element.elements['carry_forward_limit'].value = data['carry_forward_limit'];
            $('#affected_from_date').datepicker('setDate', new Date(data.affected_from_date)).trigger('change');

            $('#limit_id').val(data.id);
            $('#limitModalLabel').text('Edit Leave Carry Forward Limit');
            $('#addLimitBtn').text('Update');
            $('#addLeaveCarryForwardModal').modal('show');
        });

        // Handle Limit Delete
        $('#limit-table').on('click', 'span[data-action="delete"]', function() {
            var data = table.row($(this).closest('tr')).data();
            
            Swal.fire({
                title: 'Are you sure?',
                html: `Your are about to delete this limit: <br>This process cannot be reversed!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (!result.value) {
                    return;
                }

                ajaxRequest({
                    method: "POST",
                    url: route('leaveCarryForward.destroy', { leaveCarryForward: data.id }),
                    data: {
                        _method: 'DELETE'
                    }
                }).done(function(response) {
                    Swal.fire({
                        icon: "success",
                        title: "Success",
                        text: response.message,
                    });

                    table.ajax.reload();
                }).fail(defaultErrorHandler);
            })
        });

        $('#addLeaveCarryForwardModal').on('hidden.bs.modal', function () {
            $('#addLimitForm')[0].reset();
        });

        parsleyForm.$element.on('reset', function () {
            parsleyForm.reset();
            $('#limit_id').val('');
            $('#limitModalLabel').text('Add Leave Carry Forward Limit'); 
            $('#addLimitBtn').text('Add Leave Carry Forward Limit'); 
        });
        
    });
</script>

@endpush