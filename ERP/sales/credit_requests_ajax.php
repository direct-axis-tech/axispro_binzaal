<?php
/**
 * Created by PhpStorm.
 * User: hp
 * Date: 2/28/2019
 * Time: 1:28 PM
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

if($_GET['cr_request'] == 1) {

    add_credit_approval_request(); exit;

}

if($_GET['take_action'] == 1) {

    take_action_on_request(); exit;

}

function add_credit_approval_request() {

    $customer_id = $_POST['customer_id'];
    $req_amount = 0;
    $req_desc = $_POST['req_desc'];
    $requested_by = $_SESSION['wa_current_user']->user;

    if(empty($customer_id)) {
        echo json_encode(["status" => "FAIL","msg" => "No Customer Error"]);
        exit();
    }

//    if(empty($req_amount)) {
//        echo json_encode(["status" => "FAIL","msg" => "Please input request amount"]);
//        exit();
//    }


    $sql = "INSERT into 0_credit_requests (customer_id,req_amount,description,requested_by) 
             VALUES ($customer_id,$req_amount,".db_escape($req_desc).",$requested_by)";
    db_query($sql);

    echo json_encode(["status" => "SUCCESS","msg" => "Request sent to Administrator."]);

    exit();

}

function take_action_on_request() {

    $request_id = $_POST['request_id'];
    $desc = $_POST['action_description'];
    $action = $_POST['action'];
    $amount = 0;

    $action_by = $_SESSION['wa_current_user']->user;

    if(empty($request_id)) {
        echo json_encode(["status" => "FAIL","msg" => "Invalid Request ID"]);
        exit();
    }

    $sql = "UPDATE 0_credit_requests SET status=".db_escape($action).", approved_amount=$amount,
    action_description=".db_escape($desc).",action_by=$action_by WHERE id=$request_id";

    db_query($sql);

    $msg = "Request Rejected";
    if($action == 'ACCEPTED'){
        $msg = "Request Approved";
    }

    echo json_encode(["status" => "SUCCESS","msg" => $msg]);

    exit();


}