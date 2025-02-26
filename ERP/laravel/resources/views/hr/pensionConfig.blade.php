@extends('layout.app')

@section('title', 'GPSSA Configuration')

@section('page')
<div class="container">
    <div>
        <h1 class="d-inline-block my-10">Manage Employee Pension Configuration</h1>
        <button type="button" data-bs-toggle="modal" data-bs-target="#addPensionConfigModal" class="btn btn-primary float-end my-10">
            <span class="fa fa-plus mx-2"></span> Add Pension Configuration
        </button>
    </div>
    <div class="card mt-10">
        <div class="card-body">
            <div class="w-100 table-responsive">
                <table id="pension-config-table" class="table-striped table w-100 table-row-bordered g-3 text-nowrap thead-strong">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Employee Share</th>
                            <th>Employer Share</th>
                            <th></th>
                        </tr>
                    <thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for adding employee pension config -->
    <div class="modal fade" id="addPensionConfigModal" tabindex="-1" aria-labelledby="pensionConfigModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="pensionConfigModalLabel">Add Employee Pension Configuration</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="addPensionConfigForm" method="POST">
                    <div class="modal-body p-5 bg-light">
                        <div class="form-group row mt-3">
                            <label class="col-sm-3 col-form-label" for="name">Config Name:</label>
                            <div class="col-sm-9">
                                <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    class="form-control"
                                    required>
                            </div>
                        </div>

                        <div class="form-group row mt-3">
                            <label class="col-sm-3 col-form-label" for="employee_share">Employee Share (%):</label>
                            <div class="col-sm-9">
                                <input
                                    type="number"
                                    name="employee_share"
                                    id="employee_share"
                                    class="form-control"
                                    min="0"
                                    max="100"
                                    step="any"
                                    required >
                            </div>
                        </div>

                        <div class="form-group row mt-3">
                            <label class="col-sm-3 col-form-label" for="employer_share">Employer Share (%):</label>
                            <div class="col-sm-9">
                                <input
                                    type="number"
                                    name="employer_share"
                                    id="employer_share"
                                    class="form-control"
                                    min="0"
                                    max="100"
                                    step="any"
                                    required >
                            </div>
                        </div>

                    </div>
                    
                    <div class="modal-footer">
                        <input type="hidden" name="pension_config_id" class="pension_config_id" id="pension_config_id" value="" >
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="addPensionConfigBtn" class="btn btn-primary">Add</button>
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

        route.push('employeePensionConfig.store', '{{ rawRoute('employeePensionConfig.store') }}');
        route.push('employeePensionConfig.update', '{{ rawRoute('employeePensionConfig.update') }}');
        route.push('employeePensionConfig.destroy', '{{ rawRoute('employeePensionConfig.destroy') }}');

        var table = $('#pension-config-table').DataTable({
            ajax: ajaxRequest({
                url: '{{ route('api.dataTable.employeePensionConfig') }}',
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
                    class: 'text-nowrap',
                },
                {
                    data: 'employee_share',
                    title: 'Employee Share',
                    class: 'text-nowrap',
                },
                {
                    data: 'employer_share',
                    title: 'Employer Share',
                    class: 'text-nowrap'
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

        // Store Pension Config
        const parsleyForm = $('#addPensionConfigForm').parsley();
        parsleyForm.on('form:submit', function() {
            const data = parsleyForm.$element.serializeArray()
                .reduce((acc, ob) => {
                    acc[ob.name] = ob.value;
                    return acc;
                }, {});

            data._method = data.pension_config_id ? 'PATCH' : 'POST';
            ajaxRequest({
                method: "POST",
                url: data.pension_config_id
                    ? route('employeePensionConfig.update', { employeePensionConfig: data.pension_config_id })
                    : route('employeePensionConfig.store'),
                data: data
            }).done(function(response) {
                Swal.fire({
                    icon: "success",
                    title: "Success",
                    text: response.message
                });

                $('#addPensionConfigModal').modal('hide');
                table.ajax.reload();
            }).fail(defaultErrorHandler);

            return false;
        });

        // Handle Pension Config Update
        $('#pension-config-table').on('click', 'span[data-action="edit"]', function() {
            var data = table.row($(this).closest('tr')).data();

            (['name', 'employee_share', 'employer_share']).forEach(k => {
                parsleyForm.element.elements[k].value = data[k];
            });
                    
            $('#pension_config_id').val(data.id);
            $('#pensionConfigModalLabel').text('Edit Employee Pension Configuration');
            $('#addPensionConfigBtn').text('Update');
            $('#addPensionConfigModal').modal('show');
        });

        $('#addPensionConfigModal').on('hidden.bs.modal', function () {
            $('#addPensionConfigForm')[0].reset();
        });

        parsleyForm.$element.on('reset', function () {
            parsleyForm.reset();
            $('#pension_config_id').val('');
            $('#pensionConfigModalLabel').text('Add Employee Pension Configuration'); 
            $('#addPensionConfigBtn').text('Add'); 
        })


        // Handle Pension Config  Delete
        $('#pension-config-table').on('click', 'span[data-action="delete"]', function() {
            var data = table.row($(this).closest('tr')).data();
            
            Swal.fire({
                title: 'Are you sure?',
                html: `Your are about to delete this : '${data.name}'. <br>This process cannot be reversed!`,
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
                    url: route('employeePensionConfig.destroy', { employeePensionConfig: data.id }),
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


    });
</script>

@endpush