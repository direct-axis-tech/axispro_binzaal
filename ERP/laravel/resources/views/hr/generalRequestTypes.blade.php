@extends('layout.app')

@section('title', 'General Requests Types')

@section('page')

<div class="container">
    <div>
        <h1 class="d-inline-block my-10">Manage General Request Types</h1>
        <button type="button" data-bs-toggle="modal" data-bs-target="#requestTypeModal" class="btn btn-primary float-end my-10">
            <span class="fa fa-plus mx-2"></span> Add New General Request Type
        </button>
    </div>
    <div class="card mt-10">
        <div class="card-body">
            <div class="w-100 table-responsive">
                <table id="request-type-table" class="table-striped table w-100 table-row-bordered g-3 text-nowrap thead-strong">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Remarks</th>
                            <th></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for adding General Requests Modal -->
    <div class="modal fade" id="requestTypeModal" tabindex="-1" aria-labelledby="requestTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="requestTypeModalLabel">Add General Request Type</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="requestTypeForm" method="POST">
                    <div class="modal-body p-5 bg-light">

                        <div class="form-group row mt-3">
                            <label class="col-sm-3 col-form-label" for="request_type">Request Type:</label>
                            <div class="col-sm-9">
                                <input type="text" name="request_type" class="form-control request_type" placeholder="Enter Request Type" required >
                            </div>
                        </div>

                        <div class="form-group row mt-3">
                            <label class="col-sm-3 col-form-label" for="remarks">Remarks:</label>
                            <div class="col-sm-9">
                                <textarea required 
                                    placeholder="Description"
                                    name="remarks"
                                    class="form-control"
                                    id="remarks"></textarea>
                            </div>
                        </div>
                        
                    </div>

                    <div class="modal-footer">
                        <input type="hidden" name="request_type_id" class="request_type_id" id="request_type_id" value="" >
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="addRequestTypeBtn" class="btn btn-primary">Add Request Type</button>
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

        route.push('general_request_types.store', '{{ rawRoute('general-request-types.store') }}');
        route.push('general_request_types.update', '{{ rawRoute('general-request-types.update') }}');
        route.push('general_request_types.destroy', '{{ rawRoute('general-request-types.destroy') }}');

        const parsleyForm = $('#requestTypeForm').parsley();

        var table = $('#request-type-table').DataTable({
            ajax: ajaxRequest({
                url: '{{ route('api.dataTable.generalRequestTypes') }}',
                method: 'post',
                eject: true,
            }),
            processing: true,
            serverSide: true,
            paging: true,
            ordering: true,
            rowId: 'id',
            columns: [
                {
                    data: 'request_type',
                    title: 'Type',
                    class: 'text-nowrap',
                },
                {
                    data: 'remarks',
                    title: 'Remarks',
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
                            actions += `<span data-action="delete" title="Delete" class="text-accent mx-1 fa fs-1 p-2 cursor-pointer fa-trash"></span>`;
                        return actions;
                    },
                }
            ],
        });

        parsleyForm.on('form:submit', function() {
            const data = parsleyForm.$element.serializeArray()
                            .reduce((acc, ob) => {
                                acc[ob.name] = ob.value;
                                return acc;
                            }, {});
            data._method = data.request_type_id ? 'PATCH' : 'POST';
            ajaxRequest({
                method: "POST",
                url: data.request_type_id
                    ? route('general_request_types.update', { general_request_type: data.request_type_id })
                    : route('general_request_types.store'),
                data: data
            }).done(function(response) {
                Swal.fire({
                    icon: "success",
                    title: "Success",
                    text: response.message
                });
                $('#requestTypeModal').modal('hide');
                table.ajax.reload();
            }).fail(defaultErrorHandler);
            return false;
        });

        $('#request-type-table').on('click', 'span[data-action="edit"]', function() {
            var data = table.row($(this).closest('tr')).data();

            (['request_type', 'remarks']).forEach(k => {
                parsleyForm.element.elements[k].value = data[k];
            });

            $('#request_type_id').val(data.id);
            $('#requestTypeModalLabel').text('Edit General Request Type');
            $('#addRequestTypeBtn').text('Update');
            $('#requestTypeModal').modal('show');
        });

        $('#request-type-table').on('click', 'span[data-action="delete"]', function() {
            var data = table.row($(this).closest('tr')).data();
            Swal.fire({
                title: 'Are you sure?',
                html: `You are about to delete this '${data.request_type}'. <br>This process cannot be reversed!`,
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
                    url: route('general_request_types.destroy', { general_request_type: data.id }),
                    data: {
                        _method: 'DELETE'
                    }
                }).done(function (response) {
                    Swal.fire({
                        icon: "success",
                        title: "Success",
                        text: response.message,
                    });
                    table.ajax.reload();
                }).fail(defaultErrorHandler);
            })
        });

        $('#requestTypeModal').on('hidden.bs.modal', function () {
            $('#requestTypeForm')[0].reset();
        });

        parsleyForm.$element.on('reset', function () {
            parsleyForm.reset();
            $('#request_type_id').val('');
            $('#requestTypeModalLabel, #addRequestTypeBtn').text('Add General Request Type'); 
            $('#request_type, #remarks').val('');
        })

    });

</script>
@endpush