<?php

use App\Models\Accounting\BankAccount;

ob_start(); ?>
<style>
    .refund_amt {
        width: 100px;
        pointer-events: none;
    }

    input:focus-visible {
        outline: dashed 2px #eee;
        border-radius: 4px;
    }
</style>
<?php $GLOBALS['__HEAD__'][] = ob_get_clean(); include "header.php" ?>

<div class="card w-100">
    <div class="card-header fs-3 fw-bold"><?= trans('REFUND PROCESS') ?></div>
    <div class="card-body">
        <fieldset class="border border-2 p-3">
            <legend class="fs-5 w-auto px-3"> <?= trans('FIND') ?></legend>

            <div class="row">
                <div class="col-lg-3 col-md-4 col-sm-12">
                    <div class="form-group row">
                        <label for="rcpt_no" class="col-sm-4 col-form-label"><?= trans('RCPT NO') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="rcpt_no">
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-4 col-sm-12">
                    <div class="form-group row">
                        <label for="inv_no" class="col-sm-4 col-form-label"><?= trans('INV NO') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="inv_no">
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-4 col-sm-12">
                    <button type="button" class="btn btn-primary" id="btn-find">
                        <?= trans('SEARCH') ?>
                    </button>
                </div>
            </div>
        </fieldset>

        <form action="" method="post" id="rep-form">
            <fieldset class="border border-2 mw-700px mx-auto p-3 my-10">
                <legend class="fs-5 w-auto px-3"><?= trans('REFUND PROCESS') ?></legend>
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group row">
                            <label for="customer_id" class="col-sm-4 col-form-label"><?= trans('Customer') ?></label>
                            <div class="col-sm-8">
                                <select required class="custom-select form-select" id="customer_id" name="customer_id">
                                    <option value="">-- select --</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="tran_date" class="col-sm-4 col-form-label"><?= trans('Date') ?></label>
                            <div class="col-sm-8">
                                <input
                                    required
                                    data-provide="datepicker"
                                    data-date-format="<?= getBSDatepickerDateFormat() ?>"
                                    data-date-today-highlight="true"
                                    type="text"
                                    class="form-control"
                                    id="tran_date"
                                    name="tran_date"
                                    value="<?= Today() ?>">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="balance_to_refund" class="col-sm-4 col-form-label text-danger"><?= trans('Payable Amt') ?></label>
                            <div class="col-sm-8">
                                <input
                                    readonly
                                    type="text"
                                    class="form-control-plaintext text-danger fs-4"
                                    data-parsley-type="number"
                                    data-parsley-gt="0"
                                    id="balance_to_refund"
                                    value="0.00">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="bank_account" class="col-sm-4 col-form-label"><?= trans('From Account') ?></label>
                            <div class="col-sm-8">
                                <select required class="custom-select form-select ap-select2" id="bank_account" name="bank_account">
                                    <option value="">-- select --</option>
                                    <?php foreach (BankAccount::all() as $act): ?>
                                    <option value="<?= $act->id ?>"><?= $act->formatted_name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group row">
                            <label for="total_refund" class="col-sm-4 col-form-label"><?= trans('Refunding') ?></label>
                            <div class="col-sm-8">
                                <input
                                    required
                                    readonly
                                    type="text"
                                    class="form-control-plaintext"
                                    id="total_refund"
                                    name="total_refund"
                                    value="0.00">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="discount" class="col-sm-4 col-form-label"><?= trans('Discount') ?></label>
                            <div class="col-sm-8">
                                <input data-parsley-type="number" type="text" class="form-control" id="discount" name="discount">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="commission" class="col-sm-4 col-form-label"><?= trans('Commission') ?></label>
                            <div class="col-sm-8">
                                <input data-parsley-type="number" type="text" class="form-control" id="commission" name="commission">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="round_off" class="col-sm-4 col-form-label"><?= trans('Round Off') ?></label>
                            <div class="col-sm-8">
                                <input data-parsley-type="number" type="text" class="form-control" id="round_off" name="round_off">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-auto mx-auto">
                        <button type="submit" class="btn btn-success" id="btn-process-refund">
                            <?= trans('PROCESS REFUND') ?>
                        </button>
                    </div>
                </div>
            </fieldset>

            <div class="table-responsive">
                <table class="table table-bordered table-striped text-nowrap gx-3" id="table-alloc">
                    <thead class="thead-strong">
                        <tr>
                            <th><?= trans('#') ?></th>
                            <th class="min-w-125px"><?= trans('RCPT NO') ?></th>
                            <th class="min-w-100px"><?= trans('DATE') ?></th>
                            <th class="mw-100px text-wrap"><?= trans('INVOICES') ?></th>
                            <th><?= trans('RCPT AMT') ?></th>
                            <th><?= trans('DISCOUNT') ?></th>
                            <th><?= trans('COMMISSION') ?></th>
                            <th><?= trans('ROUND OFF') ?></th>
                            <th><?= trans('ALLOC AMT') ?></th>
                            <th><?= trans('BALANCE') ?></th>
                            <th><?= trans('REFUND AMT') ?></th>
                            <th class="min-w-75px">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-alloc"></tbody>
                </table>
            </div>
        </form>
    </div>
</div>

<?php ob_start(); ?>
<script>
$(function () {
    const colspan = document.querySelectorAll('#table-alloc thead tr:first-child th').length;
    const dec = <?= user_price_dec() ?>;
    
    // initialize
    initializeCustomersSelect2('#customer_id');
    emptyAllocTable();
    const form = $(document.forms.namedItem('rep-form')).parsley();

    form.$element.on('reset', () => {
        setTimeout(() => {
            form.reset();
            emptyAllocTable();
            $('select').trigger('change.select2');
            $('input[data-provide="datepicker"]').datepicker('update')
        })
    })

    form.on('form:submit', function () {
        ajaxRequest({
            method: 'post',
            url: route('API_Call', {method: 'process_refund'}),
            data: new FormData(form.element),
            contentType: false,
            processData: false
        }).done(function (respJson, msg, xhr) {
            if (typeof respJson.refund_no == 'undefined') {
                return defaultErrorHandler(xhr);
            }

            form.element.reset();

            Swal.fire('Success!', respJson.msg, 'success');
            
            createPopup(url("ERP/reporting/prn_redirect.php", {
                PARAM_0: respJson.refund_no + "-<?= ST_CUSTREFUND ?>",
                PARAM_1: respJson.refund_no + "-<?= ST_CUSTREFUND ?>",
                PARAM_2: '',
                PARAM_3: '0',
                PARAM_4: '', PARAM_5: '', PARAM_6: '',
                PARAM_7: '0',
                REP_ID: '116',
            }));
        })
        .fail(defaultErrorHandler);

        return false;
    });

    $('#btn-find').on('click', populateCustomerAdvances);
    $('#customer_id').on('change', populateCustomerAdvances);

    function emptyAllocTable() {
        $('#tbody-alloc').html(`<tr><td class="text-center" colspan="${colspan}">No Data</td></tr>`);
    }

    function populateCustomerAdvances() {
        const customerSelect = form.element.elements.namedItem('customer_id');
        const params = {
            customer_id: document.querySelector('#customer_id').value,
            rcpt_no: document.querySelector('#rcpt_no').value,
            inv_no: document.querySelector('#inv_no').value
        }

        form.element.reset();
        customerSelect.value = params.customer_id;
        emptyAllocTable();

        if (!params.customer_id.length && !params.rcpt_no.length && !params.inv_no.length) {
            return;
        }

        ajaxRequest({
            url: route('API_Call', {method: 'get_customer_advances'}),
            method: 'get',
            data: params
        })
        .done(function (respJson, msg, xhr) { 
            if (typeof respJson.data == 'undefined') {
                return defaultErrorHandler(xhr);
            }

            if (!respJson.data.length) return;

            var tbody_html = "";
            respJson.data.forEach((val, key) => {
                let i = key + 1;
                tbody_html += (
                    `<tr>
                        <td>${i}</td>
                        <td>
                            <input type="hidden" data-name="type" name="alloc[${i}][type]" value="${val.type}">
                            <input type="hidden" data-name="trans_no" name="alloc[${i}][trans_no]" value="${val.trans_no}">
                            <input
                                type="text"
                                readonly
                                data-name="reference"
                                name="alloc[${i}][reference]"
                                class="form-control-plaintext py-0"
                                value="${val.reference}">
                        </td>
                        <td>
                            <input
                                readonly
                                name="alloc[${i}][tran_date]"
                                type="text"
                                data-name="tran_date"
                                class="form-control-plaintext py-0"
                                value="${val.tran_date}">
                        </td>
                        <td data-name="invoice_numbers">${val.invoice_numbers || ''}</td>
                        <td><input readonly type="text" data-name="rcpt_total" class="form-control-plaintext py-0" value="${(parseFloat(val.TotalAmount) || 0).toFixed(dec)}"></td>
                        <td><input readonly type="text" data-name="discount" class="form-control-plaintext py-0" value="${(parseFloat(val.ov_discount) || 0).toFixed(dec)}"></td>
                        <td><input readonly type="text" data-name="commission" class="form-control-plaintext py-0" value="${(parseFloat(val.commission) || 0).toFixed(dec)}"></td>
                        <td><input readonly type="text" data-name="round_off" class="form-control-plaintext py-0" value="${(parseFloat(val.round_of_amount) || 0).toFixed(dec)}"></td>
                        <td><input readonly type="text" data-name="alloc" class="form-control-plaintext py-0" value="${(parseFloat(val.Allocated) || 0).toFixed(dec)}"></td>
                        <td><input readonly type="text" data-name="balance" class="form-control-plaintext py-0" value="${(parseFloat(val.left_to_allocate) || 0).toFixed(dec)}"></td>
                        <td data-parsley-form-group>
                            <input
                                readonly
                                type="number"
                                data-name="this_alloc"
                                name="alloc[${i}][this_alloc]"
                                min="0"
                                step="0.${('1').padStart(dec, '0')}"
                                max="${(parseFloat(val.left_to_allocate) || 0).toFixed(dec)}"
                                class="form-control py-0"
                                value="">
                        </td>
                        <td>
                            <button type="button" data-action="alloc_all" class="btn btn-sm btn-info">All</button>
                            <button type="button" data-action="alloc_none" class="btn btn-sm btn-warning">None</button>
                        </td>
                    </tr>`
                )
            });

            if (!customerSelect.value) {
                if (!Array.from(customerSelect.options).some(option => option.value == respJson.customer.debtor_no)) {
                    customerSelect.append(new Option(respJson.customer.formatted_name, respJson.customer.debtor_no));
                }

                $(customerSelect).val(respJson.customer.debtor_no).trigger('change.select2');
            }

            $("#tbody-alloc").html(tbody_html);
        });
    }

    $("#tbody-alloc").on('click', '[data-action="alloc_all"]', (ev) => {
        const tr = ev.target.closest('tr');
        const bal = tr.querySelector('[data-name="balance"]').value;
        tr.querySelector('[data-name="this_alloc"]').value = bal;
        $(tr.querySelector('[data-name="this_alloc"]')).trigger('change');
    });

    $("#tbody-alloc").on('click', '[data-action="alloc_none"]', (ev) => {
        const tr = ev.target.closest('tr');
        tr.querySelector('[data-name="this_alloc"]').value = '';
        $(tr.querySelector('[data-name="this_alloc"]')).trigger('change');
    });

    $("#tbody-alloc").on('change', '[data-name="this_alloc"]', (ev) => {
        let totalDiscount, totalCommission, totalRoundOff, totalRefund;
        totalDiscount = totalCommission = totalRoundOff = totalRefund = 0;

        $('#tbody-alloc tr').each(function() {
            let thisAlloc = parseFloat($(this).find('[data-name="this_alloc"]').val()) || 0;

            // if the allocation amount is 0, No need to proceed any further
            if (thisAlloc == 0) return;
            
            let rcptTotal = parseFloat($(this).find('[data-name="rcpt_total"]').val()) || 0;
            let discount = parseFloat($(this).find('[data-name="discount"]').val()) || 0;
            let roundOff = parseFloat($(this).find('[data-name="round_off"]').val()) || 0;
            let commission = parseFloat($(this).find('[data-name="commission"]').val()) || 0;

            totalDiscount += round(thisAlloc * (discount/rcptTotal), dec);
            totalCommission += round(thisAlloc * (commission/rcptTotal), dec);
            totalRoundOff += round(thisAlloc * (roundOff/rcptTotal), dec);
            totalRefund += round(thisAlloc, dec);
        });

        $('#discount').val(round(totalDiscount, dec));
        $('#commission').val(round(totalCommission, dec));
        $('#round_off').val(round(totalRoundOff, dec));
        $('#total_refund').val(round(totalRefund, dec)).trigger('change');
    });

    $('#total_refund, #discount, #commission, #round_off').on('change', function () {
        let discount = parseFloat($('#discount').val()) || 0;
        let commission = parseFloat($('#commission').val()) || 0;
        let roundOff = parseFloat($('#round_off').val()) || 0;
        let refundAmt = parseFloat($('#total_refund').val()) || 0;
        
        $('#balance_to_refund').val(round(refundAmt - discount - commission + roundOff, dec)).trigger('change');
    });
});
</script>

<?php $GLOBALS['__FOOT__'][] = ob_get_clean(); include "footer.php";