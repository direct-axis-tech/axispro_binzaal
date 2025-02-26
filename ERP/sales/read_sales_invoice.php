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

$barcode = $_POST['invoice_ref'];


//error_reporting(E_ALL);

$sql = (
    "SELECT 
        trans.*
    FROM `0_debtor_trans` AS trans
        LEFT JOIN `0_voided` AS v ON
	        trans.trans_no = v.id
	        AND trans.type = v.type
    WHERE 
        ISNULL(v.date_)
        AND trans.`type` = 10
        AND (
            trans.reference=".db_escape($barcode)."
            OR trans.barcode=".db_escape($barcode)."
        )
    LIMIT 1"
);


$result = db_query($sql, "The debtor transaction could not be queried");
$row = db_fetch_assoc($result);
if (db_num_rows($result) > 0) {
    echo json_encode($row);
    exit;
} else {
    echo "false";
    exit;
}