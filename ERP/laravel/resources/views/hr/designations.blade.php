@extends('layout.app')

@section('title', 'View/Manage Designation')

@section('page')
<div class="container">
    <div>
        <h1 class="d-inline-block my-10">Manage Designation</h1>
        <button id="addNewDesignationBtn" class="btn btn-primary float-end my-10">
            <span class="fa fa-plus mx-2"></span> Add New Designation
        </button>
    </div>
    <div class="card mt-10">
        <div class="card-body">
            <div class="w-100 table-responsive">
                <table id="designations-table" class="table-striped table w-100 table-row-bordered g-3 text-nowrap thead-strong">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th></th>
                        </tr>
                    <thead>
                </table>
            </div>
        </div>
    </div>

    <form id="updateDesignationForm">
        <div class="modal fade" id="editDesignationModal" tabindex="-1" aria-labelledby="designationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <input type="hidden" name="designation_id" id="designation_id" class="form-control" value="">

                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="designationModalLabel">Edit Designation</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-5 bg-light">
                        <div class="form-group row mt-5">
                            <label class="col-sm-3 col-form-label" for="name">Name:</label>
                            <div class="col-sm-9">
                                <input
                                    type="text"
                                    name="name"
                                    data-parsley-pattern2="/^[\p{L}\p{M}\p{N}_\- ]+$/u"
                                    data-parsley-pattern2-message="The name must only contains alphabets, numbers, dashes, underscore or spaces"
                                    id="name"
                                    class="form-control"
                                    value=""
                                    required>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="submitBtn" class="btn btn-info">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
@push('scripts')
<script>
    route.push('designations.store', '{{ route('designations.store') }}');
    route.push('designations.update', '{{ rawRoute('designations.update') }}');
    route.push('designations.destroy', '{{ rawRoute('designations.destroy') }}');

    // Adds Custom Validator Required if select
    window.Parsley.addValidator('pattern2', {
        validateString: function validateString(value, regexp) {
            if (!value) return true;

            var flags = '';
            if (/^\/.*\/(?:[gisumy]*)$/.test(regexp)) {
                flags = regexp.replace(/.*\/([gisumy]*)$/, '$1');
                regexp = regexp.replace(new RegExp('^/(.*?)/' + flags + '$'), '$1');
            } else {
                regexp = '^' + regexp + '$';
            }

            regexp = new RegExp(regexp, flags);
            return regexp.test(value);
        },
        requirementType: 'string',
        messages: {en: 'This value seems to be invalid'}
    });

    $(function() {
        const table = $('#designations-table').DataTable({
            ajax: ajaxRequest({
                url: '{{ route('api.dataTable.designations') }}',
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
                    data: 'name',
                    title: 'Name',
                    class: 'text-nowrap'
                },
                {
                    data: null,
                    defaultContent: '',
                    title: '',
                    width: '20px',
                    searchable: false,
                    orderable: false,
                    render: (data, type, row) => {
                        if (type != 'display' || parseInt(row.is_used)) {
                            return null;
                        }
                        
                        return (
                              '<span data-action="edit" title="Edit" class="text-warning mx-1 fa fs-1 p-2 cursor-pointer fa-pencil-alt"></span>'
                            + '<span data-action="delete" title="Delete" class="text-accent mx-1 fa fs-1 p-2 cursor-pointer fa-trash"></span>'
                        )
                    }
                },
            ]
        })

        const parsleyForm = $('#updateDesignationForm').parsley();

        $('#addNewDesignationBtn').on('click', () => {
            $('#designationModalLabel').text('Add New Designation');
            $('#submitBtn').text('Save');
            $('#designation_id').val('');
            $('#editDesignationModal').modal('show');
        })

        $('#designations-table').on('click', '[data-action="edit"]', function (event) {
            const editBtn = event.target;
            const designation = {...table.row(editBtn.closest('tr')).data()};
            const form = parsleyForm.element;

            $('#designationModalLabel').text(`Update Designation (${designation.name})`);
            $('#submitBtn').text('Update')
            
            form.elements.designation_id.value = designation.id;
            form.elements.name.value = designation.name;
            $('#editDesignationModal').modal('show');
        });

        $('#designations-table').on('click', '[data-action="delete"]', function (event) {
            const designation = table.row(event.target.closest('tr')).data();

            Swal.fire({
                title: 'Are you sure?',
                html: `Your are about to delete this designation: '${designation.name}'. <br>This process cannot be reversed!`,
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
                    method: 'post',
                    url: route('designations.destroy', {designation: designation.id}),
                    data: {
                        '_method': 'delete'
                    }
                }).done(resp => {
                    if (resp && resp.message) {
                        Swal.fire(resp.message, '', 'success');
                        table.ajax.reload();
                        return;
                    }

                    defaultErrorHandler();
                }).fail(defaultErrorHandler);
            })
        });

        parsleyForm.on('form:submit', function() {
            var formData = new FormData(parsleyForm.element);
            var designationId = formData.get('designation_id') || '';
            var actionUrl = designationId.length 
                ? route('designations.update', {designation: designationId})
                : route('designations.store');
            
            if (designationId.length) {
                formData.set('_method', 'patch');
            }

            ajaxRequest({
                method: 'post',
                url: actionUrl,
                data: formData,
                processData: false,
                contentType: false
            }).done(data => {
                if (data && data.message) {
                    toastr.success(data.message);
                    resetForm();
                    designationId.length && $('#editDesignationModal').modal('hide');
                    table.ajax.reload();
                    return;
                }

                defaultErrorHandler()
            }).fail(defaultErrorHandler)

            return false;
        })

        $('#editDesignationModal').on('hidden.bs.modal', resetForm);

        function resetForm() {
            parsleyForm.element.reset();
            parsleyForm.reset();
        }
    });
</script>
@endpush
