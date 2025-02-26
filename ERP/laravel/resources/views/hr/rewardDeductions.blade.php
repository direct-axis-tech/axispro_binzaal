@extends('layout.app')

@section('title', 'Deduction / Rewards')

@section('page')

@php
    use App\Permissions;
@endphp

<div class="container-fluid">
    <div>
        <h1 class="d-inline-block my-10">Manage Employees Deduction / Rewards</h1>
        <button type="button" data-bs-toggle="modal" data-bs-target="#deductionRewardModal" class="btn btn-primary float-end my-10">
            <span class="fa fa-plus mx-2"></span> Add New Deduction / Rewards
        </button>
    </div>
    <div class="card mt-10">
        <div class="card-body">
            <div class="w-100 table-responsive">
                <table id="deduction-reward-table" class="table-striped table w-100 table-row-bordered g-3 text-nowrap thead-strong">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Type</th>
                            <th>Element</th>
                            <th>Sub Element</th>
                            <th>Amount</th>
                            <th>Effective Date</th>
                            <th>Deduction Period</th>
                            <th>Remarks</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for adding Deduction or Reward Modal -->
    <div class="modal fade" id="deductionRewardModal" tabindex="-1" aria-labelledby="deductionRewardModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="deductionRewardModalLabel">Add Deduction / Rewards</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="deductionRewardForm" method="POST">
                    <div class="modal-body p-5 bg-light">
                        <div class="form-group row mt-3">
                            <label class="col-sm-3 col-form-label" for="employee_id">Employees:</label>
                            <div class="col-sm-9">
                                <select name="employee_id" class="form-control" data-control="select2" data-placeholder=" -- Select Employee -- " 
                                    id="employee_id" required >
                                    <option value=""> -- Select Employee -- </option>
                                    @foreach ($employees as $key => $employee) 
                                        <option value="{{ $employee->id }}">{{ $employee->formatted_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row mt-3">
                            <label class="col-sm-3 col-form-label" for="element_type">Type:</label>
                            <div class="col-sm-9">
                                <select name="element_type" id="element_type" class="form-control" data-control="select2" 
                                data-placeholder=" -- Select --" required >
                                    <option value=""> -- Select Type -- </option>
                                    @foreach ($elementTypes as $key => $type) 
                                        <option value="{{ $key }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- DEDUCTION TYPE BLOCK -->
                        <div class="deduction-type-configs d-none">

                            <div class="form-group row mt-3">
                                <label class="col-sm-3 col-form-label" for="deduction_element">Deduction Element:</label>
                                <div class="col-sm-9">
                                    <select name="deduction_element" id="deduction_element" class="form-control elements" data-control="select2" 
                                    data-placeholder=" -- Select --"  >
                                        <option value=""> -- Select Element -- </option>
                                        @foreach ($deductionElements as $key => $element) 
                                            <option value="{{ $element->id }}">{{ $element->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row mt-3">
                                <label class="col-sm-3 col-form-label" for="deduction_sub_element">Sub Element:</label>
                                <div class="col-sm-9">
                                    <select name="deduction_sub_element" id="deduction_sub_element" class="form-control sub_elements" data-control="select2" 
                                    data-placeholder=" -- Select --"  >
                                        <option value=""> -- Select Sub Element -- </option>
                                        @foreach ($deductionSubTypes as $sub => $type) 
                                            <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            @if (pref('hr.auto_journal_deduction_entry'))
                                <div class="form-group row mt-3">
                                    <label class="col-sm-3 col-form-label" for="gl_account">Paid From A/c:</label>
                                    <div class="col-sm-9">
                                        <select name="gl_account" id="gl_account" class="form-control gl_account" data-control="select2" 
                                        data-placeholder=" -- Select --"  >
                                            <option value=""> -- Select A/c -- </option>
                                        </select>
                                    </div>
                                </div>
                            @endif

                            <div class="form-group row mt-3">
                                <label class="col-sm-3 col-form-label" for="number_of_installments">No. Installments:</label>
                                <div class="col-sm-9">
                                    <input type="number" name="number_of_installments" id="number_of_installments" class="form-control" 
                                        step="1" min="1" max="5" data-placeholder=" 0 " value="1" >
                                </div>
                            </div>

                        </div>
                        <!-- DEDUCTION TYPE BLOCK END -->

                        <!-- ALLOWANCE TYPE BLOCK -->
                        <div class="addition-type-configs d-none">

                            <div class="form-group row mt-3">
                                <label class="col-sm-3 col-form-label" for="allowance_element">Allowance Element:</label>
                                <div class="col-sm-9">
                                    <select name="allowance_element" id="allowance_element" class="form-control elements" data-control="select2" 
                                    data-placeholder=" -- Select --"  >
                                        <option value=""> -- Select Element -- </option>
                                        @foreach ($allowanceElements as $key => $element)
                                            <option value="{{ $element->id }}">{{ $element->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row mt-3">
                                <label class="col-sm-3 col-form-label" for="allowance_sub_element">Sub Element:</label>
                                <div class="col-sm-9">
                                    <select name="allowance_sub_element" id="allowance_sub_element" class="form-control sub_elements" data-control="select2" 
                                    data-placeholder=" -- Select --"  >
                                        <option value=""> -- Select Sub Element -- </option>
                                        @foreach ($allowanceSubTypes as $sub => $type) 
                                            <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        </div>
                        <!-- ADDITION TYPE BLOCK END -->

                        <div class="form-group row mt-3">
                            <label class="col-sm-3 col-form-label" for="amount">Total Amount:</label>
                            <div class="col-sm-9">
                                <input
                                    type="number"
                                    name="amount"
                                    id="amount"
                                    class="form-control"
                                    min="0"
                                    step="0.05"
                                    value="0"
                                    required >
                            </div>
                        </div>

                        <div class="form-group row mt-3">
                            <label class="col-sm-3 col-form-label" for="effective_date"> Effective Date:</label>
                            <div class="col-sm-9">
                                <input
                                    type="text"
                                    name="effective_date"
                                    id="effective_date"
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
                            <label class="col-sm-3 col-form-label" for="document_date"> Document Date:</label>
                            <div class="col-sm-9">
                                <input
                                    type="text"
                                    name="document_date"
                                    id="document_date"
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
                                    class="form-control remarks"
                                    id="remarks"></textarea>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <input type="hidden" name="reward_deduction_id" class="reward_deduction_id" id="reward_deduction_id" value="" >
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="addDeductionRewardBtn" class="btn btn-primary">Add Deduction / Rewards</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

</div>
@endsection
@push('scripts')

<script>

    var elementTypes = {
        CREDIT: <?= App\Models\Hr\PayElement::TYPE_ALLOWANCE ?>,
        DEBIT: <?= App\Models\Hr\PayElement::TYPE_DEDUCTION ?>,
    }

    $(document).ready(function() {

        const ledgerAccounts = @json($ledgerAccounts); 
        const bankAccounts   = @json($bankAccounts);

        route.push('empRewardsDeductions.destroy', '{{ rawRoute('empRewardsDeductions.destroy') }}');

        $('#employee_id, #element_type, #deduction_element, #allowance_element, #deduction_sub_element, #allowance_sub_element, #gl_account').select2({dropdownParent: $('#deductionRewardModal')});
        const parsleyForm = $('#deductionRewardForm').parsley();

        $('#element_type').on('change', function() {
            var selectedType = $(this).val();
            $('.remarks, .elements, .sub_elements').val('').trigger('change');
            $('#number_of_installments').val(1).trigger('change');
            
            (['effective_date', 'document_date']).forEach(k => {
                $(parsleyForm.element.elements[k]).datepicker('setDate', new Date()).trigger('change');
            });
            
            if(selectedType == elementTypes.CREDIT) {
                $('.deduction-type-configs').addClass('d-none');
                $('.addition-type-configs').removeClass('d-none');
            } else if(selectedType == elementTypes.DEBIT) {
                $('.addition-type-configs').addClass('d-none');
                $('.deduction-type-configs').removeClass('d-none');
            } else {
                $('.addition-type-configs, .deduction-type-configs').addClass('d-none');
            }
        });

        $('.elements').on('change', function() {
            $('.remarks, .sub_elements').val('').trigger('change');
            $('#number_of_installments').val(1).trigger('change');
        });

        var table = $('#deduction-reward-table').DataTable({
            ajax: ajaxRequest({
                url: '{{ route('api.dataTable.empRewardsDeductions') }}',
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
                    data: 'employee_name',
                    title: 'Employee Name',
                    class: 'text-nowrap',
                },
                {
                    data: 'type',
                    title: 'Type',
                    class: 'text-nowrap',
                },
                {
                    data: 'pay_element',
                    title: 'Element',
                    class: 'text-nowrap'
                },
                {
                    data: 'sub_element_name',
                    title: 'Sub Element',
                    class: 'text-nowrap'
                },
                {
                    data: 'amount',
                    title: 'Amount',
                    class: 'text-nowrap'
                },
                {
                    data: 'number_of_installments',
                    title: 'Installments',
                    class: 'text-nowrap',
                },
                {
                    data: 'effective_date',
                    title: 'Effective Date',
                    class: 'text-nowrap',
                    render: function (data, type, row) {
                        if (type === 'display') {
                            return moment(data).format('{{ dateformat('momentJs') }}');
                        }
                        return data;
                    },
                },
                {
                    data: 'remarks',
                    title: 'Remarks',
                    class: 'text-nowrap',
                },
                {
                    data: 'request_status',
                    title: 'Status',
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
                        var actions = `<span data-action="delete" title="Delete" class="text-accent mx-1 fa fs-1 p-2 cursor-pointer fa-trash"></span>`;
                        return actions;
                    },
                },
                {
                    "data": "employee_id",
                    "visible": false
                },
                {
                    "data": "element_type",
                    "visible": false
                },
                {
                    "data": "allowance_element",
                    "visible": false
                },
                {
                    "data": "deduction_element",
                    "visible": false
                },
                {
                    "data": "allowance_sub_element",
                    "visible": false
                },
                {
                    "data": "deduction_sub_element",
                    "visible": false
                },
                {
                    "data":"trans_no",
                    "visible": false
                },
                {
                    "data":"reference",
                    "visible": false
                },
                {
                    "data":"_is_voided",
                    "visible": false
                }
            ],
        });

        parsleyForm.on('form:submit', function() {
            const data = parsleyForm.$element.serializeArray()
                .reduce((acc, ob) => {
                    acc[ob.name] = ob.value;
                    return acc;
                }, {});
            data._method = data.reward_deduction_id ? 'PATCH' : 'POST';
            ajaxRequest({
                method: "POST",
                url: data.reward_deduction_id
                    ? url('/ERP/hrm/reward_deductions.php', { empRewardDeduction: data.reward_deduction_id })
                    : url('/ERP/hrm/reward_deductions.php'),
                data: data
            }).done(function(response) {
                Swal.fire({
                    icon: "success",
                    title: "Success",
                    text: response.message
                });
                $('#deductionRewardModal').modal('hide');
                table.ajax.reload();
            }).fail(defaultErrorHandler);
            return false;
        });

        $('#deduction-reward-table').on('click', 'span[data-action="edit"]', function() {
            var data = table.row($(this).closest('tr')).data();

            (['employee_id', 'element_type', 'amount', 'deduction_element', 'allowance_element', 'deduction_sub_element', 'allowance_sub_element', 'number_of_installments', 'remarks']).forEach(k => {
                parsleyForm.element.elements[k].value = data[k];
                $(parsleyForm.element.elements[k]).trigger('change');
            });

            (['effective_date', 'document_date']).forEach(k => {
                $(parsleyForm.element.elements[k]).datepicker('setDate', new Date(data[k])).trigger('change');
            });
            
            $('#reward_deduction_id').val(data.id);
            $('#deductionRewardModalLabel').text('Edit Deduction / Rewards');
            $('#addDeductionRewardBtn').text('Update');
            $('#deductionRewardModal').modal('show');
        });

        $('#deduction-reward-table').on('click', 'span[data-action="delete"]', function() {
            var data = table.row($(this).closest('tr')).data();

            if (data.trans_no && !data._is_voided) {
                Swal.fire({
                    icon: 'error',
                    title: 'Cannot Delete',
                    text: 'The transaction "'+ data.reference +'" has not been voided yet. Please void the transaction first if you want to remove it.',
                });
                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                html: `You are about to delete this '${data.type}' for '${data.employee_name}'. <br>This process cannot be reversed!`,
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
                    url: route('empRewardsDeductions.destroy', { empRewardDeduction: data.id }),
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

        $('#deductionRewardModal').on('hidden.bs.modal', function () {
            $('#deductionRewardForm')[0].reset();
        });

        parsleyForm.$element.on('reset', function () {
            parsleyForm.reset();
            $('#reward_deduction_id').val('');
            $('#deductionRewardModalLabel, #addDeductionRewardBtn').text('Add Deduction / Rewards'); 
            $('#employee_id, #element_type, #remarks, .elements, .sub_elements').val('').trigger('change');
            $('#number_of_installments').val(1).trigger('change');
            $('#effective_date, #effective_date').datepicker('setDate', new Date()).trigger('change');
        })

        $('#deduction_element').on('change', function() {
            const selectedElement = $(this).find(':selected').val();

            $('#gl_account').empty();
            $('#gl_account').append('<option value=""> -- Select A/c -- </option>');
            
            if (selectedElement == <?= pref('hr.violations_el') ?>) {
                $('#gl_account').closest('.form-group').find('label').text('Category:');
                ledgerAccounts.forEach(function(ledger) {
                    $('#gl_account').append(`<option value="${ledger.account_code}">${ledger.account_name}</option>`);
                });
            } else {
                $('#gl_account').closest('.form-group').find('label').text('Paid From A/c:');
                bankAccounts.forEach(function(ledger) {
                    $('#gl_account').append(`<option value="${ledger.account_code}">${ledger.account_name}</option>`);
                });
            }
            $('#gl_account').select2({dropdownParent: $('#deductionRewardModal')});
        });

    });

</script>
@endpush