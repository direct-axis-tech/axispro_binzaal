<?php
/**
 * Created by PhpStorm.
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

$barcode = $_GET['barcode'];

$extra_where = "";

$cost_center = $_GET['cost_center'];

if(!empty($extra_where)) {
    $extra_where .= " AND dimension_id = $cost_center ";
}


//error_reporting(E_ALL);

$sql = "SELECT debtor_no FROM " . TB_PREF . "debtor_trans WHERE (barcode=" . db_escape($barcode) . " 
OR reference=" . db_escape($barcode) . ") and ov_amount <> 0 $extra_where LIMIT 1";

$result = db_query($sql, "The debtor transaction could not be queried");
$row = db_fetch($result);
if (db_num_rows($result) > 0) {
    echo $row['debtor_no'];
    exit;
} else {
    echo "false";
    exit;
}