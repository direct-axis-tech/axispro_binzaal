<div id="mark-completion-model"
    class="modal fade"
    data-bs-backdrop="static"
    tabindex="-1"
    aria-labelledby="mark-completion-model-label"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <!-- Modal content-->
        <div class="modal-content">
            <form action="" method="post" id="completion_form">
                <div class="modal-header">
                    <h4 class="modal-title" id="mark-completion-model-label">Mark transaction #<span id="lbl_line_ref"></span> as completed</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @csrf
                    
                    <input required type="hidden" name="line_reference">

                    <div class="form-group row mb-1">
                        <label class="col-sm-4 col-form-label" for="lbl_customer">Customer</label>
                        <div class="col-sm-8">
                            <input
                                type="text"
                                class="form-control-plaintext form-control-sm px-4"
                                id="lbl_customer"
                                readonly>
                        </div>
                    </div>

                    <div class="form-group row mb-1">
                        <label class="col-sm-4 col-form-label" for="lbl_stock_name">Service Name</label>
                        <div class="col-sm-8">
                            <input
                                type="text"
                                class="form-control-plaintext form-control-sm px-4"
                                id="lbl_stock_name"
                                readonly>
                        </div>
                    </div>
                    
                    <div class="form-group row mb-1">
                        <label class="col-sm-4 col-form-label" for="lbl_ref_name">Ref Name</label>
                        <div class="col-sm-8">
                            <input
                                type="text"
                                class="form-control-plaintext form-control-sm px-4"
                                id="lbl_ref_name"
                                readonly>
                        </div>
                    </div>

                    <div class="form-group row mb-1">
                        <label class="col-sm-4 col-form-label" for="transaction_id">Transaction ID</label>
                        <div class="col-sm-8">
                            <input
                                type="text"
                                class="form-control form-control-sm"
                                id="transaction_id"
                                name="transaction_id">
                        </div>
                    </div>

                    <div class="form-group row mb-1 d-none">
                        <label class="col-sm-4 col-form-label" for="lbl_govt_fee">Govt Fee</label>
                        <div class="col-sm-8">
                            <input
                                type="text"
                                class="form-control-plaintext form-control-sm px-4"
                                id="lbl_govt_fee"
                                readonly>
                        </div>
                    </div>
                    
                    <div class="form-group row mb-1 d-none">
                        <label class="col-sm-4 col-form-label" for="govt_bank_account">Govt Bank Account</label>
                        <div class="col-sm-8">
                            <select data-control="select2" name="govt_bank_account" id="govt_bank_account" class="form-select form-select-sm">
                                <option value="">-- All --</option>
                                @foreach (\App\Models\Accounting\Ledger::active()->get() as $c)
                                <option value="{{ $c->account_code }}">{{ $c->formatted_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row mb-1">
                        <label class="col-sm-4 col-form-label" for="delivery_date">Completion Date</label>
                        <div class="col-sm-8">
                            <input
                                type="text"
                                required
                                data-parsley-trigger-after-failure="change"
                                data-parsley-date="{{ dateformat('momentJs') }}"
                                data-control="bsDatepicker"
                                class="form-control form-control-sm"
                                data-date-today-btn="linked"
                                autocomplete="off"
                                name="delivery_date"
                                id="delivery_date"
                                value="{{ Today() }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer w-100 text-center">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <span class="fa fa-check fs-3 pe-2"></span>Mark as Completed
                    </button>
                    <button type="reset" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(function () {
        window.Transaction = window.Transaction || {};

        const modalEl = document.getElementById('mark-completion-model');
        const modal = new bootstrap.Modal(modalEl);
        const form = document.getElementById('completion_form')
        const parsleyCompletionForm = $(form).parsley();
        const numberFormatter = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: {{ user_price_dec() }},
            maximumFractionDigits: {{ user_price_dec() }},
        });

        parsleyCompletionForm.on('form:submit', function() {
            ajaxRequest({
                method: 'post',
                url: url('/ERP/sales/order_detail.php', { action: 'complete' }),
                data: new FormData(form),
                processData: false,
                contentType: false
            }).done(function(data, textStatus, xhr) {
                if (xhr && xhr.status == 204) {
                    toastr.success(`Transaction ${form.elements.namedItem('line_reference').value} is completed successfully`);
                    window.OrderDetailsDataTable.ajax.reload();
                    modal.hide()
                    return;
                }

                handleError(xhr);
            }).fail(handleError);

            return false;
        });

        parsleyCompletionForm.$element.on('reset', function() {
            parsleyCompletionForm.reset();
            setTimeout(() => {
                $('[data-parsley-trigger-after-failure]')
                    .datepicker('update')
                    .trigger('changeDate')
                    .trigger('change');

                $('[data-control="select2"]')
                    .val('')
                    .trigger('change.select2');
            }, 0);
        })

        modalEl.addEventListener('hidden.bs.modal', function (event) {
            form.elements.namedItem('line_reference').value = '';
            form.reset();
        })

        function populateForm(transaction) {
            form.elements.namedItem('line_reference').value = transaction.line_reference;
            document.getElementById('lbl_line_ref').textContent = transaction.line_reference;
            document.getElementById('lbl_customer').value = transaction.formatted_customer_name;
            document.getElementById('lbl_stock_name').value = transaction.formatted_stock_name;
            document.getElementById('lbl_ref_name').value = transaction.ref_name || '--';
            form.elements.namedItem('transaction_id').value = '';

            let govtBankAccountEl = form.elements.namedItem('govt_bank_account');
            let govtFeeEl = document.getElementById('lbl_govt_fee');
            
            $(govtBankAccountEl).val(transaction.govt_bank_account).trigger('change.select2');

            if (transaction.costing_method != '{{ COSTING_METHOD_EXPENSE }}') {
                govtBankAccountEl.closest('.form-group').classList.remove('d-none');
                govtFeeEl.closest('.form-group').classList.remove('d-none');

                let nonTaxableAmount = parseFloat(transaction.non_taxable_amount) || 0;
                if (nonTaxableAmount != 0) {
                    govtBankAccountEl.required = true;
                }

                govtFeeEl.value = numberFormatter.format(transaction.non_taxable_amount);
            }

            else {
                govtBankAccountEl.closest('.form-group').classList.add('d-none');
                govtFeeEl.closest('.form-group').classList.add('d-none');
                govtBankAccountEl.removeAttribute('required');
                govtFeeEl.value = numberFormatter.format(0);
            }
        }

        function handleError(xhr) {
            defaultErrorHandler(xhr);
        }

        function initiateCompletionProcess(transaction) {
            populateForm(transaction);
            modal.show();
        }

        // Expose the function for initiating the completion process
        window.Transaction.initiateCompletionProcess = initiateCompletionProcess;
    })
</script>
@endpush