@extends('layout.app')

@section('title', 'Manage Document Type')

@section('page')
    <div class="container">
        <div>
            <h1 class="d-inline-block my-10">Manage Document Types</h1>
            <button id="addNewTypeBtn" class="btn btn-primary float-end my-10">
                <span class="fa fa-plus mx-2"></span> Add New Type
            </button>
        </div>
        <div class="card mt-10">
            <div class="card-body">
                <div class="w-100 table-responsive">
                    <table id="types-table" class="table-striped table w-100 table-row-bordered g-3 text-nowrap thead-strong">
                        <thead>
                            <tr>
                                <th>Entity Type</th>
                                <th>Name</th>
                                <th>Required</th>
                                <th>Notify Before</th>
                                <th>Notify Before Unit</th>
                                <th></th>
                            </tr>
                        <thead>
                    </table>
                </div>
            </div>
        </div>

        <form id="updateTypeForm">
            <div class="modal fade" id="editTypeModal" tabindex="-1" aria-labelledby="typeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <input type="hidden" name="id" id="id" class="form-control" value="">

                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="modal-title" id="typeModalLabel">Edit Type</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body p-5 bg-light">

                            <div class="form-group row mt-5">
                                <label class="col-sm-3 col-form-label required" for="entity_type">Entity Type:</label>
                                <div class="col-sm-9">
                                    <select
                                        name="entity_type"
                                        class="form-select"
                                        data-placeholder="-- Select Type --"
                                        data-control="select2"
                                        id="entity_type"
                                        required>
                                        <option value="">-- Select Type --</option>
                                        <?php foreach ($entities as $entity): ?>
                                        <option value="<?= $entity->id ?>"><?= $entity->name ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row mt-5">
                                <label class="col-sm-3 col-form-label required" for="name">Name:</label>
                                <div class="col-sm-9">
                                    <input
                                        type="text"
                                        name="name"
                                        id="name"
                                        class="form-control"
                                        value=""
                                        required>
                                </div>
                            </div>

                            <div class="row mt-5">
                                <legend class="col-form-label col-sm-3 pt-0">&nbsp;</legend>
                                <div class="col-sm-9">
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="is_required"
                                            id="is_required"
                                            value="1">
                                        <label class="form-check-label" for="is_required">Required</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row mt-5">
                                <label class="col-sm-3 col-form-label" for="notify_before">Notify Before:</label>
                                <div class="col-sm-9">
                                    <input
                                        type="number"
                                        name="notify_before"
                                        id="notify_before"
                                        class="form-control"
                                        value=""
                                        step="any">
                                </div>
                            </div>

                            <div class="form-group row mt-5">
                                <label class="col-sm-3 col-form-label" for="notify_before_unit">Notify Before Unit:</label>
                                <div class="col-sm-9">
                                    <select
                                        name="notify_before_unit"
                                        class="form-select"
                                        data-placeholder="-- Select Unit --"
                                        data-control="select2"
                                        id="notify_before_unit">
                                        <option value="">-- Select Unit --</option>
                                        @foreach (notify_before_units() as $k => $v)
                                        <option value="{{ $k }}">{{ $v }}</option>
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
        route.push('documentTypes.store', '{{ route('documentTypes.store') }}');
        route.push('documentTypes.update', '{{ rawRoute('documentTypes.update') }}');
        route.push('documentTypes.destroy', '{{ rawRoute('documentTypes.destroy') }}');

        $(function() {
            const storage = {
                notifyBeforeUnits: {}
            }

            $('#entity_type, #notify_before_unit').select2({
                dropdownParent: $('#editTypeModal')
            });

            // Read and initialize the units of notify before
            document.querySelector('#notify_before_unit').options.forEach(option => {
                if (option.value) {
                    storage.notifyBeforeUnits[option.value] = option.text;
                }
            })

            const table = $('#types-table').DataTable({
                ajax: ajaxRequest({
                    url: '{{ route('api.dataTable.documentTypes') }}',
                    method: 'post',
                    eject: true,
                }),
                processing: true,
                serverSide: true,
                paging: true,
                ordering: true,
                order: [[2, 'asc']],
                rowId: 'id',
                columns: [
                    {
                        data: 'entity_type_name',
                        title: 'Entity Name',
                        orderable: false,
                        searchable: false, 
                    },
                    {
                        data: 'name',
                        title: 'Name',
                        width: '250px',
                        class: 'text-wrap'
                    },
                    {
                        data: 'formatted_is_required',
                        title: 'Required',
                        defaultContent: '--',
                    },
                    {
                        data: 'notify_before',
                        title: 'Notify Before',
                        defaultContent: '--',
                    },
                    {
                        data: 'notify_before_unit',
                        title: 'Notify Before Unit',
                        defaultContent: '--',
                        render: function(data, type) {
                            if (type !== 'display') {
                                return data;
                            }

                            return storage.notifyBeforeUnits[data] || null
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
                            var actions = '<span data-action="edit" title="Edit" class="text-warning mx-1 fa fs-1 p-2 cursor-pointer fa-pencil-alt"></span>';

                            if (type != 'display') {
                                return null;
                            }
                            if (parseInt(row.is_used)) {
                                return actions;
                            }
                            return (
                                actions += '<span data-action="delete" title="Delete" class="text-accent mx-1 fa fs-1 p-2 cursor-pointer fa-trash"></span>'
                            );
                        }
                    },
                    {
                        data: 'is_used',
                        visible: false
                    },
                ]
            })

            const parsleyForm = $('#updateTypeForm').parsley();

            $('#addNewTypeBtn').on('click', () => {
                $('#typeModalLabel').text('Add New Type');
                $('#submitBtn').text('Save')
                $('#editTypeModal').modal('show');
            })

            $('#types-table').on('click', '[data-action="edit"]', function (event) {
                const editBtn = event.target;
                const DocType = {...table.row(editBtn.closest('tr')).data()};
                const form = parsleyForm.element;

                $('#typeModalLabel').text(`Update Type (${DocType.name})`);
                $('#submitBtn').text('Update')
                
                const fields = ['id', 'name', 'notify_before', 'entity_type', 'notify_before_unit'];
                fields.forEach(key => (form.elements[key].value = DocType[key]));

                const booleanFields = ['is_required'];
                booleanFields.forEach(key => (form.elements[key].checked = Boolean(parseInt(DocType[key]))));

                $('#entity_type, #notify_before_unit').trigger('change.select2');
                
                if (DocType.is_used) {
                    $('#entity_type').closest('form-group').addClass('inactive-control');
                    $('#name').prop('readonly', true);
                }

                $('#editTypeModal').modal('show');
            });

            $('#types-table').on('click', '[data-action="delete"]', function (event) {
                const DocType = table.row(event.target.closest('tr')).data();
                Swal.fire({
                    title: 'Are you sure?',
                    html: `Your are delete this type ${DocType.name}`,
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
                        url: route('documentTypes.destroy', {documentType: DocType.id}),
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
                var typeId = formData.get('id') || '';
                var actionUrl = typeId.length 
                    ? route('documentTypes.update', {documentType: typeId})
                    : route('documentTypes.store');
                if (typeId.length) {
                    formData.set('_method', 'put');
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
                        typeId.length && $('#editTypeModal').modal('hide');
                        table.ajax.reload(null, false);
                        return;
                    }

                    defaultErrorHandler()
                }).fail(function() {
                    defaultErrorHandler
                })

                return false;
            })

            $('#editTypeModal').on('hidden.bs.modal', resetForm);

            function resetForm() {
                parsleyForm.element.reset();
                parsleyForm.reset();
                parsleyForm.element.elements.id.value = '';
                $('#entity_type').val('').trigger('change.select2');
                $('#notify_before_unit').val('').trigger('change.select2');
                $('#entity_type').closest('form-group').removeClass('inactive-control');
                $('#name').prop('readonly', false);
            }

            $('#notify_before').on('change', function() {
                const unitRequired = (parseFloat(this.value) || 0) != 0;
                $("#notify_before_unit").prop('required', unitRequired);
                $( 'label[for="notify_before_unit"]' )[unitRequired ? 'addClass' : 'removeClass']('required');
            })
        });
    </script>
@endpush