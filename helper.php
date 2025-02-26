<?php include_once "ERP/includes/date_functions.inc"; ?>
<?php
include_once "ERP/API/API_Call.php";
$api = new API_Call();
?>
<?php

/**
 * @param string $name
 * @return mixed|string
 * Defining routes
 */
function getRoute($name = "")
{

    $route_array = [

        "" => "",

        //SALES
        "direct_invoice" => 'ERP/sales/sales_order_entry.php?NewInvoice=0',
        "edirham_invoice" => 'ERP/sales-tasheel/sales_order_entry.php?NewInvoice=0&is_tadbeer=1&show_items=ts',
        "customer_payment" => 'ERP/sales/customer_payments.php?',
        "manage_invoice" => 'ERP/sales/inquiry/customer_inquiry.php?',
        'allocate_cust_pmts_n_cr_notes' => 'ERP/sales/allocations/customer_allocation_main.php',
        "allocation_inquiry" => 'ERP/sales/inquiry/customer_allocation_inquiry.php?',
        "customers" => 'ERP/sales/manage/customers.php?',
        "view_print_trans" => 'ERP/admin/view_print_transaction.php?',
        "sales_person" => 'ERP/sales/manage/sales_people.php?',
        "cust_rcpt_vchr" => 'ERP/sales/customer_reciept_voucher.php',


        //PURCHASE
        'suppliers' => 'ERP/purchasing/manage/suppliers.php?',
        'direct_supplier_invoice' => 'ERP/purchasing/po_entry_items.php?NewInvoice=Yes',
        'supplier_transactions' => 'ERP/purchasing/inquiry/supplier_inquiry.php?',
        'supplier_payment' => 'ERP/purchasing/supplier_payment.php?',
        'purchase_order' => 'ERP/purchasing/po_entry_items.php?NewOrder=Yes',
        'direct_grn' => 'ERP/purchasing/po_entry_items.php?NewGRN=Yes',
        'supplier_invoice' => 'ERP/purchasing/supplier_invoice.php?New=1',
        'supplier_creditnote' => 'ERP/purchasing/supplier_credit.php?New=1',
        'purchase_enquiry' => 'ERP/purchasing/inquiry/po_search_completed.php?',
        'supplier_enquiry' => 'ERP/purchasing/inquiry/supplier_inquiry.php?',
        'supplier_allocation_enquiry' => 'ERP/purchasing/inquiry/supplier_allocation_inquiry.php?',
        'supplier_purchase_report' => 'ERP/reporting/reports_main.php?Class=2',
        'projects' => 'ERP/dimensions/dimension_entry.php?',
        'list_projects' => 'ERP/dimensions/inquiry/search_dimensions.php?',
        'recevie_items' => 'ERP/purchasing/inquiry/po_search.php?',


        //SERVICES
        'services' => 'ERP/inventory/manage/items.php?',
        'manage_items' => 'ERP/inventory/manage/items.php?',
        'category' => 'ERP/inventory/manage/item_categories.php?',
        'category_groups' => 'ERP/inventory/manage/category_groups.php?',
        'service_list' => 'items.php?action=list',
        //'service_list' => 'ERP/inventory/manage/items.php?',
        'sales_price' => 'ERP/inventory/prices.php?',
        'purchase_price' => 'ERP/inventory/purchasing_data.php?',
        'standard_cost' => 'ERP/inventory/cost_update.php?',
        'inve_loc' => 'ERP/inventory/transfers.php?NewTransfer=1',
        'inven_adjust' => 'ERP/inventory/adjustments.php?NewAdjustment=1',
        'item_movement' => 'ERP/inventory/inquiry/stock_movements.php?',
        'item_status' => 'ERP/inventory/inquiry/stock_status.php?',
        'warehoue' => 'ERP/inventory/manage/locations.php?',
        'measure_unit' => 'ERP/inventory/manage/item_units.php?',
        'reorder_level' => 'ERP/inventory/reorder_level.php?',
        'purchase_order_enq' => 'ERP/purchasing/inquiry/po_search_completed.php?',
        'supplier_transaction' => 'ERP/purchasing/inquiry/supplier_inquiry.php?',
        'supplier_allocation_enqu' => 'ERP/purchasing/inquiry/supplier_allocation_inquiry.php?',


        //FINANCE
        'journal_entry' => 'ERP/gl/gl_journal.php?NewJournal=Yes',
        'journal_inquiry' => 'ERP/gl/inquiry/journal_inquiry.php?',
        'gl_inquiry' => 'ERP/gl/inquiry/gl_account_inquiry.php?',
        'gl_accounts' => 'ERP/gl/manage/gl_accounts.php?',
        'gl_groups' => 'ERP/gl/manage/gl_account_types.php?',
        'gl_classes' => 'ERP/gl/manage/gl_account_classes.php?',
        'bank_transfer' => 'ERP/gl/bank_transfer.php?',
        'edirham_recharge' => 'ERP/gl/edirham_recharge.php?',
        'bank_accounts' => 'ERP/gl/manage/bank_accounts.php?',
        'manual_reconciliation' => 'ERP/gl/bank_account_reconcile.php?',
        'auto_reconciliation' => 'ERP/gl/reconciliation.php?',
        'chart_of_accounts' => 'chart_of_accounts.php',
        'drill_pl' => 'profit_and_loss_drill.php',
        'drill_balance_sheet' => 'balance_sheet_drill.php',
        'drill_trial_balance' => 'trial_balance_drill.php',


        //REPORTS
        'cash_handover_report' => 'ERP/gl/inquiry/cash_handover_inquiry.php',
        'category_wise_sales' => 'ERP/sales/inquiry/categorywise_sales_inquiry.php?',
        'employee_wise_sales' => 'ERP/sales/inquiry/categorywise_employee_report.php?',
        'service_wise_sales' => 'ERP/sales/inquiry/service_wise_inquiry.php',
        'cust_bal_statement' => 'ERP/sales/inquiry/customer_balance_statement.php',
        'customer_bal_inquiry' => 'ERP/sales/inquiry/customer_balance_inquiry.php',
        'customer_wise_sales' => 'ERP/sales/inquiry/categorywise_customer_report.php?',
        'customer_wise_sales_summary' => 'ERP/sales/inquiry/customer_wise_sales.php',
        'daily_collection' => 'ERP/sales/inquiry/daily_collection_inquiry.php?',
        'invoice_collection' => 'ERP/sales/inquiry/invoice_payment_inquiry.php?',
        'rep_customer_balance' => 'rep_customer_balances.php',
        'rep_tb' => 'rep_trial_balance.php',
        'rep_pl' => 'rep_profit_and_loss_statement.php',
        'rep_bs' => 'rep_balance_sheet.php',
        'rep_gl' => 'rep_gl_report.php',
        'overall_collection_report' => 'acc_balances_report.php',
        'overall_sales_report' => 'ERP/sales/inquiry/sales_collection_report.php?',
        'service_report' => 'management_report.php?report=service_report',
        'invoice_report' => 'invoice_report.php?application=reports',
        'employee_commission_adheed' => 'ERP/sales/inquiry/employee_commission_adheed.php',
        'cr_inv_report' => 'ERP/sales/inquiry/credit_invoice_inquiry.php',
        'view_cust_detail' => 'ERP/sales/inquiry/customer_details.php',
        'staff_mistakes' => 'ERP/sales/inquiry/staff_mistake.php',
        'customers_list' => 'ERP/reporting/rep_visitors.php',
        'reception_invoice' => 'ERP/sales/inquiry/reception_invoice.php',


        //SETTINGS
        'company_setup' => 'ERP/admin/company_preferences.php?',
        'user_setup' => 'ERP/admin/users.php?',
        'tax_types_setup' => 'ERP/taxes/tax_types.php?',
        'gl_setup' => 'ERP/admin/gl_setup.php?',
        'hr_setup' => 'ERP/admin/hr_setup.php?',
        'fsy_setup' => 'ERP/admin/fiscalyears.php?',
        'item_tax_types_setup' => 'ERP/taxes/item_tax_types.php?',
        'void_trans' => 'ERP/admin/void_transaction.php?',
        'access_setup' => 'ERP/admin/security_roles.php?',
        'voided_trans' => 'ERP/admin/voided_transactions.php?',
        'attach_documents' => 'ERP/admin/attachments.php',
        'acl_list'  => 'ERP/admin/access_control_list.php',
        'activity_log' => 'activity_log.php',

        //HRM Routes
        'timesheet' => 'ERP/hrm/workdays/timesheet.php',
        'process_payroll' => 'ERP/hrm/payroll.php',
        'add_employee' => 'ERP/hrm/employees/add_employee.php',
        'edit_employee' => 'ERP/hrm/employees/edit_employee.php',
        'add_emp_salary_update' => 'ERP/hrm/employees/add_emp_salary_update.php',
        'add_emp_job_update' => 'ERP/hrm/employees/add_emp_job_update.php',
        'add_emp_cancelation' => 'ERP/hrm/employees/add_emp_cancelation.php',
        'hold_emp_salary' => 'ERP/hrm/employees/hold_emp_salary.php',
        'holded_emp_salary' => 'ERP/hrm/employees/holded_emp_salary.php',
        'shifts' => 'ERP/hrm/workdays/employee_shift.php',
        'attendance_metrics' => 'ERP/hrm/workdays/attendance_metrics.php',
        'view_employees' => 'ERP/hrm/employees/view_employees.php',
        'view_payslip' => 'ERP/hrm/payslip.php',
        'add_employee_leaves' => 'ERP/hrm/workdays/add_employee_leaves.php',
        'end_of_service_report' => 'ERP/hrm/employees/end_of_service_report.php',
        'salary_certificate' => 'ERP/hrm/employees/salary_certificate.php',
        'salary_transfer_letter' => 'ERP/hrm/employees/salary_transfer_letter.php',
        'employee_document_view' => 'employee_document_view.php',
        'leave_adjustment' => 'ERP/hrm/employees/leave_adjustment.php',
        'employee_personal_timeout' => 'ERP/hrm/workdays/employee_personal_timeout.php',

        /* CRM */
        'reception_report' => 'reception_report.php'
    ];

    return isset($route_array[$name]) ? $route_array[$name] : "#";

}


/**
 * @return string
 * Get date format from ERP
 */
function getDateFormat()
{

    $dateFormat = pref('date.format');
    $sep = pref('date.separators')[pref('date.separator')];;

//    $dateFormat = 3;

    switch ($dateFormat) {
        case 0:
            $fmt = "m" . $sep . "d" . $sep . "yyyy";
            break;
        case 1:
            $fmt = "d" . $sep . "m" . $sep . "yyyy";
            break;
        case 2:
            $fmt = "Y" . $sep . "m" . $sep . "d";
            break;
        case 3:
            $fmt = "M" . $sep . "dd" . $sep . "yyyy";
            break;
        case 4:
            $fmt = "d" . $sep . "M" . $sep . "yyyy";
            break;
        default:
            $fmt = "Y" . $sep . "M" . $sep . "dd";
    }

    return $fmt;


}

/**
 * @param $cfg_obj
 * @param $cfg_key
 * @return bool
 * Get default configs
 */
function APConfig($cfg_obj, $cfg_key)
{
    if (empty($cfg_obj) || empty($_SESSION['wa_current_user']))
        return false;

    $result = $_SESSION['wa_current_user']->axispro_config[$cfg_obj];

    if (!empty($cfg_key))
        $result = $result[$cfg_key];

    return $result;

}

/**
 * @param $elements
 * @param int $parentId
 * @return array
 * Build tree, parent child relationship
 */
function buildTree($elements, $parentId = 0)
{

    $tree = array();

    foreach ($elements as $element) {
        if ($element->parent_id == $parentId) {
            $children = buildTree($elements, $element->id);
            if ($children) {
                $element->children = $children;
            }
            $tree[] = $element;
        }
    }

    return $tree;
}

/**
 * @param $value
 * @param string $default
 * @return string
 * get value from http request
 */
function REQUEST_INPUT($value, $default = "")
{
    if (isset($_REQUEST[$value]) && !empty($_REQUEST[$value]))
        return $_REQUEST[$value];
    return $default;

}


/**
 * @param $array
 * @param string $value
 * @return string
 * get array value by key
 */
function getArrayValue($array, $value = "")
{
    if (isset($array[$value]) && !empty($array[$value]))
        return $array[$value];
    return '';

}

/**
 * @param $data
 * @param $value
 * @param $text
 * @param bool $selected_id
 * @param string $place_holder
 * @return string
 * Preparing selection option html dynamically
 */
function prepareSelectOptions($data, $value, $text, $selected_id = false, $place_holder = "Select")
{

    $options = "";


    if ($place_holder !== false)
        $options .= "<option value=''>$place_holder</option>";

    foreach ($data as $row) {

        $opt_text = $row[$text];
        $opt_value = $row[$value];

        $selected = "";

        if ($opt_value == $selected_id)
            $selected = 'selected';

        $options .= "<option value='$opt_value' $selected>$opt_text</option>";

    }

    return $options;

}

/**
 * @param string|string[] $access The security area which this UI falls under
 * @return string
 * Hiding tiles, unless user has access permission.
 */
function HideMenu($access)
{
    $hidden = true;
    if (!is_array($access)) {
        $access = [$access];
    }

    foreach($access as $sec_area) {
        if ($_SESSION["wa_current_user"]->can_access($sec_area)) {
            $hidden = false;
            break;
        }
    }

    return $hidden ? "hidden_elem" : "";
}


/**
 * @param $access
 * @return string
 * Hiding tiles, unless user has access permission.
 */
function HideApplication($access)
{

    if (!$_SESSION["wa_current_user"]->can_access($access))
        return "hidden_elem";

    return "";

}


function bt_random()
{

    $items = [
        'success',
//        'danger',
        'warning',
//        'brand',
        'info'
        //'primary',
    ];

    return $items[array_rand($items)];

}

function createMenuTile($permission, $main_title, $sub_title, $route, $fa_icon_class, $target = "", $icon_image = "",$hidden=false)
{
    if($hidden) { return ''; }

    /**
     * Check if the user has access to the tile
     */
    $has_access = false;
    $sec_areas = $permission;
    if (!is_array($permission)) {
        $sec_areas = [$permission];
    }

    foreach($sec_areas as $sec_area) {
        if (user_check_access($sec_area)) {
            $has_access = true;
            break;
        }
    }

    if(!$has_access) { return ''; }

    $random_bt = bt_random();

    $icon_dom = ' <i class="fa ' . $fa_icon_class . ' fa-4x kt-font-' . $random_bt . ' "></i>';
    if (!empty($icon_image)) {
        $icon_dom = '<img src="assets/images/' . $icon_image . '" width="50" />';
    }

    return '
    
                    <div class="col-lg-3">
                        <div class="kt-portlet kt-iconbox kt-iconbox--' . $random_bt . ' kt-iconbox--animate-fast">
                            <div class="kt-portlet__body">
                                <div class="kt-iconbox__body">
                                    <div class="kt-iconbox__icon">
                                    
                                    
                                    ' . $icon_dom . '

                                        	</div>
                                    <div class="kt-iconbox__desc">
                                        <h3 class="kt-iconbox__title">
                                            <a class="kt-link" target="' . $target . '" href="' . $route . '">' . $main_title . '</a>
                                        </h3>
                                        <div class="kt-iconbox__content">
                                            ' . $sub_title . '
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
    
    ';

}












