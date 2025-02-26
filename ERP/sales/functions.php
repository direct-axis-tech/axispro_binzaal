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
include_once($path_to_root . "/themes/daxis/kvcodes.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/admin/db/company_db.inc");
include_once($path_to_root . "/admin/db/fiscalyears_db.inc");

$method = $_GET['method'];


if ($_GET['method'] == 'daily_sales') {

    $sql = "select tran_date,ROUND(sum(ov_amount+ov_gst),2) amount from 0_debtor_trans 
    where type=10 group by tran_date order by tran_date ASC limit 10";

    $result = db_query($sql);

    $daily_sales = [];
    while ($row = db_fetch($result)) {
        $daily_sales[$row['tran_date']] = $row['amount'];
    }


    if ($_GET['format'] == 'json') {
        echo json_encode($daily_sales);
        exit();
    }
    return $daily_sales;

}


if ($_GET['method'] == 'employee_service_count') {

    $sql = "select users.user_id,sum(dt_detail.quantity) qty from 0_debtor_trans_details dt_detail 
        left join 0_users users on users.id=dt_detail.created_by 
        group by dt_detail.created_by order by qty desc limit 5";

    $result = db_query($sql);

    $employee_service_count = [];
    while ($row = db_fetch($result)) {
        $employee_service_count[$row['user_id']] = $row['qty'];
    }


    if ($_GET['format'] == 'json') {
        echo json_encode($employee_service_count);
        exit();
    }
    return $employee_service_count;

}


if ($_GET['method'] == 'top_five_category') {

    $sql = "select sc.description, SUM(dt_detail.quantity) qty from 0_debtor_trans_details dt_detail
            left join 0_stock_master sm on sm.stock_id=dt_detail.stock_id
            left join 0_stock_category sc on sc.category_id=sm.category_id
            group by sc.category_id order by qty desc limit 5";

    $result = db_query($sql);

    $top_five_category = [];
    while ($row = db_fetch($result)) {
        $top_five_category[$row['description']] = $row['qty'];
    }


    if ($_GET['format'] == 'json') {
        echo json_encode($top_five_category);
        exit();
    }
    return $top_five_category;

}

if ($_GET['method'] == 'todays_invoices') {


    $status = isset($_GET['status']) ? $_GET['status'] : '';

    $today = Today();
    $today = date2sql($today);


    $sql = "SELECT * FROM (SELECT `b`.`reference` AS `invoice_no`,
(`b`.`ov_amount` + `b`.`ov_gst`) AS `invoice_amount`, b.order_,b.trans_no,
`a`.`created_by` AS `created_by`,b.payment_flag,b.display_customer,c.name as customer_name, 
`0_users`.`user_id` AS `created_employee`,`b`.`tran_date` AS `transaction_date`,
(CASE WHEN (`b`.`alloc` >= (`b`.`ov_amount` + `b`.`ov_gst`)) THEN '1' 
WHEN (`b`.`alloc` = 0) THEN '2' WHEN (`b`.`alloc` < (`b`.`ov_amount` + `b`.`ov_gst`)) THEN '3' END) AS `payment_status`
FROM (((`0_debtor_trans_details` `a`
LEFT JOIN `0_debtor_trans` `b` ON((`b`.`trans_no` = `a`.`debtor_trans_no`)))
LEFT JOIN `0_debtors_master` `c` ON((`c`.`debtor_no` = `b`.`debtor_no`)))
LEFT JOIN `0_users` ON((`0_users`.`id` = `a`.`created_by`)))
WHERE ((`a`.`debtor_trans_type` = 10) AND (`b`.`reference` <> 'auto') AND (`b`.`type` = 10) AND (`a`.`quantity` <> 0))  

AND b.tran_date = '$today' 

GROUP BY `b`.`reference`
ORDER BY `b`.`trans_no` DESC) As MyTable WHERE 1=1 ";


    if (!empty($status)) {
        $sql .= " AND payment_status = $status";
    }

    $result = db_query($sql, "Transactions could not be calculated");

    $return_result = [];
    while ($row = db_fetch_assoc($result)) {

        $class = 'class="oddrow"';
        if ($i % 2 == 0)
            $class = 'class="evenrow"';

        $payment_status = "Not Paid";
        if ($myrow['payment_status'] == '1') {
            $payment_status = 'Fully Paid';
        }
        if ($myrow['payment_status'] == '3') {
            $payment_status = 'Partially Paid';
        }

        $update_transaction_id_link = "sales/customer_invoice.php?ModifyInvoice=" . $myrow['trans_no'];

        if ($myrow['payment_flag'] != 0 && $myrow['payment_flag'] != 3) {//TASHEEL
            $update_transaction_id_link .= "&is_tadbeer=1&show_items=ts";
        }

        if ($myrow['payment_flag'] == 4 || $myrow['payment_flag'] == 5) {//TADBEER
            $update_transaction_id_link .= "&is_tadbeer=1&show_items=tb";
        }

        $row['payment_status'] = $payment_status;
        $row['edit_trans_id_link'] = $update_transaction_id_link;

        array_push($return_result, $row);

    }

    if ($_GET['format'] == 'json') {
        echo json_encode($return_result);
        exit();
    }
    return $return_result;

}


if ($_GET['method'] == 'category_sales_count') {

    $sql = "select sc.description, SUM(dt_detail.quantity) qty from 0_debtor_trans_details dt_detail
            left join 0_stock_master sm on sm.stock_id=dt_detail.stock_id
            left join 0_stock_category sc on sc.category_id=sm.category_id
            group by sc.category_id";

    $result = db_query($sql);

    $category_sales_count = [];
    while ($row = db_fetch($result)) {
        $category_sales_count[$row['description']] = $row['qty'];
    }


    if ($_GET['format'] == 'json') {
        echo json_encode($category_sales_count);
        exit();
    }
    return $category_sales_count;

}


if ($_GET['method'] == 'category_sales_report') {

    $date = null;

    $sql = "select c.description,sum(a.quantity) as inv_count,sum(a.quantity*a.unit_price) as service_charge from 0_debtor_trans_details a 
left join 0_stock_master b on b.stock_id=a.stock_id 
left join 0_stock_category c on c.category_id=b.category_id 
left join 0_debtor_trans d on d.trans_no = a.debtor_trans_no and d.`type`=10 where a.debtor_trans_type=10";

    if ($date) {
        $sql .= " and d.tran_date='$date' ";
    }

    $sql .= " group by b.category_id";
    $result = db_query($sql, "Transactions could not be calculated");

    $category_sales_report = [];
    while ($row = db_fetch($result)) {
        $category_sales_report[] = $row;
    }


    if ($_GET['format'] == 'json') {
        echo json_encode($category_sales_report);
        exit();
    }
    return $category_sales_report;

}


if ($_GET['method'] == 'bank_balances') {

    $today = Today();
    $today = date2sql($today);
//    $sql = "SELECT bank_act, bank_account_name, bank_curr_code, SUM(amount) balance FROM " . TB_PREF . "bank_trans bt
//                INNER JOIN " . TB_PREF . "bank_accounts ba ON bt.bank_act = ba.id    WHERE trans_date <= '$today'  AND inactive <> 1    GROUP BY bank_act, bank_account_name    ORDER BY bank_account_name";

    $sql = "select b.account_name as bank_account_name,ROUND(sum(amount),2) balance from 0_gl_trans a 
left join 0_chart_master b on b.account_code = a.account 
 inner join 0_bank_accounts c on c.account_code=b.account_code 
 where a.tran_date <= '$today' 
group by account order by b.account_name";

    $result = db_query($sql);

    $bank_balances = [];
    while ($row = db_fetch($result)) {
        $bank_balances[$row['bank_account_name']] = $row['balance'];
    }


    if ($_GET['format'] == 'json') {
        echo json_encode($bank_balances);
        exit();
    }
    return $bank_balances;

}


if ($_GET['method'] == 'expenses') {

    $options = null;

    if ($options == 'Last Month') {
        $today1 = date('Y-m-d', strtotime('last day of previous month'));
        $begin1 = date('Y-m-d', strtotime('first day of previous month'));
    } elseif ($options == 'This Month') {
        $begin1 = date("Y-m-d", strtotime("first day of this month"));
        $today1 = date("Y-m-d", strtotime("last day of this month"));
    } elseif ($options == 'Last Quarter Year') {

    } elseif ($options == 'Last Week') {
        $begin1 = date("Y-m-d", strtotime("last week monday"));
        $today1 = date("Y-m-d", strtotime("last week sunday"));
    } elseif ($options == 'Today') {
        $begin1 = date("Y-m-d", strtotime("now"));
        $today1 = date("Y-m-d", strtotime("now"));
    } else {
        $f_year = kv_get_current_fiscalyear();
        $begin1 = $f_year['begin'];
        $today1 = date('Y-m-d');
    }

    $f_year = kv_get_current_fiscalyear();
    $begin1 = $f_year['begin'];
    $today1 = date('Y-m-d');


    $charts_list = kv_get_expenses_chartlists();
    $final = array();
    foreach ($charts_list as $single_char) {
        $sql = "SELECT SUM(IF(amount >= 0, amount, 0)) as debit, 
            SUM(IF(amount < 0, -amount, 0)) as credit, SUM(amount) as balance 
            FROM " . TB_PREF . "gl_trans," . TB_PREF . "chart_master," . TB_PREF . "chart_types, " . TB_PREF . "chart_class 
            WHERE " . TB_PREF . "gl_trans.account=" . TB_PREF . "chart_master.account_code AND " . TB_PREF . "chart_master.account_type=" . TB_PREF . "chart_types.id 
            AND " . TB_PREF . "chart_types.class_id=" . TB_PREF . "chart_class.cid AND account='" . $single_char[0] . "' AND tran_date > IF(ctype>0 AND ctype<4, '0000-00-00', '" . $begin1 . "') AND tran_date <= '" . $today1 . "' ";
        $result = db_query($sql, "could not get Company Details");

        while ($row = db_fetch_assoc($result)) {
            if ($row['balance'] > 0) {
                $row['code'] = $single_char[0];
                $row['name'] = $single_char[1];
                $final[] = $row;
            }
        }
    }
    if ($_GET['format'] == 'json') {
        echo json_encode($final);
        exit();
    }
    return $final;

}


if ($_GET['method'] == 'top_ten_customers') {

    $category_sales_count = get_top_customers($options = null);


    if ($_GET['format'] == 'json') {
        echo json_encode($category_sales_count);
        exit();
    }
    return $category_sales_count;

}


if ($_GET['method'] == 'top_ten_services') {

    $today = Today();

    $begin = begin_fiscalyear();
    $begin1 = date2sql($begin);
    $today1 = date2sql($today);


    $sql = $sql = "SELECT SUM((trans.unit_price * trans.quantity) * d.rate) AS total, s.stock_id, s.description, 
            SUM(trans.quantity) AS qty, SUM((trans.govt_fee) * trans.quantity) AS costs FROM
            " . TB_PREF . "debtor_trans_details AS trans, " . TB_PREF . "stock_master AS s, " . TB_PREF . "debtor_trans AS d 
            WHERE trans.stock_id=s.stock_id AND trans.debtor_trans_type=d.type AND trans.debtor_trans_no=d.trans_no
            AND (d.type = " . ST_SALESINVOICE . " OR d.type = " . ST_CUSTCREDIT . ") ";

    $sql .= "AND tran_date >= '$begin1' ";

    $sql .= "AND tran_date <= '$today1' GROUP by s.stock_id ORDER BY total DESC, s.stock_id 
        LIMIT 10";
    $result = db_query($sql);

    $top_ten_services = [];
    while ($myrow = db_fetch($result)) {

        $top_ten_services[] = $myrow;

    }

    if ($_GET['format'] == 'json') {
        echo json_encode($top_ten_services);
        exit();
    }
    return $top_ten_services;


}


if ($_GET['method'] == 'get_all_customers') {

    $sql = "SELECT * FROM 0_debtors_master";
    $result = db_query($sql);

    $all_customers = [];
    while ($myrow = db_fetch($result)) {

        $all_customers[] = $myrow;

    }

    if ($_GET['format'] == 'json') {
        echo json_encode($all_customers);
        exit();
    }
    return $all_customers;

}


if ($_GET['method'] == 'get_all_gl_accounts') {

    $sql = "SELECT * FROM 0_chart_master";
    $result = db_query($sql);

    $return_result = [];
    while ($myrow = db_fetch($result)) {

        $return_result[] = $myrow;

    }

    if ($_GET['format'] == 'json') {
        echo json_encode($return_result);
        exit();
    }
    return $return_result;

}


if ($_GET['method'] == 'get_all_coa_groups') {

    $sql = "SELECT * FROM 0_chart_types WHERE 1=1 ";

    if (isset($_GET['class_id']) && !empty($_GET['class_id'])) {
        $sql .= " AND class_id=" . $_GET['class_id'];
    }


    $result = db_query($sql);

    $return_result = [];
    while ($myrow = db_fetch($result)) {

        $myrow['name'] = $myrow['id'] . " - " . $myrow['name'];

        $return_result[] = $myrow;

    }

    if ($_GET['format'] == 'json') {
        echo json_encode($return_result);
        exit();
    }
    return $return_result;

}


if ($_GET['method'] == 'get_all_coa_classes') {

    $sql = "SELECT * FROM 0_chart_class";
    $result = db_query($sql);

    $return_result = [];
    while ($myrow = db_fetch($result)) {

        $myrow['class_name'] = $myrow['cid'] . " - " . $myrow['class_name'];

        $return_result[] = $myrow;

    }

    if ($_GET['format'] == 'json') {
        echo json_encode($return_result);
        exit();
    }
    return $return_result;

}


if ($_GET['method'] == 'common_settings') {

    $settings = [];
    $curr_fs_yr = get_current_fiscalyear();
    $settings['curr_fiscal_year']['begin'] = sql2date($curr_fs_yr['begin']);
    $settings['curr_fiscal_year']['end'] = sql2date($curr_fs_yr['end']);


    if ($_GET['format'] == 'json') {
        echo json_encode($settings);
        exit();
    }
    return $settings;
}


if ($_GET['method'] == 'chart_of_accounts') {

    $sql = "SELECT CONCAT('CLS_',cid) id, CONCAT(class.cid,' - ',class.class_name) text,0 parent_id, 'class' AS type, 
            cid real_id, 0 as p_id_one,0 as p_id_two  
            FROM 0_chart_class class UNION
            
            SELECT CONCAT('GRP_',id), CONCAT(id,' - ',name) text, 
            CASE WHEN (parent='' OR parent=0 ) THEN CONCAT('CLS_',class_id) ELSE CONCAT('GRP_',parent) END AS parent_id, 
            'group' AS type, id real_id, class_id as p_id_one,parent as p_id_two  
            FROM 0_chart_types UNION 
            
            SELECT CONCAT('LGR_',account_code) id, CONCAT(account_code,' - ',account_name) text,
            CONCAT('GRP_',account_type) parent_id, 'ledger' AS type, account_code real_id,0 as p_id_one,0 as p_id_two  
            FROM 0_chart_master UNION 
            
            
            SELECT CONCAT('SLR_',code) id, CONCAT(code,' - ',name) text,CONCAT('LGR_',ledger_id) parent_id, 'sub_ledger' AS type, 
            code real_id, 0 as p_id_one,0 as p_id_two  
            FROM 0_sub_ledgers ";


    $result = db_query($sql);
    $return_result = [];
    while ($myrow = db_fetch_assoc($result)) {
        $return_result[] = $myrow;
    }

    // Build array of item references:
    foreach ($return_result as $key => &$item) {
        $itemsByReference[$item['id']] = &$item;
        // Children array:
        $itemsByReference[$item['id']]['children'] = array();
        // Empty data class (so that json_encode adds "data: {}" )
        $itemsByReference[$item['id']]['data'] = new StdClass();
    }

    foreach ($return_result as $key => &$item)
        if ($item['parent_id'] && isset($itemsByReference[$item['parent_id']]))
            $itemsByReference [$item['parent_id']]['children'][] = &$item;

    // Remove items that were added to parents elsewhere:
    foreach ($return_result as $key => &$item) {
        if ($item['parent_id'] && isset($itemsByReference[$item['parent_id']]))
            unset($return_result[$key]);
    }

    if ($_GET['format'] == 'json') {
        echo json_encode($return_result);
        exit();
    }
    return $return_result;

}


if ($_GET['method'] == 'create_coa_node') {

    $type = $_POST['node_type'];
    $text = $_POST['text'];
    $node_id = $_POST['node_id'];
    $purpose = $_POST['purpose'];

    $table = '';
    $values = [];
    $primary_key = 0;

    $error = false;
    $msg = "";

    switch ($type) {

        case 'group' :

            $parent_node_id = $_POST['parent_id'];
            $parent_node_info = get_account_type($parent_node_id);
            $class_id = $parent_node_info['class_id'];
            $parent_node_type = $_POST['parent_node_type'];

            if ($parent_node_type == 'CLS') {
                $parent_node_id = 0;
            }

            if (strlen(trim($_POST['node_id'])) == 0) {
                echo json_encode(['msg' => 'The account group id cannot be empty.', 'status' => 'FAIL']);
                exit();
            }

            if (strlen(trim($_POST['text'])) == 0) {
                echo json_encode(['msg' => 'The account group name cannot be empty.', 'status' => 'FAIL']);
                exit();
            }

            if ($_POST['node_id'] === $parent_node_id) {
                echo json_encode(['msg' => 'You cannot set an account group to be a subgroup of itself.', 'status' => 'FAIL']);
                exit();
            }

            $check = get_account_type(trim($_POST['node_id']));
            if ($check && ($purpose != 'update')) {
                echo json_encode(['msg' => 'This account group id is already in use', 'status' => 'FAIL']);
                exit();
            }


            $values = [
                'id' => $_POST['node_id'],
                'parent' => $parent_node_id,
                'class_id' => $class_id,
                'name' => db_escape($_POST['text'])

            ];

            $primary_key = 'id';
            $table = '0_chart_types';


            break;

        case 'ledger' :

            $values = [
                'account_code' => $_POST['node_id'],
                'account_name' => db_escape($_POST['text']),
                'account_type' => $_POST['parent_id'],
            ];

            $primary_key = 'account_code';
            $table = '0_chart_master';

            break;
        case 'sub_ledger' :

            $values = [
                'code' => $_POST['node_id'],
                'name' => db_escape($_POST['text']),
                'ledger_id' => $_POST['parent_id'],
            ];

            $primary_key = 'code';
            $table = '0_sub_ledgers';

            break;
    }

    if (!empty($table)) {

        if ($purpose == 'create') {
            db_insert($table, $values);
        }
        if ($purpose == 'update') {
            $id = $values[$primary_key];
            unset($values[$primary_key]);
            db_update($table, $values, [$primary_key . "=" . $id]);
        }

    }


    $msg_text = "Node updated";
    if ($purpose == 'create')
        $msg_text = "New node created";
    if ($purpose == 'update')
        $msg_text = "Node updated";

    echo json_encode(['msg' => $msg_text, 'status' => 'OK']);
    exit();

}


function node_group_delete_check($id)
{

    if (key_in_foreign_table($id, 'chart_master', 'account_type')) {

        return trans("Cannot delete this account group because GL accounts have been created referring to it.");
    }

    if (key_in_foreign_table($id, 'chart_types', 'parent')) {
        return trans("Cannot delete this account group because GL account groups have been created referring to it.");
    }

    return 'DELETABLE';

}

function node_ledger_delete_check($selected_account)
{
    global $SysPrefs;
    if ($selected_account == "")
        return false;

    if($selected_account == $SysPrefs->prefs['opening_bal_equity_account']) {
        return (trans("Cannot delete this system default account."));
    }

    if (key_in_foreign_table($selected_account, 'gl_trans', 'account'))
    {
        return (trans("Cannot delete this account because transactions have been created using this account."));
    }

    if (gl_account_in_company_defaults($selected_account))
    {
        return (trans("Cannot delete this account because it is used as one of the company default GL accounts."));
    }

    if (key_in_foreign_table($selected_account, 'bank_accounts', 'account_code'))
    {
        return (trans("Cannot delete this account because it is used by a bank account."));
    }

    if (gl_account_in_stock_category($selected_account))
    {
        return (trans("Cannot delete this account because it is used by one or more Item Categories."));
    }

    if (gl_account_in_stock_master($selected_account))
    {
        return (trans("Cannot delete this account because it is used by one or more Items."));
    }

    if (gl_account_in_tax_types($selected_account))
    {
        return (trans("Cannot delete this account because it is used by one or more Taxes."));
    }

    if (gl_account_in_cust_branch($selected_account))
    {
        return (trans("Cannot delete this account because it is used by one or more Customer Branches."));
    }
    if (gl_account_in_suppliers($selected_account))
    {
        return (trans("Cannot delete this account because it is used by one or more suppliers."));
    }

    if (gl_account_in_quick_entry_lines($selected_account))
    {
        return (trans("Cannot delete this account because it is used by one or more Quick Entry Lines."));
    }

    return "DELETABLE";
}


if ($_GET['method'] == 'delete_coa_node') {

    $type = $_POST['node_type'];
    $node_id = $_POST['node_id'];

    $error = false;
    $msg = "";
    $status = "OK";


    switch ($type) {

        case 'group' :

            $check = node_group_delete_check($node_id);

            if ($check == 'DELETABLE') {
                delete_account_type($node_id);
                $msg = "Node deleted";
            }else {
                $error = true;
                $status = "FAIL";
                $msg = $check;
            }


            break;

        case 'ledger' :

            $check = node_ledger_delete_check($node_id);

            if ($check == 'DELETABLE') {
                delete_gl_account($node_id);
                $msg = "Node deleted";
            }else {
                $error = true;
                $status = "FAIL";
                $msg = $check;
            }

            break;

    }


    echo json_encode(['status' => $status, 'msg' => $msg]);
    exit();
}


if ($_GET['method'] == 'change_coa_parent') {

    $type = $_POST['node_type'];
    $node_id = $_POST['node_id'];
    $parent_id = $_POST['parent_id'];
    $class_id = $_POST['class_id'];

    if (empty($parent_id)) $parent_id = 0;


    $error = false;
    $msg = "";

    switch ($type) {
        case 'group' :

            if (empty($class_id)) $class_id = 0;
            if (empty($parent_id)) $parent_id = 0;

            db_update('0_chart_types', ['parent' => $parent_id, 'class_id' => $class_id], ["id=$node_id"]);

            break;
        case 'ledger' :

            db_update('0_chart_master', ['account_type' => $parent_id], ["account_code=$node_id"]);

            break;
    }


    echo json_encode(['status' => "OK", 'msg' => "Parent changed"]);
    exit();
}

if($_GET['method'] == 'get_class_balances') {

    $from = date2sql($_GET['from']);
    $to = date2sql($_GET['to']);

    $sql = "SELECT cls.class_name name, SUM(gl.amount) amount, cls.cid as id, 'class' as coa_type FROM 0_gl_trans gl

        LEFT JOIN 0_chart_master chart ON chart.account_code=gl.account 
        
        LEFT JOIN 0_chart_types grp on grp.id=chart.account_type
        
        LEFT JOIN 0_chart_class cls on cls.cid=grp.class_id 
        
        WHERE  cls.ctype IN (4,5,6) AND gl.tran_date >= '$from' AND gl.tran_date <= '$to' 
        
        GROUP BY cls.cid";


    $result = db_query($sql);

    $return_result = [];
    while ($myrow = db_fetch_assoc($result)) {

        $return_result[] = $myrow;
        $myrow['amount'] = round($myrow["amount"],2);

    }

    if ($_GET['format'] == 'json') {
        echo json_encode($return_result);
        exit();
    }
    return $return_result;


}


if($_GET['method'] == 'get_top_level_group_balances') {

    $from = date2sql($_GET['from']);
    $to = date2sql($_GET['to']);

    $class_id = $_GET['parent_id'];

    $sql = "SELECT grp.name, SUM(gl.amount) amount, grp.id,'group' as coa_type  FROM 0_gl_trans gl
LEFT JOIN 0_chart_master chart ON chart.account_code=gl.account 
LEFT JOIN 0_chart_types grp on grp.id=chart.account_type
LEFT JOIN 0_chart_class cls on cls.cid=grp.class_id 
WHERE  cls.ctype IN (4,5,6) AND grp.class_id=$class_id 

AND gl.tran_date >= '$from' AND gl.tran_date <= '$to' 

GROUP BY chart.account_type";


    $result = db_query($sql);

    $return_result = [];
    while ($myrow = db_fetch_assoc($result)) {

        $myrow['amount'] = round2($myrow["amount"],2);
        $return_result[] = $myrow;


    }

    if ($_GET['format'] == 'json') {
        echo json_encode($return_result);
        exit();
    }
    return $return_result;

}



if($_GET['method'] == 'get_group_balances') {

    $from = date2sql($_GET['from']);
    $to = date2sql($_GET['to']);

    $group_id = $_GET['parent_id'];

    $sql = "SELECT grp.name, IFNULL(SUM(gl.amount),0) amount, grp.id,'group' AS coa_type  
FROM 0_gl_trans gl 

left join 0_chart_master chart on chart.account_code=gl.account
right join 0_chart_types grp on grp.id=chart.account_type

where grp.parent=$group_id AND gl.tran_date >= '$from' AND gl.tran_date <= '$to' 

group by grp.id";


    $sql .= " UNION ";

    $sql .= "SELECT chart.account_name name, SUM(gl.amount) amount, chart.account_code id,'ledger' AS coa_type  
FROM 0_gl_trans gl 

left join 0_chart_master chart on chart.account_code=gl.account

where chart.account_type=$group_id AND gl.tran_date >= '$from' AND gl.tran_date <= '$to' 

group by gl.account";



    $result = db_query($sql);

    $return_result = [];
    while ($myrow = db_fetch_assoc($result)) {

        $myrow['amount'] = round($myrow["amount"],2);
        $return_result[] = $myrow;

    }

    if ($_GET['format'] == 'json') {
        echo json_encode($return_result);
        exit();
    }
    return $return_result;

}

if($_GET['method'] == 'get_ledger_transactions') {

    global $systypes_array;

    $account = $_GET['parent_id'];

    $from = date2sql($_GET['from']);
    $to = date2sql($_GET['to']);

    $start = isset($_GET['start']) ? $_GET['start'] : 0;


    $sql = "SELECT gl.*,sub_ledger.name as sub_ledger_name, j.event_date, j.doc_date, a.gl_seq, u.user_id, st.supp_reference, gl.person_id subcode,
			IFNULL(IFNULL(sup.supp_name, debt.name), bt.person_id) as person_name, 
			IFNULL(gl.person_id, IFNULL(sup.supplier_id, debt.debtor_no)) as person_id,
                        IF(gl.person_id, gl.person_type_id, IF(sup.supplier_id,".  PT_SUPPLIER . "," .  "IF(debt.debtor_no," . PT_CUSTOMER . ", -1))) as person_type_id,
			IFNULL(st.tran_date, IFNULL(dt.tran_date, IFNULL(bt.trans_date, IFNULL(grn.delivery_date, gl.tran_date)))) as doc_date,
			coa.account_name, ref.reference,voucher.chq_date,voucher.chq_no 
			 FROM "
        .TB_PREF."gl_trans gl
			LEFT JOIN ".TB_PREF."voided v ON gl.type_no=v.id AND v.type=gl.type

			LEFT JOIN ".TB_PREF."supp_trans st ON gl.type_no=st.trans_no AND st.type=gl.type AND (gl.type!=".ST_JOURNAL." OR gl.person_id=st.supplier_id)
			LEFT JOIN ".TB_PREF."grn_batch grn ON grn.id=gl.type_no AND gl.type=".ST_SUPPRECEIVE." AND gl.person_id=grn.supplier_id
			LEFT JOIN ".TB_PREF."debtor_trans dt ON gl.type_no=dt.trans_no AND dt.type=gl.type AND (gl.type!=".ST_JOURNAL." OR gl.person_id=dt.debtor_no)

			LEFT JOIN ".TB_PREF."suppliers sup ON st.supplier_id=sup.supplier_id OR grn.supplier_id=sup.supplier_id
			LEFT JOIN ".TB_PREF."cust_branch branch ON dt.branch_code=branch.branch_code
			LEFT JOIN ".TB_PREF."debtors_master debt ON dt.debtor_no=debt.debtor_no

			LEFT JOIN ".TB_PREF."bank_trans bt ON bt.type=gl.type AND bt.trans_no=gl.type_no AND bt.amount!=0
				 AND bt.person_type_id=gl.person_type_id AND bt.person_id=gl.person_id

			LEFT JOIN ".TB_PREF."journal j ON j.type=gl.type AND j.trans_no=gl.type_no
			LEFT JOIN ".TB_PREF."audit_trail a ON a.type=gl.type AND a.trans_no=gl.type_no AND NOT ISNULL(gl_seq)
			LEFT JOIN ".TB_PREF."users u ON a.user=u.id 
			
			LEFT JOIN ".TB_PREF."vouchers AS voucher ON voucher.trans_no=gl.type_no 
			    AND gl.type=IF(voucher.voucher_type='PV',1,2) 
			    
			LEFT JOIN 0_sub_ledgers sub_ledger ON sub_ledger.code = gl.axispro_subledger_code 

			LEFT JOIN ".TB_PREF."refs ref ON ref.type=gl.type AND ref.id=gl.type_no,"
        .TB_PREF."chart_master coa
		WHERE coa.account_code=gl.account
		AND ISNULL(v.date_)
		
		AND gl.tran_date >= '$from' AND gl.tran_date <= '$to' 
		
		
		AND gl.amount <> 0";

    if ($account != null)
        $sql .= " AND gl.account = ".db_escape($account);



    $sql .= " LIMIT $start,10 ";


    $result = db_query($sql);

    $return_result = [];
    while ($myrow = db_fetch_assoc($result)) {


        if(empty($myrow['person_name']))
            $myrow['person_name'] = "";

        if(empty($myrow['sub_ledger_name']))
            $myrow['sub_ledger_name'] = "";

        $myrow['tran_date'] = sql2date($myrow['tran_date']);
        $myrow['type'] = $systypes_array[$myrow["type"]];
        $myrow['amount'] = round($myrow["amount"],2);

        $return_result[] = $myrow;

    }


    $op_bal = 0;

    if($start == 0) {
        $op_bal = get_gl_balance_from_to(null, null, $account);
    }



    if ($_GET['format'] == 'json') {
        echo json_encode(['data'=>$return_result,'ob_bal' => $op_bal]);
        exit();
    }
    return $return_result;


}

