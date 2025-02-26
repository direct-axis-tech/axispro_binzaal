@extends('layout.app')

@section('title', 'Departments')

@section('page')
<div class="container">
    <div>
        <h1 class="d-inline-block my-10">Manage Department</h1>
        <button id="addNewDepartmentBtn" class="btn btn-primary float-end my-10">
            <span class="fa fa-plus mx-2"></span> Add New Department
        </button>
    </div>
    <div class="card mt-10">
        <div class="card-body">
            <div class="w-100 table-responsive">
                <table id="departments-table" class="table-striped table w-100 table-row-bordered g-3 text-nowrap thead-strong">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>HOD</th>
                            <th></th>
                        </tr>
                    <thead>
                </table>
            </div>
        </div>
    </div>

    <form id="updateDepartmentForm">
        <div class="modal fade" id="editDepartmentModal" tabindex="-1" aria-labelledby="departmentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <input type="hidden" name="department_id" id="department_id" class="form-control" value="">

                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="departmentModalLabel">Edit Department</h2>
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
                        <div class="form-group row mt-5">
                            <label class="col-sm-3 col-form-label" for="hod_id">HOD:</label>
                            <div class="col-sm-9">
                                <select name="hod_id[]" id="hod_id" class="form-select" data-control="select2" multiple>
                                    <option value="">-- select --</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
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
    route.push('departments.store', '{{ route('departments.store') }}');
    route.push('departments.update', '{{ rawRoute('departments.update') }}');
    route.push('departments.destroy', '{{ rawRoute('departments.destroy') }}');

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
        const table = $('#departments-table').DataTable({
            ajax: ajaxRequest({
                url: '{{ route('api.dataTable.departments') }}',
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
                    data: 'hod_name',
                    title: 'HOD',
                    class: 'text-nowrap',
                    render: (data, type, row) => {
                        if (type != 'display') {
                            return data;
                        }  
                        
                        return data && data.replaceAll(',', '<br>');
                    }
                },
                {
                    data: null,
                    defaultContent: '',
                    title: '',
                    width: '20px',
                    searchable: false,
                    orderable: false,
                    render: (data, type, row) => {
                        if (type != 'display') {
                            return null;
                        }
                        
                        let actions = '<span data-action="edit" title="Edit" class="text-warning mx-1 fa fs-1 p-2 cursor-pointer fa-pencil-alt"></span>';
                        if (!parseInt(row.is_used)) {
                            actions += '<span data-action="delete" title="Delete" class="text-accent mx-1 fa fs-1 p-2 cursor-pointer fa-trash"></span>';
                        }

                        return actions;
                    }
                },
            ]
        })

        const parsleyForm = $('#updateDepartmentForm').parsley();

        $('#addNewDepartmentBtn').on('click', () => {
            $('#departmentModalLabel').text('Add New Department');
            $('#submitBtn').text('Save');
            $('#department_id').val('');
            $('#editDepartmentModal').modal('show');
        })

        $('#departments-table').on('click', '[data-action="edit"]', function (event) {
            const editBtn = event.target;
            const department = {...table.row(editBtn.closest('tr')).data()};
            const form = parsleyForm.element;

            $('#departmentModalLabel').text(`Update Department (${department.name})`);
            $('#submitBtn').text('Update')
            
            form.elements.department_id.value = department.id;
            form.elements.name.value = department.name;

            if (parseInt(department.is_used)) {
                form.elements.name.readOnly = true;
            }

            $('#hod_id').val(JSON.parse(department.hod_id)).trigger('change.select2');
            $('#editDepartmentModal').modal('show');
        });

        $('#departments-table').on('click', '[data-action="delete"]', function (event) {
            const department = table.row(event.target.closest('tr')).data();

            Swal.fire({
                title: 'Are you sure?',
                html: `Your are about to delete this department: '${department.name}'. <br>This process cannot be reversed!`,
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
                    url: route('departments.destroy', {department: department.id}),
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
            var departmentId = formData.get('department_id') || '';
            var actionUrl = departmentId.length 
                ? route('departments.update', {department: departmentId})
                : route('departments.store');
            
            if (departmentId.length) {
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
                    departmentId.length && $('#editDepartmentModal').modal('hide');
                    table.ajax.reload();
                    return;
                }

                defaultErrorHandler()
            }).fail(defaultErrorHandler)

            return false;
        })

        $('#editDepartmentModal').on('hidden.bs.modal', resetForm);

        function resetForm() {
            parsleyForm.element.reset();
            parsleyForm.reset();
            parsleyForm.element.elements.department_id.value = '';
            parsleyForm.element.elements.name.readOnly = false;
            $('#hod_id').val([]).trigger('change.select2');
        }
    });
</script>
@endpush