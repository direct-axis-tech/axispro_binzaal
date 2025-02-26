<div class="collapse" id="supp_inv_collapse">
    <div class="form-check form-check-sm mb-7 mt-7">
        <input
            class="form-check-input"
            type="checkbox"
            value="1"
            name="auto_supplier_invoice"
            id="supp_inv_collapse__make_invoice">
        <label class="form-check-label" for="supp_inv_collapse__make_invoice">
            Create Supplier Invoice Automatically
        </label>
    </div>

    <div class="collapse p-3 border border-dashed" id="supp_inv_collapse__sub_collapse">
        <div class="form-group row mb-1">
            <label class="col-sm-4 col-form-label" for="purchase_stock_id">Service</label>
            <div class="col-sm-8">
                <select
                    data-parsley-required-with="#supp_inv_collapse__make_invoice"
                    data-placeholder="-- select --"
                    data-control="select2"
                    name="purchase_stock_id"
                    class="form-control form-control-sm">
                    <option value="" selected>-- select --</option>
                </select>
            </div>
        </div>
    
        <div class="form-group row mb-1">
            <label
                class="col-sm-4 col-form-label"
                for="supp_inv_collapse__purchase_date">
                Purchase Date
            </label>
            <div class="col-sm-8">
                <input
                    type="text"
                    data-parsley-required-with="#supp_inv_collapse__make_invoice"
                    data-parsley-trigger-after-failure="change"
                    data-parsley-date="{{ dateformat('momentJs') }}"
                    data-control="bsDatepicker"
                    class="form-control form-control-sm"
                    autocomplete="off"
                    name="purchase_date"
                    id="supp_inv_collapse__purchase_date"
                    value="{{ Today() }}">
            </div>
        </div>
    
        <div class="form-group row mb-1">
            <label
                class="col-sm-4 col-form-label"
                for="supp_ref">
                Supplier Invoice #
            </label>
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
            <label
                class="col-sm-4 col-form-label"
                for="supp_inv_collapse__supplier_id">
                Supplier
            </label>
            <div class="col-sm-8">
                <select
                    data-parsley-required-with="#supp_inv_collapse__make_invoice"
                    data-allow-clear="true"
                    data-placeholder="-- select --"
                    data-control="select2"
                    name="supplier_id"
                    id="supp_inv_collapse__supplier_id"
                    class="form-select form-select-sm">
                    <option value="" selected>-- select --</option>
                </select>
            </div>
        </div>
    
        <div class="form-group row mb-1 d-none">
            <div class="col-sm-4 col-form-label">Supplier Tax Status</div>
            <div class="col-sm-8">
                <div class="form-check form-check-sm mb-2">
                    <input
                        class="form-check-input form-check-input-sm"
                        type="checkbox"
                        value=""
                        id="supp_inv_collapse__lbl_supplier_taxable"
                        disabled>
                    <label
                        class="form-check-label"
                        for="supp_inv_collapse__lbl_supplier_taxable">
                        Taxable
                    </label>
                </div>
                <div class="form-check form-check-sm mb-2">
                    <input
                        class="form-check-input form-check-input-sm"
                        type="checkbox"
                        value=""
                        id="supp_inv_collapse__lbl_supplier_price_includes_tax"
                        disabled>
                    <label
                        class="form-check-label"
                        for="supp_inv_collapse__lbl_supplier_price_includes_tax">
                        Price Includes Tax
                    </label>
                </div>
            </div>
        </div>
    
        <div class="form-group row mb-1">
            <label class="col-sm-4 col-form-label" for="purchase_price">Purchase Price</label>
            <div class="col-sm-8">
                <div class="input-group input-group-sm">
                    <input
                        data-parsley-required-with="#supp_inv_collapse__make_invoice"
                        data-parsley-type="number"
                        type="text"
                        name="purchase_price"
                        class="form-control form-control-sm">
                    <input
                        data-tax=""
                        type="text"
                        id="supp_inv_collapse__lbl_tax"
                        class="form-control-plaintext form-control-sm px-2 mw-100px border"
                        readonly>
                    <span class="input-group-text">Vat</span>
                </div>
            </div>
        </div>
        
        <div class="form-group row mb-1 d-none">
            <div class="col-sm-4 col-form-label"></div>
            <div class="col-sm-8">
                <div class="form-check form-check-sm">
                    <input
                        class="form-check-input form-check-input-sm"
                        type="checkbox"
                        value="1"
                        name="supp_is_taxable">
                    <label
                        class="form-check-label"
                        for="supp_is_taxable">
                        Taxable
                    </label>
                </div>
            </div>
        </div>
    
        <div class="form-group row mb-1">
            <label class="col-sm-4 col-form-label" for="supp_inv_comments">Comments</label>
            <div class="col-sm-8">
                <textarea
                    data-parsley-pattern2="/^[\p{L}\p{M}\p{N}_\- .:,'/]*$/u"
                    data-parsley-pattern2-message="Special characters except [underscore], [hyphen], [space], [dot], [colon], [single quote], [slash] are not allowed"
                    type="text"
                    name="supp_inv_comments"
                    class="form-control form-control-sm px-2"></textarea>
            </div>
        </div>
    
        <div class="form-group row mb-1">
            <label class="col-sm-4 col-form-label" for="supp_inv_collapse__lbl_payable">Total Payable</label>
            <div class="col-sm-8">
                <input
                    data-value=""
                    type="text"
                    id="supp_inv_collapse__lbl_payable"
                    class="form-control-plaintext form-control-sm px-2 text-danger"
                    readonly>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
route.push('dimension.find', '{{ rawRoute('dimension.find') }}');
$(function () {
    window.LabourContract = window.LabourContract || new EventTarget();
    window.SupplierInvoice = window.SupplierInvoice || new EventTarget();

    const numberFormatter = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: {{ user_price_dec() }},
        maximumFractionDigits: {{ user_price_dec() }},
    });
    const after_tax_label = ' after tax';
    const masterCheckbox = document.getElementById('supp_inv_collapse__make_invoice');
    let listener = null;

    SupplierInvoice.Collapse = bootstrap.Collapse.getOrCreateInstance(document.querySelector('#supp_inv_collapse'), {toggle: false});
    SupplierInvoice.SubCollapse = bootstrap.Collapse.getOrCreateInstance(document.querySelector('#supp_inv_collapse__sub_collapse'), {toggle: false});

    SupplierInvoice.SubCollapse.resetForm = () => {
        $('[name="purchase_stock_id"]').val('').trigger('change');
        $('[name="purchase_price"]').val('').trigger('change');
        $('[name="supp_inv_comments"]').text('');
    }

    SupplierInvoice.initialize = function (contract_id, dimension_id) {
        if (listener) {
            masterCheckbox.removeEventListener('change', listener);
        }

        listener = () => {
            if (!masterCheckbox.checked) {
                SupplierInvoice.SubCollapse.hide();
                return;
            }

            SupplierInvoice.SubCollapse.show();

            ajaxRequest(url('ERP/sales/labour_contract.php', {
                action: 'getSupplierInvoiceInfo',
                contract_id
            }))
            .done((respJson, msg, xhr) => {
                if (!respJson.data) {
                    return defaultErrorHandler(xhr);
                }

                populateDefaults(respJson.data);
            })
            .fail(defaultErrorHandler)
        }

        masterCheckbox.addEventListener('change', listener);

        SupplierInvoice.Collapse.show();

        ajaxRequest(route('dimension.find', {dimension: dimension_id}))
            .done((respJson, msg, xhr) => {
                if (typeof respJson.auto_purchase_maid == 'undefined') {
                    return defaultErrorHandler(xhr);
                }

                if (respJson.auto_purchase_maid) {
                    masterCheckbox.checked = true;
                    masterCheckbox.dispatchEvent(new Event('change', { bubbles: true }));
                }
            })
            .fail(defaultErrorHandler);
    }

    $('[name="purchase_stock_id"], [name="supplier_id"]').on('change', function () {
        let supplier_id = $('[name="supplier_id"]').val();
        let stock_id = $('[name="purchase_stock_id"]').val();
        let label_for_price = document.querySelector('label[for="purchase_price"]');

        if (!supplier_id.length || !stock_id.length) {
            $('#supp_inv_collapse__lbl_supplier_taxable').prop("checked", false);
            $('#supp_inv_collapse__lbl_supplier_price_includes_tax').prop("checked", false);
            $('[name="supp_is_taxable"]').prop("checked", false);

            if (label_for_price.textContent.endsWith(after_tax_label)) {
                label_for_price.textContent = label_for_price.textContent.substring(0, label_for_price.textContent.indexOf(after_tax_label));
            }

            $('[name="supp_is_taxable"]').trigger('change');
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

            $('#supp_inv_collapse__lbl_supplier_taxable').prop("checked", isTaxable);
            $('#supp_inv_collapse__lbl_supplier_price_includes_tax').prop("checked", data.taxInfo.tax_included);
            $('[name="supp_is_taxable"]').prop("checked", isTaxable);
            $('[name="purchase_price"]').val(data.itemInfo.purchase_price);

            if (data.taxInfo.tax_included && !label_for_price.textContent.endsWith(after_tax_label)) {
                label_for_price.textContent = label_for_price.textContent + after_tax_label;
            }

            $(document.querySelector('#supp_inv_collapse [data-tax]')).trigger('change:taxValue');
            $($('[name="purchase_price"]')).trigger('change');
            return;
        }).fail(defaultErrorHandler);
    })

    // Trigger tax recalculation on change in any of the factors that affect the tax
    $('[name="purchase_stock_id"], [name="supplier_id"], [name="purchase_price"]').on('change', function () {
        let supplier_id = $('[name="supplier_id"]').val();
        let stock_id = $('[name="purchase_stock_id"]').val();
        let price = parseFloat($('[name="purchase_price"]').val()) || 0;
        let taxInput = document.querySelector('#supp_inv_collapse [data-tax]');

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

    $('[name="supp_is_taxable"]').on('change', function () {
        $('#supp_inv_collapse [data-tax]').trigger('change:taxValue');
    })

    $('#supp_inv_collapse [data-tax]').on('change:taxValue', function () {
        let taxInput = this;
        let taxIncluded = $('#supp_inv_collapse__lbl_supplier_price_includes_tax').prop("checked");
        let isTaxable = $('[name="supp_is_taxable"]').prop("checked");
        let tax = parseFloat(isTaxable ? taxInput.dataset.tax : 0) || 0;

        let taxLabel = taxIncluded ? `Inc. Tax (${numberFormatter.format(tax)})` : numberFormatter.format(tax);
        $(taxInput).val(taxLabel).trigger('change');
    })

    // Update the total payable
    $('[name="purchase_price"], #supp_inv_collapse [data-tax]').on('change', function () {
        let price = parseFloat($('[name="purchase_price"]').val()) || 0;
        let tax = parseFloat(document.querySelector('#supp_inv_collapse [data-tax]').dataset.tax) || 0;
        
        let taxIncluded = $('#supp_inv_collapse__lbl_supplier_price_includes_tax').prop("checked");
        let isTaxable = $('[name="supp_is_taxable"]').prop("checked");

        let total = price;
        
        if (isTaxable && !taxIncluded) {
            total += tax;
        }

        if (!isTaxable && taxIncluded) {
            total -= tax;
        }

        total = round(total, {{ user_price_dec() }});

        $('#supp_inv_collapse__lbl_payable')[0].dataset.value = total;
        $('#supp_inv_collapse__lbl_payable').val(numberFormatter.format(total)).trigger('change');
    });

    $(masterCheckbox.form).on('reset', () => {
        SupplierInvoice.SubCollapse.hide();
        SupplierInvoice.SubCollapse.resetForm();
        SupplierInvoice.Collapse.hide();
    })

    $(SupplierInvoice.SubCollapse._element).on('hidden.bs.collapse', () => {
        masterCheckbox.checked = false;
        masterCheckbox.dispatchEvent(new Event('change', { bubbles: true }));
    })

    /**
     * Populates the default values for supplier invoice making
     * 
     * @param object data 
     */
    function populateDefaults(data) {
        $('[name="supplier_id"]')
            .html(
                `<option value="${data.supplier.supplier_id}" selected>${data.supplier.formatted_name}</option>`
            )
            .val(data.supplier.supplier_id)
            .trigger('change');

        const stocks =  data.stocks.map(item => `<option value="${item.stock_id}">${item.description}</option>`);
        $('[name="purchase_stock_id"]')
            .html(
                `<option value="" selected>-- select --</option>
                ${stocks.join("\n")}`
            );
        
        let selectedStock = null;
        if (data.stocks.length == 1) {
            selectedStock = data.stocks[0];
        }
        
        // If there is only one stock item with the nationality set,
        // that is probably the stock item that needs to be invoiced.
        if (!selectedStock) {
            let sameNationalityServices = data.stocks.filter(el => !!el.nationality)
            if (sameNationalityServices.length == 1) {
                selectedStock = sameNationalityServices[0];
            }
        }

        if (selectedStock) {
            $('[name="purchase_stock_id"]').val(selectedStock.stock_id).trigger('change');
        }
    }
})
</script>
@endpush