<?php include "header.php" ?>

<style>
    .denom-amt {
        text-align: right !important;
    }

    /* Chrome, Safari, Edge, Opera */
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* Firefox */
    input[type=number] {
        -moz-appearance: textfield;
    }



    select[readonly].select2-hidden-accessible + .select2-container {
        pointer-events: none;
        touch-action: none;
    }

    select[readonly].select2-hidden-accessible + .select2-container .select2-selection {
        background: #eee;
        box-shadow: none;
    }

    select[readonly].select2-hidden-accessible + .select2-container .select2-selection__arrow, select[readonly].select2-hidden-accessible + .select2-container .select2-selection__clear {
        display: none;
    }

</style>

<div class="kt-container  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch">
    <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
        <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <!-- begin:: Content Head -->


            <!-- end:: Content Head -->

            <!-- begin:: Content -->
            <div class="kt-container  kt-grid__item kt-grid__item--fluid">


                <div class="row">
                    <div class="kt-portlet">
                        <div class="kt-portlet__head" style="text-align: center !important;">
                            <div class="kt-portlet__head-label" style="width: 100%;display: grid !important;">
                                <h3 class="kt-portlet__head-title">
                                    <?= trans('CASH HANDOVER ENTRY') ?>
                                </h3>
                            </div>
                        </div>
                        <?php 
                            $role_ids = implode(',', $api->getRoleIdsWithAccess('SA_CASH_HANDOVER'));
                            if ($role_ids) {
                                $users = db_query(
                                    "SELECT id, user_id FROM 0_users WHERE role_id IN ($role_ids)"
                                )->fetch_all(MYSQLI_ASSOC);
                            } else {
                                $users = [];
                            }

                            $bankAccounts = $api->get_records_from_table(
                                '0_bank_accounts', 
                                ['id', 'bank_account_name']
                            );

                            $_GET['user_id'] = $_SESSION['wa_current_user']->user;
                            $user = $api->get_user_info('array')['data'];
                            $user['bank_account_name'] = array_column($bankAccounts, 'bank_account_name', 'id')[$user['cashier_account']];

                            $userCanUse = $_SESSION['wa_current_user']->can_access('SA_CASH_HANDOVER');
                            $userCanManage = $_SESSION['wa_current_user']->can_access('SA_CASH_HANDOVER_ALL');

                            if (!$userCanUse && !$userCanManage) {
                                echo (
                                    '<div class="w-100 text-center align-middle h-25">
                                        <span>The security settings on your account do not permit you to access this function</span>
                                    </div>
                                    </div></div></div></div></div></div></body></html>'
                                );
                                exit();
                            }

                            $cashTotal = '0.00';
                            $summary = [
                                'Cash' => '0.00',
                                'CreditCard' => '0.00',
                                'BankTransfer' => '0.00',
                                '_total' => '0.00'
                            ];
                            $roundedGlBalance = '0.00';
                            $adjustments = '0.00';
                            if (!isset($_GET['trans_date'])) {
                                $_GET['trans_date'] = Today();
                            }

                            if (!$userCanManage) {
                                if (empty($user['cashier_account'])) {
                                    echo (
                                        '<div class="alert alert-warning mx-5" role="alert">
                                            <span class="fa fa-exclamation-triangle mt-1 mr-2"></span>
                                            Could Not find any Cashier A/C associated with this user!
                                        </div>
                                        </div></div></div></div></div></div></body></html>'
                                    );
                                    exit();
                                }

                                if (!($user['bank_account'] = get_bank_gl_account($user['cashier_account']))){
                                    echo (
                                        '<div class="alert alert-warning mx-5" role="alert">
                                            <span class="fa fa-exclamation-triangle mt-1 mr-2"></span>
                                            This cashier account is not a bank account!
                                        </div>
                                        </div></div></div></div></div></div></body></html>'
                                    );
                                    exit();
                                }

                                $_GET['cash_acc'] = $user['cashier_account'];
                                $cashTotal = $api->getBalanceCashCollection('array')['data'];
                                $summary = $api->getPaymentSummaryByMethod('array')['data'];
                                $summary['Cash'] = $cashTotal;
                                $summary['_total'] =  (float)$summary['Cash']
                                                    + (float)$summary['CreditCard']
                                                    + (float)$summary['BankTransfer'];
                                $roundedGlBalance = ceil($cashTotal / 0.25) * 0.25;
                                $adjustments = round2($roundedGlBalance - $cashTotal, 2);
                            }
                        ?>
                        <!--begin::Form-->
                        <form method="post" action="#" id="cash-handover-form" style="margin-top: 10px"
                              class=" kt-form kt-form--fit kt-form--label-right">
                            <div class="kt-portlet__body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div style="max-width:500px" class="mt-5">
                                            <div class="form-group form-group-sm row">
                                                <label for="user_id" class="col-4 col-form-label">Employee Name: </label>
                                                <div class="col-8">
                                                    <?php if($userCanManage): ?>
                                                        <select class="form-control kt-select2 ap-select2"
                                                            name="user_id" id="user_id">
                                                            <?= prepareSelectOptions(
                                                                    $users,
                                                                    'id', 
                                                                    'user_id', 
                                                                    $user['id'], 
                                                                    "Select an Employee"
                                                                ) 
                                                            ?>
                                                        </select>
                                                    <?php else : ?>
                                                        <input 
                                                            type="hidden" 
                                                            id="user_id"
                                                            name="user_id"
                                                            value="<?= $user['id'] ?>">
                                                        <input 
                                                            type="text" 
                                                            readonly 
                                                            class="form-control-plaintext" 
                                                            id="user_name"
                                                            name="user_name"
                                                            value="<?= empty(trim($user['real_name'])) ? $user['user_id'] : $user['real_name'] ?>">
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="form-group form-group-sm row">
                                                <label for="cash_acc" class="col-4 col-form-label">Cash A/C: </label>
                                                <div class="col-8">
                                                    <?php if($userCanManage): ?>
                                                        <select class="form-control kt-select2 ap-select2"
                                                                name="cash_acc" 
                                                                id="cash_acc" 
                                                                readonly>
                                                            <?= 
                                                                prepareSelectOptions(
                                                                    $bankAccounts,
                                                                    'id', 
                                                                    'bank_account_name', 
                                                                    false, 
                                                                    "--"
                                                                ) 
                                                            ?>
                                                        </select>
                                                    <?php else : ?>
                                                        <input 
                                                            type="hidden"
                                                            name="cash_acc"
                                                            id="cash_acc"
                                                            value="<?= $user['cashier_account'] ?>">
                                                        <input 
                                                            type="text" 
                                                            readonly 
                                                            class="form-control-plaintext" 
                                                            name="cash_acc_name"
                                                            id="cash_acc_name"
                                                            value="<?= $user['bank_account_name'] ?>">
                                                    <?php endif; ?> 
                                                </div>
                                            </div>
                                            <div class="form-group form-group-sm row">
                                                <label for="trans_date" class="col-4 col-form-label">Date: </label>
                                                <div class="col-8">
                                                    <div 
                                                        class="input-group date" 
                                                        data-provide="datepicker"
                                                        data-date-format="<?= getDateFormatForBSDatepicker() ?>"
                                                        data-date-autoclose="true"
                                                        data-date-end-date="0d"
                                                        data-date-today-btn="linked"
                                                        data-date-today-highlight="true">
                                                        <input 
                                                            type="text" 
                                                            name="trans_date" 
                                                            id="trans_date"
                                                            class="form-control"
                                                            placeholder="--select date--"
                                                            value="<?= $_GET['trans_date'] ?>">
                                                        <div class="input-group-append">
                                                            <div class="input-group-text input-group-addon">
                                                                <span class="fa fa-calendar-alt"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group form-group-sm row">
                                                <label for="total_cash" class="col-4 col-form-label">Cash: </label>
                                                <div class="col-8">
                                                    <input 
                                                        type="text" 
                                                        name="total_cash" 
                                                        id="total_cash" 
                                                        class="form-control-plaintext"
                                                        value="<?= $cashTotal ?>"
                                                        readonly/>
                                                </div>
                                            </div>
                                            <div class="form-group form-group-sm row">
                                                <label for="total_credit" class="col-4 col-form-label">Credit Card: </label>
                                                <div class="col-8">
                                                    <input 
                                                        type="text" 
                                                        name="total_credit" 
                                                        id="total_credit" 
                                                        class="form-control-plaintext"
                                                        value="<?= $summary['CreditCard'] ?>"
                                                        readonly/>
                                                </div>
                                            </div>
                                            <div class="form-group form-group-sm row">
                                                <label for="total_bank_transfer" class="col-4 col-form-label">Bank Transfer: </label>
                                                <div class="col-8">
                                                    <input 
                                                        type="text" 
                                                        name="total_bank_transfer" 
                                                        id="total_bank_transfer" 
                                                        class="form-control-plaintext"
                                                        value="<?= $summary['BankTransfer'] ?>"
                                                        readonly/>
                                                </div>
                                            </div>
                                            <div class="form-group form-group-sm row">
                                                <label for="gross_total" class="col-4 col-form-label">Tot. A/C Bal.: </label>
                                                <div class="col-8">
                                                    <input 
                                                        type="text" 
                                                        name="gross_total" 
                                                        id="gross_total" 
                                                        class="form-control-plaintext"
                                                        value="<?= $summary['_total'] ?>"
                                                        readonly/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="text-center" style="max-width:400px">
                                            <p>Enter Denominations</p>
                                            <style>
                                                .denom-table tbody > tr > td:nth-child(2),
                                                .denom-table tbody > tr > td:nth-child(3),
                                                .denom-table tbody > tr > td:nth-child(4) {
                                                    vertical-align: middle;
                                                }

                                                .denom-table tfoot td {
                                                    vertical-align: middle;
                                                }

                                                .denom-table  tbody > tr > td:nth-child(1),
                                                .denom-table  tbody > tr > td:nth-child(3),
                                                .denom-table  tbody > tr > td:nth-child(5),
                                                .denom-table  tbody > tr > td:nth-child(1) > input,
                                                .denom-table  tbody > tr > td:nth-child(3) > input,
                                                .denom-table  tbody > tr > td:nth-child(5) > input {
                                                    text-align: right;
                                                }
                                            </style>
                                            <table class="denom-table table table-hover table-borderless mx-auto">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 26%"></th>
                                                        <th></th>
                                                        <th style="width: 15%"></th>
                                                        <th></th>
                                                        <th></th>
                                                    </tr>
                                                </thead>

                                                <tbody class="border-top border-bottom">
                                                    <tr>
                                                        <td>
                                                            <input 
                                                                type="number" 
                                                                data-value="1000" 
                                                                name="denom1000_pcs"
                                                                id="denom1000_pcs" 
                                                                class="form-control form-control-sm denom-pcs"
                                                                min="0"
                                                                step="1"
                                                                value="0">
                                                            </td>
                                                        <td>x</td>
                                                        <td>1000</td>
                                                        <td>=</td>
                                                        <td>
                                                            <input type="text" name="denom1000_amt" id="denom1000_amt"
                                                                class="form-control form-control-sm denom-amt" value="0" disabled/>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>
                                                            <input 
                                                                type="number" 
                                                                data-value="500" 
                                                                name="denom500_pcs"
                                                                id="denom500_pcs" 
                                                                min="0"
                                                                step="1"
                                                                value="0"
                                                                class="form-control form-control-sm denom-pcs"/>
                                                        </td>
                                                        <td>x</td>
                                                        <td>500</td>
                                                        <td>=</td>
                                                        <td>
                                                            <input type="text" name="denom500_amt" id="denom500_amt"
                                                                class="form-control form-control-sm denom-amt" value="0" disabled/>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>
                                                            <input 
                                                                type="number" 
                                                                data-value="200" 
                                                                name="denom200_pcs"
                                                                id="denom200_pcs"
                                                                min="0"
                                                                step="1"
                                                                value="0"
                                                                class="form-control form-control-sm denom-pcs"/>
                                                        </td>
                                                        <td>x</td>
                                                        <td>200</td>
                                                        <td>=</td>
                                                        <td>
                                                            <input type="text" name="denom200_amt" id="denom200_amt"
                                                                class="form-control form-control-sm denom-amt" value="0" disabled/>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>
                                                            <input 
                                                                type="number" 
                                                                data-value="100" 
                                                                name="denom100_pcs"
                                                                id="denom100_pcs"
                                                                min="0"
                                                                step="1"
                                                                value="0" 
                                                                class="form-control form-control-sm denom-pcs"/>
                                                        </td>
                                                        <td>x</td>
                                                        <td>100</td>
                                                        <td>=</td>
                                                        <td>
                                                            <input type="text" name="denom100_amt" id="denom100_amt"
                                                                class="form-control form-control-sm denom-amt" value="0" disabled/>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>
                                                            <input 
                                                                type="number" 
                                                                data-value="50" 
                                                                name="denom50_pcs" 
                                                                id="denom50_pcs"
                                                                min="0"
                                                                step="1"
                                                                value="0"
                                                                class="form-control form-control-sm denom-pcs"/>
                                                        </td>
                                                        <td>x</td>
                                                        <td>50</td>
                                                        <td>=</td>
                                                        <td>
                                                            <input type="text" name="denom50_amt" id="denom50_amt"
                                                                class="form-control form-control-sm denom-amt" value="0" disabled/>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>
                                                            <input 
                                                                type="number" 
                                                                data-value="20" 
                                                                name="denom20_pcs" 
                                                                id="denom20_pcs"
                                                                min="0"
                                                                step="1"
                                                                value="0"
                                                                class="form-control form-control-sm denom-pcs"/>
                                                        </td>
                                                        <td>x</td>
                                                        <td>20</td>
                                                        <td>=</td>
                                                        <td>
                                                            <input type="text" name="denom20_amt" id="denom20_amt"
                                                                class="form-control form-control-sm denom-amt" value="0" disabled/>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>
                                                            <input 
                                                                type="number" 
                                                                data-value="10" 
                                                                name="denom10_pcs" 
                                                                id="denom10_pcs"
                                                                min="0"
                                                                step="1"
                                                                value="0"
                                                                class="form-control form-control-sm denom-pcs"/>
                                                        </td>
                                                        <td>x</td>
                                                        <td>10</td>
                                                        <td>=</td>
                                                        <td>
                                                            <input type="text" name="denom10_amt" id="denom10_amt"
                                                                class="form-control form-control-sm denom-amt" value="0" disabled/>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>
                                                            <input 
                                                                type="number" 
                                                                data-value="5" 
                                                                name="denom5_pcs" 
                                                                id="denom5_pcs"
                                                                min="0"
                                                                step="1"
                                                                value="0"
                                                                class="form-control form-control-sm denom-pcs"/>
                                                        </td>
                                                        <td>x</td>
                                                        <td>5</td>
                                                        <td>=</td>
                                                        <td>
                                                            <input type="text" name="denom5_amt" id="denom5_amt"
                                                                class="form-control form-control-sm denom-amt" value="0" disabled/>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>
                                                            <input 
                                                                type="number" 
                                                                data-value="1" 
                                                                name="denom1_pcs" 
                                                                id="denom1_pcs"
                                                                min="0"
                                                                step="1"
                                                                value="0"
                                                                class="form-control form-control-sm denom-pcs"/>
                                                        </td>
                                                        <td>x</td>
                                                        <td>1</td>
                                                        <td>=</td>
                                                        <td>
                                                            <input type="text" name="denom1_amt" id="denom1_amt"
                                                                class="form-control form-control-sm denom-amt" value="0" disabled/>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>
                                                            <input 
                                                                type="number" 
                                                                data-value="0.5" 
                                                                name="denom0_5_pcs"
                                                                id="denom0_5_pcs"
                                                                min="0"
                                                                step="1"
                                                                value="0" 
                                                                class="form-control form-control-sm denom-pcs"/>
                                                        </td>
                                                        <td>x</td>
                                                        <td>0.50</td>
                                                        <td>=</td>
                                                        <td>
                                                            <input type="text" name="denom0_5_amt" id="denom0_5_amt"
                                                                class="form-control form-control-sm denom-amt" value="0" disabled/>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>
                                                            <input 
                                                                type="number" 
                                                                data-value="0.25" 
                                                                name="denom0_25_pcs"
                                                                id="denom0_25_pcs"
                                                                min="0"
                                                                step="1"
                                                                value="0" 
                                                                class="form-control form-control-sm denom-pcs"/>
                                                        </td>
                                                        <td>x</td>
                                                        <td>0.25</td>
                                                        <td>=</td>
                                                        <td>
                                                            <input type="text" name="denom0_25_amt" id="denom0_25_amt"
                                                                class="form-control form-control-sm denom-amt" value="0" disabled/>
                                                        </td>
                                                    </tr>
                                                </tbody>

                                                <tfoot>
                                                    <tr>
                                                        <td colspan="4" class="text-right">
                                                            Total Amt. to be Payed:
                                                        </td>
                                                        <td>
                                                            <input 
                                                                type="number" 
                                                                name="emp_total" 
                                                                id="emp_total"
                                                                class="form-control-plaintext form-control-sm denom-amt" 
                                                                data-actual-total="<?= $roundedGlBalance ?>"
                                                                readonly
                                                                step="0.01"
                                                                value="<?= $cashTotal ?>">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="4" class="text-right">
                                                            Adjustments:
                                                        </td>
                                                        <td>
                                                            <input 
                                                                type="number" 
                                                                name="denom_adj" 
                                                                id="denom_adj"
                                                                class="form-control-plaintext form-control-sm denom-amt" 
                                                                readonly
                                                                step="0.01"
                                                                value="<?= $adjustments ?>">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="4" class="text-right">
                                                            Total Transferring Amt.
                                                        </td>
                                                        <td>
                                                            <input 
                                                                type="number" 
                                                                name="denom_total" 
                                                                id="denom_total"
                                                                class="form-control-plaintext form-control-sm denom-amt" 
                                                                value="0.00" 
                                                                readonly>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="4" class="text-right">
                                                            Balance
                                                        </td>
                                                        <td>
                                                            <input 
                                                                type="number" 
                                                                name="amt_bal" 
                                                                id="amt_bal"
                                                                class="form-control-plaintext form-control-sm denom-amt" 
                                                                readonly 
                                                                value="0.00">
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="kt-portlet__foot kt-portlet__foot--fit-x">
                                <div class="kt-form__actions">
                                    <div class="row">
                                        <div class="col-lg-12" style="text-align: center">
                                            <button 
                                                type="button" 
                                                onclick="SaveData();"
                                                class="btn btn-success"><?= trans('PROCESS') ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>

            <!-- end:: Content -->
        </div>
    </div>
</div>

<?php include "footer.php"; ?>

<script>
    $(document).ready(function () {

        <?php if($userCanManage): ?>
            AxisPro.APICall('GET', route('API_Call', {method: 'get_all_customers'}), {}, function (data) {
                AxisPro.PrepareSelectOptions(data, 'debtor_no', 'name', 'ap-customer-select', 'Select a customer');
            });


            $('#user_id').on("change", function(ev) {

                if (!this.value) {
                    $("#cash_acc").val('');
                    $("#cash_acc").trigger('change');
                    return false;
                }

                var responseHandler = function (resp) {
                    if (resp.status === 'OK') {
                        var cashier_acc = resp.data.cashier_account;
                        if (cashier_acc.length > 0) {
                            $("#cash_acc").val(cashier_acc);
                            $("#cash_acc").trigger('change')
                        }
                        else {
                            $("#cash_acc").val('').trigger('change');
                            swal.fire('FAILED', 'No Cashier A/C set for this cashier.', 'warning');
                        }
                    } else {
                        swal.fire('Error!', 'Could not retrieve cashier A/C', 'warning');
                    }
                }

                AxisPro.APICall('GET', route('API_Call', {method: 'get_user_info'}), {
                    user_id: this.value
                }, responseHandler);
            })

            $("#cash_acc").change(function () {
                getAccountBalance();
            });

            if (document.getElementById('user_id').value.length > 0) {
                $('#user_id').trigger('change');
            }
        <?php endif; ?>

        $('.denom-table input[type="number"]').keyup(calculateDenomTotal);

        $("#trans_date").change(function () {
            getAccountBalance();
        })
    });


    function getAccountBalance() {
        var trans_date = document.getElementById("trans_date").value;
        var user_id = document.getElementById('user_id').value;
        var cash_acc = document.getElementById("cash_acc").value;

        AxisPro.APICall('GET', route('API_Call', {method: 'getBalanceCashCollection'}), {
            trans_date: trans_date,
            user_id: user_id,
            cash_acc: cash_acc
        }, function (resp) {
            if (resp.status === 'OK') {
                updatePaymentSummary(trans_date, user_id, resp.data);
            } else if (resp.status === 'FAIL'){
                swal.fire('FAILED', resp.msg, 'warning');
            }
        });
    }

    function updatePaymentSummary(trans_date, user_id, cash) {
        AxisPro.APICall('GET', route('API_Call', {method: 'getPaymentSummaryByMethod'}), {
            trans_date: trans_date,
            user_id: user_id
        }, function (resp) {
            if (resp.status === 'OK') {
                resp.data.Cash = cash;
                updatePaymentSummaryInUI(resp.data);
            } else if (resp.status === 'FAIL'){
                swal.fire('FAILED', resp.msg, 'warning');
            }
        });
    }

    function updatePaymentSummaryInUI(summary) {
        var nTotalCash          = parseFloat(summary.Cash);
        var nTotalCreditCard    = parseFloat(summary.CreditCard);
        var nTotalBankTransfer  = parseFloat(summary.BankTransfer);

        var nTotalToPay = nTotalCash;
        var sTotalToPay = nTotalToPay.toFixed(2);
        var rounded = Math.ceil(nTotalToPay / 0.25) * 0.25;
        var empTotal = document.getElementById('emp_total');

        empTotal.value = sTotalToPay;
        empTotal.dataset.actualTotal = rounded;
        document.getElementById('total_cash').value = sTotalToPay;
        document.getElementById('denom_adj').value = (rounded - nTotalToPay).toFixed(2);

        ntotal =   nTotalCash
                + nTotalCreditCard
                + nTotalBankTransfer;
        document.getElementById('total_credit').value = nTotalCreditCard.toFixed(2);
        document.getElementById('total_bank_transfer').value = nTotalBankTransfer.toFixed(2);
        document.getElementById('gross_total').value = ntotal.toFixed(2);
    }

    function SaveData() {

        var $form = $("#cash-handover-form");
        var params = AxisPro.getFormData($form);


        AxisPro.APICall('POST', route('API_Call', {method: 'saveCashHandOverRequest'}), params, function (data) {

            if (data.status === 'FAIL') {

                swal.fire(
                    'Warning!',
                    data.msg,
                    'warning'
                );

            }
            else {

                swal.fire(
                    'Success!',
                    'Cash Hand-Over Request Placed.',
                    'success'
                ).then(function () {
                    window.location.reload();
                });

            }

        });

    }

    function emptyThenZero(val) {
        if (val === '' || parseInt(val) < 0)
            return 0;

        return parseInt(val);
    }

    function calculateDenomTotal() {
        var grandTotal = 0.00;

        $('.denom-table tbody tr').each(function (i, row) {
            var $row = $(row);

            var denomination = +($row.find('input[type="number"]').data('value'));
            var qty = +($row.find('input[type="number"]').val());
            var total = denomination * qty;
            
            $row.find('input[type="text"]').val(total);
            grandTotal += total;
        });

        var roundedActual = document.getElementById('emp_total').dataset.actualTotal;
        document.getElementById('denom_total').value = grandTotal.toFixed(2);
        document.getElementById('amt_bal').value = (grandTotal - roundedActual).toFixed(2);
    }
</script>
