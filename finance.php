<div class="kt-container  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch">
    <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
        <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <!-- begin:: Content Head -->


            <style>
                .kt-iconbox {
                    padding: 8px !important;
                }
            </style>

            <!-- end:: Content Head -->

            <!-- begin:: Content -->
            <div class="kt-container  kt-grid__item kt-grid__item--fluid">

                <!--Begin::Dashboard 2-->

                <!--Begin::Row-->


                <div class="kt-subheader kt-subheader-custom   kt-grid__item">
                    <div class="kt-container ">
                        <div class="kt-subheader__main">
                            <h3 class="kt-subheader__title"><?= trans('JOURNALS') ?></h3>
                        </div>
                    </div>
                </div>

                <div class="row">


                    <?= createMenuTile('SA_JOURNALENTRY',trans('Journal Entry'),
                        trans('Normal Journal Vouchers'),getRoute('journal_entry'),'fa-print') ?>


                    <?= createMenuTile('SA_GLANALYTIC',trans('Journal Inquiry'),
                        trans('Manage Journals'),getRoute('journal_inquiry'),'fa-info') ?>



                    <?= createMenuTile('SA_GLTRANSVIEW',trans('Ledger Inquiry'),
                        trans('Ledger Inquiry'),getRoute('gl_inquiry'),'fa-info') ?>


                    <?= createMenuTile(
                        'SA_SUBLEDSUMMREP',
                        trans('Sub-ledger Summary Report'),
                        trans('Sub-ledger Summary Report'),
                        erp_url('ERP/gl/inquiry/subledger_summary_report.php'),
                        'fa-list'
                    ) ?>

                    <?= createMenuTile('SA_BANKTRANSFER',trans('REFUND TO CUSTOMER'),
                        trans('Refund to Customer'),'customer_refund.php','fa-money-bill-alt') ?>


                    <?= createMenuTile('SA_GLACCOUNT',trans('Chart Of Accounts'),
                        trans('Manage COA'),getRoute('chart_of_accounts'),'fa-swatchbook') ?>

                    <?php if ($_SESSION['wa_current_user']->is_developer_session): ?>
                        <?= createMenuTile('SA_DIMENSION',trans('Create Cost Center'),
                            trans('Create Cost Center'),'ERP/dimensions/dimension_entry.php?','fa-swatchbook') ?>

                        <?= createMenuTile('SA_DIMTRANSVIEW',trans('Cost Center List'),
                            trans('Cost Center List'),'ERP/dimensions/inquiry/search_dimensions.php?','fa-swatchbook') ?>
                    <?php endif; ?>
                    
                    <?= createMenuTile(
                        'SA_LEAVE_ACCRUALS',
                        trans('Leave Accruals'),
                        trans("Post or verify employees' leave accruals"),
                        'ERP/hrm/leave_accrual.php',
                        'fa-clone'
                    ) ?>

                    <?= createMenuTile(
                        'SA_GRATUITY_ACCRUALS',
                        trans('Gratuity Accruals'),
                        trans("Post or verify employees' gratuity accruals"),
                        'ERP/hrm/gratuity_accrual.php',
                        'fa-clone'
                    ) ?>

                </div>




                <div class="kt-subheader kt-subheader-custom   kt-grid__item">
                    <div class="kt-container ">
                        <div class="kt-subheader__main">
                            <h3 class="kt-subheader__title"><?= trans('VOUCHERS') ?></h3>
                        </div>
                    </div>
                </div>


                <div class="row">

                    <?= createMenuTile('SA_PAYMENT',trans('Payment Voucher'),
                        trans('Payment Voucher Entry'),'ERP/gl/gl_bank.php?NewPayment=Yes','fa-money-bill-alt') ?>

                    <?= createMenuTile('SA_DEPOSIT',trans('Receipts Voucher'),
                        trans('Receipt Voucher Entry'),'ERP/gl/gl_bank.php?NewDeposit=Yes','fa-money-bill-alt') ?>

                    <?= createMenuTile('SA_BANKTRANSFER',trans('Bank Transfer'),
                        trans('Bank to Bank Transfer'),getRoute('bank_transfer'),'fa-money-bill-alt') ?>


                    <?= createMenuTile('SA_BANKTRANSFER',trans('E DIRHAM Recharge'),
                        trans('E Dirham Recharge Entry'),getRoute('edirham_recharge'),'fa-plug') ?>



                </div>





                <div class="kt-subheader kt-subheader-custom   kt-grid__item">
                    <div class="kt-container ">
                        <div class="kt-subheader__main">
                            <h3 class="kt-subheader__title"><?= trans('RECONCILIATION') ?></h3>
                        </div>
                    </div>
                </div>



                <div class="row">


                    <?= createMenuTile('SA_BANKACCOUNT',trans('Bank Accounts'),
                        trans('Add and Manage Bank Accounts'),getRoute('bank_accounts'),'fa-lock') ?>

                    <?= createMenuTile('SA_RECONCILE',trans('Reconciliation'),
                        trans('Manually Reconcile Bank A/C'),getRoute('manual_reconciliation'),'fa-check-double') ?>


                    <?= createMenuTile('SA_RECONCILE',trans('Auto Reconciliation'),
                        trans('Auto Bank Reconciliation by CSV'),'auto_reconcile.php','fa-check-double') ?>


                    <?= createMenuTile('SA_GLCLOSE',trans('Closing GL Transactions'),
                        trans('Closing GL Transactions'),'ERP/gl/manage/close_period.php','fa-lock') ?>
                    

                </div>





                <!--End::Row-->



                <!--End::Row-->

                <!--End::Dashboard 2-->
            </div>

            <!-- end:: Content -->
        </div>
    </div>
</div>