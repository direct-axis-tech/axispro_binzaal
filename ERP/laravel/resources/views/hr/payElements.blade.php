@extends('layout.app')

@section('title', 'Pay Elements')

@section('page')
<div class="container">
    <div>
        <h1 class="d-inline-block my-10">Manage Pay Elements</h1>
        <button id="addNewPayElementBtn" class="btn btn-primary float-end my-10">
            <span class="fa fa-plus mx-2"></span> Add New Pay Element
        </button>
    </div>
    <div class="card mt-10">
        <div class="card-body">
            <div class="w-100 table-responsive">
                <table id="pay-elements-table" class="table-striped table w-100 table-row-bordered g-3 text-nowrap thead-strong">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Is Fixed</th>
                            <th>Account</th>
                            <th></th>
                        </tr>
                    <thead>
                </table>
            </div>
        </div>
    </div>

    <form id="updatePayElementForm">
        <div class="modal fade" id="editPayElementModal" tabindex="-1" aria-labelledby="payElementModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <input type="hidden" name="pay_element_id" id="pay_element_id" class="form-control" value="">

                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="payElementModalLabel">Edit Pay Element</h2>
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
                            <label class="col-sm-3 col-form-label" for="type">Type:</label>
                            <div class="col-sm-9">
                                <select required name="type" id="type" class="form-select" data-control="select2">
                                    <option value="">-- select --</option>
                                    @foreach(pay_element_types() as $k => $v)
                                        <option value="{{ $k }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row mt-5">
                            <div class="col-sm-9 offset-sm-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" name="is_fixed" id="is_fixed">
                                    <label class="form-check-label" for="is_fixed">Fixed Amount</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row mt-5">
                            <label class="col-sm-3 col-form-label" for="account_code">Account:</label>
                            <div class="col-sm-9">
                                <select name="account_code" id="account_code" class="form-select">
                                    <option value="">-- select --</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->account_code }}">{{ $account->account_name }}</option>
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
    route.push('payElements.store', '{{ route('payElements.store') }}');
    route.push('payElements.update', '{{ rawRoute('payElements.update') }}');
    route.push('payElements.destroy', '{{ rawRoute('payElements.destroy') }}');

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
        $('#account_code').select2({dropdownParent: $('#editPayElementModal')});
        const table = $('#pay-elements-table').DataTable({
            ajax: ajaxRequest({
                url: '{{ route('api.dataTable.payElements') }}',
                method: 'post',
                eject: true,
            }),
            processing: true,
            serverSide: true,
            paging: true,
            ordering: true,
            order: [[1, 'asc'], [2, 'desc'], [0, 'asc']],
            rowId: 'id',
            columns: [
                {
                    data: 'name',
                    title: 'Name',
                    class: 'text-nowrap'
                },
                {
                    data: 'type_name',
                    title: 'Type',
                    class: 'text-nowrap'
                },
                {
                    data: 'is_fixed_label',
                    title: 'Fixed Amount',
                    class: 'text-nowrap'
                },
                {
                    data: 'account_name',
                    title: 'Account',
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

        const parsleyForm = $('#updatePayElementForm').parsley();

        $('#addNewPayElementBtn').on('click', () => {
            $('#payElementModalLabel').text('Add New Pay Element');
            $('#submitBtn').text('Save');
            $('#pay_element_id').val('');
            $('#editPayElementModal').modal('show');
        })

        $('#pay-elements-table').on('click', '[data-action="edit"]', function (event) {
            const editBtn = event.target;
            const payElement = {...table.row(editBtn.closest('tr')).data()};
            const form = parsleyForm.element;

            $('#payElementModalLabel').text(`Update Pay Element (${payElement.name})`);
            $('#submitBtn').text('Update')
            
            form.elements.pay_element_id.value = payElement.id;
            form.elements.name.value = payElement.name;
            form.elements.is_fixed.checked = Boolean(parseInt(payElement.is_fixed))
            $('#account_code').val(payElement.account_code).trigger('change.select2');
            $('#type').val(payElement.type).trigger('change.select2');

            if (parseInt(payElement.is_used)) {
                setDisabled(true);
            }

            $('#editPayElementModal').modal('show');
        });

        $('#pay-elements-table').on('click', '[data-action="delete"]', function (event) {
            const payElement = table.row(event.target.closest('tr')).data();

            Swal.fire({
                title: 'Are you sure?',
                html: `Your are about to delete this payElement: '${payElement.name}'. <br>This process cannot be reversed!`,
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
                    url: route('payElements.destroy', {payElement: payElement.id}),
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
            var payElementId = formData.get('pay_element_id') || '';
            var actionUrl = payElementId.length 
                ? route('payElements.update', {payElement: payElementId})
                : route('payElements.store');
            
            if (payElementId.length) {
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
                    payElementId.length && $('#editPayElementModal').modal('hide');
                    table.ajax.reload(null, false);
                    return;
                }

                defaultErrorHandler()
            }).fail(defaultErrorHandler)

            return false;
        })

        $('#editPayElementModal').on('hidden.bs.modal', resetForm);

        function resetForm() {
            parsleyForm.element.reset();
            parsleyForm.reset();
            parsleyForm.element.elements.pay_element_id.value = '';
            setDisabled(false);
            $('#account_code').val('').trigger('change.select2');
            $('#type').val('').trigger('change.select2');
        }

        function setDisabled(isDisabled = true) {
            const formElements = parsleyForm.element.elements;
            formElements.name.readOnly = isDisabled;
            formElements.type.closest('div').classList[isDisabled ? 'add' : 'remove']('inactive-control');
            formElements.is_fixed.disabled = isDisabled;
        }
    });
</script>
@endpush