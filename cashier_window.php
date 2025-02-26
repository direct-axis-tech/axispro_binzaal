<?php

use App\Models\Accounting\Dimension;
use App\Permissions as P;
use Illuminate\Support\Arr;

$dimensions = Dimension::query();

user_check_access('SA_CUSTPYMNT_ALWD')
    ? $dimensions->whereIN('id', authUser()->authorized_dimensions)
    : $dimensions->where('id', authUser()->dflt_dimension_id);

$dimensions = $dimensions->select('id', 'name')->get()->toArray();
?>
<style>
    tbody#invoice-pending-tbody td {
        width: 27%;
        font-size: 12px;
        font-weight: bold;
        color: black;
    }

    table#pending-invoice-table th {
        border: 1px solid #ccc;
    }

    .credit_cards {
        width: 20%;
        height: 50px;
    }

    .btn:focus {
        background: #d08221 !important;
        border: 0px solid black;
        color: white;
    }

    #invoices-table-div {
        max-height: 400px;
        overflow-y: auto;
        padding: 0 !important;
    }

    #invoice-list-div {
        max-height: 300px !important;
        overflow-y: auto !important;
    }


    #pending-invoice-table td {
        border: 1px solid #fff;
        font-weight: bolder;
        color: #644942;
    }

    .btn-secondary {
        background: #b2bac5 !important;
        color: #fff;
    }
</style>
<input type="hidden" id="credit_card_charge_percent" value="<?= get_company_pref('default_card_charge') ?: '0.00' ?>">
<div id="system-cashier-dashboard" class="kt-container  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch">
    <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
        <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">
            <div class="kt-container  kt-grid__item kt-grid__item--fluid">

                <div class="row" style="padding-top:10px;">
                    <div class="col-md-9">
                        <div class="card">
                            <div class="card-body" id="cashier-form-div">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h3>CASHIER WINDOW</h3>
                                    </div>
                                    <div class="col-md-6">
                                        <h3 id="home_icon" style="float: right;margin-right: 0px; cursor: pointer; background: #ccc; border-radius: 5px; padding: 2px">
                                            <i class="fa fa-home"></i>
                                        </h3>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-5">
                                        <label for="cc" style="font-weight: bold">COST CENTER :</label>
                                        <select class="form-control kt-select2" name="dim_id" id="dim_id">
                                            <?= prepareSelectOptions(
                                                $dimensions,
                                                'id',
                                                'name',
                                                (user_check_access('SA_RCVPMTWITHOUTDIM') ? false : authUser()->dflt_dimension_id),
                                                (user_check_access('SA_RCVPMTWITHOUTDIM') ? "All" : false)
                                            ) ?>
                                        </select>
                                        <p></p>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-5">
                                        <label for="cc_name">BARCODE</label>
                                    </div>
                                    <div class="col-md-2"></div>
                                    <div class="col-md-5">
                                        <label for="cc_name">Customer Name</label>
                                        <span id="customer_balance" class=" pull-right badge badge-warning"></span>
                                    </div>
                                    <div class="col-md-5">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-5 input-group">
                                        <input type="text" autofocus="autofocus" class="form-control" id="barcode">
                                        <div class="input-group-append">
                                            <button id="fetch_barcode" class="btn btn-info">
                                                <i style="color:#fff !important;" class="menu-icon flaticon-refresh"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-2"></div>
                                    <div class="col-md-5" id="customerSelect">
                                        <select class="form-control" id="customer_id_in_select">
                                            <option value="" selected disabled>Select a Customer</option>
                                        </select>
                                    </div>
                                    <div class="col-md-5" style="display:none;" id="customer-name-div">
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="customer_name"
                                            autocomplete="off"
                                            maxlength="3"
                                            readonly
                                            value="">
                                    </div>

                                    <input type="hidden" id="date_format" value="<?= dateformat('bsDatepicker'); ?>">
                                    <input type="hidden" id="date" value="<?= Today() ?>">
                                    <input type="hidden" id="invoice_selected_type" value="">
                                    <input type="hidden" name="customer_id" id="customer_id">
                                    <input type="hidden" name="trans_no" id="trans_no">
                                    <input type="hidden" name="payment_method" id="payment_method">
                                    <input type="hidden" name="bank_acc" id="bank_acc">
                                </div>

                                <div class="row" id="invoice-list-div">
                                    <div class="col-md-12">
                                        <div class="form-group row" style="margin-top:20px;">
                                            <div class="col-md-2"><label>Invoice #</label></div>
                                            <div class="col-md-2"><label>Date</label></div>
                                            <div class="col-md-2"><label>Amount</label></div>
                                            <div class="col-md-2"><label>Paid Amount</label></div>
                                            <div class="col-md-2"><label>Pay Balance</label></div>
                                            <div class="col-md-2"><label>This Alloc</label></div>
                                        </div>
                                        <div
                                            class="form-group row alloc_inv_table"
                                            style="margin-top:-25px;"
                                            id="barcode-invoice">
                                            <div class="col-md-2">
                                                <input
                                                    style="font-size:80%;"
                                                    type="text"
                                                    data-trans_no=""
                                                    class="form-control aInvNumber"
                                                    id="invoice_number"
                                                    autocomplete="off"
                                                    maxlength="3"
                                                    readonly=""
                                                    value="">
                                            </div>
                                            <div class="col-md-2">
                                                <input
                                                    style="font-size:80%;"
                                                    type="text"
                                                    name="tran_date"
                                                    id="tran_date"
                                                    class="form-control"
                                                    placeholder="Select date"
                                                    value="<?= Today() ?>">
                                            </div>
                                            <div class="col-md-2">
                                                <input
                                                    style="font-size:80%;"
                                                    type="text"
                                                    class="form-control"
                                                    id="invoice_amount"
                                                    autocomplete="off"
                                                    maxlength="3"
                                                    readonly=""
                                                    value="">
                                            </div>
                                            <div class="col-md-2">
                                                <input
                                                    style="font-size:80%;"
                                                    type="text"
                                                    class="form-control"
                                                    id="paid_amount"
                                                    readonly
                                                    value="">
                                            </div>
                                            <div class="col-md-2">
                                                <input
                                                    style="font-size:80%;"
                                                    type="text"
                                                    class="form-control"
                                                    id="display_amount"
                                                    readonly
                                                    value="">
                                                
                                                <input
                                                    type="hidden"
                                                    class="form-control"
                                                    id="max_alloc_for_barcode"
                                                    value="">
                                            </div>
                                            <div class="col-md-2">
                                                <input
                                                    style="font-size:80%;"
                                                    type="number"
                                                    step="any"
                                                    min="0"
                                                    name="this_alloc_amount"
                                                    class="form-control aAllocAmount"
                                                    id="this_alloc_amount"
                                                    value="">
                                            </div>
                                        </div>
                                        <div style="margin-top:-25px;" id="invoice-list" class="alloc_inv_table">
                                            <!-- Generated automatically -->
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>

                                <div class="form-group row">
                                    <div class="col-md-2">
                                        <label for="max_allocatable_amount">Max Allocatable Amt.</label>
                                        <input
                                            type="number"
                                            class="form-control"
                                            placeholder="0.00"
                                            id="max_allocatable_amount"
                                            autocomplete="off"
                                            step="0.01"
                                            value="0.00"
                                            data-value="0.00">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="mt-8 btn btn-primary" id="alloc_all">Allocate All</button>
                                    </div>
                                    <div class="col-md-2">
                                        <label></label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            placeholder="0.00"
                                            id="invoice_amount_final"
                                            autocomplete="off"
                                            readonly=""
                                            value="">
                                    </div>
                                    <div class="col-md-2">
                                        <label></label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            placeholder="0.00"
                                            id="paid_amount_final"
                                            autocomplete="off"
                                            readonly=""
                                            value="">
                                    </div>
                                    <div class="col-md-2">
                                        <label></label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            placeholder="0.00"
                                            id="pay_balance_final"
                                            autocomplete="off"
                                            readonly=""
                                            value="">
                                    </div>
                                    <div class="col-md-2">
                                        <label style="margin-bottom:0!important;">Amount Total</label>
                                        <input
                                            type="number"
                                            min="0"
                                            step="any"
                                            class="form-control"
                                            placeholder="0.00"
                                            id="this_alloc_final"
                                            autocomplete="off"
                                            value=""
                                            style="border: 2px solid #2786fb; font-weight: bold">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <button
                                            id="btn_cancel"
                                            type="button"
                                            style="width:50%;height: 50px; font-size: 25px;"
                                            class="btn btn-secondary pull-left btn-block">
                                            CANCEL
                                        </button>
                                    </div>

                                    <div class="col-md-6">
                                        <button
                                            id="paynow_btn_single"
                                            type="button"
                                            data-card-charge=""
                                            style="height: 50px; font-size: 25px; right: 10px;"
                                            class="btn btn-primary float-end">
                                            PAY NOW
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body" id="invoices-table-div">
                                <table class="table table-responsive" id="pending-invoice-table" style="display: table !important;">
                                    <thead style="background:#eeeeee;">
                                        <tr>
                                            <th>Invoice #</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody id="invoice-pending-tbody">
                                        <!-- Generated automatically -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>
                
                <?php if(user_check_access(P::SA_DSH_FIND_INV)): ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-3" id="find-invoice-card">
                            <div class="card-header">
                                <div class="card-title"><?=  __('Find invoice') ?></div>
                            </div>
                            <div class="card-body">
                                <div class="form-group mb-5" data-parsley-form-group>
                                    <input
                                        type="text"
                                        required
                                        autocomplete="off"
                                        name="reference"
                                        class="form-control"
                                        placeholder="<?=  __('Enter invoice number') ?>">
                                </div>
                                <div class="form-group">
                                    <button type="button" data-method="print"  class="btn btn-facebook"><?=  __('Print Invoice') ?></button>
                                    <button type="button" data-method="edit"  class="btn btn-success"><?=  __('Update Transaction ID') ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                    
                <?php if (user_check_access(P::SA_DSH_TODAYS_INV)): ?>
                <div class="row">
                    <div class="col-12">
                        <div class="card mb-3" id="todays-invoices-card">
                            <div class="card-header">
                                <div class="card-title"><?=  __("Today's invoices") ?></div>
                            </div>
                            <div class="card-body">
                                <table
                                    class="table table-striped table-row-bordered g-3 text-nowrap thead-strong"
                                    data-control="dataTable">
                                    <thead>
                                    <th><?= __('Reference') ?></th>
                                    <th><?= __('Token Number') ?></th>
                                    <th><?= __('Customer Name') ?></th>
                                    <th><?= __('Display Customer') ?></th>
                                    <th><?= __('Amount') ?></th>
                                    <th><?= __('Payment Status') ?></th>
                                    <th><?= __('Payment Method') ?></th>
                                    <th><?= __('Employee') ?></th>
                                    <th><?= __('Transaction Status') ?></th>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (user_check_access(P::SA_DSH_TODAYS_REC)): ?>
                <div class="card mb-5" id="todays-receipts-card">
                    <div class="card-header">
                        <div class="card-title"><?=  __("Today's Receipts") ?></div>
                    </div>
                    <div class="card-body">
                        <table
                                class="table table-striped table-row-bordered g-3 text-nowrap thead-strong"
                                data-control="dataTable">
                            <thead>
                            <th><?= __('Reference') ?></th>
                            <th><?= __('Customer Name') ?></th>
                            <th><?= __('Amount') ?></th>
                            <th><?= __('Employee') ?></th>
                            </thead>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <!-- end:: Content -->
        </div>
    </div>
</div>


<div class="modal fade" role="dialog" id="PaymentModel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button
                    type="button"
                    class="close"
                    data-dismiss="modal"
                    data-bs-dismiss="modal"
                    aria-label="Close">
                    <i class="fa fa-close"></i>
                </button>
                <h4 class="modal-title" style="position: absolute;">PAYMENT</h4>
            </div>


            <div class="modal-body">
                <div class="col-md-12">
                    <?php foreach (Arr::only(PAYMENT_METHODS, array_filter(explode(',', pref('axispro.enabled_payment_methods')))) as $k => $v): ?>
                    <button
                        type="button"
                        style="width:32%; height: 100px; font-size: 25px;right: 10px;"
                        class="btn btn-primary paymentchooser text-uppercase"
                        data-method="<?= $k ?>">
                        <?= $v ?>
                    </button>
                    <?php endforeach; ?>
                </div>

                <div class="col-md-12" id="card_data" style="margin-top: 8px;display:none">
                    <!-- Generated Automatically -->
                </div>

                <!-- split start-->
                <div class="col-md-12 split_choose_div" style="margin-top: 8px;display:none">
                    <div id="split_info">
                        <div id="split_cash_div" class="mb-3" style="border: 1px solid #ccc; padding-bottom: 10px">
                            <label style="display: table-cell;font-weight: bold;padding: 8px;">Cash Payment Details</label>
                            <div id="cash_accounts_list" class="btn-group" data-toggle="buttons"></div>
                            <div class="col-md-12" style="display: inline-flex">
                                <div class="col-md-3" style="margin-top: 8px;">
                                    <span>
                                        Cash Amount :
                                        <input
                                            type="number"
                                            step="any"
                                            id="cash_amt"
                                            class="form-control"
                                            placeholder="Amount"
                                            value="0">
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div id="split_card_div" style="border: 1px solid #ccc; padding-bottom: 10px">
                            <label style="display: table-cell;font-weight: bold;padding: 8px;">
                                Card Payment Details
                            </label>
                            <div id="card_accounts_list" class="btn-group" data-toggle="buttons"></div>
                            <div class="col-md-12" style="display: inline-flex">
                                <div class="col-md-3" style="margin-top: 8px;">
                                    <span>
                                        Card Amount :
                                        <input
                                            type="number"
                                            step="any"
                                            id="card_amt"
                                            class="form-control"
                                            placeholder="Amount"
                                            value="0">
                                    </span>
                                </div>
                                <div class="col-md-3" style="margin-top: 8px;">
                                    <span>
                                        Bank Charge (%) :
                                        <input
                                            type="number"
                                            step="any"
                                            id="split_bank_charge"
                                            class="form-control"
                                            placeholder="Bank Charge (%)"
                                            value="0"
                                            min="0">
                                    </span>
                                </div>
                                <div class="col-md-6" style="margin-top: 8px;">
                                    <span
                                        id="lbl_total_card_amnt"
                                        style="font-size: 17pt; color: green; font-weight: bold;">
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- split end-->

                <div class="row col-md-12">
                    <div class="col-md-3" style="margin-top: 8px;">
                        <span>
                            TRANSACTION DATE :
                            <input
                                type="text"
                                step="any"
                                id="pay_date"
                                class="form-control ap-datepicker"
                                readonly
                                placeholder=""
                                value="<?= Today() ?>">
                        </span>
                    </div>
                    <div class="col-md-3" style="margin-top: 8px;">
                        <span>
                            Paying Amount :
                            <input
                                type="number"
                                step="any"
                                id="paying_amount"
                                class="form-control"
                                placeholder="Amount"
                                value="0"
                                readonly>
                        </span>
                    </div>
                    <div class="col-md-3" style="margin-top: 8px;">
                        <span>
                            Discount :
                            <input
                                type="number"
                                step="any"
                                id="discount"
                                class="form-control"
                                placeholder="Discount"
                                value="0"
                                min="0"
                                readonly
                                onclick="admin_approval_modal();">
                        </span>
                    </div>
                    <div class="col-md-3" style="margin-top: 8px;">
                        <span>
                            Commission :
                            <input
                                type="number"
                                step="any"
                                id="commission"
                                class="form-control"
                                placeholder="Commission"
                                value="0"
                                min="0">
                            <small class="text-muted">Payable <span data-commission-payable>0.00</span></small>
                        </span>
                    </div>
                    <div class="col-md-3" style="margin-top: 8px;">
                        <span>
                            Bank Charge (%) :
                            <input
                                type="number"
                                step="any"
                                id="bank_charge"
                                class="form-control"
                                placeholder="Bank Charge (%)"
                                value="<?= get_company_pref('default_card_charge') ?: '0.00' ?>"
                                min="0">
                            <input type="hidden" id="bank_charge_hidden" value="<?= get_company_pref('default_card_charge') ?: '0.00' ?>">
                        </span>
                    </div>
                    <div class="col-md-3" style="margin-top: 8px;">
                        <span>
                            Round of Amount :
                            <input
                                type="number"
                                step="any"
                                id="rounded_difference"
                                name="rounded_difference"
                                class="form-control"
                                placeholder="Round of Amount"
                                value="0">
                        </span>
                    </div>
                    <div class="col-md-3" style="margin-top: 8px;">
                        <span>
                            Comments :
                            <input
                                type="test"
                                id="comment"
                                name="comment"
                                class="form-control">
                        </span>
                    </div>
                    <div class="col-md-3" style="margin-top: 8px;display:none" id="auth_code_block">
                        <span>
                            Auth Code :
                            <input
                                type="text"
                                id="auth_code"
                                name="auth_code"
                                class="form-control">
                        </span>
                    </div>
                </div>

                <hr>
                <hr>

                <div class="row col-md-12">
                    <div class="col-md-4" style="margin-top: 8px;">
                        <span>
                            Given Amount :
                            <input
                                type="number"
                                step="any"
                                id="given_amount"
                                class="form-control"
                                value="0">
                        </span>
                    </div>

                    <div class="col-md-4" style="margin-top: 8px;">
                        <span>
                            Change :
                            <input
                                type="number"
                                step="any"
                                id="change_amount"
                                class="form-control"
                                disabled>
                        </span>
                    </div>
                </div>

                <h3 class="text-center border" style="margin: 12px;color: #063f08;">
                    Amount to be Collected :
                    <span id="amount_to_be_collected" style="padding: 4px;font-weight: bold;"></span> AED
                </h3>

                <button
                    type="button"
                    style="float: right; margin: 8px 9px 3px 9px;"
                    class="btn btn-primary"
                    id="btn_make_payment">
                    Proceed To Pay
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Admin Password Modal -->
<div class="modal fade" role="dialog" id="AdminModal">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button
                    type="button"
                    class="close"
                    data-dismiss="modal"
                    data-bs-dismiss="modal"
                    aria-label="Close">
                    <i class="fa fa-close"></i>
                </button>
                <h4 class="modal-title" style="position: absolute;">
                    Admin Approval
                </h4>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label for="discount_for_admin_approval" class="col-form-label">
                        Discount
                    </label>
                    <input
                        type="number"
                        step="any"
                        class="form-control"
                        placeholder="Enter Discount"
                        id="discount_for_admin_approval">
                </div>
                <div class="form-group">
                    <label
                        for="admin_password"
                        class="col-form-label">
                        Password for user: <b>'admin'</b>
                    </label>
                    <input
                        type="password"
                        class="form-control"
                        id="admin_password"
                        placeholder="Password">
                </div>
                <button
                    type="submit"
                    class="btn btn-primary mb-2 pull-right"
                    id="admin_password_confirm">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script src="assets/plugins/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
<script src="assets/js/pages/crud/forms/widgets/bootstrap-datepicker.js" type="text/javascript"></script>
<script src="assets/js/jquery-dateformat.min.js" type="text/javascript"></script>
<script src="assets/js/jquery.doubleScroll.js" type="text/javascript"></script>
<script src="assets/plugins/general/sweetalert2/dist/sweetalert2.min.js" type="text/javascript"></script>
<script src="assets/plugins/general/js/global/integration/plugins/sweetalert2.init.js" type="text/javascript"></script>

<script type="text/javascript">

    var allocation_objects = [];
    const isAuthCodeRequired = Boolean(parseInt('<?= pref('axispro.req_auth_code_4_cc_pmt') ?>'));
    route.push('api.customers.commissionPayable', '<?= rawRoute('api.customers.commissionPayable') ?>');

    $(function() {
        resize_invoices_table();
        $('#home_icon').click(() => window.location.href = "index.php");
        $('#dim_id').change(loadPendingInvoices);
        $('#fetch_barcode').click(fetchBarcode);
        $('#btn_cancel').click(cancel);
        $('#paynow_btn_single').click(paydata);
        $('#admin_password_confirm').click(check_password);
        $("#alloc_all").click(allocateAll);
        
        $('#max_allocatable_amount').change(function () {
            update_max_alloc();
            let max_allocated_amount = parseFloat($('#this_alloc_final').val());
            max_allocated_amount = isNaN(max_allocated_amount) ? 0 : max_allocated_amount;

            let max_allocatable_amount = isNaN(this.value) ? 0 : this.value;
            if (max_allocatable_amount && max_allocated_amount > max_allocatable_amount) {
                this.value = this.dataset.value;
                swal.fire('Error!', 'This causes the maximum allocated amount to overflow', 'warning');
                return false;
            }

            this.dataset.value = this.value;
            return true;
        })
    });

    function admin_approval_modal() {
        $('#AdminModal').modal('show');
    }

    function check_password() {
        var pass = $('#admin_password').val();
        var disc = $('#discount_for_admin_approval').val();

        if (pass.length == 0) {
            return swal.fire('Warning!', 'No Password Entered!!!', 'warning');
        }

        ajaxRequest({
            method: 'post',
            url: route('API_Call', {method: 'check_admin_password'}),
            data: {
                password: pass,
                discount: parseFloat(disc)
            }
        }).done(function (data, msg, xhr) {
            if (typeof data.status === "undefined") {
                return defaultErrorHandler(xhr);
            }

            else if (!data.status) {
                return toastr.error("Password does not match!");
            }

            $("#discount").val(data.discount).trigger("change");
            $('#admin_password').val('');
            $('#discount_for_admin_approval').val('');
            $('#PaymentModel').modal('hide');
            setTimeout(() => $('#AdminModal').modal('hide'), 0);
        }).fail(defaultErrorHandler);
    }

    function resize_invoices_table() {
        $('#invoices-table-div').css({'max-height': $('#cashier-form-div').height() + 33 + 'px'});
    }

    function cancel() {
        window.location.href = "<?= getRoute('cashier_touch_screen') ?>";
    }

    function update_this_alloc(rowid) {
        update_max_alloc();

        let max_allocatable_amount = parseFloat($('#max_allocatable_amount').val());
        if (isNaN(max_allocatable_amount)) {
            max_allocatable_amount = 0;
        }
        
        let max_allocated_amount = parseFloat($('#this_alloc_final').val());
        if (isNaN(max_allocated_amount)) {
            max_allocated_amount = 0;
        }

        let allocating_amount = parseFloat($(`#hidden_display_amount_${rowid}`).val());
        if (isNaN(allocating_amount)) {
            allocating_amount = 0;
        }

        let balance_allocatable_amt = max_allocatable_amount
            ? (max_allocatable_amount - max_allocated_amount)
            : allocating_amount;
        
        allocating_amount = round(Math.min(balance_allocatable_amt, allocating_amount), 2);
        
        if (!allocating_amount) {
            return false;
        }

        $(`#this_alloc_amount_${rowid}`).val(allocating_amount.toFixed(2));
        $('#this_alloc_final').val((max_allocated_amount + allocating_amount).toFixed(2));
        return true;
    }

    function update_max_alloc() {
        var sum = 0.0;
        $('#invoice-list > .row  > .alloc_div').each(function () {
            var alloc_val = parseFloat($(this).find('.alloc_val').val());
            sum += isNaN(alloc_val) ? 0 : alloc_val;
        });
        $('#this_alloc_final').val(sum.toFixed(2));
    }

    function allocateAll() {
        setBusyState();
        const allocatable = $('#invoice-list [data-row]').toArray();
        for (let i = 0; i < allocatable.length; i++) {
            const rowId = allocatable[i].dataset.row;
            if (parseFloat($(`#this_alloc_amount_${rowId}`).val())) {
                continue;
            }
            if (!update_this_alloc(rowId)) {
                break;
            }
        }
        unsetBusyState();
    }

    function onChangeThisAlloc(rowid) {
        update_max_alloc();

        let max_allocatable_amount = parseFloat($('#max_allocatable_amount').val());
        if (isNaN(max_allocatable_amount)) {
            max_allocatable_amount = 0;
        }
        
        let max_allocated_amount = parseFloat($('#this_alloc_final').val());
        if (isNaN(max_allocated_amount)) {
            max_allocated_amount = 0;
        }

        let current_allocated = parseFloat($(`#this_alloc_amount_${rowid}`).val());
        current_allocated = isNaN(current_allocated) ? 0 : current_allocated;

        if (max_allocatable_amount && max_allocated_amount > max_allocatable_amount) {
            let previous_allocated = parseFloat($(`#this_alloc_amount_${rowid}`).data('value'));
            previous_allocated = isNaN(previous_allocated) ? 0 : previous_allocated;

            $(`#this_alloc_amount_${rowid}`).val(previous_allocated.toFixed(2));
            $('#this_alloc_final').val((max_allocated_amount - current_allocated + previous_allocated).toFixed(2));
            swal.fire('Error!', 'This allocation causes the maximum allocatable amount to overflow', 'warning');
            return false;
        }

        $(`#this_alloc_amount_${rowid}`).data('value', current_allocated.toFixed(2));
        return true;
    }

    function paydata() {
        var sum = 0.0;
        //flag to check if atleast one invoice value is selected based on alloc value
        var alloc_invoice_select_flag = false;
        var open_payment_modal_flag = true;
        var invoice_alloc_error_flag = false;
        $('#invoice-list > .row  > .alloc_div').each(function () {
            var alloc_val = parseFloat($(this).find('.alloc_val').val());
            var max_alloc_val = parseFloat($(this).find('.hidden_display_amount').val());
            if (alloc_val > max_alloc_val) {
                open_payment_modal_flag = false;
                invoice_alloc_error_flag = true;
            }

            if (alloc_val > 0.00) {
                alloc_invoice_select_flag = true;
            }
            sum += parseFloat(alloc_val);
        });

        if (invoice_alloc_error_flag == true) {
            swal.fire(
                'Warning!',
                'Alloc Amount should be less than or equal to Pay Balance!',
                'warning'
            );
        }

        var alloc_final_val = parseFloat($('#this_alloc_final').val());

        if ((round(alloc_final_val, 2) < round(sum, 2)) && (alloc_invoice_select_flag === true)) {
            open_payment_modal_flag = false;
            swal.fire(
                'Warning!',
                'The paying amount must be greater, or equal to the sum of all allocations!',
                'warning'
            );
        }

        var barcode_alloc_val = parseFloat($("#this_alloc_amount").val()); //under barcode
        var max_alloc_for_barcode = parseFloat($("#max_alloc_for_barcode").val()); //max alloc under barcode

        if (barcode_alloc_val > max_alloc_for_barcode) {
            open_payment_modal_flag = false;
            swal.fire(
                'Warning!',
                'Alloc Amount should be less than or equal to Pay Balance!',
                'warning'
            );
        }

        if ((alloc_final_val != barcode_alloc_val) && (barcode_alloc_val > 0.00)) {
            open_payment_modal_flag = false;
            swal.fire(
                'Warning!',
                'Maximum Alloc Amount should be equal to Sum of all Alloc Amount!',
                'warning'
            );
        }

        if (alloc_final_val > 0) {
            if (open_payment_modal_flag === true) {
                proceed_to_payment(alloc_final_val);
            }
        } else {
            swal.fire(
                'Warning!',
                'No Invoice Selected or No Amount entered!',
                'warning'
            );
        }

        calculateChange();
        updateAmountToBeCollected();
    }

    $("#btn_make_payment").click(function () {
        var this_btn = $(this);

        var auth_code = $("#auth_code").val();
        var payment_method = $("#payment_method").val();

        if (
            isAuthCodeRequired
            && (payment_method === "CreditCard"  || payment_method === "Split")
            && !auth_code.trim().length 
        ) { 
            swal.fire('Warning!', 'Please add Auth Code', 'warning');
            return false;
        }
        
        this_btn.html("Please Wait ....");
        this_btn.attr("disabled", "disabled");
        var tran_date = $("#pay_date").val();
        var customer_id = $("#customer_id").val();
        var amount = $("#paying_amount").val();
        var discount = $("#discount").val();
        var bank_acc = $("#bank_acc").val();
        var bank_charge = $("#bank_charge").val();
        var comment = $("#comment").val();

        // used for round off
        discount = parseFloat(discount);

        if(!discount) discount = 0;

        if(bank_charge) {
            bank_charge = parseFloat(bank_charge);
            amount = parseFloat(amount);
            amount = amount-discount;
            coll_amount = amount+((amount*bank_charge)/100)
        }
        var rounded_amount = $("#rounded_difference").val();

        if (payment_method === "") {
            swal.fire(
                'Warning!',
                'Please select a Payment Method (CASH or CARD)',
                'warning'
            );

            this_btn.html("Proceed To Pay");
            this_btn.removeAttr('disabled');
            return false;
        }

        var cash_amt;   
        var card_amt;   
        var cash_acc;   
        var card_acc;   
        if (payment_method === "Split") {   
            cash_amt = $("#cash_amt").val();    
            card_amt = $("#card_amt").val();    
            cash_acc = $('input[name="split_cash_account"]:checked').val(); 
            card_acc = $('input[name="split_card_account"]:checked').val(); 
            bank_charge = $("#split_bank_charge").val();    
        } else{   
            cash_amt = 0;   
            card_amt = 0;   
            cash_acc = '';  
            card_acc = '';  
        }

        ajaxRequest({
            method: 'POST',
            dataType: 'json',
            url: route('API_Call', {method: 'pay_invoice'}),
            data: {
                payment_method: payment_method,
                amount: amount,
                rounded_difference: rounded_amount,
                customer_id: customer_id,
                discount: discount,
                bank_acc: bank_acc,
                bank_charge: bank_charge,
                tran_date: tran_date,
                comment: comment,
                commission: $('#commission').val(),
                alloc_invoices: allocation_objects,
                dim_id : $("#dim_id").val(),
                cash_amt: cash_amt, 
                card_amt: card_amt, 
                cash_acc: cash_acc, 
                card_acc: card_acc,
                auth_code: auth_code
            }
        })
        .done(function(data) {
            this_btn.removeAttr('disabled');
            
            if (data.status != "OK") {
                return swal.fire('Error!', data.msg, 'error').then(() => window.location.reload());
            }

            toastr.success(data.msg);
            window.open(url("ERP/reporting/prn_redirect.php", {
                PARAM_0: data.payment_no + "-12",
                PARAM_1: data.payment_no + "-12",
                PARAM_2: '',
                PARAM_3: '0',
                PARAM_4: '', PARAM_5: '', PARAM_6: '',
                PARAM_7: '0',
                REP_ID: '112',
            }), '_blank');
            setTimeout(function () { window.location.reload();});
        }).fail(defaultErrorHandler);

    });
    
    function proceed_to_payment(paying_amount) {
        const customerId = $("#customer_id").val();
        if (!customerId) {
            return toastr.error('Please select a customer');
        }

        ajaxRequest({
            url: route('api.customers.commissionPayable', {
                customer: customerId
            }),
            method: 'get'
        }).done(function (resp, msg, xhr) {
            if ('undefined' == resp.balance) {
                toastr.error('Could not fetch commission payable');
            }

            var commission_payable = parseFloat(resp.balance) || 0;
            $("[data-commission-payable]").text((-commission_payable).toFixed(2));
            $("#commission").val(0).attr({max: commission_payable > 0 ? 0 : -commission_payable});
            $("#paying_amount").val(paying_amount)[0].dataset.amount = paying_amount;

            allocation_objects = [];
            var barcode_inv_num = $("#invoice_number").val();
            var barcode_trans_no = $("#invoice_number").data('trans_no');
            var barcode_tran_date = $("#invoice_number").data('tran_date');
            var barcode_amount = parseFloat($("#this_alloc_amount").val()) || 0;

            if (barcode_amount != 0) {
                allocation_objects.push({
                    inv_no: barcode_inv_num,
                    trans_no: barcode_trans_no,
                    amount: barcode_amount,
                    tran_date: barcode_tran_date
                })
            }

            else {
                $('#invoice-list [data-row]').each(function () {
                    var rowId = this.dataset.row;
                    var inv_no = $(this).find("#invoice_number_" + rowId).val();
                    var trans_no = $(this).find("#invoice_number_" + rowId).data('trans_no');
                    var tran_date = $(this).find("#invoice_number_" + rowId).data('tran_date');
                    var amount = parseFloat($(this).find("#this_alloc_amount_" + rowId).val()) || 0;

                    if (amount) {
                        allocation_objects.push({inv_no, trans_no, amount, tran_date});
                    }
                });
            }

            $('.paymentchooser').eq(0).trigger('click');
            $('#PaymentModel').modal('show');
        }).fail(defaultErrorHandler);
    }

    function loadPendingInvoices() {
        ajaxRequest({
            url: route('api.sales.reports.todaysInvoices'),
            data: {
                show_only_pending:'1',
                dim_id : $("#dim_id").val()
            },
            blocking: false
        }).done(function ({data}) {
            var tbody_html = "";

            $.each(data, function (key, value) {
                tbody_html += (
                    "\n"
                    + "<tr class='bg-light-warning'>"
                        + "<td>" + value.invoice_no + "</td>"
                        + "<td>"
                            + value.transaction_date + " "
                            + "<button data-ref='"+value.invoice_no+"' "
                                +  "class='btn btn-block btn-sm btn-primary btn-call btn-cashier-call'>"
                                + "Call"
                            + "</button>"
                        + "</td>"
                    + "</tr>"
                );
            });

            $("#invoice-pending-tbody").html(tbody_html);
        }).fail(defaultErrorHandler);
    }

    $(document).on("click",".btn-cashier-call",function () {
        $("#barcode").val("");

        var this_ref = $(this).data("ref");

        $("#barcode").val(this_ref);
        $("#barcode").change();
    });

    $(document).ready(function () {
        //load pending invoices
        loadPendingInvoices();

        $("#customer-name-div").hide();

        initializeCustomersSelect2('#customer_id_in_select')


        $(document).on('click', '.paymentchooser', function () {
            var method = $(this).data("method");
            
            $("#auth_code_block").css(
                "display",
                isAuthCodeRequired && ['CreditCard', 'Split'].indexOf(method) != -1
                    ? "block"
                    : "none"
            );

            $('#payment_method').val(method);
            var credit_card_charge_percent = $("#credit_card_charge_percent").val();
            /*start::split*/
            if(method=='Split')
            {
                $('.split_choose_div').show();
                $('#card_data').hide();
                $('#payment_method').val("Split");
                loadSplitPaymentAccounts();
                $("#split_bank_charge").val(credit_card_charge_percent);
                $('#commission').val('0').trigger('change');
                var card_charge = $('#bank_charge_hidden').val();
            }
            else{
                $('#card_data').show();
                $('.split_choose_div').hide();
                $("#split_bank_charge").val(0);
                var card_charge = $('#bank_charge_hidden').val();
            }
            /*end::split*/

            $("#bank_charge").val((method != 'CreditCard') ? 0 : card_charge);
            loadBanks(method);
            $('.paymentchooser').css('background-color', '#384ad7');
            $(this).css('background-color', '#D08221');
            $('#payment_method').trigger('change');
        });

        $('#customer_id_in_select').on('change',  get_unpaid_invoices);

        $('#customer_name').on('click', function () {
            $("#customer-name-div").hide();
            $("#customerSelect").show();
        });

        function loadBanks(acc_type) {
            ajaxRequest({
                method: 'GET',
                dataType: 'json',
                url: route('API_Call', {method: 'get_bank_accounts'}),
                data: {
                    acc_type: acc_type,dimension:$('#dim_id').val()
                }, 
            })
            .done(function(data) {
                if (!data) {
                    return swal.fire('Error!', 'Something went wrong!!!', 'error');
                }

                var html = "";
                $.each(data, function (key, val) {
                    html += '<button type="button" data-id="' + val.id + '" class="btn btn-primary credit_cards">' + val.bank_account_name + '</button>';
                });

                $("#card_data").html(html);

                //Set First listed bank selected first
                $("#card_data button").eq(0).trigger('click');
            }).fail(defaultErrorHandler);

        }

        $("#barcode").change(function (e) {
            var ref = $(this).val();
            var dim_id = $("#dim_id").val();
            var params = {
                ref: ref,
                dim_id: dim_id
            };

            ajaxRequest({
                method: 'GET',
                dataType: 'json',
                url: route('API_Call', {method: 'find_invoice'}),
                data: params,
            })
            .done(function(data) {
                if (!data) {
                    return swal.fire('Error!', 'Something went wrong!!!', 'error');
                }
                    
                $("#customer_id").val(data.debtor_no);
                $("#customer_name").val(data.name);
                $("#invoice_number").val(data.reference);
                $("#invoice_number").attr('data-trans_no', data.trans_no);
                $("#invoice_number").attr('data-tran_date', data.tran_date);
                $("#trans_no").val(data.trans_no);
                $("#tran_date").val(data.tran_date);
                $("#invoice_amount").val(parseFloat(data.total_amount).toFixed(2));
                $("#paid_amount").val(parseFloat(data.alloc).toFixed(2));
                $("#display_amount").val(parseFloat(data.remaining_amount).toFixed(2));
                $("#max_alloc_for_barcode").val(parseFloat(data.remaining_amount).toFixed(2));
                $("#paying_amount").val(parseFloat(data.remaining_amount).toFixed(2));
                $("#this_alloc_amount").val(parseFloat(data.remaining_amount).toFixed(2));
                $("#this_alloc_amount").attr({"max": parseFloat(data.remaining_amount).toFixed(2)});
                $("#invoice-list").hide();
                $("#invoice_amount_final").val(parseFloat(data.total_amount).toFixed(2));
                $("#paid_amount_final").val(parseFloat(data.alloc).toFixed(2));
                $("#pay_balance_final").val(parseFloat(data.remaining_amount).toFixed(2)); //display amount final
                $("#this_alloc_final").val(parseFloat(data.remaining_amount).toFixed(2)); //alloc amount final
                $("#barcode-invoice").show();
                $("#invoice_selected_type").val('barcode');
                $("#customer-name-div").show();
                $("#customerSelect").hide();
                $('#customer_id_in_select').val([]); //previous select value cleared

                resize_invoices_table();

                update_customer_balance(data.debtor_no);
            }).fail(defaultErrorHandler);
        });

        $('#AdminModal').on('hidden.bs.modal', () => $('#PaymentModel').modal('show'));
        
        $("#commission").on('change', function () {
            const max = parseFloat(this.max) || 0;
            const paying_amount_el = document.getElementById('paying_amount');
            let paying_commission = parseFloat(this.value) || 0;
            let paying_amount = parseFloat(paying_amount_el.dataset.amount) || 0;
            
            if ($("#payment_method").val() == 'Split') {
                this.value = paying_commission = 0;
            }

            if (paying_commission > max) {
                this.value = paying_commission = max;
            }

            if (paying_commission > (paying_amount - 1)) {
                this.value = paying_commission = (paying_amount - 1);
            }

            $(paying_amount_el).val((paying_amount - paying_commission).toFixed(2)).trigger('change');
        })
    });

    $("#this_alloc_amount").change(function (e) {
        var alloc_amount = $("#this_alloc_amount").val(); //under barcode
        var max_alloc_amount = $("#max_alloc_for_barcode").val();
        $("#this_alloc_final").val(alloc_amount); //alloc amount final
    });

    function get_unpaid_invoices() {
        $("#barcode").val('');
        $("#customer_id").val($('#customer_id_in_select').val());
        $("#customer_name").val('');
        $("#invoice_number").val('');
        $("#trans_no").val('');
        $("#invoice_amount").val('');
        $("#paid_amount").val('');
        $("#display_amount").val('');
        $("#paying_amount").val('');
        $("#invoice_amount_final").val('');
        $("#paid_amount_final").val('');
        $("#pay_balance_final").val(''); //display amount final
        $("#max_alloc_for_barcode").val(''); //pay balance amount final
        $("#this_alloc_amount").val('');

        var debtor_no = $('#customer_id_in_select').val();
        var dim_id = $("#dim_id").val();
        ajaxRequest({
            method: 'GET',
            dataType: 'json',
            url: route('API_Call', {method: 'get_unpaid_invoices'}),
            data: {
                debtor_no: debtor_no,
                // except_trans_no: data.trans_no,
                dim_id : dim_id
            },
        }).done(function(data) {
            if (!data) {
                return swal.fire('Error!', 'Something went wrong!!!', 'error');
            }

            var invoiceAppendData = '';
            var i = 0;
            var invoice_amount_final = 0;
            var paid_amount_final = 0;
            var pay_balance_final = 0;

            data.forEach(function (item) {
                i++;
                var invoice_number = item.reference;
                var tran_date = item.tran_date;
                var invoice_amount = (item.total_amount);
                var paid_amount = (item.alloc);
                var display_amount = (item.remaining_amount);
                invoice_amount_final += parseFloat(invoice_amount);
                paid_amount_final += parseFloat(paid_amount);
                pay_balance_final += parseFloat(display_amount);

                invoiceAppendData += (
                    `<div
                        class="form-group row"
                        data-row="${i}"
                        id="row_${i}"
                        data-invoice_id="${invoice_number}">
                        <div class="col-md-2" style="margin-top:5px;">
                            <input
                                style="font-size:80%;"
                                disabled placeholder="0.00"
                                type="text"
                                data-trans_no="${item.trans_no}"
                                data-tran_date="${item.tran_date}"
                                class="form-control"
                                id="invoice_number_${i}"
                                autocomplete="off"
                                disabled=""
                                value="${invoice_number}">
                        </div>
                        <div class="col-md-2" style="margin-top:5px;">
                            <input
                                style="font-size:80%;"
                                disabled
                                placeholder="0.00"
                                type="text"
                                id="tran_date_${i}"
                                class="form-control"
                                placeholder="Select date"
                                value="${tran_date}"/>
                        </div>
                        <div class="col-md-2" style="margin-top:5px;">
                            <input
                                style="font-size:80%;"
                                disabled
                                placeholder="0.00"
                                type="text"
                                class="form-control"
                                id="invoice_amount_${i}"
                                autocomplete="off"
                                disabled=""
                                value="${invoice_amount}">
                        </div>
                        <div class="col-md-2" style="margin-top:5px;">
                            <input
                                style="font-size:80%;"
                                disabled
                                placeholder="0.00"
                                type="text"
                                class="form-control"
                                id="paid_amount_${i}"
                                disabled
                                value="${paid_amount}">
                        </div>
                        <div class="col-md-2" style="margin-top:5px;">
                            <input
                                style="font-size:80%;"
                                disabled
                                placeholder="0.00"
                                type="text"
                                class="form-control"
                                id="display_amount_${i}"
                                disabled
                                value="${display_amount}">
                        </div>
                        <div class="col-md-2 input-group alloc_div" style="margin-top:5px;">
                            <input
                                style="font-size:80%;"
                                type="number"
                                step="any"
                                min="0"
                                data-control="alloc_amount"
                                name="this_alloc_amount_${i}"
                                class="form-control alloc_val"
                                id="this_alloc_amount_${i}"
                                value="0.00"
                                data-value="0.00"
                                max="${display_amount}"
                                onchange="onChangeThisAlloc(${i});">
                            <input
                                type="hidden"
                                class="hidden_display_amount"
                                id="hidden_display_amount_${i}"
                                value="${display_amount}">
                            <div class="input-group-append">
                                <button
                                    style="font-size:80%;"
                                    class="btn btn-success alloc-btn"
                                    onclick="update_this_alloc(${i})"
                                    data-control="btn_alloc"
                                    type="button">
                                    all
                                </button>
                            </div>
                        </div>
                    </div>`
                );
            })

            $("#invoice-list").html(invoiceAppendData);
            $("#invoice_amount_final").val((invoice_amount_final.toFixed(2)));
            $("#paid_amount_final").val((paid_amount_final.toFixed(2)));
            $("#pay_balance_final").val((pay_balance_final.toFixed(2))); //display amount final
            $("#invoice-list").show();
            $("#barcode-invoice").hide();
            $("#invoice_selected_type").val('select_dropdown');
            $("#customer-name-div").hide();
            $("#customerSelect").show();
            update_max_alloc();

            resize_invoices_table();

            update_customer_balance($('#customer_id_in_select').val());
        }).fail(defaultErrorHandler);
    }

    $(document).on('click', '.credit_cards', function () {
        var bank_acc = $(this).data("id");
        $("#bank_acc").val(bank_acc);
    });

    function fetchBarcode() {
        $("#barcode").trigger("change");
    }

    function update_customer_balance(debtor_no) {
        ajaxRequest({
            method: 'GET',
            dataType: 'json',
            url: route('API_Call', {method: 'get_customer_balance'}),
            data: {
                customer_id: debtor_no || -1
            },
        }).done(function(data) {
            if (!data) {
                return toastr.error("Couldn't fetch customer balance");
            }

            $("#customer_balance").html("Balance : <b>"+parseFloat(data.customer_balance).toFixed(2)+"</b>");
        });
    }

    function calculateChange() {
        var paying_amount = parseFloat($("#amount_to_be_collected").text()) || 0;
        var given_amount = parseFloat($('#given_amount').val()) || 0;
        var payment_method = $("#payment_method").val();

        if (payment_method == 'Split') {
            paying_amount = parseFloat($('#cash_amt').val()) || 0;
        }

        if (paying_amount <= 0 || given_amount <= 0)
            return false;

        var change_amount = given_amount - paying_amount;

        $("#change_amount").val(change_amount.toFixed(2));
    }

    $("#payment_method, #given_amount, #discount, #paying_amount, #bank_charge, #rounded_difference, #split_bank_charge, #card_amt, #cash_amt").on('change', () => {
        calculateChange();
        updateAmountToBeCollected();
    });

    function updateAmountToBeCollected() {
        var payment_amount = parseFloat($("#paying_amount").val()) || 0;
        var charge = parseFloat($("#bank_charge").val()) || 0;
        var split_bank_charge = parseFloat($('#split_bank_charge').val()) || 0;
        var card_amount = parseFloat($('#card_amt').val()) || 0;
        var discount = parseFloat($("#discount").val()) || 0;
        var round_off = parseFloat($("#rounded_difference").val()) || 0;

        var coll_amount = payment_amount - discount;

        if ($("#payment_method").val() == 'Split') {
            coll_amount += (card_amount * split_bank_charge / 100);
        }

        else if (charge) {
            coll_amount += (coll_amount * charge / 100);
        }

        $("#amount_before_rounding").html(coll_amount.toFixed(2));
        $("#amount_to_be_collected").html(parseFloat(coll_amount + round_off).toFixed(2));
    }

    function loadSplitPaymentAccounts() {
         ajaxRequest({
            method: 'GET',
            dataType: 'json',
            url: route('API_Call', {method: 'get_split_accounts'}),
            data: {
                dimension_id: $("#dim_id").val()
            }
        }).done(function(data) {
            if (!data) {
                return swal.fire('Error!', 'Something went wrong!!!', 'error');
            }

            var cash_accounts = data.cash_accounts
            var card_accounts = data.card_accounts

            var cash_html = "";
            var i = 0;
            $.each(cash_accounts, function (key, val) {
                var selected = "";
                var extra_cls = "";
                var style = "";

                if (i === 0) {
                    selected = "selected";
                    extra_cls = "active";
                }

                cash_html += '<label class="btn btn-primary m-2 ac_list ' + extra_cls + '" style="' + style + '">' +
                    '<input ' + selected + ' type="radio" name="split_cash_account" class="payment_account " style="visibility:hidden;" value="' + val.id + '" >' +
                    val.bank_account_name +
                    '</label>';
                i++;
            });

            $("#cash_accounts_list").html(cash_html);
            var first_button = $("#cash_accounts_list label").eq(0);
            $("input[name='split_cash_account']").eq(0).attr('checked','true');
            $(first_button).css({background: "#d08221"});
            $(first_button).css({border: "0px solid black"});
            $(first_button).css({color: "white"});

            var card_html = "";
            var j = 0;
            $.each(card_accounts, function (key, val) {
                var selected = "";
                var extra_cls = "";
                var style="";
                if (j === 0) {
                    selected = "selected";
                    extra_cls = "active";
                }
                else {
                    selected = "";
                }
                card_html += '<label class="btn btn-primary m-2 card_ac_list ' + extra_cls + ' " style="' + style + '" data-id="' + val.id + '" >' +
                    '<input ' + selected + ' type="radio" name="split_card_account" class="payment_account " style="visibility:hidden;" value="' + val.id + '" >' +
                    val.bank_account_name +
                    '</label>';
                j++;
            });

            $("#card_accounts_list").html(card_html);
            var first_button = $("#card_accounts_list label").eq(0);
            $("input[name='split_card_account']").eq(0).attr('checked','true');
            $(first_button).css({background: "#d08221"});
            $(first_button).css({border: "0px solid black"});
            $(first_button).css({color: "white"});


            /**
             * Check whether the cash and card accounts are exists.
             * If not, Show error msg and hide the place invoice btn
             */
            $("#confirm_place_invoice").show();

            if (cash_accounts.length === 0) {
                $("#btn_make_payment").hide();
                $("#cash_accounts_list").html("<p style='color:red; font-weight: bold; padding: 3px'>" +
                    "No payment accounts found for the selected CASH payment method.</p>");
            }

            if (card_accounts.length === 0) {
                $("#btn_make_payment").hide();
                $("#card_accounts_list").html("<p style='color:red; font-weight: bold; padding: 3px'>" +
                    "No payment accounts found for the selected CARD payment method.</p>");
            }
        });
    }

    $(document).on('click', '.card_ac_list', function () {
        $('input[name="split_card_account"]').removeAttr('selected');
        $('.card_ac_list').css('background-color', '#384ad7');
        $(this).css('background-color', '#D08221');
        $(this).css('border','0px solid black');
        $(this).css('color','white');
        var value= $(this).find('input[name="split_card_account"]').val();
        $(this).find("input[name='split_card_account'][value='"+ value +"']").attr('selected','selected');
    });

    $(document).on('click', '.cash_ac_list', function () {
        $('input[name="split_cash_account"]').removeAttr('selected');
        $('.cash_ac_list').css('background-color', '#384ad7');
        $(this).css('background-color', '#D08221');
        $(this).css('border','0px solid black');
        $(this).css('color','white');
        var value= $(this).find('input[name="split_cash_account"]').val();
        $(this).find("input[name='split_cash_account'][value='"+ value +"']").attr('selected','selected');
    });

    $('#cash_amt, #paying_amount').change(function() {
        var cash_amount = parseFloat($("#cash_amt").val()) || 0;
        var discount = parseFloat($('#discount').val()) || 0;
        var paying_amount = parseFloat($("#paying_amount").val()) || 0;

        if (cash_amount > (paying_amount - discount)) {
            cash_amount = paying_amount - discount;
            $("#cash_amt").val(cash_amount);
        }

        $('#card_amt').val((paying_amount - discount - cash_amount).toFixed(2)).trigger('change');
        calculate_split_bank_charge();
    });

    $('#split_bank_charge').change(function() {
        calculate_split_bank_charge();
    });

    function calculate_split_bank_charge() {
        var card_amount = $("#card_amt").val();
        var split_bank_charge=$("#split_bank_charge").val();

        $("#lbl_total_card_amnt").html('TOTAL AMOUNT  : ' + (parseFloat(card_amount)+((parseFloat(card_amount)*split_bank_charge)/100)).toFixed(2));
    }
</script>
<?php $GLOBALS['__FOOT__'][] = ob_get_clean() ?>