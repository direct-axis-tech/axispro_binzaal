@extends('layout.app')

@section('title', 'Companies')

@section('page')
<div class="container">
    <div>
        <h1 class="d-inline-block my-10">Manage Companies</h1>
        <button id="addNewCompanyBtn" class="btn btn-primary float-end my-10">
            <span class="fa fa-plus mx-2"></span> Add New Company
        </button>
    </div>
    <div class="card mt-10">
        <div class="card-body">
            <div class="w-100 table-responsive">
                <table id="company-table" class="table-striped table w-100 table-row-bordered g-3 text-nowrap thead-strong">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Prefix</th>
                            <th>Company Incharge</th>
                            <th>Mol Id</th>
                            <th></th>
                        </tr>
                    <thead>
                </table>
            </div>
        </div>
    </div>

    <form id="updateCompanyForm">
        <div class="modal fade" id="editCompanyModal" tabindex="-1" aria-labelledby="CompanyModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <input type="hidden" name="company_id" id="company_id" class="form-control" value="">

                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="CompanyModalLabel">Edit Company</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-5 bg-light">

                        <div class="form-group row mt-5">
                            <label class="col-sm-3 col-form-label" for="name">Name:</label>
                            <div class="col-sm-9">
                                <input
                                    type="text" name="name" id="name" 
                                    data-parsley-pattern2="/^[\p{L}\p{M}\p{N}_\- ]+$/u"
                                    data-parsley-pattern2-message="The name must only contains alphabets, numbers, dashes, underscore or spaces"
                                    class="form-control" value="" required>
                            </div>
                        </div>
                        <div class="form-group row mt-5">
                            <label class="col-sm-3 col-form-label" for="prefix">Prefix:</label>
                            <div class="col-sm-9">
                                <input
                                    type="text"
                                    name="prefix"
                                    data-parsley-pattern="[0-9a-zA-Z]{2,3}"
                                    data-parsley-pattern-message="The prefix must be alpha numeric with 2-3 letters"
                                    id="prefix"
                                    class="form-control"
                                    value=""
                                    required>
                            </div>
                        </div>
                        <div class="form-group row mt-5">
                            <label class="col-sm-3 col-form-label" for="in_charge_id">Company Incharge:</label>
                            <div class="col-sm-9">
                                <select multiple name="in_charge_id[]" id="in_charge_id" class="form-select" data-control="select2">
                                    <option value="">-- select --</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row mt-5">
                            <label class="col-sm-3 col-form-label" for="mol_id">Mol ID:</label>
                            <div class="col-sm-9">
                                <input
                                    type="text"
                                    name="mol_id"
                                    data-parsley-pattern="[0-9]{13}"
                                    data-parsley-pattern-message="This MOL id is not valid"
                                    id="mol_id"
                                    class="form-control"
                                    value="">
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
    route.push('companies.store', '{{ route('companies.store') }}');
    route.push('companies.update', '{{ rawRoute('companies.update') }}');
    route.push('companies.destroy', '{{ rawRoute('companies.destroy') }}');

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
        const table = $('#company-table').DataTable({
            ajax: ajaxRequest({
                url: '{{ route('api.dataTable.companies') }}',
                method: 'post',
                eject: true,
            }),
            processing: true,
            serverSide: true,
            paging: true,
            ordering: true,
            order: [[1, 'asc']],
            rowId: 'id',
            columns: [
                {
                    data: 'name',
                    title: 'Name',
                    class: 'text-nowrap'
                }, 
                {
                    data: 'prefix',
                    title: 'Prefix',
                    class: 'text-nowrap'
                },
                {
                    data: 'in_charge_name',
                    title: 'Company Incharge',
                    class: 'text-nowrap',
                    render: (data, type, row) => {
                        if (type != 'display') {
                            return data;
                        }  
                        
                        return data && data.replaceAll(',', '<br>');
                    }

                },
                {
                    data: 'mol_id',
                    title: 'Mol Id',
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

        const parsleyForm = $('#updateCompanyForm').parsley();

        $('#addNewCompanyBtn').on('click', () => {
            $('#CompanyModalLabel').text('Add New Company');
            $('#submitBtn').text('Save');
            $('#company_id').val('');
            $('#editCompanyModal').modal('show');
        })

        $('#company-table').on('click', '[data-action="edit"]', function (event) {
            const editBtn = event.target;
            const Company = {...table.row(editBtn.closest('tr')).data()};
            const form = parsleyForm.element;
            $('#CompanyModalLabel').text(`Update Company (${Company.name})`);
            $('#submitBtn').text('Update')
            
            form.elements.company_id.value = Company.id;
            form.elements.name.value = Company.name;
            form.elements.prefix.value = Company.prefix;
            form.elements.mol_id.value = Company.mol_id;
            if (parseInt(Company.is_used)) {
                form.elements.name.readOnly = true;
                form.elements.prefix.readOnly = true;
                form.elements.mol_id.readOnly = form.elements.mol_id.value.length != 0;
            } else {
                form.elements.name.readOnly = false;
                form.elements.prefix.readOnly = false;
                form.elements.mol_id.readOnly = false;
            }
            $('#in_charge_id').val(JSON.parse(Company.in_charge_id)).trigger('change.select2');
            $('#editCompanyModal').modal('show');
        });
        $('#company-table').on('click', '[data-action="delete"]', function (event) {
            const Company = table.row(event.target.closest('tr')).data();

            Swal.fire({
                title: 'Are you sure?',
                html: `Your are about to delete this Company: '${Company.name}'. <br>This process cannot be reversed!`,
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
                    url: route('companies.destroy', {company: Company.id}),
                    data: {
                        '_method': 'delete'
                    }
                }).done(resp => {
                    if (resp && resp.message) {
                        Swal.fire(resp.message, '', 'success');
                        table.ajax.reload(null, false);
                        return;
                    }

                    defaultErrorHandler();
                }).fail(defaultErrorHandler);
            })
        });

        parsleyForm.on('form:submit', function() {
            var formData = new FormData(parsleyForm.element);
            console.log(formData);
            var CompanyId = formData.get('company_id') || '';
            var actionUrl = CompanyId.length 
                ? route('companies.update', {company: CompanyId})
                : route('companies.store');
            
            if (CompanyId.length) {
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
                    CompanyId.length && $('#editCompanyModal').modal('hide');
                    table.ajax.reload(null, false);
                    return;
                }

                defaultErrorHandler()
            }).fail(defaultErrorHandler)

            return false;
        })

        $('#editCompanyModal').on('hidden.bs.modal', resetForm);

        function resetForm() {
            parsleyForm.element.reset();
            parsleyForm.reset();
            parsleyForm.element.elements.company_id.value = '';
            parsleyForm.element.elements.mol_id.readOnly = false;
            parsleyForm.element.elements.prefix.readOnly = false;
            parsleyForm.element.elements.name.readOnly = false;
            $('#in_charge_id').val('').trigger('change.select2');
        }
    });
</script>
@endpush