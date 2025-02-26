<div class="kt-container  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch">
    <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
        <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <!-- begin:: Content Head -->


            <style>

            </style>


            <!-- end:: Content Head -->

            <!-- begin:: Content -->
            <div class="kt-container  kt-grid__item kt-grid__item--fluid">

                <!--Begin::Dashboard 2-->

                <!--Begin::Row-->


                <div class="kt-subheader kt-subheader-custom   kt-grid__item">
                    <div class="kt-container ">
                        <div class="kt-subheader__main">
                            <h3 class="kt-subheader__title"><?= trans('INVOICE') ?></h3>
                        </div>
                    </div>
                </div>

                <div class="row" style="border: 1px solid #ccc; padding: 18px;background: #fff9ec">
                <?php

                    $curr_user = get_user($_SESSION["wa_current_user"]->user);

                    $user_dim = $curr_user['dflt_dimension_id'];
                    $dim_info = get_dimension($user_dim);

                    $logos = $GLOBALS['department_logos'];

                    $result = get_dimensions();
                    $dimensions = [];
                    $prepaidDims = [];
                    while ($dim = db_fetch_assoc($result)) {
                        $dim['logo'] = isset($logos[$dim['center_type']]) ? $logos[$dim['center_type']] : '';
                        $dimensions[$dim['id']] = $dim;
                        
                        if ($dim['dflt_payment_term'] == PMT_TERMS_PREPAID) {
                            $prepaidDims[$dim['id']] = $dim;
                        }
                    }
                    
                    $allowed_dims = array_flip(explode(",", $curr_user['allowed_dims']));
                    $allowed_dims[$curr_user['dflt_dimension_id']] = true;
                    $prepaidDims = array_intersect_key($prepaidDims, $allowed_dims);
                    $allowed_dims = array_intersect_key($dimensions, $allowed_dims);
                    
                    $has_service_request = array_column($allowed_dims, 'has_service_request', 'id');

                    if (in_array("1", $has_service_request)) {
                        echo createMenuTile(
                            'SA_SERVICE_REQUEST',
                            trans('NEW SERVICE REQUEST'),
                            trans('Pre-Invoice / Service Request'),
                            'new_service_request.php',
                            'fa-file-invoice',
                            ''
                        );
                    }

                    foreach ($allowed_dims as $dim) {
                        if ($dim['pos_type'] == POS_CAFETERIA) {
                            echo createMenuTile(
                                'SA_SALESINVOICE',
                                trans($dim['name']),
                                trans('Invoice'),
                                "ERP/sales/cafeteria.php?dim_id={$dim['id']}",
                                'fa-mug-hot',
                                '',
                                $dim['logo']
                            );
                        } else {
                            echo createMenuTile(
                                'SA_SALESINVOICE',
                                trans($dim['name']),
                                trans('Invoice'),
                                "ERP/sales/sales_order_entry.php?NewInvoice=0&dim_id={$dim['id']}",
                                'fa-file-invoice',
                                '',
                                $dim['logo']
                            );
                        }
                    }
                ?>

                </div>

                <?php if (authUser()->hasAnyPermission(
                    'SA_SALESORDER',
                    'SA_INV_PREPAID_ORDERS',
                    'SA_SALES_LINE_VIEW',
                    'SA_SALESORDER_VIEW',
                    'SA_SALESORDER_VIEW_DEP',
                    'SA_SALESORDER_VIEW_ALL'
                )): ?>
                <div class="kt-subheader kt-subheader-custom   kt-grid__item">
                    <div class="kt-container ">
                        <div class="kt-subheader__main">
                            <h3 class="kt-subheader__title"><?= trans('JOB ORDER') ?></h3>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="row">
                    <?php foreach ($prepaidDims as $dim):
                        echo createMenuTile(
                            'SA_SALESORDER',
                            trans($dim['name']),
                            trans('Job Order'),
                            erp_url('/ERP/sales/sales_order_entry.php', [
                                'NewOrder' => 'Yes',
                                'dim_id' => $dim['id']
                            ]),
                            'fa-file-invoice',
                            '',
                            $dim['logo']
                        ); 
                        
                        echo createMenuTile(
                            'SA_DIRECTINVORDER',
                            trans($dim['name']),
                            trans('Direct Invoice + Job Order'),
                            erp_url('/ERP/sales/sales_order_entry.php', [
                                'NewInvoiceOrder' => 'Yes',
                                'dim_id' => $dim['id'],
                            ]),
                            'fa-file-invoice',
                            '',
                            $dim['logo']
                        );

                        echo createMenuTile(
                            'SA_DIRECTDLVRORDER',
                            trans($dim['name']),
                            trans('Job Order with Auto Completion'),
                            erp_url('/ERP/sales/sales_order_entry.php', [
                                'NewCompletionOrder' => 'Yes',
                                'dim_id' => $dim['id'],
                            ]),
                            'fa-file-invoice',
                            '',
                            $dim['logo']
                        );

                        echo createMenuTile(
                            'SA_DIRECTINVDLVRORDER',
                            trans($dim['name']),
                            trans('Direct Invoice + Job Order with Auto Completion'),
                            erp_url('/ERP/sales/sales_order_entry.php', [
                                'NewInvoiceCompletionOrder' => 'Yes',
                                'dim_id' => $dim['id'],
                            ]),
                            'fa-file-invoice',
                            '',
                            $dim['logo']
                        ); 
                    endforeach; ?>

                    <?= createMenuTile(
                        'SA_SALESPAYMNT',
                        trans('Customer Payment'),
                        trans('Customer Payment'),
                        erp_url("/ERP/sales/customer_payments.php?dimension_id=".(reset($prepaidDims)['id'] ?? '')),
                        'fa-money-bill',
                        '',
                        '',
                        empty($prepaidDims)
                    ) ?>

                    <?= createMenuTile(
                        [
                            'SA_SALES_LINE_VIEW',
                            'SA_SALES_LINE_VIEW_OWN',
                            'SA_SALES_LINE_VIEW_DEP'
                        ],
                        trans('Job Order - Transactions'),
                        trans('Job Order - Transactions'),
                        route('sales.orders.details.index'),
                        'fa-table'
                    ) ?>

                    <?= createMenuTile(
                        'SA_INV_PREPAID_ORDERS',
                        trans('Job Order List'),
                        trans('Job Order List'),
                        erp_url('/ERP/sales/inquiry/sales_orders_view.php?PrepaidOrders=Yes'),
                        'fa-list-ol'
                    ) ?>
                </div>

                <div class="kt-subheader kt-subheader-custom   kt-grid__item">
                    <div class="kt-container ">
                        <div class="kt-subheader__main">
                            <h3 class="kt-subheader__title"><?= trans('MANAGE') ?></h3>
                        </div>
                    </div>
                </div>



                <div class="row">

                    <?= createMenuTile(
                        'SA_RECEPTION_REPORT',
                        'Reception report',
                        'Customer reception list',
                        getRoute('reception_report'),
                        'fa-clipboard-list'
                    ) ?>

                    <?= createMenuTile(
                        'SA_RECEPTION',
                        trans('RECEPTION'),
                        trans('Reception'),
                        'reception.php',
                        'fa-money-bill'
                    ) ?>


                    <?= createMenuTile(
                        'SA_SRVREQLI',
                        trans('SERVICE REQUEST LIST'),
                        trans('SERVICE REQUEST LIST'),
                        'service_request_list.php',
                        'fa-money-bill'
                    ) ?>

                    <?= createMenuTile(
                        'SA_SALESPAYMNT',
                        trans('Cashier Dashboard'),
                        trans('Cashier Dashboard'),
                        'index.php?dashboard=cashier',
                        'fa-money-bill'
                    ) ?>

                    <?= createMenuTile(
                        'SA_SALESALLOC',
                        trans('Allocate Customer Payments'),
                        trans('Allocate Customer Payments or Credit Notes'),
                        getRoute('allocate_cust_pmts_n_cr_notes'),
                        'fa-map-signs'
                    ); ?>

                    <?= createMenuTile(
                        [
                            'SA_MANAGEINV',
                            'SA_MANAGEINVDEP',
                            'SA_MANAGEINVALL'
                        ],
                        trans('Manage Invoices'),
                        trans('Edit and Manage Invoices'),
                        getRoute('manage_invoice'),
                        'fa-info'
                    ) ?>
                    
                    <?= createMenuTile(
                        [
                            'SA_CASH_HANDOVER',
                            'SA_CASH_HANDOVER_ALL'
                        ], 
                        trans('Cash Handover Request'),
                        trans('Cash Handover Request'),
                        'cash_handover_request.php',
                        'fa-money-bill'
                    ) ?>

                    <?= createMenuTile(
                        'SA_CASH_HANDOVER_LIST',
                        trans('Cash Handover Request List'),
                        trans('Cash Handover Request List'), 
                        'cash_handover_request_list.php', 
                        'fa-clipboard-list'
                    ) ?>

                    <?= createMenuTile(
                        'SA_CUSTRCPTVCHR', 
                        trans('Cust. reciept voucher'), 
                        trans('Reciept voucher for customer'),
                        getRoute('cust_rcpt_vchr'),
                        'fa-receipt'
                    ) ?>

                    <?= createMenuTile(
                        'SA_SALESALLOC',
                        trans('Advance Allocation'),
                        trans('Payment allocation Enquiry'),
                        getRoute('allocation_inquiry'),
                        'fa-wallet'
                    ) ?>

                    <?= createMenuTile(
                        'SA_CUSTOMER',
                        trans('Customers'),
                        trans('Manage Customers'),
                        getRoute('customers'),
                        'fa-users-cog'
                    ) ?>

                    <?= createMenuTile(
                        'SA_SALESMAN',
                        trans('SalesMan'),
                        trans('Manage SalesMan'),
                        getRoute('sales_person'),
                        'fa-user-tie'
                    ) ?>


                    <?= createMenuTile(
                        'SA_VIEWPRINTTRANSACTION',
                        trans('Print or View'),
                        trans('View or Print Transactions'),
                        getRoute('view_print_trans'),
                        'fa-print'
                    ) ?>

                    <?= createMenuTile(
                        'SA_CUSTPAYMREP',
                        trans('Customer Balance'),
                        trans('Customer Balance Report'),
                        getRoute('rep_customer_balance'),
                        'fa-balance-scale'
                    ) ?>

                    <?= createMenuTile(
                        'SA_CUSTPAYMREP',
                        trans('Aged Customer Analysis'),
                        trans('Aged Customer Analysis'),
                        'rep_customer_aged.php',
                        'fa-balance-scale'
                    ) ?>
                  
                    <?= createMenuTile(
                        [
                            'SA_EMPANALYTIC',
                            'SA_EMPANALYTICDEP',
                            'SA_EMPANALYTICALL'
                        ],
                        trans('Sales Report'),
                        trans('Employee wise sales report'),
                        getRoute('employee_wise_sales'),
                        'fa-info'
                    ) ?>

                    <?= createMenuTile(
                        'SA_ITEM',
                        trans('Service List'),
                        trans('Add and Manage Services'),
                        getRoute('service_list'),
                        'fa-people-carry'
                    ) ?>

                    <?= createMenuTile(
                        'SA_ITEM',
                        trans('Add New Service'),
                        trans('Add and Manage Items'),
                        'items.php?action=new',
                        'fa-people-carry'
                    ) ?>

                    <?= createMenuTile(
                        'SA_SALESBULKREP',
                        trans('Bulk Invoice Print'),
                        trans('Bulk Invoice Print'),
                        'ERP/sales/inquiry/bulk_invoice_print.php',
                        'fa-print'
                    ) ?>

                    <?= createMenuTile(
                        'SA_SALESKIT',
                        trans('Sales Kit'),
                        trans('Add or Manage - Sales Kits'),
                        erp_url('/ERP/inventory/manage/sales_kits.php'),
                        'fa-cubes'
                    ) ?>
                </div>
            </div>

        </div>
    </div>
</div>
