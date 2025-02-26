@extends('layout.app')

@section('title', $title)
@section('page')
<div class="container">
    <form action="{{ route('contract.installment.store', $contract->id) }}" method="post" id="create_installment_form">
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <h2>{{ $title }}</h2>
                </div>
            </div>
            @if ($contract->installment()->exists())
            <div class="text-center my-20">
                This contract is already on an installment plan. Please select another one
            </div>
            @else
            <div class="card-body">
                <input type="hidden" name="contract_id" id="contract_id" value="{{ $contract->id }}">

                <div class="row mb-2">
                    <label for="contract_ref" class="col-sm-3 col-form-label">Contract</label>
                    <div class="col-sm-9">
                        <input
                            type="text"
                            class="form-control-plaintext ps-4"
                            id="contract_ref"
                            value="{{ $contract->reference }}"
                            readonly>
                    </div>
                </div>

                <div class="row mb-2">
                    <label for="customer" class="col-sm-3 col-form-label">Customer</label>
                    <div class="col-sm-9">
                        <input
                            type="text"
                            class="form-control-plaintext ps-4"
                            id="customer"
                            value="{{ $contract->customer->name }}"
                            readonly>
                    </div>
                </div>

                <div class="row mb-2">
                    <label for="maid" class="col-sm-3 col-form-label">Domestic Worker</label>
                    <div class="col-sm-9">
                        <input
                            type="text"
                            class="form-control-plaintext ps-4"
                            id="maid"
                            value="{{ $contract->maid->name }}"
                            readonly>
                    </div>
                </div>

                <div class="row mb-2">
                    <label for="category" class="col-sm-3 col-form-label">Category</label>
                    <div class="col-sm-9">
                        <input
                            type="text"
                            class="form-control-plaintext ps-4"
                            id="category"
                            value="{{ $contract->category->description }}"
                            readonly>
                    </div>
                </div>

                <div class="row mb-2">
                    <label for="item" class="col-sm-3 col-form-label">Item</label>
                    <div class="col-sm-9">
                        <input
                            type="text"
                            class="form-control-plaintext ps-4"
                            id="item"
                            value="{{ $contract->stock->description }}"
                            readonly>
                    </div>
                </div>

                <div class="row mb-2">
                    <label for="contract_period" class="col-sm-3 col-form-label">Contract Period</label>
                    <div class="col-sm-9">
                        <div id="contract_period" class="input-group input-daterange">
                            <input
                                type="text"
                                class="form-control-plaintext px-4 w-125px text-start"
                                id="contract_from"
                                value="{{ sql2date($contract->contract_from) }}"
                                readonly>
                            <div class="input-group-text input-group-addon px-4 bg-transparent rounded-0 border-0 fw-normal text-inverse-light">to</div>
                            <input
                                type="text"
                                id="contract_till"
                                class="form-control-plaintext px-4 w-125px text-end"
                                value="{{ sql2date($contract->contract_till) }}"
                                readonly>
                        </div>
                    </div>
                </div>

                <div class="row mb-2">
                    <label for="name" class="col-sm-3 col-form-label ">Amount</label>
                    <div class="col-sm-9">
                        <input
                            type="text"
                            class="form-control-plaintext ps-4"
                            name="total_amount"
                            id="total_amount"
                            value="{{ $contract->order->total }}"
                            readonly> 
                    </div>
                </div>

                <div class="row mb-2">
                    <label for="transaction_date" class="col-sm-3 col-form-label required">Transaction Date</label>
                    <div class="col-sm-9">
                        <input
                            type="text"
                            required
                            data-parsley-group="stage-1"
                            data-provide="datepicker"
                            data-date-format="{{ getBSDatepickerDateFormat() }}"
                            data-date-clear-btn="true"
                            data-date-autoclose="true"
                            data-parsley-trigger-after-failure="change"
                            class="form-control"
                            name="transaction_date"
                            id="transaction_date"
                            autocomplete="off"
                            placeholder="{{ getBSDatepickerDateFormat() }}"
                            value="">
                    </div>
                </div>

                <div class="row mb-2">
                    <label for="no_installment" class="col-sm-3 col-form-label required">No of Installments</label>
                    <div class="col-sm-9">
                        <input
                            type="number"
                            data-parsley-group="stage-1"
                            class="form-control"
                            step="1" min="1"
                            name="no_installment"
                            id="no_installment"
                            value=""
                            required>
                    </div>
                </div>

                <div class="row mb-2">
                    <label for="interval" class="col-sm-3 col-form-label required">Installment Interval:</label>
                    <div class="col-sm-9">
                        <div class="input-group">
                            <input
                                type="number"
                                data-parsley-group="stage-1"
                                class="form-control"
                                step="0.01"
                                min="0.01"
                                name="interval"
                                id="interval"
                                value="1"
                                required>
                            <div class="input-group-text p-0">
                                <select
                                    required
                                    data-parsley-group="stage-1"
                                    name="interval_unit"
                                    class="form-select rounded-start-0 w-125px"
                                    data-placeholder="-- Unit --"
                                    data-control="select2"
                                    id="interval_unit">
                                    <option value="">-- Select Unit --</option>
                                    @foreach (installment_interval_units() as $k => $v)
                                    <option value="{{ $k }}" {{ $k == 'month' ? 'selected' : '' }}>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                          </div>
                    </div>
                </div>

                <div class="row mb-2">
                    <label for="no_installment" class="col-sm-3 col-form-label required">Installment Amount</label>
                    <div class="col-sm-9">
                        <input
                            type="number"
                            data-parsley-group="stage-1"
                            class="form-control"
                            step="0.01"
                            min="0"
                            name="installment_amount"
                            id="installment_amount"
                            value=""
                            required>
                    </div>
                </div>

                <div class="row mb-2">
                    <label for="start_date" class="col-sm-3 col-form-label required">Start Date</label>
                    <div class="col-sm-9">
                        <input
                            type="text"
                            required
                            data-parsley-group="stage-1"
                            data-provide="datepicker"
                            data-date-format="{{ getBSDatepickerDateFormat() }}"
                            data-date-clear-btn="true"
                            data-date-autoclose="true"
                            data-parsley-trigger-after-failure="change"
                            class="form-control"
                            name="start_date"
                            id="start_date"
                            autocomplete="off"
                            placeholder="{{ getBSDatepickerDateFormat() }}"
                            value="">
                    </div>
                </div>

                <div class="row mb-2">
                    <label for="ref" class="col-sm-3 col-form-label required">Bank</label>
                    <div class="col-sm-9">
                        <select
                            data-parsley-group="stage-1"
                            class="form-select"
                            name="bank_id"
                            id="bank_id"
                            data-control="select2"
                            required>
                            <option value="">--SELECT--</option>
                            @foreach ($banks as $bank)
                            <option value="{{ $bank->id }}"> {{ $bank->name }} </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-2">
                    <label for="payee_name" class="col-sm-3 col-form-label required">Payee Name</label>
                    <div class="col-sm-9">
                        <input
                            data-parsley-group="stage-1"
                            class="form-control"
                            type="text"
                            name="payee_name"
                            id="payee_name"
                            data-parsley-pattern2="/^[\p{L}\p{M} ]+$/u"
                            data-parsley-pattern2-message="The name must only contains charecters and spaces"
                            value="{{ trim(pref('company.coy_name')) }}"
                            required>
                    </div>
                </div>
                
                <div class="row mb-2">
                    <label for="initial_cheque_no" class="col-sm-3 col-form-label required">Initial Cheque Number</label>
                    <div class="col-sm-9">
                        <input
                            data-parsley-group="stage-1"
                            class="form-control"
                            type="text"
                            data-parsley-type="alphanum"
                            name="initial_cheque_no"
                            id="initial_cheque_no"
                            value=""
                            required>
                    </div>
                </div>

                <div class="form-group row mt-5">
                    <table class="table-striped table w-100 table-row-bordered g-3 text-nowrap thead-strong text-center">
                        <thead class="thead-dark">
                            <tr class="table-success">
                                <th class="w-50px">#</th>
                                <th class="w-150px">Entry date</th>
                                <th>Payee Name</th>
                                <th class="w-150px">Cheque date</th>
                                <th>bank Name</th>
                                <th class="w-150px">Cheque Number</th>  
                                <th class="w-150px">Amount</th>   
                            </tr>
                        </thead>
                        <tbody id="installmentTableBody">
                            
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" class="text-end"><b>Total</b></td>
                                <td id="installmentsTotal" class="text-end fw-bold pe-12"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="card-footer text-center">
                <button type="button" class="btn btn-success" id="generate_installments">Generate</button>
                <button type="submit" class="btn btn-success" disabled="disabled">Save</button>
            </div>
            @endif
        </div>  
    </form>
</table> 
</div>
       
@endsection  

@push('scripts')
<script>
$(function() {
    route.push('contract.installment.details', '{{ rawRoute("contract.installment.details") }}');

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

    const banks = @json($banks);
    const parsleyForm = $("#create_installment_form").parsley();

    $('#generate_installments').on('click', function () {
        parsleyForm.whenValidate({
            group: 'stage-1',
            force: true
        })
            .then(function ()  {
                return ajaxRequest({
                    method: 'GET',
                    url: route('contract.installment.details'),
                    data: parsleyForm.$element
                        .serializeArray()
                        .reduce((acc, curr) => ({...acc, [curr.name]: curr.value}), {})
                }).catch(defaultErrorHandler);
            })
            .then(function(response, textStatus, xhr) {
                if (!response.installments) {
                    return defaultErrorHandler(xhr);
                }

                let html = '';
                response.installments.forEach((installment, k)=> {
                    const _banks = banks.map(bank => `<option value="${bank.id}" ${bank.id == installment.bank_id ? 'selected' : ''}>${bank.name}</option>`).join("\n");

                    html += (
                        `<tr>
                            <td>
                                <input
                                    type="text"
                                    name="details[${k}][installment_number]"
                                    value="${installment.installment_number}"
                                    class="form-control-plaintext text-end"
                                    readonly>
                            </td>
                            <td>
                                <input
                                    type="text"
                                    name="details[${k}][entry_date]"
                                    value="${installment.entry_date}"
                                    class="form-control-plaintext text-center"
                                    readonly>
                            </td>
                            <td>
                                <input
                                    type="text"
                                    name="details[${k}][payee_name]"
                                    value="${installment.payee_name}"
                                    class="form-control"
                                    required>
                            </td>
                            <td>
                                <input
                                    type="text"
                                    name="details[${k}][due_date]"
                                    value="${installment.due_date}"
                                    class="form-control text-center"
                                    data-provide="datepicker"
                                    data-date-format="{{ getBSDatepickerDateFormat() }}"
                                    data-date-clear-btn="true"
                                    data-date-autoclose="true"
                                    data-parsley-trigger-after-failure="change"
                                    required>
                            </td>
                            <td>
                                <select
                                    class="form-select"
                                    name="details[${k}][bank_id]"
                                    data-control="select2"
                                    required>
                                    <option value="">--SELECT--</option>
                                    ${_banks}
                                </select>
                            </td>
                            <td>
                                <input
                                    type="text"
                                    name="details[${k}][cheque_no]"
                                    value="${installment.cheque_no}"
                                    class="form-control text-center"
                                    required>
                            </td>
                            <td>
                                <input
                                    data-installment-amount
                                    type="number"
                                    name="details[${k}][amount]"
                                    value="${installment.amount}"
                                    class="form-control text-end">
                            </td>    
                        </tr>`
                    )
                });

                $('#installmentTableBody').html(html);
                updateTotalInstallment();
                $('#installmentTableBody [data-control="select2"]').select2();
                $('button[type="submit"]').prop("disabled", false); 
            });
    });

    $('#no_installment, #start_date, #initial_check_num, #interval_unit, #interval, #bank_id, #transaction_date').on('change', function(){
        $('button[type="submit"]').prop("disabled", true); 
    });

    $('#installmentTableBody').on('change', '[data-installment-amount]', updateTotalInstallment);

    function updateTotalInstallment () {
        const installments = document.querySelectorAll('#installmentTableBody [data-installment-amount]');
        
        let totalInstallment = 0;
        for (i = 0; i < installments.length; ++i) {
            totalInstallment  += (parseFloat(installments[i].value) || 0);
        }

        document.querySelector('#installmentsTotal').textContent = totalInstallment.toFixed(2);
    }

    parsleyForm.on('form:submit', function(e) {
        var form = parsleyForm.element;
        var formData = new FormData(form);
        ajaxRequest({
            method: 'post',
            url: form.action,
            data: formData,
            processData: false,
            contentType: false,
        }).done(function (response, textStatus, xhr) {
            if (!response.message) {
                return defaultErrorHandler(xhr);
            }
            toastr.success(response.message);
            form.reset();
            $('button[type="submit"]').prop("disabled", true); 
            $('#installmentTableBody').html('');
            updateTotalInstallment();
            window.location = '{{ route('contract.index') }}';
        }).fail(defaultErrorHandler);

        return false;
    });

    parsleyForm.$element.on('reset', () => {
        parsleyForm.reset();
        setTimeout(() => {
            $('select').trigger('change.select2');
            $('[data-parsley-trigger-after-failure]').datepicker('update').trigger('changeDate').trigger('change')
        }, 0);
    })
});
</script>
@endpush