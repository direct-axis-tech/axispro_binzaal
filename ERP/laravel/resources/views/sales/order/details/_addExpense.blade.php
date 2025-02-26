<div id="add-expense-model"
    class="modal fade"
    data-bs-backdrop="static"
    tabindex="-1"
    aria-labelledby="add-expense-model-label"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered mw-550px">
        <!-- Modal content-->
        <div class="modal-content">
            <form action="" method="post" id="expense_addition_form">
                <div class="modal-header">
                    <h4 class="modal-title" id="add-expense-model-label">Add expense against transaction #<span id="expense--lbl_line_ref"></span></h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @csrf
                    
                    <input required type="hidden" name="line_reference">

                    <div class="form-group row mb-1">
                        <label class="col-sm-4 col-form-label" for="stock_id">Service</label>
                        <div class="col-sm-8">
                            <select
                                required
                                data-placeholder="-- select --"
                                data-control="select2"
                                name="stock_id"
                                class="form-control form-control-sm">
                                <option value="" selected>-- select --</option>
                                @foreach (
                                    \App\Models\Inventory\StockItem::active()
                                        ->where('no_purchase', 0)
                                        ->where('mb_flag', '<>', STOCK_TYPE_FIXED_ASSET)
                                        ->get() as $s
                                )
                                <option value="{{ $s->stock_id }}">{{ $s->formatted_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row mb-1">
                        <label class="col-sm-4 col-form-label" for="expense_date">Expense Date</label>
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
                                name="expense_date"
                                id="expense_date"
                                value="{{ Today() }}">
                        </div>
                    </div>

                    <div class="form-group row mb-1">
                        <label class="col-sm-4 col-form-label" for="supp_ref">Supplier Invoice #</label>
                        <div class="col-sm-8">
                            <input
                                type="text"
                                name="supp_ref"
                                data-parsley-pattern2="/^[\p{L}\p{M}\p{N}_\- /]*$/u"
                                data-parsley-pattern2-message="Special characters except [underscore], [hyphen], [space], [slash] are not allowed"
                                class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="form-group row mb-1">
                        <label class="col-sm-4 col-form-label" for="supplier_id">Supplier</label>
                        <div class="col-sm-8">
                            <select
                                required
                                data-allow-clear="true"
                                data-placeholder="-- select --"
                                data-control="select2"
                                name="supplier_id"
                                id="supplier_id"
                                class="form-select form-select-sm">
                                <option value="" selected>-- select --</option>
                                @foreach (\App\Models\Purchase\Supplier::active()->get() as $s)
                                <option value="{{ $s->supplier_id }}">{{ $s->formatted_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row mb-1 d-none">
                        <div class="col-sm-4 col-form-label">Supplier Tax Status</div>
                        <div class="col-sm-8">
                            <div class="form-check form-check-sm mb-2">
                                <input class="form-check-input form-check-input-sm" type="checkbox" value="" id="lbl_supplier_taxable" disabled>
                                <label class="form-check-label" for="is_taxable">Taxable</label>
                            </div>
                            <div class="form-check form-check-sm mb-2">
                                <input class="form-check-input form-check-input-sm" type="checkbox" value="" id="lbl_supplier_price_includes_tax" disabled>
                                <label class="form-check-label" for="is_taxable">Price Includes Tax</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row mb-1">
                        <label class="col-sm-4 col-form-label" for="qty">Qty</label>
                        <div class="col-sm-8">
                            <input
                                required
                                readonly
                                data-parsley-min="0"
                                data-parsley-type="number"
                                type="text"
                                name="qty"
                                class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="form-group row mb-1">
                        <label class="col-sm-4 col-form-label" for="govt_fee">Govt Fee</label>
                        <div class="col-sm-8">
                            <input
                                required
                                data-parsley-min="0"
                                data-parsley-type="number"
                                type="text"
                                name="govt_fee"
                                class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="form-group row mb-1">
                        <label class="col-sm-4 col-form-label" for="price">Service Chg</label>
                        <div class="col-sm-8">
                            <div class="input-group input-group-sm">
                                <input
                                    required
                                    data-parsley-min="0"
                                    data-parsley-type="number"
                                    type="text"
                                    name="price"
                                    class="form-control form-control-sm">
                                <input
                                    data-tax=""
                                    type="text"
                                    id="lbl_tax"
                                    class="form-control-plaintext form-control-sm px-2 mw-100px border"
                                    readonly>
                                <span class="input-group-text">Vat</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group row mb-1">
                        <label class="col-sm-4 col-form-label" for="supp_commission">
                            Commission <span id="expense--extra-info"></span>
                        </label>
                        <div class="col-sm-8">
                            <input
                                required
                                data-parsley-min="0"
                                data-parsley-type="number"
                                type="text"
                                name="supp_commission"
                                class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="form-group row mb-1 d-none">
                        <div class="col-sm-4 col-form-label"></div>
                        <div class="col-sm-8">
                            <div class="form-check form-check-sm">
                                <input class="form-check-input form-check-input-sm" type="checkbox" value="1" name="is_taxable">
                                <label class="form-check-label" for="is_taxable">Taxable</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row mb-1">
                        <label class="col-sm-4 col-form-label" for="comments">Comments</label>
                        <div class="col-sm-8">
                            <textarea
                                data-parsley-pattern2="/^[\p{L}\p{M}\p{N}_\- .:,'/]*$/u"
                                data-parsley-pattern2-message="Special characters except [underscore], [hyphen], [space], [dot], [colon], [single quote], [slash] are not allowed"
                                type="text"
                                name="comments"
                                class="form-control form-control-sm px-2"></textarea>
                        </div>
                    </div>

                    <div class="form-group row mb-1">
                        <label class="col-sm-4 col-form-label" for="lbl_payable">Total Payable</label>
                        <div class="col-sm-8">
                            <input
                                data-value=""
                                type="text"
                                id="lbl_payable"
                                class="form-control-plaintext form-control-sm px-2 text-danger"
                                readonly>
                        </div>
                    </div>

                    <div class="form-group row mb-1">
                        <label class="col-sm-4 col-form-label" for="payment_account">Payment</label>
                        <div class="col-sm-8">
                            <select
                                data-allow-clear="true"
                                data-placeholder="Delayed"
                                data-control="select2"
                                name="payment_account"
                                class="form-select form-select-sm">
                                <option value="" selected>Delayed</option>
                                @foreach (\App\Models\Accounting\BankAccount::active()->get() as $b)
                                <option value="{{ $b->id }}">{{ $b->formatted_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="collapse" id="paymentDetailsCollapse">
                        <div class="form-group row mb-1">
                            <label class="col-sm-4 col-form-label" for="payment_date">Payment Date</label>
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
                                    name="payment_date"
                                    id="payment_date"
                                    value="{{ Today() }}">
                            </div>
                        </div>

                        <div class="form-group row mb-1">
                            <label class="col-sm-4 col-form-label" for="paying_amt">Paying Amount</label>
                            <div class="col-sm-8">
                                <input
                                    type="text"
                                    data-parsley-min="0"
                                    data-parsley-type="number"
                                    name="paying_amt"
                                    class="form-control form-control-sm">
                            </div>
                        </div>

                        <div class="form-group row mb-1">
                            <label class="col-sm-4 col-form-label" for="payment_ref">Payment Reference</label>
                            <div class="col-sm-8">
                                <input
                                    type="text"
                                    data-parsley-pattern2="/^[\p{L}\p{M}\p{N}]*$/u"
                                    data-parsley-pattern2-message="Only alphabets and numbers are allowed"
                                    class="form-control form-control-sm"
                                    id="payment_ref"
                                    name="payment_ref">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer w-100 text-center">
                    @if ($hasCwEPermission = authUser()->hasPermission(\App\Permissions::SA_SALESLNCMPLTWEXP))
                    <button type="submit" name="process" value="AddExpenseAndCompleteTransaction" class="btn btn-sm btn-primary">
                        <span class="fa fa-paper-plane pe-2"></span>Add Expense & Mark as Completed
                    </button>
                    @endif
                    @if (!$hasCwEPermission || authUser()->hasPermission(\App\Permissions::SA_SALESLNEXPONLY))
                    <button type="submit" name="process" value="AddExpense" class="btn btn-sm btn-primary">
                        <span class="fa fa-dollar-sign pe-2"></span>Add Expense
                    </button>
                    @endif
                    <button type="reset" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    
    $(document).ready(function() {
        $("#supplier_id").select2({
            dropdownParent: $("#add-expense-model")
        });
    });
    
    $(function () {
        window.Transaction = window.Transaction || {};

        const modalEl = document.getElementById('add-expense-model');
        const modal = new bootstrap.Modal(modalEl);
        const paymentCollapse = new bootstrap.Collapse(document.getElementById('paymentDetailsCollapse'), {toggle: false});
        const form = document.getElementById('expense_addition_form')
        const parsleyExpenseAdditionForm = $(form).parsley();
        const numberFormatter = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: {{ user_price_dec() }},
            maximumFractionDigits: {{ user_price_dec() }},
        });

        $('[name="stock_id"]').on('change', function () {
            // Clear the existing supplier to avoid race conditions
            // and multiple api calls to getItemInfo
            $(form.elements.namedItem('supplier_id')).val('').trigger('change.select2');

            // auto populate supplier by stockid
            let stock_id = form.elements.namedItem('stock_id').value;
            if (stock_id.length) {
                ajaxRequest({
                    method: 'post',
                    url: url('/ERP/purchasing/transaction_expense.php', { action: 'getLastPurchaseDetails' }),
                    data: {stock_id}
                }).done(function(respJson, textStatus, xhr) {
                    if (respJson.data && respJson.data.supplier_id)
                        $(form.elements.namedItem('supplier_id')).val(respJson.data.supplier_id).trigger('change'); 
                }).fail(defaultErrorHandler);
            }
        });

        // Update the item and tax info of supplier when there is a change
        $('[name="supplier_id"], [name="stock_id"]').on('change', function () {
            let supplier_id = form.elements.namedItem('supplier_id').value;
            let stock_id = form.elements.namedItem('stock_id').value;
            const after_tax_label = ' after tax';
            let label_for_price = document.querySelector('label[for="price"]');
            let label_for_comm = document.getElementById('expense--extra-info');

            if (!supplier_id.length || !stock_id.length) {
                form.elements.namedItem('lbl_supplier_taxable').checked = false;
                form.elements.namedItem('lbl_supplier_price_includes_tax').checked = false;
                form.elements.namedItem('is_taxable').checked = false;
                form.elements.namedItem('supp_commission').value = 0;

                label_for_comm.textContent = 'AED';
                if (label_for_price.textContent.endsWith(after_tax_label)) {
                    label_for_price.textContent = label_for_price.textContent.substring(0, label_for_price.textContent.indexOf(after_tax_label));
                }

                $(form.elements.namedItem('is_taxable')).trigger('change');
                return;
            }

            ajaxRequest({
                method: 'post',
                url: url('/ERP/purchasing/transaction_expense.php', { action: 'getItemInfo' }),
                data: {supplier_id, stock_id}
            }).done(function(data, textStatus, xhr) {
                if (!data.taxInfo) {
                    return defaultErrorHandler(xhr);
                }

                let isTaxable = data.taxInfo.rate > 0;

                form.elements.namedItem('lbl_supplier_taxable').checked = isTaxable;
                form.elements.namedItem('lbl_supplier_price_includes_tax').checked = data.taxInfo.tax_included;
                form.elements.namedItem('is_taxable').checked = isTaxable;
                form.elements.namedItem('price').value = data.itemInfo.purchase_price;
                form.elements.namedItem('govt_fee').value = data.itemInfo.govt_fee;

                form.elements.namedItem('supp_commission').value = data.discountInfo.commission;
                label_for_comm.textContent = (data.discountInfo.comm_calc_method == '{{ CCM_PERCENTAGE }}') ? '%' : 'AED';

                if (data.taxInfo.tax_included && !label_for_price.textContent.endsWith(after_tax_label)) {
                    label_for_price.textContent = label_for_price.textContent + after_tax_label;
                }

                $(document.querySelector('[data-tax]')).trigger('change:taxValue');
                $(form.elements.namedItem('price')).trigger('change');
                return;
            }).fail(defaultErrorHandler);
        })

        // Trigger tax recalculation on change in any of the factors that affect the tax
        $('[name="supplier_id"], [name="stock_id"], [name="price"]').on('change', function () {
            let supplier_id = form.elements.namedItem('supplier_id').value;
            let stock_id = form.elements.namedItem('stock_id').value;
            let price = parseFloat(form.elements.namedItem('price').value) || 0;
            let taxInput = document.querySelector('[data-tax]');

            if (
                   !supplier_id.length
                || !stock_id.length
                || price == 0
            ) {
                taxInput.dataset.tax = 0;
                $(taxInput).trigger('change:taxValue');
                return;
            }

            ajaxRequest({
                method: 'post',
                url: url('/ERP/purchasing/transaction_expense.php', { action: 'calculateTax' }),
                data: {supplier_id, stock_id, price}
            }).done(function(data, textStatus, xhr) {
                if (data.tax === undefined) {
                    return defaultErrorHandler(xhr);
                }

                taxInput.dataset.tax = data.tax;
                $(taxInput).trigger('change:taxValue');
            }).fail(defaultErrorHandler);
        })

        $('[name="is_taxable"]').on('change', function () {
            $('[data-tax]').trigger('change:taxValue');
        })

        // Update the actual tax value when there is a change
        $('[data-tax]').on('change:taxValue', function() {
            let taxInput = this;
            let taxIncluded = form.elements.namedItem('lbl_supplier_price_includes_tax').checked;
            let isTaxable = form.elements.namedItem('is_taxable').checked;
            let tax = parseFloat(isTaxable ? taxInput.dataset.tax : 0) || 0;

            let taxLabel = taxIncluded ? `Inc. Tax (${numberFormatter.format(tax)})` : numberFormatter.format(tax);
            $(taxInput).val(taxLabel).trigger('change');
        })

        // Update the total payable
        $('[name="price"], [name="govt_fee"], [name="qty"], [data-tax]').on('change', function () {
            let price = parseFloat(form.elements.namedItem('price').value) || 0;
            let govtFee = parseFloat(form.elements.namedItem('govt_fee').value) || 0;
            let tax = parseFloat(document.querySelector('[data-tax]').dataset.tax) || 0;
            let qty = parseFloat(form.elements.namedItem('qty').value) || 0;
            
            let taxIncluded = form.elements.namedItem('lbl_supplier_price_includes_tax').checked;
            let isTaxable = form.elements.namedItem('is_taxable').checked;

            let total = price + govtFee;
            
            if (isTaxable && !taxIncluded) {
                total += tax;
            }

            if (!isTaxable && taxIncluded) {
                total -= tax;
            }

            total = round(total * qty, {{ user_price_dec() }});

            form.elements.namedItem('lbl_payable').dataset.value = total;
            form.elements.namedItem('lbl_payable').value = numberFormatter.format(total);
            $(form.elements.namedItem('lbl_payable')).trigger('change');
        });

        // Set the maximum payment that can be made
        $('#lbl_payable').on('change', function () {
            let totalPayable = parseFloat(this.dataset.value) || 0;
            form.elements.namedItem('paying_amt').setAttribute('data-parsley-max', totalPayable);
            form.elements.namedItem('paying_amt').value = totalPayable;
        })

        // Handle the payment changes
        $('[name="payment_account"]').on('change', function () {
            let account = this.value;

            paymentCollapse[account.length ? 'show' : 'hide']();
            form.elements.namedItem('payment_date').required = !!account.length;
            form.elements.namedItem('paying_amt').required = !!account.length;
        })

        parsleyExpenseAdditionForm.on('form:submit', function(parsleyFormInstance) {
            const data = new FormData(form);
            const submitter = parsleyFormInstance.submitEvent.originalEvent.submitter;
            data.set(submitter.name, submitter.value);

            ajaxRequest({
                method: 'post',
                url: url('/ERP/purchasing/transaction_expense.php', { action: 'addExpense' }),
                data: data,
                processData: false,
                contentType: false
            }).done(function(data, textStatus, xhr) {
                if (xhr && xhr.status == 204) {
                    toastr.success(`Expense against transaction #${form.elements.namedItem('line_reference').value} added successfully`);
                    window.OrderDetailsDataTable.ajax.reload();
                    modal.hide()
                    return;
                }

                defaultErrorHandler(xhr);
            }).fail(defaultErrorHandler);

            return false;
        });

        parsleyExpenseAdditionForm.$element.on('reset', function() {
            parsleyExpenseAdditionForm.reset();
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

        function initiateExpenseAdditionProcess(transaction) {
            document.getElementById('expense--lbl_line_ref').textContent = transaction.line_reference;
            form.elements.namedItem('qty').value = transaction.qty_not_sent;
            form.elements.namedItem('line_reference').value = transaction.line_reference;
            if ((parseInt(transaction.qty_expensed) || 0) == 0) {
                $(form.elements.namedItem('stock_id')).val(transaction.stock_id).trigger('change');
            }
            modal.show();
        }

        window.Transaction.initiateExpenseAdditionProcess = initiateExpenseAdditionProcess;
    })
</script>
@endpush