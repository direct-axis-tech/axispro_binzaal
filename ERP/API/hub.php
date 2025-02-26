<?php
/**
 * Created by Bipin.
 * User: hp
 * Date: 6/6/2018
 * Time: 4:52 PM
 */
$page_security = 'SA_SALESINVOICE';
$path_to_root = "..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");
include_once($path_to_root . "/taxes/tax_calc.inc");
include_once($path_to_root . "/admin/db/shipping_db.inc");
include_once($path_to_root . "/themes/daxis/kvcodes.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/admin/db/company_db.inc");
include_once($path_to_root . "/admin/db/fiscalyears_db.inc");
include_once($path_to_root . "/includes/db/crm_contacts_db.inc");


include_once($path_to_root . "/API/API_Call.php");
include_once($path_to_root . "/API/AxisProLog.php");
include_once($path_to_root . "/API/Log.php");
include_once($path_to_root . "/API/API_Subledger_Report.php");
include_once($path_to_root . "/API/API_Finance.php");



$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : "";


if (empty($method)) {
    echo json_encode(['status' => 'FAIL', 'msg' => 'PARAM_METHOD_EMPTY']);
    exit();
}

$api = new API_Call();
if(method_exists($api,$method)) {
    $api->$method();
}

$api_subled_report = new API_Subledger_Report();
if(method_exists($api_subled_report,$method)) {
    $api_subled_report->$method();
}

$api_finance = new API_Finance();
if(method_exists($api_finance,$method)) {
    $api_finance->$method();
}