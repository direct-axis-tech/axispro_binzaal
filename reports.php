<?php

use App\Models\Accounting\Dimension;
use App\Permissions;

?>
<div class="kt-container  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch">
    <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
        <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <!-- begin:: Content Head -->


            <!-- end:: Content Head -->

            <!-- begin:: Content -->
            <div class="kt-container  kt-grid__item kt-grid__item--fluid">

                <!--Begin::Dashboard 2-->

                <!--Begin::Row-->



                <div class="kt-subheader  kt-subheader-custom kt-grid__item" >
                    <div class="kt-container ">
                        <div class="kt-subheader__main">
                            <h3 class="kt-subheader__title"><?= trans('CUSTOM REPORTS') ?></h3>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <?php if(canAccessAny([
                        'SA_SRVREPORT',
                        'SA_SRVREPORTALL',
                        'SA_SRVREPORTDEP'
                    ])): $custom_reports = $api->get_custom_reports(); ?>
                        
                        <?= createMenuTile(
                            'SA_SHOWSERVREP',
                            trans('SERVICE REPORT'),
                            trans('Service Report'),
                            getRoute('service_report'),
                            'fa-file'
                        ) ?>
                        
                        <?php foreach ($custom_reports as $row): ?>
                            <div class="col-lg-3 <?= HideMenu('SA_CUSREP') ?>">
                                <div class="kt-portlet kt-iconbox kt-iconbox--warning kt-iconbox--animate-fast">
                                    <i 
                                        class="flaticon-delete del-custom-rep"
                                        onclick="AxisPro.DeleteCustomReport(<?= $row['id'] ?>)"
                                        data-id="<?= $row['id'] ?>"
                                        style="position: absolute;right: 4px;top: 4px; cursor: pointer; z-index: 999;font-size: 20px">
                                    </i>
                                    <?php //endif; ?>
                                    <div class="kt-portlet__body">
                                        <div class="kt-iconbox__body">
                                            <div class="kt-iconbox__icon">
                                                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon">
                                                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                        <rect x="0" y="0" width="24" height="24"/>
                                                        <path d="M4,4 L11.6314229,2.5691082 C11.8750185,2.52343403 12.1249815,2.52343403 12.3685771,2.5691082 L20,4 L20,13.2830094 C20,16.2173861 18.4883464,18.9447835 16,20.5 L12.5299989,22.6687507 C12.2057287,22.8714196 11.7942713,22.8714196 11.4700011,22.6687507 L8,20.5 C5.51165358,18.9447835 4,16.2173861 4,13.2830094 L4,4 Z" fill="#000000" opacity="0.3"/>
                                                        <path d="M12,11 C10.8954305,11 10,10.1045695 10,9 C10,7.8954305 10.8954305,7 12,7 C13.1045695,7 14,7.8954305 14,9 C14,10.1045695 13.1045695,11 12,11 Z" fill="#000000" opacity="0.3"/>
                                                        <path d="M7.00036205,16.4995035 C7.21569918,13.5165724 9.36772908,12 11.9907452,12 C14.6506758,12 16.8360465,13.4332455 16.9988413,16.5 C17.0053266,16.6221713 16.9988413,17 16.5815,17 C14.5228466,17 11.463736,17 7.4041679,17 C7.26484009,17 6.98863236,16.6619875 7.00036205,16.4995035 Z" fill="#000000" opacity="0.3"/>
                                                    </g>
                                                </svg>	</div>
                                            <div class="kt-iconbox__desc">
                                                <h3 class="kt-iconbox__title">
                                                    <a class="kt-link" href="<?= getRoute('service_report')."&custom_report_id=".$row['id'] ?>"><?= $row['name'] ?></a>
                                                </h3>
                                                <div class="kt-iconbox__content">
                                                    <?= trans('Custom Report') ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="kt-subheader kt-subheader-custom   kt-grid__item">
                    <div class="kt-container ">
                        <div class="kt-subheader__main">
                            <h3 class="kt-subheader__title"><?= trans('SALES REPORTS') ?></h3>
                        </div>
                    </div>
                </div>


                 <div class="row">

                    <?= createMenuTile(
                        [
                            'SA_EMPANALYTIC',
                            'SA_EMPANALYTICDEP',
                            'SA_EMPANALYTICALL'
                        ],
                        trans('Employee Sales'),
                        trans('Employee category sales inquiry'),
                        getRoute('employee_wise_sales'),
                        'fa-info'
                    ) ?>

                    <?= createMenuTile(
                        [
                            'SA_EMPCOMMAAD',
                            'SA_EMPCOMMAADDEP',
                            'SA_EMPCOMMAADALL'
                        ],
                        trans('Employee Commission - ADHEED'),
                        trans('Employee commission report for adheed'),
                        getRoute('employee_commission_adheed'),
                        'fa-info',
                        "",
                        "",
                        (
                            (
                                !user_check_access('SA_EMPCOMMAADALL')
                                && !in_array(
                                    $_SESSION['wa_current_user']->default_cost_center,
                                    [DT_ADHEED, DT_ADHEED_OTH]
                                )
                            ) || data_get(Dimension::find(DT_ADHEED_OTH), 'closed') == 1
                        )
                    ) ?>

                    <?= createMenuTile(
                        [
                            'SA_CUSTWISEALLREP',
                            'SA_CUSTWISEOWNREP'
                        ],
                        trans('Sales Summary Report'),
                        trans('Sales summary report'),
                        getRoute('customer_wise_sales_summary'),
                        'fa-users'
                    ) ?>

                    <?= createMenuTile(
                        'SA_SALESANALYTIC',
                        trans('Category Sales'),
                        trans('Category-wise sales inquiry'),
                        getRoute('category_wise_sales'),
                        'fa-info')
                    ?>

                    <?= createMenuTile(
                        'SA_SALESANALYTIC',
                        trans('Daily Report'),
                        trans('Daily report'),
                        getRoute('daily_collection'),
                        'fa-chart-area'
                    ) ?>

                    <?= createMenuTile(
                        [
                            'SA_CSHCOLLECTREP',
                            'SA_CSHCOLLECTREPALL'
                        ],
                        trans('Collection Report'),
                        trans('Collection Report'),
                        getRoute('invoice_collection'),
                        'fa-chart-bar'
                    ) ?>

                    <?= createMenuTile(
                        'SA_YBCDLYREP',
                        trans('YBC Daily Sales Report'),
                        trans('Daily sales report'),
                        'daily_report.php',
                        'fa-balance-scale'
                    ) ?>

                    <?= createMenuTile(
                        'SA_CUSTPAYMREP',
                        trans('Customer Balance'),
                        trans('Customer Balance Report'),
                        getRoute('rep_customer_balance'),
                        'fa-balance-scale')
                    ?>

                    <?= createMenuTile(
                        'SA_REP',
                        trans('Reports and Analysis'),
                        trans('Reports and Analysis'),
                        'ERP/reporting/reports_main.php?Class=6',
                        'fa-balance-scale'
                    ) ?>

                    <!-- <?= createMenuTile(
                        'SA_MGMTREP',
                        trans('Management Report'),
                        trans('Management Report'),
                        route('reports.sales.managementReport'),
                        'fa-chart-bar',
                        "_blank"
                    ) ?> -->

                    <?= createMenuTile(
                        'SA_CUSTBULKREP',
                        trans('Customer Information Report'),
                        trans('Customer Information Report'),
                        'customers.php?action=list',
                        'fa-info'
                    ) ?>

                    <?= createMenuTile(
                        'SA_INVOICEREPORT',
                        trans('Invoice Report - Payment Summary'),
                        trans('Invoice Report - Payment Summary'),
                        getRoute('invoice_report'),
                        'fa-money-bill'
                    ) ?>

                    <?= createMenuTile(
                        'SA_CUSTANALYTIC',
                        trans('Customer Balance Inquiry'),
                        trans('Customer Balance Inquiry'),
                        getRoute('customer_bal_inquiry'),
                        'fa-info'
                    ) ?>

                    <?= createMenuTile(
                        [
                            'SA_CRSALESREP_OWN',
                            'SA_CRSALESREP_DEP',
                            'SA_CRSALESREP_ALL',
                        ],
                        trans('Credit Invoice Report'),
                        trans('Credit Invoice Report'),
                        getRoute('cr_inv_report'),
                        'fa-chart-bar'
                    ) ?>

                    <?= createMenuTile(
                        'SA_CUSTDETREP',
                        trans('Customer Master'),
                        trans('View customer details'),
                        getRoute('view_cust_detail'),
                        'fa-users'
                    ) ?>

                    <?= createMenuTile(
                        'SA_CASH_HANDOVER_INQ',
                        trans('Cash Handover Report'),
                        trans('Cash Handover Report'), 
                        getRoute('cash_handover_report'), 
                        'fa-exchange-alt'
                    ) ?>

                    <?= createMenuTile(
                        'SA_RECEPTION_INVOICE',
                        trans('Reception Invoice Report'),
                        trans('Reception invoice Report'),
                        getRoute('reception_invoice'),
                        'far fa-file-invoice'
                    ) ?>
                    <?= createMenuTile(
                        'SA_SERVICEMSTRREP',
                        trans('Service List'),
                        trans('Service List'),
                        route('reports.sales.services'),
                        'fa-chart-bar',
                        "_blank"
                    ) ?>
                    <?= createMenuTile(
                        'SA_INVOICEREP',
                        trans('Invoice Report'),
                        trans('Invoice Report'),
                        route('reports.sales.invoices'),
                        'fa-chart-bar',
                        "_blank"
                    ) ?>
                    <?= createMenuTile(
                        'SA_INVOICEPMTREP',
                        trans('Invoice Payment Report'),
                        trans('Invoice Payment Report'),
                        route('reports.sales.invoicesPayments'),
                        'fa-chart-bar',
                        "_blank"
                    ) ?>
                    <?= createMenuTile(
                       [
                        'SA_SERVICETRANSREP_OWN',
                        'SA_SERVICETRANSREP_ALL'
                       ], 
                        trans('Service Report'),
                        trans('Service Report'),
                        route('reports.sales.serviceTransactions'),
                        'fa-chart-bar',
                        "_blank"
                    ) ?>
                    <?= createMenuTile(
                        'SA_VOIDEDTRANSACTIONS',
                        trans('Voided Transaction Report'),
                        trans('Voided Transaction Report'),
                        route('reports.sales.voidedTransactions'),
                        'fa-chart-bar',
                        "_blank"
                    ) ?>

                    <?= createMenuTile(
                        'SA_AUTOFETCHREPORT',
                        trans('Auto Fetch Report'),
                        trans('Auto Fetch Report'),
                        route('reports.sales.autoFetchTransactions'),
                        'fa-chart-bar',
                        "_blank"
                    ) ?>

                </div>

                <div class="kt-subheader  kt-subheader-custom kt-grid__item" >
                    <div class="kt-container ">
                        <div class="kt-subheader__main">
                            <h3 class="kt-subheader__title"><?= trans('FINANCE REPORTS') ?></h3>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <?= createMenuTile(
                        'SA_GLANALYTIC',
                        trans('Profit & Loss - DrillDown'),
                        trans('Drill Down Report'),
                        getRoute('drill_pl'),
                        'fa-balance-scale-left'
                    ) ?>
                    <?= createMenuTile(
                        'SA_GLANALYTIC',
                        trans('Balance Sheet - DrillDown'),
                        trans('Balance Sheet Drill Down Report'),
                        getRoute('drill_balance_sheet'),
                        'fa-balance-scale-left'
                    ) ?>
                    <?= createMenuTile(
                        'SA_GLANALYTIC',
                        trans('Trial Balance - DrillDown'),
                        trans('Balance Sheet Drill Down Report'),
                        getRoute('drill_trial_balance'),
                        'fa-balance-scale-left'
                    ) ?>

                    <?= createMenuTile(
                        'SA_GLREP',
                        trans('Ledger Report'),
                        trans('Ledger Transaction Report'),
                        getRoute('rep_gl'),
                        'fa-list')
                    ?>

                    <?= createMenuTile(
                        'SA_SUBLEDSUMMREP',
                        trans('Sub-ledger Summary Report'),
                        trans('Sub-ledger Summary Report'),
                        erp_url('rep_subledger_summary.php'),
                        'fa-list'
                    ) ?>

                    <?= createMenuTile(
                        'SA_GLANALYTIC',
                        trans('Trial Balance'),
                        trans('Trial Balance Report'),
                        getRoute('rep_tb'),
                        'fa-balance-scale'
                    ) ?>

                    <?= createMenuTile(
                        'SA_PNLREP',
                        trans('Profit & Loss'),
                        trans('Profit and loss report'),
                        getRoute('rep_pl'),
                        'fa-wave-square'
                    ) ?>

                    <?= createMenuTile(
                        'SA_GLANALYTIC',
                        trans('Balance Sheet'),
                        trans('Balance Sheet'),
                        getRoute('rep_bs'),
                        'fa-wave-square'
                    ) ?>
                </div>
            </div>

            <!-- end:: Content -->
        </div>
    </div>
</div>