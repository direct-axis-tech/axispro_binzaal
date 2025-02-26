@extends('layout.app')

@section('title', 'View/Manage Shifts')

@section('page')
    <div class="container">
        <div>
            <h1 class="d-inline-block my-10">Manage Shifts</h1>
            <button id="addNewShiftBtn" class="btn btn-primary float-end my-10">
                <span class="fa fa-plus mx-2"></span> Add New Shift
            </button>
        </div>
        <div class="card mt-10">
            <div class="card-body">
                <div class="w-100 table-responsive">
                    <table id="shifts-table" class="table-striped table w-100 table-row-bordered g-3 text-nowrap thead-strong">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Code</th>
                                <th>Description</th>
                                <th>1st Shift From</th>
                                <th>1st Shift Till</th>
                                <th>2nd Shift From</th>
                                <th>2nd Shift Till</th>
                                <th>Total Duration</th>
                                <th>Color</th>
                                <th></th>
                            </tr>
                        <thead>
                    </table>
                </div>
            </div>
        </div>

        <form id="updateShiftForm">
            <div class="modal fade" id="editShiftModal" tabindex="-1" aria-labelledby="shiftModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <input type="hidden" name="id" id="id" class="form-control" value="">

                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="modal-title" id="shiftModalLabel">Edit Shift</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body p-5 bg-light">
                            <div class="form-group row mt-5">
                                <label class="col-sm-3 col-form-label" for="code">Code:</label>
                                <div class="col-sm-9">
                                    <input
                                        type="text"
                                        name="code"
                                        id="code"
                                        class="form-control"
                                        value=""
                                        required>
                                </div>
                            </div>

                            <div class="form-group row mt-5">
                                <label class="col-sm-3 col-form-label" for="description">Description:</label>
                                <div class="col-sm-9">
                                    <input
                                        type="text"
                                        name="description"
                                        id="description"
                                        class="form-control"
                                        value=""
                                        required>
                                </div>
                            </div>

                            <div class="form-group row mt-5">
                                <label class="col-sm-3 col-form-label" for="from">1<sup>st</sup> Shift From:</label>
                                <div class="col-sm-9">
                                    <input
                                        type="time"
                                        step="1800"
                                        data-parsley-pattern="(2[0-3]|[01][0-9]):(00|30)"
                                        data-parsley-pattern-message="Time must be in HH:mm format with an interval of 30 minutes Eg. 20:30"
                                        name="from"
                                        id="from"
                                        class="form-control"
                                        autocomplete="off"
                                        value=""
                                        required>
                                </div>
                            </div>

                            <div class="form-group row mt-5">
                                <label class="col-sm-3 col-form-label" for="till">1<sup>st</sup> Shift Till:</label>
                                <div class="col-sm-9">
                                    <input
                                        type="time"
                                        step="1800"
                                        data-parsley-pattern="(2[0-3]|[01][0-9]):(00|30)"
                                        data-parsley-pattern-message="Time must be in HH:mm format with an interval of 30 minutes Eg. 20:30"
                                        name="till"
                                        id="till"
                                        class="form-control"
                                        autocomplete="off"
                                        value=""
                                        required>
                                </div>
                            </div>

                            <div class="form-group row mt-5">
                                <label class="col-sm-3 col-form-label" for="from2">2<sup>nd</sup> Shift From:</label>
                                <div class="col-sm-9">
                                    <input
                                        type="time"
                                        step="1800"
                                        name="from2"
                                        data-parsley-pattern="(2[0-3]|[01][0-9]):(00|30)"
                                        data-parsley-pattern-message="Time must be in HH:mm format with an interval of 30 minutes Eg. 20:30"
                                        id="from2"
                                        class="form-control"
                                        autocomplete="off"
                                        value="">
                                </div>
                            </div>

                            <div class="form-group row mt-5">
                                <label class="col-sm-3 col-form-label" for="till2">2<sup>nd</sup> Shift Till:</label>
                                <div class="col-sm-9">
                                    <input
                                        type="time"
                                        step="1800"
                                        name="till2"
                                        data-parsley-pattern="(2[0-3]|[01][0-9]):(00|30)"
                                        data-parsley-pattern-message="Time must be in HH:mm format with an interval of 30 minutes Eg. 20:30"
                                        data-parsley-required-if="from2"
                                        data-parsley-validate-if-empty="true"
                                        id="till2"
                                        class="form-control"
                                        autocomplete="off"
                                        value="">
                                </div>
                            </div>

                            <div class="form-group row mt-5">
                                <label class="col-sm-3 col-form-label" for="color">Color:</label>
                                <div class="col-sm-9">
                                    <input
                                        type="color"
                                        name="color"
                                        id="color"
                                        data-parsley-pattern="#(?!(000000|ffffff))[0-9a-fA-F]{6}"
                                        data-parsley-pattern-message="Color must be in 7 character hexa-decimal format and must not be white or black"
                                        class="form-control"
                                        autocomplete="off"
                                        list="suggestedColors"
                                        required>
                                    <datalist id="suggestedColors"></datalist>
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
        // Adds Custom Validator Required if select
        window.Parsley.addValidator('requiredIf', {
            messages: {en: 'This value is required'},
            requirementType: 'string',
            validate: function(_value, requirement) {
                var formEl = document.getElementById(requirement);

                return !formEl.value.length || _value.length != 0;
            }
        });

        route.push('shifts.store', '{{ route('shifts.store') }}');
        route.push('shifts.update', '{{ rawRoute('shifts.update') }}');
        route.push('shifts.destroy', '{{ rawRoute('shifts.destroy') }}');

        $(function() {
            const table = $('#shifts-table').DataTable({
                ajax: ajaxRequest({
                    url: '{{ route('api.dataTable.shifts') }}',
                    method: 'post',
                    eject: true,
                }),
                processing: true,
                serverSide: true,
                paging: true,
                ordering: true,
                order: [[2, 'asc'], [3, 'asc'], [4, 'asc'], [5, 'asc']],
                rowId: 'id',
                columns: [
                    {data: 'code', title: 'Code'},
                    {
                        data: 'description',
                        title: 'Description',
                        width: '250px',
                        class: 'text-wrap'
                    },
                    {
                        data: 'from',
                        title: '1st Shift From',
                        render: (data, type, row) => {
                            if (type == 'display') {
                                return row.formatted_from;
                            }

                            return row.from;
                        }
                    },
                    {
                        data: 'till',
                        title: '1st Shift Till',
                        render: (data, type, row) => {
                            if (type == 'display') {
                                return row.formatted_till;
                            }

                            return row.till;
                        }
                    },
                    {
                        data: 'from2',
                        title: '2nd Shift From',
                        render: (data, type, row) => {
                            if (type == 'display') {
                                return row.formatted_from2;
                            }

                            return row.from2;
                        }
                    },
                    {
                        data: 'till2',
                        title: '2nd Shift Till',
                        render: (data, type, row) => {
                            if (type == 'display') {
                                return row.formatted_till2;
                            }

                            return row.till2;
                        }
                    },
                    {
                        data: 'total_duration',
                        title: 'Total Duration',
                        render: (data, type, row) => {
                            if (type == 'display') {
                                return row.formatted_total_duration;
                            }

                            return row.total_duration;
                        }
                    },
                    {
                        data: 'color',
                        title: 'Color',
                        searchable: false,
                        orderable: false,
                        render: {
                            display: data => `<span class="p-2 d-block text-center" style="background-color: ${data}">${data}</span>`
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

            const parsleyForm = $('#updateShiftForm').parsley();

            $('#addNewShiftBtn').on('click', () => {
                updateSuggestedColorsList()
                    .then(() => {
                        $('#shiftModalLabel').text('Add New Shift');
                        $('#submitBtn').text('Save')
                        $('#editShiftModal').modal('show');
                    })
                    .catch(defaultErrorHandler);
            })

            $('#shifts-table').on('click', '[data-action="edit"]', function (event) {
                const editBtn = event.target;
                const shift = {...table.row(editBtn.closest('tr')).data()};
                const form = parsleyForm.element;

                (['from', 'till', 'from2', 'till2']).forEach(key => {
                    if (shift[key])
                        shift[key] = shift[key].substring(0, 5);
                });

                updateSuggestedColorsList()
                    .then(() => {
                        $('#shiftModalLabel').text(`Update Shift (${shift.code})`);
                        $('#submitBtn').text('Update')
                        
                        const fields = ['id', 'code', 'description', 'from', 'till', 'from2', 'till2', 'color'];
                        fields.forEach(key => (form.elements[key].value = shift[key]));
                        $('#editShiftModal').modal('show');
                    })
                    .catch(defaultErrorHandler);
            });

            $('#shifts-table').on('click', '[data-action="delete"]', function (event) {
                const shift = table.row(event.target.closest('tr')).data();

                Swal.fire({
                    title: 'Are you sure?',
                    html: `Your are about to delete this shift: '${shift.description}'. <br>This process cannot be reversed!`,
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
                        url: route('shifts.destroy', {shift: shift.id}),
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
                var shiftId = formData.get('id') || '';
                var actionUrl = shiftId.length 
                    ? route('shifts.update', {shift: shiftId})
                    : route('shifts.store');
                
                if (shiftId.length) {
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
                        shiftId.length && $('#editShiftModal').modal('hide');
                        table.ajax.reload();
                        return;
                    }

                    defaultErrorHandler()
                }).fail(defaultErrorHandler)

                return false;
            })

            $('#editShiftModal').on('hidden.bs.modal', resetForm);

            function resetForm() {
                parsleyForm.element.reset();
                parsleyForm.reset();
                parsleyForm.element.elements.id.value = '';
            }

            function updateSuggestedColorsList() {
                return new Promise((resolve, reject) => {
                    ajaxRequest('{{ route('api.shifts.suggestedColors') }}')
                        .done(function (respJson) {
                            const fragment = document.createDocumentFragment(); 
                            respJson.forEach(color => {
                                fragment.appendChild(new Option(undefined, color));
                            })

                            const dataList = document.getElementById('suggestedColors');
                            empty(dataList);
                            dataList.appendChild(fragment);

                            resolve()
                        })
                        .fail(reject)
                })
            }
        });
    </script>
@endpush