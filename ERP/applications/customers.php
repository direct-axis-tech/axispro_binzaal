<?php
/**********************************************************************
Direct Axis Technology L.L.C.
Released under the terms of the GNU General Public License, GPL,
as published by the Free Software Foundation, either version 3
of the License, or (at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
 ***********************************************************************/
class customers_app extends application
{
    function __construct()
    {
        parent::__construct("orders", trans($this->help_context = "Sales"));

        $this->add_module(trans("Transactions"));
//		$this->add_lapp_function(0, trans("Sales &Quotation Entry"),
//			"sales/sales_order_entry.php?NewQuotation=Yes", 'SA_SALESQUOTE', MENU_TRANSACTION);
//		$this->add_lapp_function(0, trans("Sales &Order Entry"),
//			"sales/sales_order_entry.php?NewOrder=Yes", 'SA_SALESORDER', MENU_TRANSACTION);
//		$this->add_lapp_function(0, trans("Direct &Delivery"),
//			"sales/sales_order_entry.php?NewDelivery=0", 'SA_SALESDELIVERY', MENU_TRANSACTION);
        $this->add_lapp_function(0, trans("Direct &Invoice"),
            "sales/sales_order_entry.php?NewInvoice=0", 'SA_SALESINVOICE', MENU_TRANSACTION);

        $this->add_lapp_function(0, trans("RTA Invoice"),
            "sales-tasheel/sales_order_entry.php?NewInvoice=0&is_tadbeer=1&show_items=ts", 'SA_SALESINVOICE', MENU_TRANSACTION);
//
//
//        $this->add_lapp_function(0, trans("Direct &Invoice (TADBEER)"),
//            "sales-tasheel/sales_order_entry.php?NewInvoice=0&is_tadbeer=1&show_items=tb", 'SA_SALESINVOICE', MENU_TRANSACTION);


//		$this->add_lapp_function(0, "","");
//		$this->add_lapp_function(0, trans("&Delivery Against Sales Orders"),
//			"sales/inquiry/sales_orders_view.php?OutstandingOnly=1", 'SA_SALESDELIVERY', MENU_TRANSACTION);
//		$this->add_lapp_function(0, trans("&Invoice Against Sales Delivery"),
//			"sales/inquiry/sales_deliveries_view.php?OutstandingOnly=1", 'SA_SALESINVOICE', MENU_TRANSACTION);
//
//		$this->add_rapp_function(0, trans("&Template Delivery"),
//			"sales/inquiry/sales_orders_view.php?DeliveryTemplates=Yes", 'SA_SALESDELIVERY', MENU_TRANSACTION);
//		$this->add_rapp_function(0, trans("&Template Invoice"),
//			"sales/inquiry/sales_orders_view.php?InvoiceTemplates=Yes", 'SA_SALESINVOICE', MENU_TRANSACTION);
//		$this->add_rapp_function(0, trans("&Create and Print Recurrent Invoices"),
//			"sales/create_recurrent_invoices.php?", 'SA_SALESINVOICE', MENU_TRANSACTION);
//		$this->add_rapp_function(0, "","");
        $this->add_rapp_function(0, trans("Customer &Payments"),
            "sales/customer_payments.php?", 'SA_SALESPAYMNT', MENU_TRANSACTION);
//		$this->add_lapp_function(0, trans("Invoice &Prepaid Orders"),
//			"sales/inquiry/sales_orders_view.php?PrepaidOrders=Yes", 'SA_SALESINVOICE', MENU_TRANSACTION);
//		$this->add_rapp_function(0, trans("Customer &Credit Notes"),
//			"sales/credit_note_entry.php?NewCredit=Yes", 'SA_SALESCREDIT', MENU_TRANSACTION);
//        $this->add_rapp_function(0, trans("&Allocate Customer Payments or Credit Notes"),
//            "sales/allocations/customer_allocation_main.php?", 'SA_SALESALLOC', MENU_TRANSACTION);



        $this->add_rapp_function(0, trans("Discount Payments"),
            "sales/discount_payments.php?", 'SA_SALESPAYMNT', MENU_TRANSACTION);



        $this->add_module(trans("Inquiries and Reports"));
//		$this->add_lapp_function(1, trans("Sales Quotation I&nquiry"),
//			"sales/inquiry/sales_orders_view.php?type=32", 'SA_SALESTRANSVIEW', MENU_INQUIRY);
//		$this->add_lapp_function(1, trans("Sales Order &Inquiry"),
//			"sales/inquiry/sales_orders_view.php?type=30", 'SA_SALESTRANSVIEW', MENU_INQUIRY);
        $this->add_lapp_function(1, trans("Edit and Manage Invoices"),
            "sales/inquiry/customer_inquiry.php?", 'SA_SALESTRANSVIEW', MENU_INQUIRY);
        $this->add_lapp_function(1, trans("Customer Allocation &Inquiry"),
            "sales/inquiry/customer_allocation_inquiry.php?", 'SA_SALESALLOC', MENU_INQUIRY);


        $this->add_lapp_function(1, trans("Category Wise Sales Inquiry"),
            "sales/inquiry/categorywise_sales_inquiry.php?", 'SA_SALESALLOC', MENU_INQUIRY);


        $this->add_lapp_function(1, trans("Employee-Category Sales"),
            "sales/inquiry/categorywise_employee_report.php?", 'SA_SALESALLOC', MENU_INQUIRY);

        $this->add_lapp_function(1, trans("Customer-Category Sales"),
            "sales/inquiry/categorywise_customer_report.php?", 'SA_SALESALLOC', MENU_INQUIRY);

//        $this->add_lapp_function(1, trans("Customer-Category Sales"),
//            "sales/inquiry/categorywise_customer_report.php?", 'SA_SALESALLOC', MENU_INQUIRY);



//        $this->add_lapp_function(1, trans("Customer Rewards Inquiry"),
//            "sales/inquiry/customer_rewards_inquiry.php?", 'SA_SALESALLOC', MENU_INQUIRY);
//
//
//        $this->add_rapp_function(1, trans("Customer Rewards Inquiry - Detailed"),
//            "sales/inquiry/customer_rewards_inquiry_detailed.php?", 'SA_SALESALLOC', MENU_INQUIRY);


        $this->add_lapp_function(1, trans("Daily Collection Report"),
            "sales/inquiry/daily_collection_inquiry.php?", 'SA_SALESALLOC', MENU_INQUIRY);


        $this->add_lapp_function(1, trans("Account Balances"),
            "sales/inquiry/account_balances.php?", 'SA_SALESALLOC', MENU_INQUIRY);

        $this->add_lapp_function(1, trans("Service Transaction Report"),
            "sales/inquiry/service_transactions_report.php?", 'SA_SALESALLOC', MENU_INQUIRY);


        $this->add_rapp_function(1, trans("Invoice Collection Report"),
            "sales/inquiry/invoice_payment_inquiry.php?", 'SA_SALESALLOC', MENU_INQUIRY);

        $this->add_rapp_function(1, trans("Customer Balance Inquiry"),
            "sales/inquiry/customer_balance_inquiry.php?", 'SA_SALESALLOC', MENU_INQUIRY);


        $this->add_rapp_function(1, trans("Customer Balance Statement"),
            "sales/inquiry/customer_balance_statement.php?", 'SA_SALESALLOC', MENU_INQUIRY);

        $this->add_rapp_function(1, trans("Customer and Sales &Reports"),
            "reporting/reports_main.php?Class=0", 'SA_SALESTRANSVIEW', MENU_REPORT);

        $this->add_module(trans("Maintenance"));
        $this->add_lapp_function(2, trans("Add and Manage &Customers"),
            "sales/manage/customers.php?", 'SA_CUSTOMER', MENU_ENTRY);
        $this->add_lapp_function(2, trans("Customer &Branches"),
            "sales/manage/customer_branches.php?", 'SA_CUSTOMER', MENU_ENTRY);


        $this->add_lapp_function(2, trans("View or &Print Transactions"),
            "admin/view_print_transaction.php?", 'SA_VIEWPRINTTRANSACTION', MENU_MAINTENANCE);


        $this->add_lapp_function(2, trans("Sales &Groups"),
            "sales/manage/sales_groups.php?", 'SA_SALESGROUP', MENU_MAINTENANCE);
//		$this->add_lapp_function(2, trans("Recurrent &Invoices"),
//			"sales/manage/recurrent_invoices.php?", 'SA_SRECURRENT', MENU_MAINTENANCE);
        $this->add_lapp_function(2, trans("Sales T&ypes"),
            "sales/manage/sales_types.php?", 'SA_SALESTYPES', MENU_MAINTENANCE);
        $this->add_rapp_function(2, trans("Add/Manage PRO's"),
            "sales/manage/sales_people.php?", 'SA_SALESMAN', MENU_MAINTENANCE);
        $this->add_rapp_function(2, trans("Sales &Areas"),
            "sales/manage/sales_areas.php?", 'SA_SALESAREA', MENU_MAINTENANCE);
        $this->add_rapp_function(2, trans("Credit &Status Setup"),
            "sales/manage/credit_status.php?", 'SA_CRSTATUS', MENU_MAINTENANCE);

        $this->add_extensions();
    }
}



