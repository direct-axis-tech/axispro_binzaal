<?php

/**
 * Class API_Call
 * Created By : Bipin
 */

use App\Events\Accounting\BankCredited;
use App\Events\Sales\CustomerPaid;
use App\Events\Sales\CustomerRefunded;
use App\Exceptions\BusinessLogicException;
use App\Http\Controllers\Sales\CustomersController;
use App\Models\Accounting\BankAccount;
use App\Models\Accounting\BankTransaction;
use App\Models\Accounting\Dimension;
use App\Models\Accounting\FiscalYear;
use App\Models\Hr\EmployeeLeave;
use App\Models\MetaReference;
use App\Models\Sales\Customer;
use App\Models\Sales\CustomerTransaction;
use App\Models\Sales\ServiceRequest;
use Illuminate\Support\Arr;
use App\Permissions;

include_once("AxisPro.php");
include_once("PrepareQuery.php");

Class API_Call
{

    /**
     * @param $data
     * @param string $format
     * @return mixed
     * Return HTTP Response
     */
    public function HttpResponse($data, $format = 'json')
    {
        if ($format == 'json') {
            echo json_encode($data);
            exit();
        }
        return $data;

    }

    /**
     * @return array
     * Get sales report
     */
    public function daily_sales()
    {
        try {

            $sql = "select tran_date,ROUND(sum(ov_amount+ov_gst),2) amount from 0_debtor_trans 
            where type=10 group by tran_date order by tran_date ASC limit 10";

            $result = db_query($sql);

            $daily_sales = [];
            while ($row = db_fetch($result)) {
                $daily_sales[$row['tran_date']] = $row['amount'];
            }

            return AxisPro::SendResponse($daily_sales);

        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }

    /**
     * @return array
     * Get employee wise service count
     */
    public function employee_service_count()
    {
        try {
            $sql = "select users.user_id,sum(dt_detail.quantity) qty from 0_debtor_trans_details dt_detail 
            left join 0_users users on users.id=dt_detail.created_by 
            group by dt_detail.created_by order by qty desc limit 5";

            $result = db_query($sql);

            $employee_service_count = [];
            while ($row = db_fetch($result)) {
                $employee_service_count[$row['user_id']] = $row['qty'];
            }


            return AxisPro::SendResponse($employee_service_count);
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }

    /**
     * @return array
     * Get top5 selling category
     */
    public function top_five_category()
    {

        try {
            $sql = "select sc.description, SUM(dt_detail.quantity) qty from 0_debtor_trans_details dt_detail
            left join 0_stock_master sm on sm.stock_id=dt_detail.stock_id
            left join 0_stock_category sc on sc.category_id=sm.category_id
            group by sc.category_id order by qty desc limit 5";

            $result = db_query($sql);

            $top_five_category = [];
            while ($row = db_fetch($result)) {
                $top_five_category[$row['description']] = $row['qty'];
            }

            return AxisPro::SendResponse($top_five_category);

        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }


    }

    /**
     * @return array
     * Get category wise sales count
     */
    public function category_sales_count()
    {

        try {
            $sql = "select sc.description, SUM(dt_detail.quantity) qty from 0_debtor_trans_details dt_detail
            left join 0_stock_master sm on sm.stock_id=dt_detail.stock_id
            left join 0_stock_category sc on sc.category_id=sm.category_id
            group by sc.category_id";

            $result = db_query($sql);

            $category_sales_count = [];
            while ($row = db_fetch($result)) {
                $category_sales_count[$row['description']] = $row['qty'];
            }

            return AxisPro::SendResponse($category_sales_count);

        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }


    }

    /**
     * @return array
     * Get category wise sales report
     */
    public function category_sales_report()
    {

        try {

            $date = null;

            $sql = (
                "SELECT
                    c.description,
                    SUM(a.quantity) AS inv_count,
                    ROUND(
                        SUM(
                            (
                                a.unit_price
                                + a.returnable_amt
                                - IF(d.tax_included, a.unit_tax, 0)
                            ) * a.quantity
                        ),
                        2
                    ) AS service_charge 
                FROM 0_debtor_trans_details a 
                LEFT JOIN 0_stock_master b ON b.stock_id=a.stock_id 
                LEFT JOIN 0_stock_category c ON c.category_id=b.category_id 
                LEFT JOIN 0_debtor_trans d ON d.trans_no = a.debtor_trans_no AND d.`type`=10
                WHERE
                    a.debtor_trans_type = 10
                    AND d.ov_amount + d.ov_gst + d.ov_freight + d.ov_freight_tax + d.ov_discount <> 0"
            );

            if ($date) {
                $sql .= " and d.tran_date='$date' ";
            }

            $sql .= " group by b.category_id";
            $result = db_query($sql, "Transactions could not be calculated");

            $category_sales_report = [];
            while ($row = db_fetch($result)) {
                $category_sales_report[] = $row;
            }

            return AxisPro::SendResponse($category_sales_report);

        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }

    /**
     * @return array
     * Get the bank balances
     */
    public function bank_balances()
    {

        try {
            $today = Today();
            $today = date2sql($today);
            
            $mysqliResult = db_query(
                "SELECT
                    chart.account_code,
                    chart.account_name,
                    ROUND(SUM(trans.amount), 2) balance
                FROM `0_chart_master` chart
                LEFT JOIN `0_bank_accounts` bank ON
                    bank.account_code = chart.account_code
                LEFT JOIN `0_gl_trans` trans ON
                    trans.account = chart.account_code
                WHERE
                    bank.id IS NOT NULL
                    AND trans.tran_date <= '{$today}'
                    AND trans.amount <> 0
                GROUP BY account
                ORDER BY chart.account_name",
                "Could not retrieve bank balances"
            );

            // key by account
            $bankBalances = [];
            while ($row = $mysqliResult->fetch_assoc()) {
                $bankBalances[$row['account_name']] = $row['balance'];
            }

        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }


    }

    /**
     * @return array
     * For expense chart
     */
    public function expenses()
    {

        try {
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
                $sql = "SELECT ROUND(SUM(IF(amount >= 0, amount, 0)),2) as debit, 
                ROUND(SUM(IF(amount < 0, -amount, 0)),2) as credit, ROUND(SUM(amount),2) as balance 
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
            return AxisPro::SendResponse($final);

        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }

    /**
     * @return array
     * Get top 10 customers
     */
    public function top_ten_customers()
    {
        try {
            $category_sales_count = get_top_customers($options = null);
            return AxisPro::SendResponse($category_sales_count);
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }

    /**
     * @return array
     * Get top10 selling services
     */
    public function top_ten_services()
    {

        try {
            $today = Today();

            $begin = begin_fiscalyear();
            $begin1 = date2sql($begin);
            $today1 = date2sql($today);


            $sql = $sql = "SELECT ROUND(SUM((trans.unit_price * trans.quantity) * d.rate),2) AS total, s.stock_id, s.description, 
            SUM(trans.quantity) AS qty, ROUND(SUM((trans.govt_fee) * trans.quantity),2) AS costs FROM
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

            return AxisPro::SendResponse($top_ten_services);


        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }

    /**
     * @return array
     * List all customers
     */
    public function get_all_customers($format='json')
    {

        try {
            $sql = "SELECT *, concat(debtor_ref, ' - ', `name`) AS formatted_name FROM 0_debtors_master";
            $result = db_query($sql);

            $all_customers = [];
            while ($myrow = db_fetch($result)) {

                $all_customers[] = $myrow;

            }

            return AxisPro::SendResponse($all_customers, $format);
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }


    }

    /**
     * @param string $format
     * @return array
     * List all GL Accounts
     */
    public function get_all_gl_accounts($format = 'json')
    {
        try {
            $sql = "SELECT * FROM 0_chart_master";
            $result = db_query($sql);

            $return_result = [];
            while ($myrow = db_fetch($result)) {
                $return_result[] = $myrow;
            }

            return AxisPro::SendResponse($return_result, $format);
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }


    /**
     * @return array
     * Get all COA groups
     */
    public function get_all_coa_groups()
    {

        try {
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

            return AxisPro::SendResponse($return_result);
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }
    }

    /**
     * @return array
     * Get all COA classes
     */
    public function get_all_coa_classes()
    {
        try {
            $sql = "SELECT * FROM 0_chart_class";
            $result = db_query($sql);

            $return_result = [];
            while ($myrow = db_fetch($result)) {

                $myrow['class_name'] = $myrow['cid'] . " - " . $myrow['class_name'];

                $return_result[] = $myrow;

            }

            return AxisPro::SendResponse($return_result);
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }

    /**
     * @return array
     * Get common Application Settings
     */
    public function common_settings()
    {
        try {
            $settings = [];
            $curr_fs_yr = get_current_fiscalyear();
            $settings['curr_fiscal_year']['begin'] = sql2date($curr_fs_yr['begin']);
            $settings['curr_fiscal_year']['end'] = sql2date($curr_fs_yr['end']);

            return AxisPro::SendResponse($settings);
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }

    /**
     * @return array
     * Get the chart of accounts
     */
    public function chart_of_accounts()
    {

        try {
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

                return AxisPro::SendResponse($return_result);


            } catch (Exception $e) {
                return AxisPro::catchException($e);
            }

        }

    /**
     * Create new node in chart of accounts tree
     */
    public function create_coa_node()
    {
        try {
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
                    $class_id = $_POST['parent_id'];
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
                'account_code' => db_escape($_POST['node_id']),
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

            return AxisPro::SendResponse(['msg' => $msg_text, 'status' => 'OK']);


        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }

    /**
     * @param $id
     * @return mixed|string|\Symfony\Component\Translation\TranslatorInterface
     * Check before deleting COA group
     */
    public function node_group_delete_check($id)
    {
        try {
            if (key_in_foreign_table($id, 'chart_master', 'account_type')) {

                return trans("Cannot delete this account group because GL accounts have been created referring to it.");
            }

            if (key_in_foreign_table($id, 'chart_types', 'parent')) {
                return trans("Cannot delete this account group because GL account groups have been created referring to it.");
            }

            return 'DELETABLE';
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }


    }

    /**
     * @param $selected_account
     * @return bool|mixed|string|\Symfony\Component\Translation\TranslatorInterface
     * Check before deleting COA GL
     */
    public function node_ledger_delete_check($selected_account)
    {
        try {
            global $SysPrefs;
            if ($selected_account == "")
                return false;

            if ($selected_account == $SysPrefs->prefs['opening_bal_equity_account']) {
                return (trans("Cannot delete this system default account."));
            }

            if (key_in_foreign_table($selected_account, 'gl_trans', 'account')) {
                return (trans("Cannot delete this account because transactions have been created using this account."));
            }

            if (gl_account_in_company_defaults($selected_account)) {
                return (trans("Cannot delete this account because it is used as one of the company default GL accounts."));
            }

            if (key_in_foreign_table($selected_account, 'bank_accounts', 'account_code')) {
                return (trans("Cannot delete this account because it is used by a bank account."));
            }

            if (gl_account_in_stock_category($selected_account)) {
                return (trans("Cannot delete this account because it is used by one or more Item Categories."));
            }

            if (gl_account_in_stock_master($selected_account)) {
                return (trans("Cannot delete this account because it is used by one or more Items."));
            }

            if (gl_account_in_tax_types($selected_account)) {
                return (trans("Cannot delete this account because it is used by one or more Taxes."));
            }

            if (gl_account_in_cust_branch($selected_account)) {
                return (trans("Cannot delete this account because it is used by one or more Customer Branches."));
            }
            if (gl_account_in_suppliers($selected_account)) {
                return (trans("Cannot delete this account because it is used by one or more suppliers."));
            }

            if (gl_account_in_quick_entry_lines($selected_account)) {
                return (trans("Cannot delete this account because it is used by one or more Quick Entry Lines."));
            }

            return "DELETABLE";
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }

    /**
     * Delete a COA Node
     */
    public function delete_coa_node()
    {

        try {
            $type = $_POST['node_type'];
            $node_id = $_POST['node_id'];

            $error = false;
            $msg = "";
            $status = "OK";


            switch ($type) {

                case 'group' :

                $check = $this->node_group_delete_check($node_id);

                if ($check == 'DELETABLE') {
                    delete_account_type($node_id);
                    $msg = "Node deleted";
                } else {
                    $error = true;
                    $status = "FAIL";
                    $msg = $check;
                }


                break;

                case 'ledger' :

                $check = $this->node_ledger_delete_check($node_id);

                if ($check == 'DELETABLE') {
                    delete_gl_account($node_id);
                    $msg = "Node deleted";
                } else {
                    $error = true;
                    $status = "FAIL";
                    $msg = $check;
                }

                break;

                case 'sub_ledger' :

                    //$check = $this->node_ledger_delete_check($node_id);

                    //if ($check == 'DELETABLE') {
                $this->delete_subledger($node_id);
                $msg = "Node deleted";
                    // } else {
                    //    $error = true;
                    //     $status = "FAIL";
                    //    $msg = $check;
                    //  }


                break;


            }


            echo json_encode(['status' => $status, 'msg' => $msg]);
            exit();

        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }


    }


    function delete_subledger($node_id)
    {
        $sql = "DELETE FROM 0_sub_ledgers WHERE code='" . $node_id . "'";
        db_query($sql);
    }


    /**
     * Change COA node parent
     */
    public function change_coa_parent()
    {

        try {
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

            return AxisPro::SendResponse(['status' => "OK", 'msg' => "Parent changed"]);

        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }


    }

    /**
     * @return array
     * Get the COA Class balances
     */
    public function get_class_balances()
    {

        try {
            $from = date2sql($_GET['from']);
            $to = date2sql($_GET['to']);
            $ctype = $_GET['ctype'];

            $sql = "SELECT cls.class_name name, SUM(gl.amount) amount, cls.cid as id, 'class' as coa_type FROM 0_gl_trans gl

            LEFT JOIN 0_chart_master chart ON chart.account_code=gl.account 

            LEFT JOIN 0_chart_types grp on grp.id=chart.account_type

            LEFT JOIN 0_chart_class cls on cls.cid=grp.class_id 

            WHERE  gl.tran_date >= '$from' AND gl.tran_date <= '$to' and gl.account != '0'";

            if(!empty($ctype))
                $sql.= " and cls.ctype IN (".$ctype.")";

            $sql.= " GROUP BY cls.cid ORDER BY cls.ctype";


            $result = db_query($sql);

            $return_result = [];
            while ($myrow = db_fetch_assoc($result)) {

                $return_result[] = $myrow;
                $myrow['amount'] = round($myrow["amount"], 2);

            }

            return AxisPro::SendResponse($return_result);

        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }


    }

    /**
     * @return array
     * Get Top level COA group balances
     */
    public function get_top_level_group_balances()
    {

        try {
            $from = date2sql($_GET['from']);
            $to = date2sql($_GET['to']);
            $ctype = $_GET['ctype'];

            $class_id = $_GET['parent_id'];

            $sql = "SELECT grp.name, SUM(gl.amount) amount, grp.id,'group' as coa_type  FROM 0_gl_trans gl
            LEFT JOIN 0_chart_master chart ON chart.account_code=gl.account 
            LEFT JOIN 0_chart_types grp on grp.id=chart.account_type
            LEFT JOIN 0_chart_class cls on cls.cid=grp.class_id
            WHERE  grp.class_id=$class_id

            AND gl.tran_date >= '$from' AND gl.tran_date <= '$to'";
            if(!empty($ctype))
                $sql.= " and cls.ctype IN (".$ctype.")";
        
            $sql.= " GROUP BY chart.account_type";


            $result = db_query($sql);

            $return_result = [];
            while ($myrow = db_fetch_assoc($result)) {

                $myrow['amount'] = round2($myrow["amount"], 2);
                $return_result[] = $myrow;


            }

            return AxisPro::SendResponse($return_result);


        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }

    /**
     * @return array
     * Get COA group balances
     */
    public function get_group_balances()
    {

        try {

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

                $myrow['amount'] = round($myrow["amount"], 2);
                $return_result[] = $myrow;

            }

            return AxisPro::SendResponse($return_result);


        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }


    }

    /**
     * @return array
     * Get the Ledger transactions
     */
    public function get_ledger_transactions()
    {

        try {
            global $systypes_array;

            $account = $_GET['parent_id'];

            $from = date2sql($_GET['from']);
            $to = date2sql($_GET['to']);

            $start = isset($_GET['start']) ? $_GET['start'] : 0;


            $sql = "SELECT gl.*,sub_ledger.name as sub_ledger_name, j.event_date, j.doc_date, a.gl_seq, u.user_id, st.supp_reference, gl.person_id subcode,
            IFNULL(IFNULL(sup.supp_name, debt.name), bt.person_id) as person_name, 
            IFNULL(gl.person_id, IFNULL(sup.supplier_id, debt.debtor_no)) as person_id,
            IF(gl.person_id, gl.person_type_id, IF(sup.supplier_id," . PT_SUPPLIER . "," . "IF(debt.debtor_no," . PT_CUSTOMER . ", -1))) as person_type_id,
            IFNULL(st.tran_date, IFNULL(dt.tran_date, IFNULL(bt.trans_date, IFNULL(grn.delivery_date, gl.tran_date)))) as doc_date,
            coa.account_name, ref.reference,voucher.chq_date,voucher.chq_no 
            FROM "
            . TB_PREF . "gl_trans gl
            LEFT JOIN " . TB_PREF . "voided v ON gl.type_no=v.id AND v.type=gl.type

            LEFT JOIN " . TB_PREF . "supp_trans st ON gl.type_no=st.trans_no AND st.type=gl.type AND (gl.type!=" . ST_JOURNAL . " OR gl.person_id=st.supplier_id)
            LEFT JOIN " . TB_PREF . "grn_batch grn ON grn.id=gl.type_no AND gl.type=" . ST_SUPPRECEIVE . " AND gl.person_id=grn.supplier_id
            LEFT JOIN " . TB_PREF . "debtor_trans dt ON gl.type_no=dt.trans_no AND dt.type=gl.type AND (gl.type!=" . ST_JOURNAL . " OR gl.person_id=dt.debtor_no)

            LEFT JOIN " . TB_PREF . "suppliers sup ON st.supplier_id=sup.supplier_id OR grn.supplier_id=sup.supplier_id
            LEFT JOIN " . TB_PREF . "cust_branch branch ON dt.branch_code=branch.branch_code
            LEFT JOIN " . TB_PREF . "debtors_master debt ON dt.debtor_no=debt.debtor_no

            LEFT JOIN " . TB_PREF . "bank_trans bt ON bt.type=gl.type AND bt.trans_no=gl.type_no AND bt.amount!=0
            AND bt.person_type_id=gl.person_type_id AND bt.person_id=gl.person_id

            LEFT JOIN " . TB_PREF . "journal j ON j.type=gl.type AND j.trans_no=gl.type_no
            LEFT JOIN " . TB_PREF . "audit_trail a ON a.type=gl.type AND a.trans_no=gl.type_no AND NOT ISNULL(gl_seq)
            LEFT JOIN " . TB_PREF . "users u ON a.user=u.id 

            LEFT JOIN " . TB_PREF . "vouchers AS voucher ON voucher.trans_no=gl.type_no 
            AND gl.type=IF(voucher.voucher_type='PV',1,2) 

            LEFT JOIN 0_sub_ledgers sub_ledger ON sub_ledger.code = gl.axispro_subledger_code 

            LEFT JOIN " . TB_PREF . "refs ref ON ref.type=gl.type AND ref.id=gl.type_no,"
            . TB_PREF . "chart_master coa
            WHERE coa.account_code=gl.account
            AND ISNULL(v.date_)

            AND gl.tran_date >= '$from' AND gl.tran_date <= '$to' 


            AND gl.amount <> 0";

            if ($account != null)
                $sql .= " AND gl.account = " . db_escape($account);


            $sql .= " LIMIT $start,10 ";


            $result = db_query($sql);

            $return_result = [];
            while ($myrow = db_fetch_assoc($result)) {


                if (empty($myrow['person_name']))
                    $myrow['person_name'] = "";

                if (empty($myrow['sub_ledger_name']))
                    $myrow['sub_ledger_name'] = "";

                $myrow['tran_date'] = sql2date($myrow['tran_date']);
                $myrow['type'] = $systypes_array[$myrow["type"]];
                $myrow['amount'] = round($myrow["amount"], 2);

                $return_result[] = $myrow;

            }


            $op_bal = 0;

            if ($start == 0) {
                $op_bal = get_gl_balance_from_to(null, null, $account);
            }


            return AxisPro::SendResponse(['data' => $return_result, 'ob_bal' => $op_bal]);

        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }


    }

    /**
     * @return mixed
     * Get a category info
     */
    public function get_category()
    {

        try {
            $id = $_GET['category_id'];

            $sql = "SELECT * FROM 0_stock_category WHERE category_id = $id";
            $result = db_query($sql);

            $return_result = db_fetch_assoc($result);

            return AxisPro::SendResponse($return_result);
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }


    }


    /**
     * @param bool $new_item
     * @param string $Mode
     * @return array
     * Validation check fot item create
     */
    public function validate_before_item_create($new_item = true, $Mode = "ADD_ITEM")
    {

        try {
            $errors = [];

            if (strlen($_POST['description']) == 0) {
                $errors['description'] = trans('The item name must be entered.');
            }
            if (strlen($_POST['NewStockID']) == 0) {
                $errors['NewStockID'] = trans('The item code cannot be empty.');
            }
            if (strstr($_POST['NewStockID'], " ") || strstr($_POST['NewStockID'], "'") ||
                strstr($_POST['NewStockID'], "+") || strstr($_POST['NewStockID'], "\"") ||
                strstr($_POST['NewStockID'], "&") || strstr($_POST['NewStockID'], "\t")) {

                $errors['NewStockID'] = trans('The item code cannot contain any of the following characters -  & + OR a space OR quotes.');

        }
        if ($new_item && db_num_rows(get_item_kit($_POST['NewStockID']))) {
            $errors['NewStockID'] = trans('This item code is already assigned to stock item or sale kit.');

        }

        if (get_post('fixed_asset')) {
            if ($_POST['depreciation_rate'] > 100) {
                $_POST['depreciation_rate'] = 100;
            } elseif ($_POST['depreciation_rate'] < 0) {
                $_POST['depreciation_rate'] = 0;
            }
            $move_row = get_fixed_asset_move($_POST['NewStockID'], ST_SUPPRECEIVE);
            if (isset($_POST['depreciation_start']) && strtotime($_POST['depreciation_start']) < strtotime($move_row['tran_date'])) {
                $errors['depreciation_start'] = trans('The depreciation cannot start before the fixed asset purchase date.');
            }
        }

        $total_gov_fee = (
            (float)get_post('govt_fee', 0)
            + (float)get_post('bank_service_charge', 0)
            + (float)get_post('bank_service_charge_vat', 0)
            + (float)get_post('pf_amount', 0)
        );
        if (
            empty($_POST['govt_bank_account'])
            && $total_gov_fee > 0
        ) {
            $errors['govt_bank_account'] = trans("Please select the account to credit the cost");
        }

        if (empty(get_post('returnable_to', null)) && get_post('returnable_amt', 0) > 0) {
            $errors['returnable_to'] = trans("Please select the account to debit the recievable");
        }

        if (empty(get_post('split_govt_fee_acc', null)) && get_post('split_govt_fee_amt', 0) > 0) {
            $errors['split_govt_fee_acc'] = trans("Please select the account to split the govt fee");
        }

        if (empty(get_post('receivable_commission_account', null)) && get_post('receivable_commission_amount', 0) > 0) {
            $errors['receivable_commission_account'] = trans("Please select an account for receivable commission");
        }

        if (
            (
                (float)get_post('split_govt_fee_amt', 0)
                + (float)get_post('returnable_amt', 0)
                + (float)get_post('receivable_commission_amount', 0)
            ) > $total_gov_fee
        ) {
            $errors['govt_fee'] = trans("The govt fee is too small for this item");
        }

        if(empty($_POST['tax_type_id'])) {
            $errors['tax_type_id'] = trans("Please select the item's tax configuration");
        }

        if(empty($_POST['category_id'])) {
            $errors['category_id'] = trans("Please select the category the item belongs to");
        }

        if (!check_num('price', 0)) {
            $errors['price'] = trans("The service charge entered must be numeric.");
        } elseif ($Mode == 'ADD_ITEM' && get_stock_price_type_currency($_POST['NewStockID'], $_POST['sales_type_id'], $_POST['curr_abrev'])) {
            $errors['price'] = trans("The sales pricing for this item, sales type and currency has already been added.");
        }


        return $errors;

    } catch (Exception $e) {
        return AxisPro::catchException($e);
    }


}


    /**
     * @return mixed
     * Save an item
     */
    public function save_item()
    {

        try {
            $mode = 'ADD_ITEM';
            $new_item = true;
            if (!empty($_POST['edit_stock_id'])) {
                $mode = 'EDIT_ITEM';
                $new_item = false;
            }

            $errors = $this->validate_before_item_create($new_item, $mode);

            if (!empty($errors)) {
                return AxisPro::SendResponse(['status' => 'FAIL', 'msg' => 'VALIDATION_FAILED', 'data' => $errors]);
            }

            $_POST['sub_category_id'] = 0;
            if (isset($_POST['sub_cat_1']) && !empty($_POST['sub_cat_1']))
                $_POST['sub_category_id'] = $_POST['sub_cat_1'];

            if (isset($_POST['sub_cat_2']) && !empty($_POST['sub_cat_2']))
                $_POST['sub_category_id'] = $_POST['sub_cat_2'];

            if ($new_item) {

                add_item(
                    $_POST['NewStockID'],
                    $_POST['description'],
                    $_POST['long_description'],
                    $_POST['category_id'],
                    $_POST['tax_type_id'],
                    $_POST['units'],
                    get_post('fixed_asset') ? 'F' : get_post('mb_flag'),
                    $_POST['sales_account'],
                    $_POST['inventory_account'],
                    $_POST['cogs_account'],
                    $_POST['adjustment_account'],
                    $_POST['wip_account'],
                    $_POST['dimension_id'],
                    $_POST['dimension2_id'],
                    check_value('no_sale'),
                    check_value('editable'),
                    check_value('no_purchase'),
                    'D',
                    100,
                    1,
                    '1971-01-01',
                    null,
                    get_post('sub_category_id', 0),
                    $govt_fee = 0,
                    $govt_bank_account = '',
                    $bank_service_charge = 0,
                    $bank_service_charge_vat = 0,
                    $commission_loc_user = 0,
                    $commission_non_loc_user = 0,
                    $pf_amount = 0,
                    $returnable_amt = 0,
                    $returnable_to = null,
                    $split_govt_fee_amt = 0.00,
                    $split_govt_fee_acc = null,
                    $_POST['receivable_commission_amount'],
                    $_POST['receivable_commission_account'],
                    $_POST['nationality'],
                    get_post('costing_method')
                );


                /** Add Item Price */

                add_item_price($_POST['NewStockID'], $_POST['sales_type_id'],
                    $_POST['curr_abrev'], input_num('price'));
            } else {

                $update_params = [
                    'description' => db_escape($_POST['description']),
                    'long_description' => db_escape($_POST['long_description']),
                    'category_id' => db_escape($_POST['category_id']),
                    'tax_type_id' => db_escape($_POST['tax_type_id']),
                    'sales_account' => db_escape($_POST['sales_account']),
                    'cogs_account' => db_escape($_POST['cogs_account']),
                    'inventory_account' => db_escape($_POST['inventory_account']),
                    'adjustment_account' => db_escape($_POST['adjustment_account']),
                    'wip_account' => db_escape($_POST['wip_account']),
                    'editable' => db_escape($_POST['editable']),
                    'receivable_commission_amount' => db_escape($_POST['receivable_commission_amount']),
                    'receivable_commission_account' => db_escape($_POST['receivable_commission_account']),
                    'nationality' =>  db_escape($_POST['nationality']),
                    'no_sale' => check_value('no_sale'),
                    'no_purchase' => check_value('no_purchase'),
                ];

                if (item_in_foreign_codes(get_post('NewStockID')) == '') {
                    $update_params['units'] = db_escape($_POST['units']);
                    $update_params['costing_method'] = db_escape($_POST['costing_method']);
                    $update_params['mb_flag'] = db_escape($_POST['mb_flag']);
                }

                db_update('0_stock_master', $update_params, ['stock_id = ' . db_escape($_POST['NewStockID'])]);

                $result = get_prices($_POST['NewStockID'])->fetch_assoc();
                if (!empty($result)) {
                    db_update('0_prices', ['price' => input_num('price')], ['stock_id = ' . db_escape($_POST['NewStockID'])]);
                } else {
                    add_item_price($_POST['NewStockID'], $_POST['sales_type_id'], $_POST['curr_abrev'], input_num('price'));
                }

                update_item_code(-1, $_POST['NewStockID'], $_POST['NewStockID'],$_POST['description'], $_POST['category_id'], 1, 0);

                update_record_status($_POST['NewStockID'], $_POST['inactive'],
                    'stock_master', 'stock_id');
                update_record_status($_POST['NewStockID'], $_POST['inactive'],
                    'item_codes', 'item_code');

            }

            /** Update additional pricing fields */
            update_item_additional_charges_info(
                $_POST['NewStockID'],
                input_num('govt_fee'),
                get_post('govt_bank_account'),
                input_num('bank_service_charge'),
                input_num('bank_service_charge_vat'),
                input_num('commission_loc_user'),
                input_num('commission_non_loc_user'),
                input_num('pf_amount'),
                input_num('returnable_amt'),
                get_post('returnable_to'),
                input_num('split_govt_fee_amt'),
                get_post('split_govt_fee_acc'),
                input_num('extra_srv_chg')
            );


            return AxisPro::SendResponse(['status' => 'OK', 'msg' => 'Item Saved']);
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }

    /**
     * @return mixed
     * Generate item code
     */
    public function generate_item_code()
    {
        try {
            $code = $this->generateBarcode();
            return AxisPro::SendResponse(['status' => 'OK', 'data' => $code]);
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }

    /**
     * @return array|string
     *  Generate auto item code
     */
    public function generateBarcode()
    {
        try {
            $tmpBarcodeID = "";
            $tmpCountTrys = 0;
            while ($tmpBarcodeID == "") {
                srand((double)microtime() * 1000000);
                $random_1 = rand(1, 9);
                $random_2 = rand(0, 9);
                $random_3 = rand(0, 9);
                $random_4 = rand(0, 9);
                $random_5 = rand(0, 9);
                $random_6 = rand(0, 9);
                $random_7 = rand(0, 9);
                //$random_8  = rand(0,9);

                // http://stackoverflow.com/questions/1136642/ean-8-how-to-calculate-checksum-digit
                $sum1 = $random_2 + $random_4 + $random_6;
                $sum2 = 3 * ($random_1 + $random_3 + $random_5 + $random_7);
                $checksum_value = $sum1 + $sum2;

                $checksum_digit = 10 - ($checksum_value % 10);
                if ($checksum_digit == 10)
                    $checksum_digit = 0;

                $random_8 = $checksum_digit;

                $tmpBarcodeID = $random_1 . $random_2 . $random_3 . $random_4 . $random_5 . $random_6 . $random_7 . $random_8;

                // LETS CHECK TO SEE IF THIS NUMBER HAS EVER BEEN USED
                $query = "SELECT stock_id FROM " . TB_PREF . "stock_master WHERE stock_id='" . $tmpBarcodeID . "'";
                $arr_stock = db_fetch(db_query($query));

                if (!$arr_stock['stock_id']) {
                    return $tmpBarcodeID;
                }
                $tmpBarcodeID = "";
            }
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }
    }

    /**
     * @return mixed
     * Get subcategory
     */
    public function get_subcategory($cat_id, $p_id = 0, $format = 'json')
    {

        try {
            $category_id = $_GET['category_id'];
            $parent_id = isset($_GET['parent_id']) ? $_GET['parent_id'] : 0;

            if (!empty($cat_id))
                $category_id = $cat_id;

            if (!empty($p_id))
                $parent_id = $p_id;

            $result = get_subcategory($parent_id, $category_id);
            $return_result = [];
            foreach ($result as $key => $value) {
                array_push($return_result, ['id' => $key, 'value' => $value]);
            }

            return AxisPro::SendResponse($return_result, $format);

        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }


    }

    /**
     * @return mixed
     * Get all Item Tax Types
     */
    public function get_item_tax_types($format = 'json')
    {
        try {
            $sql = "SELECT id, name FROM " . TB_PREF . "item_tax_types WHERE inactive = 0";
            $result = db_query($sql);

            $return_result = [];
            while ($myrow = db_fetch($result)) {

                $return_result[] = $myrow;

            }
            return AxisPro::SendResponse($return_result, $format);
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }

    /**
     * @param bool $stock_id
     * @param string $format
     * @return mixed
     * Get single Item data
     */
    public function get_item_info($stock_id = false, $format = 'json')
    {
        require_once $GLOBALS['path_to_root'] . "/sales/includes/cart_class.inc";

        if (isset($_GET['stock_id']) && !empty($_GET['stock_id']))
            $stock_id = $_GET['stock_id'];

        try {
            $stock_id = $stock_id ? $stock_id : $_REQUEST['stock_id'];
            $sql = (
                "SELECT
                    sm.*,
                    ba.dflt_bank_chrg
                FROM 
                    0_stock_master sm
                LEFT JOIN 0_bank_accounts ba ON 
                    ba.account_code = sm.govt_bank_account
                WHERE sm.stock_id = " . db_escape($stock_id)
            );
            $result = db_query($sql);
            $general_info = db_fetch_assoc($result);

            $sql = "SELECT * FROM 0_subcategories WHERE id=" . db_escape($general_info['sub_category_id']);
            $result = db_query($sql);
            $subcat_row = db_fetch($result);

            $sql = "SELECT price FROM 0_prices WHERE stock_id=" . db_escape($stock_id) . " AND sales_type_id = 1 LIMIT 1";
            $result = db_query($sql);
            $price = db_fetch($result);

            $sql = "SELECT tax_types.* FROM 0_tax_types tax_types 
            LEFT JOIN 0_tax_group_items tax_grp_items ON tax_grp_items.tax_type_id = tax_types.id
            LEFT JOIN 0_tax_groups tax_grp on tax_grp.id=tax_grp_items.tax_group_id
            LEFT JOIN 0_item_tax_types item_tax_type ON item_tax_type.id=tax_grp.id
            WHERE item_tax_type.id=".db_escape($general_info['tax_type_id']);
            $result = db_query($sql);
            $tax_info = db_fetch($result);


            $sql = "SELECT * FROM " . TB_PREF . "stock_category WHERE category_id = " . db_escape($general_info['category_id']);
            $result = db_query($sql);
            $category_info = db_fetch_assoc($result);


            $discount_info = [];
            if(isset($_GET['customer_id'])) {

                $sql = "SELECT * FROM `0_customer_discount_items` WHERE
                item_id= ".db_escape($general_info['category_id'])." AND customer_id = ".db_escape($_GET['customer_id']);

                $result = db_query($sql);
                $discount_info = db_fetch($result);

            }

            if (Cart::isAutomaticBankChargeApplicable($general_info['category_id'], $general_info['stock_id'])) {
                $general_info['bank_service_charge'] = 0;
                $general_info['bank_service_charge_vat'] = 0;
            }

            $return_result = [
                'g' => $general_info,
                'c' => $category_info,
                'sub' => $subcat_row,
                'p' => $price,
                'd' => $discount_info,
                't'=>$tax_info
            ];

            return AxisPro::SendResponse($return_result, $format);
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }


    }

    public function calculate_bank_charge($format = 'json') {
        try {
            $category_id = $_POST['category_id'] ?? -2;
            $govt_fee = $_POST['govt_fee'] ?? 0;
            $bank_charge = $_POST['bank_charge'] ?? 0;
            $stock_id = $_POST['stock_id'] ?? -2;

            if (Cart::isAutomaticBankChargeApplicable($category_id, $stock_id)) {
                $bank_charge = Cart::calculateAutomaticBankCharge($govt_fee);
            }

            return AxisPro::SendResponse([
                "status" => 200,
                "bank_charge" => $bank_charge
            ], $format);
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }
    }


    /**
     * @return mixed
     * Get all items
     */
    public function get_items()
    {

        try {
            $sql = (
                "SELECT
                    `a`.`stock_id` AS `stock_id`,
                    `a`.`description` AS `item_description`,
                    IFNULL(govt_acc.account_name,'') as govt_account_name,
                    cog_acc.account_name as cog_account_name,
                    sales_acc.account_name as sales_account_name,
                    CASE
                        WHEN a.inactive=0 THEN 'ACTIVE'
                        ELSE 'INACTIVE'
                    END as active_status,
                    `a`.`long_description` AS `long_description`,
                    `b`.`description` AS `category_name`,
                    `c`.`price` AS `service_charge`,
                    `a`.`govt_fee`,
                    `a`.`pf_amount` AS `pf_amount`,
                    `a`.`bank_service_charge` AS `bank_service_charge`,
                    `a`.`bank_service_charge_vat` AS `bank_service_charge_vat`,
                    `a`.`commission_loc_user` AS `commission_loc_user`,
                    `a`.`commission_non_loc_user` AS `commission_non_loc_user`,
                    `a`.`receivable_commission_amount`
                FROM `0_stock_master` `a`
                LEFT JOIN `0_stock_category` `b` ON `b`.`category_id` = `a`.`category_id`
                LEFT JOIN `0_prices` `c` ON `c`.`stock_id` = `a`.`stock_id` AND `c`.`sales_type_id` = 1 
                LEFT JOIN 0_chart_master govt_acc on govt_acc.account_code=a.govt_bank_account 
                LEFT JOIN 0_chart_master cog_acc on cog_acc.account_code=a.cogs_account 
                LEFT JOIN 0_chart_master sales_acc on sales_acc.account_code=a.sales_account 
                WHERE
                    1=1
                GROUP BY stock_id"
            );

            $result = db_query($sql);

            $return_result = [];
            while ($myrow = db_fetch_assoc($result)) {

                $return_result[] = $myrow;

            }

            return AxisPro::SendResponse($return_result);

        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }


    /**
     * @param $table
     * @param $key
     * @param $val
     * @return array
     * Get records as key value array
     */
    function get_key_value_records($table, $key, $val)
    {

        try {
            $sql = "SELECT $key,$val FROM $table";
            $result = db_query($sql);

            $return_result = [];
            while ($myrow = db_fetch_assoc($result))
                $return_result[$myrow[$key]] = $myrow[$val];

            return $return_result;
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }

    function get_permitted_items_for_invoicing($format = 'json') {
        try {
            $cost_center = $_POST['cost_center_id'];
            if (empty($cost_center)) {
               return AxisPro::SendResponse(["status" => 'FAIL', "msg" => 'Please provide a department id'], $format);
            }

            return AxisPro::SendResponse([
                "status" => 'success',
                "data" => $this->get_permitted_item_list(false, $cost_center)
            ], $format);
        } catch (Exception $e){
            return AxisPro::catchException($e);
        }
    }

    function get_permitted_item_list($show_inactive = false, $cost_center = null)
    {

        try {

            $user_id = $_SESSION['wa_current_user']->user;
            $user_info = get_user($user_id);

            $user_dimension = db_escape($cost_center ?? $user_info['dflt_dimension_id']);

            $permitted_cats = $user_info['permitted_categories'] ?? -1;

            $sql = (
                "SELECT
                    item.stock_id,
                    item.description,
                    item.category_id,
                    item.long_description,
                    CONCAT(
                        item.description,
                        '(', ROUND(item.govt_fee + item.bank_service_charge + item.bank_service_charge_vat, 2), ')',
                        ' - ', IFNULL(item.long_description, '')
                    ) full_name
                FROM 0_stock_master item
                    LEFT JOIN 0_stock_category cat ON cat.category_id = item.category_id
                WHERE 
                    json_contains(cat.belongs_to_dep, json_quote({$user_dimension}))
                    AND item.category_id IN ({$permitted_cats})"
            );

            if (!$show_inactive) {
                $sql .= " AND item.inactive = 0";
            }

            $result = db_query($sql);

            $return_result = [];
            while ($myrow = db_fetch_assoc($result))
                $return_result[] = $myrow;

            return $return_result;


        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }
    }


    /**
     * @return mixed
     * Generate service report
     */
    public function service_report()
    {

        try {
            $sql = PrepareQuery::ServiceReport($_GET);

            $total_count_sql = "select count(*) as cnt,
            SUM(net_service_charge) as sum_net_service_charge,
            SUM(line_total) sum_line_total,
            SUM(invoice_total) sum_invoice_total,
            SUM(line_discount_amount) sum_discount,
            SUM(quantity) sum_quantity,
            SUM(gross_employee_commission) sum_gross_employee_commission,  
            SUM(cust_comm_emp_share) sum_cust_comm_emp_share,  
            SUM(employee_commission) sum_employee_commission,  
            SUM(total_service_charge) sum_total_service_charge,
            SUM(total_govt_fee) sum_total_govt_fee,
            SUM(govt_fee) sum_govt_fee, 
            SUM(bank_service_charge) sum_bank_service_charge, 
            SUM(bank_service_charge_vat) sum_bank_service_charge_vat,
            SUM(customer_commission) sum_customer_commission,
            SUM(customer_commission2) sum_customer_commission2 
            from ($sql) as tmpTable";
            $total_count_exec = db_fetch_assoc(db_query($total_count_sql));
            $total_count = $total_count_exec['cnt'];

            $page = isset($_GET['page']) ? $_GET['page'] : 1;
            $perPage = 200;
            $offset = ($page * $perPage) - $perPage;


            $sql = $sql . " LIMIT $perPage OFFSET $offset";

            $result = db_query($sql);
            $report = [];
            while ($myrow = db_fetch_assoc($result))
                $report[] = $myrow;


            // $customers = $this->get_key_value_records('0_debtors_master', 'debtor_no', 'name');
            $gl_accounts = $this->get_key_value_records('0_chart_master', 'account_code', 'account_name');
            $categories = $this->get_key_value_records('0_stock_category', 'category_id', 'description');
            $service_category_map = $this->get_key_value_records('0_stock_master', 'stock_id', 'category_id');
            $users = $this->get_key_value_records('0_users', 'id', 'user_id');

            $real_name = $this->get_key_value_records('0_users', 'id', 'real_name');


            $filters = $this->getServiceReportFilters();


            $custom_report = [];
            if (isset($_GET['custom_rep_id']) && !empty(trim($_GET['custom_rep_id']))) {

                $sql = "SELECT * FROM 0_custom_reports WHERE id=" . $_GET['custom_rep_id'];

                $custom_report = db_fetch_assoc(db_query($sql));

                $custom_report['params'] = htmlspecialchars_decode($custom_report['params']);

            }

            $output = json_encode([
                'rep' => $report,
                'total_rows' => $total_count,
                'pagination_link' => AxisPro::paginate($total_count),
                // 'customers' => $customers,
                'gl_accounts' => $gl_accounts,
                'categories' => $categories,
                'service_category_map' => $service_category_map,
                'custom_report' => $custom_report,
                'filters' => $filters,
                'aggregates' => $total_count_exec,
                'users' => $users,
                'user_name' => $real_name
            ]);

            header('Content-Type: application/json; charset=utf-8');

            echo $output;
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }


    }


    /**
     * @return mixed
     * Load service report page
     */
    public function load_service_report_page()
    {

        try {
            $filters = $this->getServiceReportFilters();
            return AxisPro::SendResponse(['filters' => $filters]);
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }


    }


    /**
     * @return array
     * Get enabled filters of report
     */
    public function getServiceReportFilters()
    {

        try {

            $sql = "SELECT * FROM 0_salesman";
            $result = db_query($sql);

            $salesman = [];
            while ($myrow = db_fetch($result)) {

                $salesman[] = $myrow;

            }

            $sql = "SELECT stock_id,`description`, category_id FROM 0_stock_master";
            $result = db_query($sql);

            $services = [];
            while ($myrow = db_fetch($result)) {

                $services[] = $myrow;

            }

            $sql = "SELECT category_id,description FROM 0_stock_category";

            $result = db_query($sql);

            $categories = [];
            while ($myrow = db_fetch($result)) {

                $categories[] = $myrow;

            }

            $sql = "SELECT id, CONCAT(real_name, ' (', user_id, ')') AS real_name FROM 0_users";
            $result = db_query($sql);

            $users = [];
            while ($myrow = db_fetch($result)) {

                $users[] = $myrow;

            }

            return [
            'salesman' => $salesman,
            'services' => $services,
            'categories' => $categories,
            'users' => $users,
            ];

        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }


    /**
     * @return mixed
     * Save custom generated report
     */
    public function save_custom_report()
    {

        $authUser = auth()->user();

        if (isset($_POST['custom_rep_id']) && !empty(trim($_POST['custom_rep_id']))) {
            if($authUser->doesntHavePermission(Permissions::SA_CUSREP_ALL)){
                $sql = "SELECT * FROM 0_custom_reports csr WHERE csr.id=".$_POST['custom_rep_id'];
                $custom_report = db_fetch_assoc(db_query($sql));
                if($custom_report['created_by'] != $authUser->id)
                    return AxisPro::SendResponse(['status' => 'FAIL', 'msg' => 'You are not authorized to perform this action.']);
            }
        }


        try {
            if (!isset($_POST['custom_report_name']) || trim($_POST['custom_report_name']) == '')
                return AxisPro::SendResponse(['status' => 'FAIL', 'msg' => 'Please Provide A Report Name']);

            if (!isset($_POST['access_type']) || trim($_POST['access_type']) == '')
                return AxisPro::SendResponse(['status' => 'FAIL', 'msg' => 'Please Provide Access Type']);

            if($_POST['access_type'] == 'Dep'){
                if (!isset($_POST['department']) || trim($_POST['department']) == '')
                    return AxisPro::SendResponse(['status' => 'FAIL', 'msg' => 'Please Provide Department']);
            }

            $report_name = $_POST['custom_report_name'];
            $department  = $_POST['department'];
            $access_type = $_POST['access_type'];
            $params = json_encode($_POST);

            $array = [
            'name' => db_escape($report_name),
            'department_id' => db_escape($department),
            'access_type' => db_escape($access_type),
            'params' => db_escape($params),
            ];

            if (isset($_POST['custom_rep_id']) && !empty(trim($_POST['custom_rep_id']))) {
                $array['updated_by'] = $authUser->id;
                db_update('0_custom_reports', $array, ["id=" . $_POST['custom_rep_id']]);
            } else {
                $array['created_by'] = $authUser->id;
                db_insert('0_custom_reports', $array);

            }

            return AxisPro::SendResponse(['status' => 'OK', 'msg' => 'Report Saved']);

        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }


    }


    /**
     * @param string $format
     * @return array
     * Get all custom reports
     */
    public function get_custom_reports($format = 'json')
    {

        $authUser = auth()->user();
        $where = "1";
        if($authUser->doesntHavePermission(Permissions::SA_CUSREP_ALL)){
            $conditions = "(access_type = 'Own' && created_by =  {$authUser->id}) || access_type = 'All'";
            if ($authUser->hasPermission(Permissions::SA_CUSREP_DEP)){
                $conditions .= " || (access_type = 'Dep' && department_id = {$authUser->dflt_dimension_id})";
            }
            $where .= " AND ({$conditions})";
        }

        try {
            $sql = "SELECT * FROM 0_custom_reports  WHERE $where"; 

            $result = db_query($sql);
            $return_result = [];
            while ($myrow = db_fetch_assoc($result))
                $return_result[] = $myrow;

            return $return_result;
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }


    }


    /**
     * @return mixed
     * Delete custom report
     */
    public function delete_custom_report()
    {
        $authUser = auth()->user();

        if (isset($_POST['id']) && !empty(trim($_POST['id']))) {
            if($authUser->doesntHavePermission(Permissions::SA_CUSREP_ALL)){
                $sql = "SELECT * FROM 0_custom_reports csr WHERE csr.id=".$_POST['id'];
                $custom_report = db_fetch_assoc(db_query($sql));
                if($custom_report['created_by'] != $authUser->id)
                    return AxisPro::SendResponse(['status' => 'FAIL', 'msg' => 'You are not authorized to perform this action.']);
            }
        }
        try {
            $id = $_POST['id'];

            $sql = "DELETE FROM 0_custom_reports WHERE id=$id";
            db_query($sql);

            return AxisPro::SendResponse(['status' => 'OK', 'msg' => 'Your report has been deleted.']);
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }

    /**
     * @return mixed
     * Change system language
     */
    public function change_language()
    {

        try {
            $language = $_POST["lang"];

            $_SESSION['wa_current_user']->prefs->user_language = $language;

            return AxisPro::SendResponse(['status' => 'OK', 'msg' => 'Language Changed.']);

        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }

    /**
     * @param null $date
     * @return array
     * Get account closing balance report
     */
    function get_acc_bal_report($date = null)
    {

        try {
            $date = date2sql($date);

            if (empty($date))
                $date = date2sql(Today());


            $sql = "select ROUND(ifnull(sum(gl.amount),0),2) total_cash_in_hand from 0_gl_trans gl 
            inner join 0_bank_accounts bank on bank.account_code=gl.account and bank.account_type=3 
            where gl.tran_date<='$date'";
            $result = db_fetch(db_query($sql));
            $total_cash_in_hand = $result['total_cash_in_hand'];

            $sql = "select ROUND(ifnull(sum(gl.amount),0),2) payment_cards_total from 0_gl_trans gl 
            inner join 0_chart_master chart on chart.account_code=gl.account and chart.account_type=15
            where gl.tran_date<='$date'";
            $result = db_fetch(db_query($sql));
            $payment_cards_total = $result['payment_cards_total'];


            $sql = "select chart.account_name,ROUND(ifnull(sum(gl.amount),0),2) amount from 0_gl_trans gl 
            inner join 0_chart_master chart on chart.account_code=gl.account and chart.account_type=15
            where gl.tran_date<='$date' group by chart.account_code";
            $result = db_query($sql);

            $e_dirhams = [];
            while ($row = db_fetch($result)) {
                $e_dirhams[] = $row;
            }


            $sql = "select chart.account_name,ROUND(ifnull(sum(gl.amount),0),2) amount from 0_gl_trans gl 
            inner join 0_chart_master chart on chart.account_code=gl.account and chart.account_type in (19,191)
            where gl.tran_date<='$date' group by chart.account_code";

            $result = db_query($sql);

            $acc_rcv_groups = [];
            while ($row = db_fetch($result)) {
                $acc_rcv_groups[] = $row;
            }

            $sql = "select ROUND(ifnull(sum(gl.amount),0),2) amount from 0_gl_trans gl 
            inner join 0_chart_master chart on chart.account_code=gl.account and chart.account_type in (19,191)
            where gl.tran_date<='$date'";


            $result = db_fetch(db_query($sql));
            $acc_rcvbl_total = $result['amount'];


            $sql = "select ROUND(ifnull(sum(gl.amount),0),2) cbd_total from 0_gl_trans gl 
            where gl.account=1112 and gl.tran_date<='$date'";
            $result = db_fetch(db_query($sql));
            $cbd_total = $result['cbd_total'];

            $sql = "select ROUND(ifnull(sum(gl.amount),0),2) fab_total from 0_gl_trans gl 
            where gl.account=1117 and gl.tran_date<='$date'";
            $result = db_fetch(db_query($sql));
            $fab_total = $result['fab_total'];

            $sql = "select ROUND(ifnull(sum(gl.amount),0),2) acc_rcvbl_total from 0_gl_trans gl 
            where gl.account=1200 and gl.tran_date<='$date'";


            return [
            'cash_in_hand' => $total_cash_in_hand ?: 0,
            'payment_cards' => $payment_cards_total ?: 0,
            'cbd' => $cbd_total ?: 0,
            'fab' => $fab_total ?: 0,
            'acc_rcvbl' => $acc_rcv_groups,
            'e_dirhams' => $e_dirhams,
            'rcvbl_total' => $acc_rcvbl_total
            ];

        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }


    }


    /**
     * @return bool|mysqli_result|resource
     * Import TAWSEEL CSV File | For TANZEEL
     */
    public function import_tawseel_csv()
    {

        try {
            $result = false;

            if (isset($_POST["submit"])) {

                $date_format_excel = $_POST['date_format'];

                begin_transaction();

                $insert = [
                'created_by' => $_SESSION['wa_current_user']->user
                ];

                db_insert('0_tawseel_report', $insert);
                $report_master_id = db_insert_id();

                $cols = [
                'reference',
                ['field' => 'invoice_date', 'type' => 'date', 'format' => $date_format_excel],
                'category',
                'employee',
                'customer',
                'company',
                ['field' => 'center_fee', 'type' => 'amount'],
                ['field' => 'employee_fee', 'type' => 'amount'],
                ['field' => 'typing_fee', 'type' => 'amount'],
                ['field' => 'service_fee', 'type' => 'amount'],
                ['field' => 'discount', 'type' => 'amount'],
                'transaction_id',
                'rcpt_no',
                ['field' => 'tax_amount', 'type' => 'amount'],
                'payment_method',
                ['field' => 'total_fee', 'type' => 'amount'],
                'status',
                ];


                $result = AxisPro::import_csv('0_tawseel_report_detail', $cols, $_FILES["csv_file"],
                    ['report_id' => $report_master_id]);


                commit_transaction();
            }

            return $result;
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }


    }


    function get_tawseel_report()
    {

        try {
            $filter_from_date = db_escape(date2sql($_GET['filter_from_date']));
            $filter_to_date = db_escape(date2sql($_GET['filter_to_date']));

            $where = "";

            if (!empty($filter_from_date))
                $where .= " AND invoice_date>=$filter_from_date";

            if (!empty($filter_to_date))
                $where .= " AND invoice_date<=$filter_to_date";


            $sql = "SELECT * FROM 0_tawseel_report_detail WHERE 1=1 $where ";

            $total_count_sql = "select count(*) as cnt from ($sql) as tmpTable";

            $total_count_exec = db_fetch_assoc(db_query($total_count_sql));
            $total_count = $total_count_exec['cnt'];

            $page = isset($_GET['page']) ? $_GET['page'] : 1;
            $perPage = 200;
            $offset = ($page * $perPage) - $perPage;


            $sql = $sql . " LIMIT $perPage OFFSET $offset";

            $result = db_query($sql);
            $report = [];
            while ($myrow = db_fetch_assoc($result))
                $report[] = $myrow;


            return AxisPro::SendResponse(
                [
                'rep' => $report,
                'total_rows' => $total_count,
                'pagination_link' => AxisPro::paginate($total_count, $perPage),
                ]
                );
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }

    function CreatesalesPerson()
    {
        if ($_POST['edit_id'] != '') {
            $sql = "UPDATE 0_salesman SET salesman_name='" . $_POST['sales_person'] . "',salesman_phone='" . $_POST['telephone'] . "',
            salesman_email='" . $_POST['email'] . "' where salesman_code='" . $_POST['edit_id'] . "'";
        } else {

            $sql = "INSERT INTO 0_salesman (salesman_name,salesman_phone,salesman_email)
            values ('" . $_POST['sales_person'] . "','" . $_POST['telephone'] . "','" . $_POST['email'] . "')";
        }
        // echo $sql;
        if (db_query($sql)) {
            return AxisPro::SendResponse(['status' => 'OK', 'msg' => 'Salesman Creation Done']);
        } else {
            return AxisPro::SendResponse(['status' => 'Fail', 'msg' => 'Error']);
        }


    }

    public function list_salesman()
    {
        $draw = intval($_POST["draw"]);
        $start = intval($_POST["start"]);
        $length = intval($_POST["length"]);

        $sql = "select * from 0_salesman where inactive='0'  ";
        $result = db_query($sql);
        $data = [];
        $payslip_label = '';
        $checkbox = '';
        while ($myrow = db_fetch($result)) {


            $data[] = array(
                $myrow['salesman_name'],
                $myrow['salesman_phone'],
                /* $myrow['salesman_fax'],*/
                $myrow['salesman_email'],
                '<label class="ClsCommison" style="cursor: pointer;color: blue;
                text-decoration: underline;" alt="' . $myrow['salesman_code'] . '" >Add Product Commison</label>',
                '<label class="ClsEdit" style="cursor: pointer;" alt_id="' . $myrow['salesman_code'] . '" alt_salesman="' . $myrow['salesman_name'] . '" alt_phone="' . $myrow['salesman_phone'] . '" alt_fax="' . $myrow['salesman_fax'] . '" alt_email="' . $myrow['salesman_email'] . '"><i class=\'flaticon-edit\'></i></label>',
                '<label class="ClsRemove" style="cursor: pointer;"   alt_id="' . $myrow['salesman_code'] . '" ><i class=\'flaticon-delete\'></i></label>'
                );
        }

        $result_data = array(
            "draw" => $draw,
            "recordsTotal" => db_num_rows($result),
            "recordsFiltered" => db_num_rows($result),
            "data" => $data
            );

        return AxisPro::SendResponse($result_data);

    }

    function get_all_items($format = 'json')
    {
        $sql = (
            "SELECT
                s.stock_id,
                s.govt_bank_account,
                s.returnable_to,
                s.returnable_amt,
                i.item_code,
                i.description,
                s.description stock_name,
                s.long_description stock_name_ar,
                s.bank_service_charge,
                s.bank_service_charge_vat,
                s.category_id,
                s.commission_loc_user,
                s.commission_non_loc_user,
                p.stock_price
            FROM
                0_stock_master s
            JOIN 
                0_item_codes i ON i.stock_id = s.stock_id
            LEFT JOIN 0_stock_category c ON
                i.category_id = c.category_id
            LEFT JOIN ( SELECT stock_id, max(price) as stock_price from 0_prices group by stock_id ) AS p ON 
                s.stock_id = p.stock_id
            WHERE
                i.stock_id = s.stock_id
                AND mb_flag != '".STOCK_TYPE_FIXED_ASSET."'
                AND !i.inactive
                AND !s.inactive
                AND !s.no_sale
            GROUP BY i.item_code"
        );

        $result = db_query($sql);
        $return_result = [];
        while ($myrow = db_fetch($result)) {
            $return_result[] = $myrow;
        }

        return AxisPro::SendResponse($return_result, $format);

    }

    function save_product_commission()
    {
        $sql_check = "select * from 0_salesman_product_percent where product_id='" . $_POST['ap_items'] . "' and salesman_id='" . $_POST['salesman_id'] . "'";
        $re = db_query($sql_check);
        if (db_num_rows($re) > 0) {
            $sql = "Update 0_salesman_product_percent set commission='" . $_POST['sales_commison'] . "' 
            where product_id='" . $_POST['ap_items'] . "' and salesman_id='" . $_POST['salesman_id'] . "'";
        } else {
            $sql = "INSERT into 0_salesman_product_percent (product_id,salesman_id,commission,status)
            VALUES('" . $_POST['ap_items'] . "','" . $_POST['salesman_id'] . "','" . $_POST['sales_commison'] . "','1')";
        }
        db_query($sql);
        return AxisPro::SendResponse(['status' => 'OK', 'Status' => 'Success']);
    }

    /*function list_salesman_commission()
    {
        $draw = intval($_POST["draw"]);
        $start = intval($_POST["start"]);
        $length = intval($_POST["length"]);

        $sql = "select * from 
                0_salesman_product_percent 
                where status='1' and salesman_id='".$_POST['salesman_id']."'
                LIMIT ".$start.",".$length." ";
        $result = db_query($sql);
        $data = [];
        $payslip_label='';
        $checkbox='';
        while ($myrow = db_fetch($result)) {
            $get_name="Select description from 0_item_codes where item_code='".$myrow['product_id']."'";
            $item_name=db_fetch(db_query($get_name));
            $data[] = array(
                $myrow['product_id'],
                $item_name[0],
                $myrow['commission'],
                '<label class="ClsCommisonEdit" style="cursor: pointer;"  alt="'.$myrow['product_id'].'" alt_commison="'.$myrow['commission'].'"><i class=\'flaticon-edit\'></i></label>',
                '<label class="ClsCommisonRemove" style="cursor: pointer;"  alt="'.$myrow['id'].'"><i class=\'flaticon-delete\'></i></label>'
            );
        }

        $result_data = array(
            "draw" => $draw,
            "recordsTotal" => db_num_rows($result),
            "recordsFiltered" => db_num_rows($result),
            "data" => $data
        );

        return AxisPro::SendResponse($result_data);
    }

    function remove_salesman()
    {
        $sql_salesman="update 0_salesman set inactive='1' where salesman_code='".$_POST['salesman_id']."' ";
        db_query($sql_salesman);

        $sql_update_perce="Update 0_salesman_product_percent set status='0' where salesman_id='".$_POST['salesman_id']."'";
        db_query($sql_update_perce);

        return AxisPro::SendResponse(['status'=>'OK','msg'=>'Success']);
    }

    function remove_product_cmison()
    {
        $sql_update_perce="Update 0_salesman_product_percent set status='0' where id='".$_POST['product_commsion_id']."' ";
        if(db_query($sql_update_perce))
        {
            return AxisPro::SendResponse(['status'=>'OK','msg'=>'Product removed from salesman']);
        }

    }*/

    function get_Sales_man_sales_cnt()
    {
        $qry = "SELECT a.salesman_name,
        (SELECT COUNT(*) FROM 
            0_debtor_trans_details AS b 
            WHERE a.salesman_code=b.sales_man_id) AS sales_cnt
FROM 0_salesman AS a
WHERE a.inactive='0' LIMIT 5";

$result = db_query($qry);
$return_result = [];
while ($myrow = db_fetch($result)) {
    $return_result[] = $myrow;
}

return $return_result;

}

public function get_all_accounts()
{
    $sql = "SELECT chart.account_code,CONCAT(chart.account_code,' - ',chart.account_name) AS accname, chart.inactive, type.id
    FROM 0_chart_master chart,0_chart_types type
    WHERE chart.account_type=type.id";
    $result = db_query($sql);

    $return_result = [];
    while ($myrow = db_fetch($result)) {
        $return_result[] = $myrow;
    }

    return AxisPro::SendResponse($return_result);
}

function get_to_subacc()
{
    $ledger_id = $_POST['to_account'];
    $id = $_POST['id'];
    $sql = "SELECT code,name
    FROM 0_sub_ledgers where ledger_id='" . $ledger_id . "' ";
    $result = db_query($sql);
    $return_result = [];
    $select = '<select class="form-control"  id="ddl_to_sub_' . $id . '" style="width: 188px;"><option value="0">---Select Sub Ledger---</option>';
    while ($myrow = db_fetch($result)) {
        $select .= '<option value="' . $myrow['code'] . '">' . $myrow['name'] . '</option>';
    }
    $select .= '</select>';

    return AxisPro::SendResponse($select);
}

public function post_multi_gl()
{

    $gl_data = $_POST['gl_data'];
    $accounts_arra = $_POST['accounts'];
    $Refs = new references();

    $amount_to_gl = '';
    $account = '';
    $trans_id = '';
    for ($i = 0; $i < sizeof($gl_data); $i++) {
        $ref = $Refs->get_next(ST_JOURNAL, null, Today());
        $trans_type = 0;
        $total_gl = 0;
        $trans_id = get_next_trans_no(0);

            //$jv_date=date('d-M-Y',strtotime($gl_data[$i]['jv_date']));
        $jv_date = date('d/m/Y', strtotime($gl_data[$i]['jv_date']));


        $amount_sum = '0';
        foreach ($gl_data[$i]['accounts'] as $value) {
            if (isset($value['jv_from'])) {
                $amount_to_gl_dbt = $value['amount'];
                $account_dbt = $value['jv_from'];

                add_gl_trans($trans_type, $trans_id, $jv_date, $account_dbt, 0, 0,
                    $value['from_memo'], $amount_to_gl_dbt, 'AED', "", 0, "", 0);

                $amount_sum = $amount_sum + $amount_to_gl_dbt;

                $gl_counter = db_insert_id();
                $sql = "UPDATE 0_gl_trans SET axispro_subledger_code ='" . $value['jv_from_sub'] . "' WHERE counter = $gl_counter";
                db_query($sql);

                if ($value['tax_option'] == 1) {
                    $tax_amount = $value['amount'] * 5 / 100;
                    add_gl_trans($trans_type, $trans_id, $jv_date, $gl_data[$i]['tax_account'], 0, 0,
                        '', $tax_amount, 'AED', "", 0, "", 0);
                }
            }
            if (isset($value['jv_to'])) {
                if ($value['tax_option'] == 1) {
                    $tax_amount = $value['amount'] * 5 / 100;
                    $t = $value['amount'] + $tax_amount;
                    $amount_to_gl_crdt = '-' . $t;
                } else {
                    $amount_to_gl_crdt = '-' . $value['amount'];
                }

                $account_crdt = $value['jv_to'];

                add_gl_trans($trans_type, $trans_id, $jv_date, $account_crdt, 0, 0,
                    $value['to_memo'], $amount_to_gl_crdt, 'AED', "", 0, "", 0);
                $gl_counter = db_insert_id();
                $sql = "UPDATE 0_gl_trans SET axispro_subledger_code ='" . $value['jv_from_to'] . "' WHERE counter = $gl_counter";
                db_query($sql);
            }


        }


        $sql = "INSERT INTO " . TB_PREF . "journal(`type`,`trans_no`, `amount`, `currency`, `rate`, `reference`, `source_ref`, `tran_date`,
           `event_date`)
VALUES("
    . db_escape($trans_type) . ","
    . db_escape($trans_id) . ","
    . db_escape(round($amount_sum)) . ",'AED',"
    . db_escape(1) . ","
    . db_escape($ref) . ",'',"
    . "'" . date('Y-m-d') . "',"
    . "'" . date('Y-m-d') . "')";

db_query($sql);

$Refs->save($trans_type, $trans_id, $gl_data[$i]['jv_no']);
add_comments($trans_type, $trans_id, $jv_date, $gl_data[$i]['memo']);
add_audit_trail($trans_type, $trans_id, $jv_date);


}

return AxisPro::SendResponse('Success');
}


public function check_refencenumber()
{
    $ref_number = $_POST['refn_number'];
    $sql = "SELECT reference FROM 0_refs WHERE reference='" . $ref_number . "' and type='0'";
    $data = db_fetch(db_query($sql));

    if ($data['reference'] == '') {
        return AxisPro::SendResponse(0);
    } else {
        return AxisPro::SendResponse(1);
    }
}

public function get_reference_number()
{
    $type = '';
    if ($_GET['type'] == '1') {
        $type = '1';
    }

    if ($_GET['type'] == '2') {
        $type = '2';
    }

    $Refs = new references();
    $ref_name = $Refs->get_next($type, 2, date('d/m/Y'));
    return AxisPro::SendResponse($ref_name);
}

//    public function get_bank_accounts()
//    {
//        $sql = "SELECT  id,bank_account_name from 0_bank_accounts";
//        $result = db_query($sql);
//
//        $return_result = [];
//        while ($myrow = db_fetch($result)) {
//            $return_result[] = $myrow;
//        }
//
//        return AxisPro::SendResponse($return_result);
//    }

public function get_customers($format = 'json')
{
    $filters = [
        'except_customers' => [Customer::WALK_IN_CUSTOMER]
    ];
    $customers = get_customers($filters)->fetch_all(MYSQLI_ASSOC);

    return AxisPro::SendResponse($customers, $format);
}


public function get_customer($format = "json")
{

    try {
        $id = $_GET['id'];
        
        $return_result = get_customers([
            'inactive' => 'both',
            'debtor_no' => $id
        ])->fetch_assoc();

        return AxisPro::SendResponse(["data" => $return_result], $format);
    } catch (Exception $e) {
        return AxisPro::catchException($e);
    }


}

public function get_suppliers()
{
    $sql = "SELECT supplier_id, supp_ref, supp_name, curr_code, inactive,concat(supp_ref,' - ',supp_name) as supplier_name FROM 0_suppliers";
    $result = db_query($sql);

    $return_result = [];
    while ($myrow = db_fetch($result)) {
        $return_result[] = $myrow;
    }

    return AxisPro::SendResponse($return_result);
}


public function process_voucher()
{
    global $systypes_array;
    $type = $_POST['page_type'];
    $error_msg = '';
    if ($type == '1') {
        $limit = get_bank_account_limit($_POST['bnk_account'], $_POST['date_']);

        $amnt_chg = array_sum($_POST['tot_amount']);


        if ($limit !== null && floatcmp($limit, $amnt_chg) < 0) {
            $error_msg = sprintf(_("The total bank amount exceeds allowed limit (%s)."), price_format($limit));

        }
        if ($trans = check_bank_account_history($amnt_chg, $_POST['bank_account'], $_POST['date_'])) {

            if (isset($trans['trans_no'])) {
                $error_msg = sprintf(_("The bank transaction would result in exceed of authorized overdraft limit for transaction: %s #%s on %s."),
                    $systypes_array[$trans['type']], $trans['trans_no'], sql2date($trans['trans_date']));
            }
        }
        if (!is_date($_POST['date_'])) {
            $error_msg = "The entered date for the payment is invalid.";
        } elseif (!is_date_in_fiscalyear($_POST['date_'])) {
            $error_msg = "The entered date is out of fiscal year or is closed for further data entry.";

        }
    } else {
        $error_msg = '';
    }


    if ($error_msg == '') {
        $type = $_POST['page_type'];
        $object = new items_cart($type, $trans_no = 0);

        $_payment_data = $_POST['payment_data'];

        $voucher_type = '';

        if ($type == '1') {
            $voucher_type = '1';
        }

        if ($type == '2') {
            $voucher_type = '2';
        }
        begin_transaction();
        $amount = '';
        foreach ($_payment_data as $d) {
            $_SESSION['journal_items']->axispro_subledger_code = $d['jv_from_sub'];

            if ($type == '1') {
                $amount = $d['amount'];
            } else {
                $amount = '-' . $d['amount'];
            }

            if ($d['dimension'] == '') {
                $d['dimension'] = '0';
            }

            $gl_items[] = new gl_item($d['jv_from'], $d['dimension'], 0, $amount, '', '', $d['person_id'], '');

            $object->trans_type = $voucher_type;
            $object->line_items = '';
            $object->gl_items = $gl_items;
            $object->order_id = '';
            $object->from_loc = '';
            $object->to_loc = '';
            $object->tran_date = $_POST['v_date'];
            $object->doc_date = '';
            $object->event_date = '';
            $object->transfer_type = '';
            $object->increase = '';
            $object->memo_ = '';
            $object->branch_id = '';
            $object->reference = $_POST['v_refer'];
            $object->original_amount = '';
            $object->currency = '';
            $object->rate = '1';
            $object->source_ref = " ";
            $object->vat_category = " ";
            $object->tax_info = " ";
            $object->fixed_asset = " ";

        }

        $post_trans_no = '';
        if ($_POST['modify_voucher'] == '1') {
            $post_trans_no = $_POST['modify_trans_no'];
        } else {
            $post_trans_no = 0;
        }


        $trans = write_bank_transaction(
            $voucher_type,
            $post_trans_no,
            $_POST['v_from_bank_acc'],
            $object,
            Today(),
            $_POST['pay_to'],
            $_POST['head_person_id'],
            '0',
            $_POST['v_refer'],
            $_POST['being'],
            true,
            '',
            $_POST['pay_type'],
            $_POST['chq_no'],
            $_POST['cheq_date'],
            now()->toDateTimeString(),
            $_SESSION['wa_current_user']->user
        );

        $trans_type = $trans[0];
        $trans_no = $trans[1];
        new_doc_date($_POST['v_date']);

        commit_transaction();

        $_SESSION['journal_items']->axispro_subledger_code = [];


        return AxisPro::SendResponse(['trans_no' => $trans_no, 'trans_type' => $trans_type]);
    } else {
        return AxisPro::SendResponse(['trans_no' => $error_msg, 'trans_type' => 'error']);
    }

}

public function get_headings_data($trans_no, $type)
{
    $sql = "SELECT a.ref,a.payment_type,b.account AS payto,a.bank_act,a.person_type_id,a.cheq_no,a.cheq_date,a.person_id
    FROM 0_bank_trans AS a
    INNER JOIN 0_gl_trans AS b ON a.trans_no=b.type_no
    WHERE b.amount < 0 AND b.type_no='$trans_no' and a.type='$type'";
        // echo $sql;
    $result = db_query($sql);
    $return_result = [];
    while ($myrow = db_fetch($result)) {
        $return_result[] = $myrow;
    }

    return $return_result;
}

function get_records_from_table($table, $cols)
{

    try {

        $cols = implode(',', $cols);

        $sql = "SELECT " . $cols . " FROM $table";
        $result = db_query($sql);

        $return_result = [];
        while ($myrow = db_fetch_assoc($result))
            $return_result[] = $myrow;

        return $return_result;
    } catch (Exception $e) {
        return AxisPro::catchException($e);
    }

}

public function get_bnk_balance()
{
    $to = add_days(Today(), 1);
    $bal = get_balance_before_for_bank_account($_POST['bank_id'], $to);

    return AxisPro::SendResponse($bal);
}

public function get_dimensions()
{
    $sql = "SELECT id,name FROM 0_dimensions";
    $result = db_query($sql);

    $return_result = [];
    while ($myrow = db_fetch($result)) {
        $return_result[] = $myrow;
    }

    return AxisPro::SendResponse($return_result);
}


public function get_dimen_id_againstuser()
{
    $dimension_id = '';
    if ($_POST['hdn_modify'] == '') {
        $sql = "SELECT dflt_dimension_id FROM 0_users where id='" . $_SESSION['wa_current_user']->user . "'";
        $result = db_query($sql);
        $dflt_dimension_id = db_fetch_row($result);
        $dimension_id = $dflt_dimension_id[0];

    } else {
        $dimension_id = '0';
    }

    return AxisPro::SendResponse(['dim_id' => $dimension_id]);

}


public function get_purchase_items($format = 'json')
{
//        $sql = "select stock_id,description from 0_stock_master where NOT no_purchase";
    $sql = "SELECT s.stock_id,s.description,c.description AS category from 0_stock_master s 
    LEFT JOIN 0_stock_category c ON c.category_id=s.category_id 
    where NOT s.no_purchase";
    $result = db_query($sql);

    $return_result = [];
    while ($myrow = db_fetch($result)) {

        $myrow['description'] = $myrow['description'] . " [" . $myrow['category'] . "]";

        $return_result[] = $myrow;
    }

    return AxisPro::SendResponse($return_result, $format);
}


    public function getPurchaseRequests()
    {

        $sql = "select * from 0_purchase_requests WHERE 1=1 ";


        $user_id = $_SESSION['wa_current_user']->user;
        $user_info = get_user($user_id);

        $sql .= " AND (created_by = $user_id OR 
            staff_mgr_id = " . $user_id . " OR 
            purch_mgr_id=" . $user_id . ")";


        if (!empty($_POST['fl_ref']))
            $sql .= " AND reference = " . db_escape($_POST['fl_ref']);

        if (!empty($_POST['fl_start_date']))
            $sql .= " AND DATE(created_at) >= " . db_escape(date2sql($_POST['fl_start_date']));

        if (!empty($_POST['fl_end_date']))
            $sql .= " AND DATE(created_at) <= " . db_escape(date2sql($_POST['fl_end_date']));

        if (!empty($_POST['fl_requested_by']))
            $sql .= " AND created_by = " . $_POST['fl_requested_by'];

        if (!empty($_POST['fl_status'])) {

            $fl_status = $_POST['fl_status'];

            if ($fl_status == 'WFSMA')//Waiting for staff manager approval
            $sql .= " AND staff_mgr_action=0";

            if ($fl_status == 'WFPMA')//Waiting for Purchase manager approval
            $sql .= " AND staff_mgr_action=1 and purch_manager_action=0";

            if ($fl_status == 'RBSM')//Rejected by staff manager
            $sql .= " AND staff_mgr_action=2";

            if ($fl_status == 'ABPM')//Approved by Purchase manager
            $sql .= " AND purch_mgr_action=1";

            if ($fl_status == 'RBPM')//Rejected by Purchase manager
            $sql .= " AND purch_mgr_action=2";

            if ($fl_status == 'POC')//Purchase Order Created
            $sql .= " AND po_id<>0";
        }

        $sql .= "ORDER BY staff_mgr_action ASC";


        $total_count_sql = "select count(*) as cnt from ($sql) as tmpTable";
        $total_count_exec = db_fetch_assoc(db_query($total_count_sql));
        $total_count = $total_count_exec['cnt'];
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $perPage = 200;
        $offset = ($page * $perPage) - $perPage;
        $sql = $sql . " LIMIT $perPage OFFSET $offset";

        $result = db_query($sql);
        $report = [];
        while ($myrow = db_fetch_assoc($result))
            $report[] = $myrow;

        return AxisPro::SendResponse([
            'rep' => $report,
            'total_rows' => $total_count,
            'pagination_link' => AxisPro::paginate($total_count),
            'users' => $this->get_key_value_records('0_users', 'id', 'user_id'),
            'aggregates' => $total_count_exec,
        ]);
    }


    public function getPurchaseRequestStatus()
    {

    }

    public function storePurchReqLog($req_id, $desc)
    {
        try {
            $user_id = $_SESSION['wa_current_user']->user;

            db_insert('0_purch_request_log', [
                'user_id' => $user_id,
                'req_id' => $req_id,
                'description' => db_escape($desc)
            ]);
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }
    }

    public function handleNewPurchaseRequest()
    {
        try {
            begin_transaction();

            $edit_id = isset($_POST['edit_id']) ? $_POST['edit_id'] : null;
            $memo = $_POST['memo'];
            $user_id = $_SESSION['wa_current_user']->user;
            $user_info = get_user($user_id);

            if (empty($user_info['purch_req_send_to_level_one']))
                return AxisPro::SendResponse(['status' => 'FAIL', 'msg' => 'Purchase Request Send to Level One Not Set.']);

            $return_msg = "";

            if (!empty($edit_id)) {
                $editing_request = $this->getPurchaseRequest($edit_id, 'array');
                $revision_count = intval($editing_request['req']['revision_count']) + 1;
                $old_ref = $editing_request['req']['reference'];
                $explode_ref = explode('-', $old_ref);
                $rev_part = 0;
                if (isset($explode_ref[1]))
                    $rev_part = intval($explode_ref[1]) + 1;

                $ref = $explode_ref[0] . "-" . $rev_part;
                $array = [
                    'memo' => db_escape($memo),
                    'last_revision_by' => db_escape($user_id),
                    'revision_count' => $revision_count,
                    'reference' => db_escape($ref),
                ];


                if ($editing_request['req']['staff_mgr_id'] == $user_id) {
                    //Staff manager is editing this request
                } else if ($editing_request['req']['purch_mgr_id'] == $user_id) {
                    //purchase manager is editing this request
                } else if ($editing_request['req']['created_by'] == $user_id) {
                    //Created user is editing this request
                }

                $purchase_req_id = $edit_id;

                db_update('0_purchase_requests', $array, ["id=$purchase_req_id"]);
                $this->storePurchReqLog($purchase_req_id, "Edited / Revised");

                $sql = "DELETE FROM 0_purchase_request_items WHERE req_id=$purchase_req_id";
                db_query($sql);
                $return_msg = "Purchase Request Revised";
            } else {
                $array = [
                    'memo' => db_escape($memo),
                    'created_by' => db_escape($user_id),
                    'staff_mgr_id' => $user_info['purch_req_send_to_level_one']
                ];

                db_insert('0_purchase_requests', $array);

                $purchase_req_id = db_insert_id();
                $this->storePurchReqLog($purchase_req_id, "Purchase Request Created");

                $new_ref_int = intval($purchase_req_id);
                $new_ref_numeric_part = str_pad($new_ref_int, 3, '0', STR_PAD_LEFT);
                $ref = "PR/" . $new_ref_numeric_part;

                $sql = "update 0_purchase_requests set reference = " . db_escape($ref) . " WHERE id=$purchase_req_id";
                db_query($sql);

                $return_msg = "New Purchase Request Placed";
            }


            //insert items

            $items = $_POST['items'];
            $insert_items_array = [];

            foreach ($items as $row) {
                $temp_array = [
                'req_id' => $purchase_req_id,
                'stock_id' => db_escape($row['stock_id']),
                'description' => db_escape($row['description']),
                'qty' => $row['qty']

                ];
                array_push($insert_items_array, $temp_array);
            }

            if (!empty($insert_items_array))
                db_insert_batch('0_purchase_request_items', $insert_items_array);


            commit_transaction();

            return AxisPro::SendResponse(['status' => 'SUCCESS', 'msg' => $return_msg]);

        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }
    }

    public function getPurchaseRequest($id = null, $format = 'json')
    {
        try {
            if (empty($id))
                $id = $_GET['id'];

            $sql = "SELECT * FROM 0_purchase_requests where id = $id";
            $result = db_fetch_assoc(db_query($sql));
            $mr = $result;

            $sql = "SELECT items.*,stk.description item_name,stk.purchase_cost 
            FROM 0_purchase_request_items items 
            LEFT JOIN 0_stock_master stk ON stk.stock_id = items.stock_id
            where items.req_id = $id ";

            $result = db_query($sql);
            $items = [];
            while ($myrow = db_fetch_assoc($result)) {
                $myrow['qty_in_stock'] = get_qoh_on_date($myrow['stock_id']);

                $qty_to_be_ordered = 0;

                if ($myrow['qty'] > $myrow['qty_in_stock'])
                    $qty_to_be_ordered = $myrow['qty'] - $myrow['qty_in_stock'];

                if ($qty_to_be_ordered < 0)
                    $qty_to_be_ordered = 0;

                $myrow['qty_to_be_ordered'] = $qty_to_be_ordered;

                $items[] = $myrow;
            }

            $sql = "SELECT log.req_id,log.description,log.created_at,usr.user_id FROM 0_purch_request_log log 
            LEFT JOIN 0_users usr ON usr.id = log.user_id
            WHERE log.req_id = $id ORDER BY log.created_at DESC";

            $result = db_query($sql);
            $log = [];
            while ($myrow = db_fetch_assoc($result)) {
                $log[] = $myrow;
            }

            return AxisPro::SendResponse(['req' => $mr, 'items' => $items, 'log' => $log], $format);
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }


    }

    public function purchaseRequestActionHandler()
    {

        try {

            $req_id = $_POST['req_id'];
            $actionToUpdate = $_POST['action'];

            $req_info = $this->getPurchaseRequest($req_id, 'array');
            $req_row = $req_info['req'];

            $msg = "Request Approved";

            $user_id = $_SESSION['wa_current_user']->user;
            $user_info = get_user($user_id);

            if (empty($user_info['purch_req_send_to_level_two']))
                return AxisPro::SendResponse(['status' => 'FAIL', 'msg' => 'Level 2 Send To Not Set']);

            if ($actionToUpdate == 2)
                $msg = "Request Rejected";

            if (!empty($req_row['staff_mgr_action'])) {
                //Staff Manager is already approved, so the action is now taken by purchase manager
                $update_set = [
                'purch_mgr_action' => $actionToUpdate,
                'purch_mgr_actioned_at' => db_escape(date("Y-m-d H:i:s"))
                ];
            } else {
                //Staff Manager not approved, so the action is now taken by staff manager
                $update_set = [
                'staff_mgr_action' => $actionToUpdate,
                'staff_mgr_actioned_at' => db_escape(date("Y-m-d H:i:s")),
                'purch_mgr_id' => $user_info['purch_req_send_to_level_two']
                ];
            }

            $notification_desc = 'A new Purchase Request needs your attention';
            $notify_user = $user_info['purch_req_send_to_level_two'];

            if ($actionToUpdate == 2) {
                $notification_desc = "Your purchase request (" . $req_info['req']['reference'] . ") is rejected";
                $notify_user = $req_info['req']['created_by'];
            }

            db_update('0_purchase_requests', $update_set, ["id=$req_id"]);
            $this->storePurchReqLog($req_id, $msg);
            return AxisPro::SendResponse(['status' => 'SUCCESS', 'msg' => $msg]);
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }
    }


    public function handleNewMaterialIssue()
    {

        try {

            $user_id = $_SESSION['wa_current_user']->user;

        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }

public function handleNewPOTermsAndCondition()
{

    try {


        $title = $_POST['title'];
        $desc = $_POST['desc'];

        $array = [
        "title" => db_escape($title),
        "description" => db_escape($desc)
        ];

        if (empty(trim($title)))
            return AxisPro::SendResponse(['status' => 'FAIL', 'msg' => 'Please enter title']);

        if (empty(trim($desc)))
            return AxisPro::SendResponse(['status' => 'FAIL', 'msg' => 'Please enter description']);


        db_insert('0_po_terms_and_conditions', $array);

        return AxisPro::SendResponse(['status' => 'SUCCESS', 'msg' => 'PO - Terms and Conditions Added']);


    } catch (Exception $e) {
        return AxisPro::catchException($e);
    }

}

public function handleDeletePOTermsAndCondition()
{

    try {

        $id = $_POST['id'];
        $sql = "delete from 0_po_terms_and_conditions where id=$id";
        db_query($sql);
        return AxisPro::SendResponse(['status' => 'SUCCESS', 'msg' => 'PO - Terms and Conditions Deleted']);

    } catch (Exception $e) {
        return AxisPro::catchException($e);
    }

}
public function check_customer($customer_id,$trans_no)
{
    $sql    = "SELECT debtor_no FROM 0_debtor_trans WHERE trans_no={$trans_no} AND debtor_no={$customer_id} AND `type`=10";
    $result = db_fetch_assoc(db_query($sql));
    return $result['debtor_no'] == $customer_id ? true : false ;
}


    public function pay_invoice($format = 'json')
    {
        try {

            global $Refs;
            $invoices_total = 0;
            $dec = user_price_dec();
            $alloc_invoices = array_filter($_POST['alloc_invoices'], function($alloc) {
                return $alloc['trans_no'] != '' && $alloc['amount'] > 0;
            });
            foreach($alloc_invoices as $alloc)
            {
                if(!empty($_POST['customer_id'])){
                    if($this->check_customer($_POST['customer_id'],$alloc['trans_no']) < 1 ){
                        return AxisPro::SendResponse([
                            'status'=>400,
                            'message'=> 'The customer and invoice are not matching (Invoice No. = '.$alloc['inv_no'].' please check and retry!)'
                        ]);
                    }
                }
                $invoices_total += $alloc['amount'];

            }
            
            if (!($customer = Customer::find($_POST['customer_id']))) {
                return AxisPro::SendResponse([
                    'status' => 400,
                    'message' => 'Cannot find the specified customer'
                ]);
            }

            $commission = input_num('commission');
            $discount = input_num('discount');
            $totalCommissionPayable = app(CustomersController::class)->commissionPayable($customer)['balance'];
            if ($commission > (-$totalCommissionPayable < 0 ? 0 : -$totalCommissionPayable)) {
                return AxisPro::SendResponse([
                    'status' => 400,
                    'message' => 'The given commission amount exceeds the total commission payable'
                ]);
            }

            if (round2($invoices_total, $dec) > round2($_POST['amount'] + $commission + $discount, $dec)) {
                return AxisPro::SendResponse([
                    'status' => 400,
                    'message'=> 'The paying amount must be greater, or equal to the sum of all allocations'
                ]);
            }

            begin_transaction();

            if ($trans_nos = array_filter(array_column($alloc_invoices, 'trans_no'))) {
                $invoices = db_query(
                    "SELECT
                        dt.`type`,
                        dt.trans_no,
                        dt.reference,
                        dt.tran_date,
                        dt.dimension_id,
                        round(abs(dt.alloc), $dec) as alloc,
                        round(abs(dt.ov_amount + dt.ov_gst + dt.ov_freight + dt.ov_freight_tax + dt.ov_discount), $dec) as total
                    FROM 0_debtor_trans as dt
                    WHERE
                        (dt.ov_amount + dt.ov_gst + dt.ov_freight + dt.ov_freight_tax + dt.ov_discount) <> 0
                        AND dt.type = ".db_escape(ST_SALESINVOICE)."
                        AND dt.trans_no in (". implode(',', array_map('db_escape', $trans_nos)) .")
                    FOR UPDATE",
                    "Could not query the invoice detail for update"
                )->fetch_all(MYSQLI_ASSOC);

                $invoices = collect($invoices)->keyBy('trans_no');
                foreach ($alloc_invoices as $alloc) {
                    if (empty($inv = $invoices->get($alloc['trans_no']))) {
                        return AxisPro::ValidationError(
                            sprintf("Allocation Error: Invoice %s could not be found. It has either been edited or voided. Please refresh the page and process again", $alloc['inv_no'])
                        );
                    }

                    if (floatcmp($alloc['amount'] + $inv['alloc'], $inv['total']) > 0) {
                        return AxisPro::ValidationError(sprintf("Allocation Error: This request will result in an over allocation against invoice %s", $alloc['inv_no']));
                    }
                }
            }

            $curr_user = get_user($_SESSION["wa_current_user"]->user);

            $dim_id = get_post('dim_id') ?: 0;

            // If dimension id is not set, check whether user is authorized to collect without dimension id
            if (empty($dim_id) && !user_check_access('SA_RCVPMTWITHOUTDIM')) {
                return AxisPro::SendResponse(["status" => "FAIL",
                    "msg" => "No Cost-Center set for This User"], $format);
            }

            // If the dimension id is not selected and the payment is being collected against
            // one and only one invoice, assign the dimension id of invoice to the payment.
            if (empty($dim_id) && count($alloc_invoices) == 1 && $invoices->first()) {
                $dim_id = $invoices->first()['dimension_id'];
            }

            $dimension = Dimension::find($dim_id) ?: Dimension::make();

            // $invoice_no = $_POST['trans_no'];
            $customer_id = $_POST['customer_id'];
            $date_ = $_POST['tran_date'];
            // $date_ = Today();
            $amount = input_num('amount');
            $bank_charge = input_num('bank_charge');
            $payment_method = $_POST['payment_method'];
            $bank_account = $_POST['bank_acc'];

            $cart = new Cart(ST_CUSTPAYMENT);

            $round_off = input_num('rounded_difference');
            $cash_acc = $_POST["cash_acc"];
            $cash_amt = $_POST["cash_amt"];
            $card_acc = $_POST["card_acc"];
            $card_amt = $_POST["card_amt"];
            $auth_code = $_POST["auth_code"];

            if ($payment_method == "Split") {
                $paying_amount = $amount;
                $split_amount = $cash_amt+$card_amt;

                if (empty($cash_acc)) {
                    return AxisPro::SendResponse([
                        "status" => "FAIL",
                        "msg" => "Please select the cash account"
                    ], $format);
                }

                if (empty($card_acc)) {
                    return AxisPro::SendResponse([
                        "status" => "FAIL",
                        "msg" => "Please select the card account"
                    ], $format);
                }

                if (count($alloc_invoices) > 1) {
                    return AxisPro::SendResponse([
                        "status" => "FAIL",
                        "msg" => "Split can be done for only one invoice"
                    ], $format);
                }

                else if (round2($split_amount, $dec) !== round2($paying_amount, $dec)) {
                    return AxisPro::SendResponse([
                        "status" => "FAIL",
                        "msg" => "Split amount should be equal to total amount"
                    ], $format);
                }

                $split_paying_invoice = reset($alloc_invoices);
                $round_off = 0;
                $commission = 0;
            }

            else {
                if (empty($bank_account)) {
                    return AxisPro::SendResponse([
                        "status" => "FAIL",
                        "msg" => "Please select the account to which payment should be made"
                    ], $format);
                }
            }

            if (empty($customer_id))
                return AxisPro::SendResponse(["status" => "FAIL",
                    "msg" => "No Customer Selected"], $format);

//            $invoice_info = get_customer_trans($invoice_no, ST_SALESINVOICE);
//            $max_allocatable_amount = round($invoice_info['ov_amount'] + $invoice_info['ov_gst'] - $invoice_info['alloc']);
//
//
//            if (round($amount) > $max_allocatable_amount) {
//                return AxisPro::SendResponse(["status" => "FAIL",
//                    "msg" => "Maximum allocatable amount Exceeded. Please check the amount"], $format);
//            }

            $branch = get_default_branch($customer_id);
            $customer_branch = empty($branch['branch_code']) ? -1 : $branch['branch_code'];
            $is_advance_rcpt = true;
            foreach ($alloc_invoices as $alloc) {
                if ($alloc['amount'] > 0) {
                    $is_advance_rcpt = false;
                }
            }

            if ($payment_method == "Split") {
                $memo = "Payment for Invoice No. #{$split_paying_invoice['inv_no']}";
                $split_trans_no = $split_paying_invoice['trans_no'];
                
                // cash payment
                $ref = $Refs->get_next(
                    ST_CUSTPAYMENT,
                    null,
                    [
                        'date' => $date_,
                        'dimension' => $dimension
                    ],
                    true
                );
                $bank_charge = 0;
                $pmtno = write_customer_payment(
                    0,
                    $customer_id,
                    $customer_branch,
                    $cash_acc,
                    $date_,
                    $ref,
                    $cash_amt,
                    $discount,
                    $memo,
                    0,
                    $bank_charge,
                    $cash_amt,
                    '',
                    $dim_id,
                    0,
                    $round_off,
                    null,
                    null,
                    $_SESSION['wa_current_user']->user,
                    0,
                    now()->toDateTimeString(),
                    $customer->name,
                    $customer->tax_id,
                    $customer->mobile,
                    $customer->debtor_email,
                    null,
                    $cart->randomNumber()
                );

                add_cust_allocation($cash_amt, ST_CUSTPAYMENT, $pmtno, ST_SALESINVOICE, $split_trans_no, $customer_id, $date_, $split_paying_invoice['tran_date']);
                update_debtor_trans_allocation(ST_SALESINVOICE, $split_trans_no, $customer_id, $split_paying_invoice['tran_date']);
                update_debtor_trans_allocation(ST_CUSTPAYMENT, $pmtno, $customer_id, $date_);
                $sql = "update 0_debtor_trans  set round_of_amount=$round_off, payment_method = 'Cash' where type = 12 and trans_no=$pmtno";
                db_query($sql);


                // card payment
                $ref = $Refs->get_next(
                    ST_CUSTPAYMENT,
                    null,
                    [
                        'date' => $date_,
                        'dimension' => $dimension
                    ],
                    true
                );
                $bank_charge = $_POST['bank_charge'];
                $pmtno2 = write_customer_payment(
                    0,
                    $customer_id,
                    $customer_branch,
                    $card_acc,
                    $date_,
                    $ref,
                    $card_amt,
                    0,
                    $memo,
                    0,
                    $bank_charge,
                    $card_amt,
                    '',
                    $dim_id,
                    0,
                    $round_off,
                    null,
                    null,
                    $_SESSION['wa_current_user']->user,
                    0,
                    now()->toDateTimeString(),
                    $customer->name,
                    $customer->tax_id,
                    $customer->mobile,
                    $customer->debtor_email,
                    null,
                    $cart->randomNumber(),
                    $auth_code
                );
                add_cust_allocation($card_amt, ST_CUSTPAYMENT, $pmtno2, ST_SALESINVOICE, $split_trans_no, $customer_id, $date_, $split_paying_invoice['tran_date']);
                update_debtor_trans_allocation(ST_SALESINVOICE, $split_trans_no, $customer_id, $split_paying_invoice['tran_date']);
                update_debtor_trans_allocation(ST_CUSTPAYMENT, $pmtno2, $customer_id, $date_);
                $sql = "update 0_debtor_trans  set round_of_amount=$round_off, payment_method ='CreditCard'  where type = 12 and trans_no=$pmtno2";
                db_query($sql);
            }
            
            else {
                $memo = get_post('comment');
                if (empty($memo) && $is_advance_rcpt) {
                    $memo = "ADVANCE RECEIPT";
                }
    
                $ref = $Refs->get_next(
                    ST_CUSTPAYMENT,
                    null,
                    [
                        'date' => $date_,
                        'dimension' => $dimension
                    ],
                    true
                );
    
                $amount += $commission;
                $pmtno = write_customer_payment(
                    0,
                    $customer_id,
                    $customer_branch,
                    $bank_account,
                    $date_,
                    $ref,
                    $amount,
                    $discount,
                    $memo,
                    0,
                    $bank_charge,
                    $amount,
                    '',
                    $dim_id,
                    0,
                    $round_off,
                    null,
                    null,
                    $_SESSION['wa_current_user']->user,
                    0,
                    now()->toDateTimeString(),
                    $customer->name,
                    $customer->tax_id,
                    $customer->mobile,
                    $customer->debtor_email,
                    null,
                    $cart->randomNumber(),
                    $auth_code,
                    $commission
                );

                foreach ($alloc_invoices as $alloc) {
                    if ($alloc['amount'] > 0) {
                        add_cust_allocation($alloc['amount'], ST_CUSTPAYMENT, $pmtno, ST_SALESINVOICE, $alloc['trans_no'], $customer_id, $date_, $alloc['tran_date']);
                        update_debtor_trans_allocation(ST_SALESINVOICE, $alloc['trans_no'], $customer_id, $alloc['tran_date']);
                    }
                }

                update_debtor_trans_allocation(ST_CUSTPAYMENT, $pmtno, $customer_id, $date_);

                $sql = "update 0_debtor_trans  set round_of_amount=$round_off, payment_method = " . db_escape($payment_method) . " where type = 12 and trans_no=$pmtno";
                db_query($sql);
            }

            runAutomaticAllocation($customer_id);

            commit_transaction();

            $customerPayment = CustomerTransaction::where('type', CustomerTransaction::PAYMENT)
                ->where('trans_no', $pmtno)
                ->where('debtor_no', $customer_id)
                ->first();

            event(new CustomerPaid($customerPayment));

            return AxisPro::SendResponse(["status" => "OK", "payment_no" => $pmtno, "msg" => "Payment Done"], $format);

        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }


    }

    public function find_invoice($format = 'json')
    {
        try {
            $ref = $_GET['ref'];
            $dim_id = $_GET['dim_id'];
            $decimal = user_price_dec();
            $total = "`trans`.`ov_amount` + `trans`.`ov_gst` + `trans`.`ov_freight` + `trans`.`ov_freight_tax` + `trans`.`ov_discount`";

            $sql = (
                "SELECT
                    trans.*,
                    round($total, $decimal) as total_amount,
                    round($total - trans.alloc, $decimal) as remaining_amount,
                    cust.name
                from 0_debtor_trans trans 
                left join 0_debtors_master cust
                    on cust.debtor_no  = trans.debtor_no 
                WHERE
                    (trans.barcode = ".db_escape(trim($ref))." OR trans.reference = ".db_escape($ref).")
                    AND trans.type = 10
                    AND {$total} != 0"
            );

            if (!empty($dim_id))
                $sql .= " AND trans.dimension_id = $dim_id ";


            $result = db_fetch_assoc(db_query($sql));
            $result['tran_date'] = sql2date($result['tran_date']);
            return AxisPro::SendResponse($result, $format);

            return AxisPro::SendResponse($result, $format);
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }


    }

    public function get_bank_accounts($format = "json")
    {
        try {
            $account_type = $_GET['acc_type'];
            $dimension = $_GET['dimension'];
            $return_result = [];
            $paymentMethods = [
                'OnlinePayment',
                'CustomerCard',
                'CenterCard',
                'Cash',
                'BankTransfer',
                'CreditCard'
            ];

            if (in_array($account_type, $paymentMethods) && !empty($paymentAccounts = get_payment_accounts($account_type, null, $dimension))) {
                $paymentAccounts = array_map('get_bank_account', $paymentAccounts);
                $return_result = array_filter(array_map(function ($paymentAccount) {
                    return empty($paymentAccount)
                        ? []
                        : array_intersect_key($paymentAccount, array_flip(['id', 'bank_account_name']));
                }, $paymentAccounts));
            }

            return AxisPro::SendResponse($return_result, $format);
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }


//    public function pay_invoice($format = 'json')
//    {
//
//        try {
//
//            global $Refs;
//
//            begin_transaction();
//
////        $invoice_no = $_POST['trans_no'];
//            $customer_id = $_POST['customer_id'];
//            $date_ = $_POST['tran_date'];
////            $date_ = Today();
//            $amount = input_num('amount');
//            $discount = input_num('discount');
//            $bank_charge = input_num('bank_charge');
//            $payment_method = $_POST['payment_method'];
//            $bank_account = $_POST['bank_acc'];//IF CREDIT CARD
//
//            if ($payment_method == "Cash") {
//
//                $curr_user = get_user($_SESSION["wa_current_user"]->user);
//                $bank_account = $curr_user['cashier_account'];
//
//                if (empty($bank_account))
//                    return AxisPro::SendResponse(["status" => "FAIL",
//                        "msg" => "No Cashier-Account set for This User"], $format);
//
//            }
//
//
////            $invoice_info = get_customer_trans($invoice_no, ST_SALESINVOICE);
////            $max_allocatable_amount = round($invoice_info['ov_amount'] + $invoice_info['ov_gst'] - $invoice_info['alloc']);
////
////
////            if (round($amount) > $max_allocatable_amount) {
////                return AxisPro::SendResponse(["status" => "FAIL",
////                    "msg" => "Maximum allocatable amount Exceeded. Please check the amount"], $format);
////            }
//
//            $branch = db_fetch(db_query(get_sql_for_customer_branches($customer_id)));
//            $customer_branch = $branch['branch_code'];
//
//            $pmtno = write_customer_payment(0, $customer_id,
//                $customer_branch, $bank_account, $date_,
//                $Refs->get_next(ST_CUSTPAYMENT, null, array('customer' => $customer_id,
//                    'branch' => $customer_branch, 'date' => $date_)),
//                $amount, $discount, "", 0, $bank_charge);
//
//
//            $alloc_invoices = $_POST['alloc_invoices'];
//
//            foreach ($alloc_invoices as $alloc) {
//
//                if ($alloc['amount'] > 0) {
//
//                    add_cust_allocation($alloc['amount'], ST_CUSTPAYMENT, $pmtno, ST_SALESINVOICE, $alloc['trans_no'], $customer_id, $date_);
//                    update_debtor_trans_allocation(ST_SALESINVOICE, $alloc['trans_no'], $customer_id);
//
//                }
//
//            }
//
//
//            //TODO: Loop
////            add_cust_allocation($amount, ST_CUSTPAYMENT, $pmtno, ST_SALESINVOICE, $invoice_no, $customer_id, $date_);
////            update_debtor_trans_allocation(ST_SALESINVOICE, $invoice_no, $customer_id);
//            //TODO: loop
//
//
//            update_debtor_trans_allocation(ST_CUSTPAYMENT, $pmtno, $customer_id);
//
//
//            $sql = "update 0_debtor_trans set payment_method = " . db_escape($payment_method) . " where type = 12 and trans_no=$pmtno";
//            db_query($sql);
//
//            commit_transaction();
//
//            return AxisPro::SendResponse(["status" => "OK", "payment_no" => $pmtno, "msg" => "Payment Done"], $format);
//
//        } catch (Exception $e) {
//            return AxisPro::catchException($e);
//        }
//
//
//    }
//
//
//    public function get_bank_accounts($format = "json")
//    {
//
//        try {
//
//            $account_type = $_GET['acc_type'];
//            $sql = "SELECT * FROM 0_bank_accounts WHERE 1=1";
//
//            if (!empty($account_type))
//                $sql .= " AND account_type in ($account_type)";
//
//            $result = db_query($sql);
//            $return_result = [];
//            while ($myrow = db_fetch_assoc($result)) {
//
//                $return_result[] = $myrow;
//
//            }
//
//
//            if ($account_type == "0,3") {
//
//                $return_result = [];
//                $curr_user = get_user($_SESSION["wa_current_user"]->user);
//                $bank_account_id = $curr_user['cashier_account'];
//                $array = [];
//                if (!empty($bank_account_id)) {
//                    $bank_info = get_bank_account($bank_account_id);
//                    $array = [
//                        'id' => $bank_account_id,
//                        'bank_account_name' => $bank_info['bank_account_name']
//                    ];
//
//                    $return_result[] = $array;
//
//                }
//            }
//
//
//            return AxisPro::SendResponse($return_result, $format);
//
//
//        } catch (Exception $e) {
//            return AxisPro::catchException($e);
//        }
//
//    }

    public function get_unpaid_invoices($format = "json")
    {

        try {
            $debtor_no = $_GET['debtor_no'];
            $except_trans_no = $_GET['except_trans_no'] ?? -1;
            $decimal = user_price_dec();
            $total = "`trans`.`ov_amount` + `trans`.`ov_gst` + `trans`.`ov_freight` + `trans`.`ov_freight_tax` + `trans`.`ov_discount`";

            if (empty($debtor_no)) {
                return AxisPro::SendResponse([], $format);
            }

            $dim_id = $_REQUEST['dim_id'];
            $date_format_mysql = dateformat("mySQL");

            $sql = (
                "SELECT
                    trans.id,
                    trans.`type`,
                    trans.trans_no,
                    date_format(trans.tran_date, '{$date_format_mysql}') as tran_date,
                    trans.reference,
                    round($total, $decimal) as total_amount,
                    round(trans.alloc, $decimal) as alloc,
                    round($total - trans.alloc, $decimal) as remaining_amount
                FROM 0_debtor_trans trans
                WHERE
                    trans.debtor_no = $debtor_no
                    AND round($total, $decimal) > round(trans.alloc, $decimal)
                    AND $total <> 0
                    AND trans.`type` = 10"
            );

            if (!empty($except_trans_no))
                $sql .= " AND trans.trans_no <> $except_trans_no";

            if (!empty($dim_id))
                $sql .= " AND trans.dimension_id = $dim_id ";

            $result = db_query($sql);
            $return_result = [];
            while ($myrow = db_fetch_assoc($result)) {
                $return_result[] = $myrow;
            }

            return AxisPro::SendResponse($return_result, $format);
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }


    function get_customer_balance($cust_id = null, $format = "json")
    {
        try {
            $customer_id = $_GET['customer_id'] ?? 0;
            $balance = Customer::where('debtor_no', $customer_id)->value('balance');

            $result = [
                'customer_id' => $customer_id,
                'customer_balance' => $balance ?: '0.00'
            ];

            return AxisPro::SendResponse($result, $format);
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }


    }

    function check_admin_password($format = "json")
    {
        try {
            $discount = $_POST['discount'];

            $permission = app(App\Permissions::class)->getCode(Permissions::SA_APRVCSHDISCOUNT);
            $users = db_query(
                "select
                    user.id,
                    user.password
                from `0_users` user
                left join `0_security_roles` role on
                    role.id = user.role_id
                where
                    user.inactive = 0
                    and find_in_set({$permission}, replace(role.areas, ';', ',')) > 0",
                "could not get the authorized list of users"
            )->fetch_all(MYSQLI_ASSOC);

            $status = false;
            foreach ($users as $user) {
                if ($status = app('hash')->check($_POST['password'], $user['password'])) {
                    break;
                }
            }

            $result = [
                'discount' => $discount,
                'status' => $status
            ];

            // $result['customer_balance'] = $prepaid_bal-$out_standing_bal;
            // $result['customer_id'] = $customer_id;

            return AxisPro::SendResponse($result, $format);

        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }


    }

    public function decide()
    {
        if ($_POST['btnClick'] == 'csv') {
            $this->export_csv($_POST);
        }

        if ($_POST['btnClick'] == 'pdf') {
            $this->export_pdf($_POST);
        }
    }

    public function export_csv($data)
    {
        $sql = PrepareQuery::ServiceReport($data);
        $result = db_query($sql);

        ob_start();
        $output = fopen('php://output', 'w');
        $mapped_array = array(
            'col_invoice_number' => "INVOICE NUMBER",
            'col_tran_date' => "INVOICE DATE",
            'col_invoice_time' => "INVOICE TIME",
            'col_invoice_type' => "CARD TYPE",
            'col_dimension' => 'DEPARTMENT',
            'col_stock_id' => "STOCK ID",
            'col_service' => "SERVICE NAME",
            'col_category' => "CATEGORY",
            'col_customer_type' => "CUSTOMER TYPE",
            'col_customer_ref' => "CUSTOMER REF",
            'col_customer' => "CUSTOMER",
            'col_customer_category' => "CUSTOMER CATEGORY",
            'col_sales_man'=>"SALES MAN",
            'col_display_customer' => "DISPLAY CUSTOMER",
            'col_customer_mobile' => "CUSTOMER MOBILE",
            'col_customer_email' => "CUSTOMER EMAIL",
            'col_quantity' => "QUANTITY",
            'col_unit_price' => "SERVICE CHARGE",
            'col_returnable_amt' => "RECIEVABLE Amt",
            'col_total_price' => "TOTAL SERVICE CHARGE",
            'col_total_tax' => "TOTAL VAT",
            'col_govt_fee' => "GOVT.FEE",
            'col_govt_bank' => "GOVT.BANK",
            'col_returnable_to' => "RECIEVABLE ACC",
            'col_bank_service_charge' => "BANK SERVICE CHARGE",
            'col_bank_service_charge_vat' => "BANK SERVICE CHARGE VAT",
            'col_pf_amount' => "OTHER CHARGE",
            'col_total_govt_fee' => "TOTAL GOVT.FEE",
            'col_receivable_commission_amount' => "Receivable Commission Amt",
            'col_receivable_commission_account' => "Receivable Commission A/c",
            'col_transaction_id' => "Trans. ID",
            'col_transaction_id_updated_at' => "Trans. ID Updated on",
            'col_ed_transaction_id' => "MB/ST/DW-ID",
            'col_application_id' => "APPLICATION ID / RECEIPT ID",
            'col_passport_no' => "PASSPORT NO",
            'col_ref_name' => "REF.NAME",
            'col_gross_employee_commission' => "GROSS EMP. COMM",
            'col_cust_comm_emp_share' => "CUST COMM. EMP SHARE",
            'col_employee_commission' => "EMPLOYEE COMMISSION",
            'col_customer_commission' => "CUSTOMER COMMISSION",
            'col_customer_commission2' => "SALESMAN COMMISSION",
            'col_line_discount_amount' => "DISCOUNT AMOUNT",
            'col_reward_amount' => "REWARD AMOUNT",
            'col_payment_status' => "PAYMENT STATUS",
            'col_created_by' => "EMPLOYEE",
            'col_employee_name' => "EMPLOYEE NAME",
            'col_transaction_status' => "TRANSACTION STATUS",
            'col_completed_by' => "COMPLETED EMPLOYEE",
            'col_completer_name' => "COMPLETED EMPLOYEE NAME",
            'col_line_total' => "LINE TOTAL",
            'col_invoice_total' => "INVOICE TOTAL",
            'col_net_service_charge' => "NET SERVICE CHARGE",
        );

        $cusrtom_data = '';
        $header_one = [];
        if (isset($_POST['custom_report_hdn_id']) && !empty(trim($_POST['custom_report_hdn_id']))) {
            $sql = "SELECT * FROM 0_custom_reports WHERE id=" . $_POST['custom_report_hdn_id'];
            $custom_report = db_fetch_assoc(db_query($sql));
            $custom_report['params'] = htmlspecialchars_decode($custom_report['params']);
            $cusrtom_data = json_decode($custom_report['params']);

            $before_header = [];

            $push_data = [];
            $header_one[] = "Report Name :" . $cusrtom_data->custom_report_name;
            $header_one[] = array("Printed At : ", date(DB_DATETIME_FORMAT));
            $header_one[] = array("Printed By : ", $_SESSION['wa_current_user']->name);
            $header_one[] = array();

            foreach ($header_one as $h) {
                fputcsv($output, $h);
            }
            foreach ($cusrtom_data as $key => $val) {
                if (array_key_exists($key, $mapped_array)) {
                    array_push($before_header, $key);
                    array_push($push_data, str_replace("col_", "", $key));
                }
            }




            $header = [];
            foreach ($mapped_array as $index => $key_vals) {
                if (in_array($index, $before_header)) {
                    array_push($header, $key_vals);
                }
            }
            fputcsv($output, $header);
            
        } else {
            $header = [];
            $header[] = array("Report Name : ", "Service Report");
            $header[] = array("Printed At : ", date(DB_DATETIME_FORMAT));
            $header[] = array("Printed By : ", $_SESSION['wa_current_user']->name);
            $header[] = array();
            $header[] = array(
                "INVOICE NUMBER",
                "INVOICE DATE",
                "INVOICE TIME",
                "CARD TYPE",
                "DEPARTMENT",
                "STOCK ID",
                "SERVICE NAME",
                "CATEGORY",
                "CUSTOMER TYPE",
                "CUSTOMER REF",
                "CUSTOMER",
                "CUSTOMER CATEGORY",
                "SALES MAN",
                "DISPLAY CUSTOMER",
                "CUSTOMER MOBILE",
                "CUSTOMER EMAIL",
                "QUANTITY",
                "SERVICE CHARGE",
                "RECIEVABLE AMT",
                "TOTAL SERVICE CHARGE",
                "TOTAL VAT",
                "GOVT.FEE",
                "GOVT.BANK",
                "RECIEVABLE ACC.",
                "BANK SERVICE CHARGE",
                "BANK SERVICE CHARGE VAT",
                "OTHER CHARGE",
                "TOTAL GOVT.FEE",
                "RECEIVABLE COMMISSION AMT",
                "RECEIVABLE COMMISSION A/C",
                "BANK REFERENCE NUMBER",
                "TRANS ID UPDATED AT",
                "MB/ST/DW-ID",
                "APPLICATION ID / RECEIPT ID",
                "PASSPORT NO",
                "REF.NAME",
                "GROSS EMP. COMM",
                "CUST COMM. EMP SHARE",
                "EMPLOYEE COMMISSION",
                "CUSTOMER COMMISSION",
                "SALESMAN COMMISSION",
                "DISCOUNT AMOUNT",
                "REWARD AMOUNT",
                "PAYMENT STATUS",
                "EMPLOYEE",
                "EMPLOYEE NAME",
                "TRANSACTION STATUS",
                "COMPLETED EMPLOYEE",
                "COMPLETED EMPLOYEE NAME",
                "LINE TOTAL",
                "INVOICE TOTAL",
                "NET SERVICE CHARGE"
            );

            foreach ($header as $headerRows) {
                fputcsv($output, $headerRows);
            }
        }

        $i = 0;
        $data = [];
        while ($myrow = db_fetch_assoc($result)) {
            $sql_catname = "SELECT a.description
            FROM 0_stock_category AS a
            INNER JOIN 0_stock_master AS b ON a.category_id=b.category_id
            WHERE b.stock_id='" . $myrow['stock_id'] . "'";
            $cat_name_data = db_fetch_assoc(db_query($sql_catname));
            $cate_name = $cat_name_data['description'];

            $cust_name = "select name FROM 0_debtors_master where debtor_no='" . $myrow['debtor_no'] . "'";
            $cust_data = db_fetch_assoc(db_query($cust_name));
            $customer_name = $cust_data['name'];

            $acc_name = "select account_name FROM 0_chart_master where account_code='" . $myrow['govt_bank_account'] . "'";
            $account_name = db_fetch_assoc(db_query($acc_name));
            $account = $account_name['account_name'];

            $receivable_account = "select account_name FROM 0_chart_master where account_code='" . $myrow['receivable_commission_account'] . "'";
            $receivable_account_details = db_fetch_assoc(db_query($receivable_account));
            $receivable_commission_account = $receivable_account_details['account_name'];


            $user = "select user_id,real_name FROM 0_users where id='" . $myrow['created_by'] . "'";
            $user_data = db_fetch_assoc(db_query($user));
            $user_name = $user_data['user_id'];

            $data_to_fecth = [];
            if (isset($_POST['custom_report_hdn_id']) && !empty(trim($_POST['custom_report_hdn_id']))) {
                $data_to_fecth = array(
                    'invoice_number' => $myrow['invoice_number'],
                    'tran_date' => $myrow['tran_date'],
                    'invoice_time' => "'".$myrow['invoice_time'],
                    'invoice_type' => $myrow['invoice_type'],
                    'dimension' => $myrow['dimension'],
                    'stock_id' => $myrow['stock_id'],
                    'service' => $myrow['description'],
                    'category' => $cate_name,
                    'customer_type' => $myrow['customer_type'],
                    'customer_ref' => $myrow['debtor_ref'],
                    'customer' => $customer_name,
                    'customer_category' => $myrow['customer_category'],
                    'sales_man'=>$myrow['salesman_name'],
                    'display_customer' => $myrow['display_customer'],
                    'customer_mobile' => $myrow['customer_mobile'],
                    'customer_email' => $myrow['customer_email'],
                    'quantity' => $myrow['quantity'],
                    'unit_price' => $myrow['unit_price'],
                    'returnable_amt' => $myrow['returnable_amt'],
                    'total_price' => $myrow['total_service_charge'],
                    'total_tax' => $myrow['total_tax'],
                    'govt_fee' => $myrow['govt_fee'],
                    'govt_bank' => $account,
                    'returnable_to' => $myrow['returnable_to'],
                    'bank_service_charge' => $myrow['bank_service_charge'],
                    'bank_service_charge_vat' => $myrow['bank_service_charge_vat'],
                    'pf_amount' => $myrow['pf_amount'],
                    'total_govt_fee' => $myrow['total_govt_fee'],
                    'receivable_commission_amount' => $myrow['receivable_commission_amount'],
                    'receivable_commission_account' => $receivable_commission_account,
                    'transaction_id' => is_numeric(trim($myrow['transaction_id'])) ? '="' . trim($myrow['transaction_id']) . '"' : $myrow['transaction_id'],
                    'transaction_id_updated_at' => $myrow['transaction_id_updated_at'],
                    'ed_transaction_id' => $myrow['ed_transaction_id'],
                    'application_id' => is_numeric(trim($myrow['application_id'])) ? '="' . trim($myrow['application_id']) . '"' : $myrow['application_id'],
                    'passport_no' => $myrow['passport_no'],
                    'ref_name' => is_numeric(trim($myrow['ref_name'])) ? '="' . trim($myrow['ref_name']) . '"' : $myrow['ref_name'],
                    'gross_employee_commission' => $myrow['gross_employee_commission'],
                    'cust_comm_emp_share' => $myrow['cust_comm_emp_share'],
                    'employee_commission' => $myrow['employee_commission'],
                    'customer_commission' => $myrow['customer_commission'],
                    'customer_commission2' => $myrow['customer_commission2'],
                    'line_discount_amount' => $myrow['line_discount_amount'],
                    'reward_amount' => $myrow['reward_amount'],
                    'payment_status' => $myrow['payment_status'],
                    'created_by' => $user_name,
                    'employee_name' => $user_data['real_name'],
                    'transaction_status' => $myrow['transaction_status'],
                    'completed_by' => $myrow['completed_by'],
                    'completer_name' => $myrow['completer_name'],
                    'line_total' => $myrow['line_total'],
                    'invoice_total' => $myrow['invoice_total'],
                    'net_service_charge' => $myrow['net_service_charge'],
                );

                $test = [];
                foreach ($data_to_fecth as $keys => $vals) {
                    if (in_array($keys, $push_data)) {
                        array_push($test, $vals);
                    }
                }

                $data[] = $test;
            } else {
                $data[] = array(
                    $myrow['invoice_number'],
                    $myrow['tran_date'],
                    "'".$myrow['invoice_time'],
                    $myrow['invoice_type'],
                    $myrow['dimension'],
                    $myrow['stock_id'],
                    trim($myrow['description']),
                    $cate_name,
                    $myrow['customer_type'],
                    $myrow['debtor_ref'],
                    $customer_name,
                    $myrow['customer_category'],
                    $myrow['salesman_name'],
                    $myrow['display_customer'],
                    $myrow['customer_mobile'],
                    $myrow['customer_email'],
                    $myrow['quantity'],
                    $myrow['unit_price'],
                    $myrow['returnable_amt'],
                    $myrow['total_service_charge'],
                    $myrow['total_tax'],
                    $myrow['govt_fee'],
                    $account,
                    $myrow['returnable_to'],
                    $myrow['bank_service_charge'],
                    $myrow['bank_service_charge_vat'],
                    $myrow['pf_amount'],
                    $myrow['total_govt_fee'],
                    $myrow['receivable_commission_amount'],
                    $receivable_commission_account,
                    is_numeric(trim($myrow['transaction_id'])) ? '="' . trim($myrow['transaction_id']) . '"' : $myrow['transaction_id'],
                    $myrow['transaction_id_updated_at'],
                    $myrow['ed_transaction_id'],
                    is_numeric(trim($myrow['application_id'])) ? '="' . trim($myrow['application_id']) . '"' : $myrow['application_id'],
                    $myrow['passport_no'],
                    is_numeric(trim($myrow['ref_name'])) ? '="' . trim($myrow['ref_name']) . '"' : $myrow['ref_name'],
                    $myrow['gross_employee_commission'],
                    $myrow['cust_comm_emp_share'],
                    $myrow['employee_commission'],
                    $myrow['customer_commission'],
                    $myrow['customer_commission2'],
                    $myrow['line_discount_amount'],
                    $myrow['reward_amount'],
                    $myrow['payment_status'],
                    $user_name,
                    $user_data['real_name'],
                    $myrow['transaction_status'],
                    $myrow['completed_by'],
                    $myrow['completer_name'],
                    $myrow['line_total'],
                    $myrow['invoice_total'],
                    $myrow["net_service_charge"]
                );
            }

            fputcsv($output, $data[$i]);
            $i++;
                //unset($data_to_fecth);
        }
        fclose($output);
        $contents = ob_get_clean();
        
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=SERVICE_REPORT_".date('Ymd').'.csv');
        header("Content-Type: text/csv; charset=UTF-8");
        header('Content-Transfer-Encoding: binary');
        echo chr(0xEF).chr(0xBB).chr(0xBF).$contents;
        exit;
    }


public function export_pdf($data)
{
    $mpdf = new \Mpdf\Mpdf(['setAutoTopMargin' => 'stretch', 'default_font_size' => 7, 'default_font' => 'dejavusans']);
    $mpdf->SetDisplayMode('fullpage');


        $mpdf->list_indent_first_level = 0; // 1 or 0 - whether to indent the first level of a list
        $stylesheet = file_get_contents('style.css');
        $mpdf->WriteHTML($stylesheet, 1);
        //$mpdf->SetColumns(1, 'J', 9);
        $mpdf->list_align_style = 'L';
        $mpdf->falseBoldWeight = 2;

        /*-------------------------GET CAT NAME---------------*/
        $cat_data = "select description FROM 0_stock_category where category_id='" . $data['category'] . "'";
        $reult_data = db_fetch_assoc(db_query($cat_data));
        if ($data['category'] == '') {
            $reult_data['description'] = 'All';
        }

        $f_year = kv_get_current_fiscalyear();
        $begin1 = $f_year['begin'];
        $today1 = $f_year['end'];
        /*----------------------------END----------------------*/

        $mpdf->setHeader('
            <div>
            <div align="right">
            <span style="font-size: 9pt !important;font-weight: normal !important;">EQC - BOULEVARD BUSINESSMEN SERVICE</span><br/>
            </div>
            <div align="left" >
            <span style="font-size:12pt;">Category Report</span><br/>
            <span>Print Out Date : ' . date('d-m-Y h:i:s') . '</span><br/>
            <label style="font-weight: normal;">Fiscal Year : ' . date('d-m-Y', strtotime($begin1)) . ' - ' . date('d-m-Y', strtotime($today1)) . '</label><br/>
            <label style="font-weight: normal;">Period : ' . $data['date_from'] . ' - ' . $data['date_to'] . '</label><br/>
            <label style="font-weight: normal;">Category : ' . $reult_data['description'] . '</label><br/>
            <label></label><br/>
            </div>




            </div>

            <table style="border-top: 1px solid black;">
            <tr>
            <td>Sl.no</td>
            <td style="width:15%;">INVOICE No.</td>
            <td style="width:15%;">SERVICE NAME</td>
            <td style="width:10%;">CATEGORY</td>
            <td style="width:10%;">TOTAL SERVICE CHARGE</td>
            <td>TOTAL<br/> GOVT. FEE</td>
            <td>BANK <br/> REFERENCE No.</td>
            <td>EMP. NAME</td>
            <td>LINE TOTAL<td>
            </tr> </table>
            ');

        // $mpdf->SetHeader($arr);

$content = "<table>
";

$sql = PrepareQuery::ServiceReport($data);
$result = db_query($sql);
$i = 1;
$tot_service_chrge = '0';
$tot_govt_fee_disp = '0';
$line_tot = '0';
while ($myrow = db_fetch_assoc($result)) {

    $sql_catname = "SELECT a.description
    FROM 0_stock_category AS a
    INNER JOIN 0_stock_master AS b ON a.category_id=b.category_id
    WHERE b.stock_id='" . $myrow['stock_id'] . "'";
    $cat_name_data = db_fetch_assoc(db_query($sql_catname));
    $cate_name = $cat_name_data['description'];

    $cust_name = "select name FROM 0_debtors_master where debtor_no='" . $myrow['debtor_no'] . "'";
    $cust_data = db_fetch_assoc(db_query($cust_name));
    $customer_name = $cust_data['name'];

    $acc_name = "select account_name FROM 0_chart_master where account_code='" . $myrow['govt_bank_account'] . "'";
    $account_name = db_fetch_assoc(db_query($acc_name));
    $account = $account_name['account_name'];


    $user = "select user_id,real_name FROM 0_users where id='" . $myrow['created_by'] . "'";
    $user_data = db_fetch_assoc(db_query($user));
    $emp_name = explode(" ", $user_data['real_name']);


    $net_service_charge = $myrow['line_total'] - $myrow['reward_amount'] - $myrow['customer_commission'] - $myrow['employee_commission'];

    $content .= '<tr>
    <td>' . $i . '</td>
    <td>' . $myrow['invoice_number'] . '</td>
    <td style="width:22%;">' . $myrow['description'] . '</td>
    <td>' . $cate_name . '</td>
    <td>' . $myrow['total_service_charge'] . '</td>
    <td>' . $myrow['total_govt_fee'] . '</td>
    <td style="width:17;">' . $myrow['transaction_id'] . '</td>
    <td style="width:17%;" align="center">' . $emp_name[0] . ' ' . $emp_name[1] . '</td>
    <td>' . round($myrow['line_total'], 2) . '</td>
    </tr>';

    $i++;

    $tot_service_chrge += $myrow['total_service_charge'];
    $tot_govt_fee_disp += $myrow['total_govt_fee'];
    $line_tot += round($myrow['line_total'], 2);

}


$content .= ' </table>';

$content .= '<table style="border-top: 1px solid black;width:100%;"><tr >

<td style="font-weight: bold;">TOTAL :</td>
<td style="width: 39%;"></td>
<td style="font-weight: bold;">' . number_format($tot_service_chrge, 2) . '</td>
<td style="font-weight: bold;">' . number_format($tot_govt_fee_disp, 2) . '</td>
<td style="width: 25%;"></td>
<td style="font-weight: bold;">' . number_format($line_tot, 2) . '</td>



</tr></table>';

$mpdf->WriteHTML($content);

$mpdf->setFooter('<div style="font-weight: normal; font-size: 12px">Powered by - &copy; www.axisproerp.com</div>');


$mpdf->Output("Category_Report.pdf", \Mpdf\Output\Destination::INLINE);
}

public function upload_purchase_req_doc($format = "json")
{

    try {

        $root_url = str_replace("\ERP", "", getcwd());
        $root_url = str_replace("\API", "", $root_url);
        $root_url = getcwd();

        $pr_id = $_POST['upload_doc_pr_id'];

        $target_file = '';
        $filename = '';
        if ($_FILES["upload_doc"]["name"] != '') {
            $target_dir = $root_url . "/../../assets/uploads/";
            $fname = explode(".", $_FILES["upload_doc"]["name"]);
            $rand = rand(1000, 10000);
            $filename = $fname[0] . '_' . $rand . '.' . $fname[1];
            $target_file = $target_dir . basename($filename);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            if ($_FILES["upload_doc"]["size"] > 50000000) {
                return AxisPro::SendResponse(["status" => "FAIL", "msg" => "File size exceeded"], $format);
            }
            if ($imageFileType != "pdf" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "jpg") {
                return AxisPro::SendResponse(["status" => "FAIL", "msg" => "File format is not allowed"], $format);
            }

        }


        if (move_uploaded_file($_FILES["upload_doc"]["tmp_name"], $target_file)) {

            db_update('0_purchase_requests', [
                'upload_file' => db_escape($filename)
                ], ["id=$pr_id"]);

            return AxisPro::SendResponse(["status" => "OK", "msg" => "Document saved successfully"], $format);
        }

        return AxisPro::SendResponse(["status" => "FAIL", "msg" => "Something went wrong. Please try again"], $format);


    } catch (Exception $e) {
        return AxisPro::catchException($e);
    }


}


public function get_issuing_items($format = 'json')
{
    try {

        $purchase_req_id = 1;

        $sql = "SELECT * FROM 0_purchase_requests where id = $purchase_req_id";
        $result = db_fetch_assoc(db_query($sql));
        $mr = $result;

        $sql = "SELECT items.*,stk.description item_name,stk.purchase_cost 
        FROM 0_purchase_request_items items 
        LEFT JOIN 0_stock_master stk ON stk.stock_id = items.stock_id
        where items.req_id = $purchase_req_id ";


        $result = db_query($sql);
        $items = [];
        $line = 1;
        $return_result = [];
        while ($myrow = db_fetch_assoc($result)) {
            $myrow['qty_in_stock'] = get_qoh_on_date($myrow['stock_id']);

            $qty_to_be_ordered = 0;

            if ($myrow['qty'] > $myrow['qty_in_stock'])
                $qty_to_be_ordered = $myrow['qty'] - $myrow['qty_in_stock'];

            if ($qty_to_be_ordered < 0)
                $qty_to_be_ordered = 0;

            $qty_issuable = $myrow['qty_in_stock'];

            if ($qty_issuable > 0) {

                    // pp($myrow);

                if ($qty_issuable > $myrow['qty'])
                    $qty_issuable = $myrow['qty'];

//                    $_SESSION['adj_items']->add_to_cart($line, $myrow['stock_id'], -$qty_issuable, 0, $description = null);
//                    $line++;

                $return_result[] = [
                'item_code' => $myrow['stock_id'],
                'item_name' => $myrow['item_name'],
                'issue_qty' => $qty_issuable,
                'unit_cost' => $myrow['purchase_cost'],
                'total_cost' => $myrow['purchase_cost'] * $qty_issuable

                ];

            }

        }

        return AxisPro::SendResponse($return_result, $format);


    } catch (Exception $e) {
        return AxisPro::catchException($e);
    }
}


public function issueStockItems($format = 'json')
{

    try {

        global $Refs;

        $issueItemReqID = $_POST['issueItemReqID'];

        $items = $_POST['items'];

        $items_array = [];

        foreach ($items as $row) {

            $temp_array = [
            'stock_id' => db_escape($row['stock_id']),
            'quantity' => -$row['qty'],
            'standard_cost' => $row['standard_cost']

            ];

            array_push($items_array, $temp_array);

        }


        $ref = $Refs->get_next(17);
        $memo = 'Stock Issuing From Purchase Request';
        $date = Today();
        $loc = 'DEF';

        $items_obj = new stdClass();
        $items_obj->stock_id =

        $trans_no = $this->AddStockAdjustment($items_array,
            $loc, $date, $ref, $memo);
        new_doc_date($date);


        if (isset($issueItemReqID) && !empty($issueItemReqID)) {
            db_update('0_purchase_requests',
                    ['issued_from_stock' => 1], //issued from stock
                    ['id=' . $issueItemReqID]
                    );

            $user_id = $_SESSION['wa_current_user']->user;

            $msg = "Items issued from stock";
            db_insert('0_purch_request_log', [
                'user_id' => $user_id,
                'req_id' => $issueItemReqID,
                'description' => db_escape($msg)
                ]);

        }

        return AxisPro::SendResponse(['msg' => 'Success', 'data' => $trans_no, 'status' => 'OK'], $format);

    } catch (Exception $e) {
        return AxisPro::catchException($e);
    }


}


function AddStockAdjustment($items, $location, $date_, $reference, $memo_)
{

    try {

        global $SysPrefs, $path_to_root, $Refs;

        begin_transaction();
        $args = func_get_args();
        $args = (object)array_combine(array('items', 'location', 'date_', 'reference', 'memo_'), $args);
        $args->trans_no = 0;
        hook_db_prewrite($args, ST_INVADJUST);

        $adj_id = get_next_trans_no(ST_INVADJUST);

        foreach ($items as $line_item) {


                //dd($line_item);

            $stk_id = trim($line_item['stock_id'], "'");

            add_stock_adjustment_item($adj_id, $stk_id, $location, $date_, $reference,
                $line_item['quantity'], $line_item['standard_cost'], $memo_);
        }

        add_comments(ST_INVADJUST, $adj_id, $date_, $memo_);

        $Refs->save(ST_INVADJUST, $adj_id, $reference);
        add_audit_trail(ST_INVADJUST, $adj_id, $date_);

        $args->trans_no = $adj_id;
        hook_db_postwrite($args, ST_INVADJUST);
        commit_transaction();

        return $adj_id;

    } catch (Exception $e) {
        return AxisPro::catchException($e);
    }
}

    public function bankBalanceBelowLimitCheck()
    {
        return [];

        try {
            $acc_to_check = ["113002", "113003"];//Test purpose

            $acc_bal_limits = [
            "113002" => 5000,
            "113003" => 5000
            ];

            $gone_below_limits = [];
            foreach ($acc_to_check as $acc) {

                $from_date = begin_fiscalyear();
                $to_date = Today();
                $account = $acc;

                $from_date = add_days($from_date, -1);
                $to_date = add_days($to_date, 1);

                $balance = get_gl_balance_from_to($from_date, $to_date, $account, $dimension = 0, $dimension2 = 0);

                if (empty($balance))
                    $balance = 0;

                if ($acc_bal_limits[$acc] > $balance) {

                    $acc_name = get_gl_account_name($acc);

                    $gone_below_limits[] = [
                    'account' => $acc,
                    'account_name' => $acc_name,
                    'curr_bal' => $balance,
                    'limit' => $acc_bal_limits[$acc],
                    'type' => 'danger'
                    ];
                }

            }

            return $gone_below_limits;


        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }


    public function getCommonAlerts()
    {

        try {

            $common_alerts = [];

            $bank_bal_limit_check = $this->bankBalanceBelowLimitCheck();

            if (!empty($bank_bal_limit_check)) {

                foreach ($bank_bal_limit_check as $row) {

                    $acc_name = strtoupper($row['account_name']);
                    $curr_bal = $row['curr_bal'];
                    $limit = $row['limit'];
                    $type = $row['type'];

                    $style = "font-weight:bold;";
                    if ($type == 'danger')
                        $style .= "color:red;";
                    $alert_text = "<span style='$style'>$acc_name balance is gone below the set limit AED $limit.</span>";
                    $common_alerts[] = $alert_text;

                }

            }

//            return [];
            return $common_alerts;


        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }


    public function get_gl_acc_balance_from_to($format = 'json')
    {
        try {

            $from_date = begin_fiscalyear();
            $to_date = $_GET['trans_date'];
            $account = $_GET['cash_acc'];
            $account = get_bank_gl_account($account);

            $from_date = add_days($from_date, -1);
            $to_date = add_days($to_date, 1);

            if (empty($account))
                return AxisPro::SendResponse(["status" => "FAIL", "data" => "Required Fields Not Validated"], $format);

            $balance = get_gl_balance_from_to($from_date, $to_date, $account, $dimension = 0, $dimension2 = 0);

            if (empty($balance))
                $balance = 0;

            return AxisPro::SendResponse(["status" => "OK", "data" => $balance], $format);

        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }
    }

public function getBalanceCashCollection($format = 'json')
{
    try {
        $user_date_format = getDateFormatInNativeFormat();
        if (
            !isset($_GET['trans_date'])
            || !($dt = DateTime::createFromFormat($user_date_format, $_GET['trans_date']))
            || $dt->format($user_date_format) != $_GET['trans_date']
        ) {
            return AxisPro::SendResponse(["status" => "FAIL", "msg" => "The date is not valid"], $format);
        } else {
            $trans_date = $dt->format(DB_DATE_FORMAT);
        }

        if (!isset($_GET['user_id']) || !preg_match('/^[1-9][0-9]{0,15}$/', $_GET['user_id'])) {
            return AxisPro::SendResponse(["status" => "FAIL", "msg" => "The user id is not valid"], $format);
        }

        if (!isset($_GET['cash_acc']) || !preg_match('/^[0-9]{1,15}$/', $_GET['cash_acc'])) {
            return AxisPro::SendResponse(["status" => "FAIL", "msg" => "account code is not valid"], $format);
        }

        $account = get_bank_gl_account($_GET['cash_acc']);
        if (empty($account)) {
            return AxisPro::SendResponse(["status" => "FAIL", "msg" => "account is not a bank account"], $format);
        }

        $balance = db_fetch_row(
            db_query(
                "SELECT SUM(gl.amount) 
                FROM 0_gl_trans gl
                WHERE gl.account = '$account'
                    AND gl.tran_date = '$trans_date'
                    AND gl.created_by = {$_GET['user_id']}"
                )
            )[0];

        if (empty($balance)) {
            $balance = 0.00;
        } else {
            $balance = (float)$balance;
        } 

        /** Check if there any amount that is already handovered and miinus that amount*/
        $mysqli_result = db_query(
            "SELECT cash_in_hand 
            FROM 0_cash_handover_requests 
            WHERE trans_date = '$trans_date'
                AND cashier_id = {$_GET['user_id']}
                AND `status` = 'APPROVED'"
        );
        while ($row = $mysqli_result->fetch_assoc()){
            $balance -= (float)$row['cash_in_hand'];
        }

        return AxisPro::SendResponse(["status" => "OK", "data" => round2($balance, 2)], $format);
    } catch (Exception $e) {
        return AxisPro::catchException($e);
    }
}

public function getHandoveredCash($format = 'json') {
    $user_date_format = getDateFormatInNativeFormat();
    if (
        !($dt = DateTime::createFromFormat($user_date_format, $_GET['trans_date']))
        || $dt->format($user_date_format) != $_GET['trans_date']
    ) {
        return AxisPro::SendResponse(["status" => "FAIL", "msg" => "The date is not valid"], $format);
    } else {
        $trans_date = $dt->format(DB_DATE_FORMAT);
    }

    if (!preg_match('/^[1-9][0-9]{0,15}$/', $_GET['user_id'])) {
        return AxisPro::SendResponse(["status" => "FAIL", "msg" => "The user id is not valid"], $format);
    }

    $cash = db_query(
        "SELECT SUM(cash_in_hand) 
        FROM 0_cash_handover_requests 
        WHERE trans_date = '$trans_date'
            AND cashier_id = {$_GET['user_id']}
            AND `status` = 'APPROVED'
        GROUP BY cashier_id"
    )->fetch_row()[0];

    $cash = round2((float)$cash, 2);

    return AxisPro::SendResponse(["status" => "OK", "data" => $cash], $format);
}

public function get_cashiers($format = 'json')
{
    try {
        $sql = "select id,user_id,real_name,cashier_account from 0_users where role_id in (3,9,13,15)";
        $get = db_query($sql);
        $return_result = [];

        while ($myrow = db_fetch($get))
            $return_result[] = $myrow;

        return AxisPro::SendResponse($return_result, $format);

    } catch (Exception $e) {
        return AxisPro::catchException($e);
    }

}


public function get_user_info($format = 'json')
{
    try {

        $user_id = $_GET['user_id'];

        $sql = "select id,user_id,real_name,cashier_account, role_id from 0_users where id=$user_id";
        $get = db_query($sql);
        $data = db_fetch_assoc($get);

        return AxisPro::SendResponse(["status" => "OK", "data" => $data], $format);

    } catch (Exception $e) {
        return AxisPro::catchException($e);
    }

}

/**
 * Return security roles which have access to the specified security areas
 * 
 * Note: Does not consider whether the security section that encapsulate the area is enabled for the role or not
 * 
 * @param string|string[] $sec_areas The secutity area or areas that needs to be searched
 * @return array Array of role ids which have access to the provided security area or empty array if none exists
 */
public function getRoleIdsWithAccess($sec_areas) {
    if (!is_array($sec_areas)) {
        $sec_areas = [$sec_areas];
    }
    /** Index the security areas */
    $sec_areas = array_intersect_key($GLOBALS['security_areas']->toArray(), array_flip($sec_areas));
    $sec_areas = array_column($sec_areas, 0, 0);

    $roles = [];
    $mysqli_result = db_query("SELECT id, areas FROM 0_security_roles WHERE NOT inactive");
    while($row = $mysqli_result->fetch_assoc()) {
        $roles[$row['id']] = array_flip(explode(';', $row['areas']));
    }

    $role_ids = [];
    foreach($roles as $id => $permissions) {
        if (!empty(array_intersect_key($sec_areas, $permissions))){
            $role_ids[] = $id;
        }
    }
    return $role_ids;
}


public function saveCashHandOverRequest($format = 'json')
{
    try {
        // validate cashier id
        $cashier_id = $_GET['user_id'] = $_POST['user_id'];
        if (empty($cashier_id)) {
            return AxisPro::SendResponse([
                "status" => "FAIL",
                "msg" => "Please choose a Cashier"
            ], $format);
        }
        if (!preg_match('/^[1-9][0-9]{0,15}$/', $cashier_id)) {
            return AxisPro::SendResponse(["status" => "FAIL",
                "msg" => "The cashier id is invalid"
                ], $format);
        }

        // validate cashier's cash account.
        $res = db_fetch_assoc(
            db_query(
                "select id,user_id,real_name,cashier_account from 0_users where id = $cashier_id"
            )
        );
        if ($res) {
            $cash_acc = $res['cashier_account'];
        }
        if (empty($cash_acc)) {
            return AxisPro::SendResponse(["status" => "FAIL",
                "msg" => "No Cashier A/C set for this cashier."
                ], $format);
        }

        // validate date
        $trans_date = $_POST['trans_date'];
        $user_date_format = getDateFormatInNativeFormat();
        if (
            !($dt = DateTime::createFromFormat($user_date_format, $trans_date))
            || $dt->format($user_date_format) !== $trans_date
        ) {
            return AxisPro::SendResponse(["status" => "FAIL",
                "msg" => "Transaction date is invalid"
            ], $format);
        }
        $tdate = date2sql($trans_date);
        $today = date2sql(Today());
        $future_date = strtotime($today) < strtotime($tdate);
        if ($future_date) {
            return AxisPro::SendResponse(["status" => "FAIL",
                "msg" => "Transaction Date must not be a Future Date"
                ], $format);
        }

        $cash_acc_gl_code = get_bank_gl_account($cash_acc);
        if (empty($cash_acc_gl_code)) {
            return AxisPro::SendResponse(["status" => "FAIL",
                "msg" => "No bank account is configured for this cashier A/C."
                ], $format);
        }

        $created_by = $_SESSION['wa_current_user']->user;

        $denom1000 = !empty($_POST['denom1000_pcs']) ? $_POST['denom1000_pcs'] : 0;
        $denom500 = !empty($_POST['denom500_pcs']) ? $_POST['denom500_pcs'] : 0;
        $denom200 = !empty($_POST['denom200_pcs']) ? $_POST['denom200_pcs'] : 0;
        $denom100 = !empty($_POST['denom100_pcs']) ? $_POST['denom100_pcs'] : 0;
        $denom50 = !empty($_POST['denom50_pcs']) ? $_POST['denom50_pcs'] : 0;
        $denom20 = !empty($_POST['denom20_pcs']) ? $_POST['denom20_pcs'] : 0;
        $denom10 = !empty($_POST['denom10_pcs']) ? $_POST['denom10_pcs'] : 0;
        $denom5 = !empty($_POST['denom5_pcs']) ? $_POST['denom5_pcs'] : 0;
        $denom1 = !empty($_POST['denom1_pcs']) ? $_POST['denom1_pcs'] : 0;
        $denom0_5 = !empty($_POST['denom0_5_pcs']) ? $_POST['denom0_5_pcs'] : 0;
        $denom0_25 = !empty($_POST['denom0_25_pcs']) ? $_POST['denom0_25_pcs'] : 0;

        $denom_total = 0;
        $denom_total += ($denom1000 * 1000);
        $denom_total += ($denom500 * 500);
        $denom_total += ($denom200 * 200);
        $denom_total += ($denom100 * 100);
        $denom_total += ($denom50 * 50);
        $denom_total += ($denom20 * 20);
        $denom_total += ($denom10 * 10);
        $denom_total += ($denom5 * 5);
        $denom_total += ($denom1 * 1);
        $denom_total += ($denom0_5 * 0.5);
        $denom_total += ($denom0_25 * 0.25);

        if (empty($denom_total) || $denom_total <= 0)
            return AxisPro::SendResponse(["status" => "FAIL",
                "msg" => "Please enter valid denominations"
                ], $format);

        $_GET['trans_date'] = $trans_date;
        $_GET['cash_acc'] = $cash_acc;
        $tot_cash_in_hand = round2((float)$this->getBalanceCashCollection('array')['data'], 2);

        if (empty($tot_cash_in_hand))
            $tot_cash_in_hand = 0;

        $tot_cash_in_hand = round2($tot_cash_in_hand, 2);
        $total_to_pay = ceil($tot_cash_in_hand / 0.25) * 0.25;
        $adjustments = round2($total_to_pay - $tot_cash_in_hand, 2);
        $balance = round2($denom_total - $total_to_pay, 2);

        if ($denom_total < $total_to_pay)
            return AxisPro::SendResponse([
                "status" => "FAIL",
                "msg" => "Entered amount($denom_total) is less than the cash in hand($total_to_pay)"
            ], $format);


        $insert_data = [
            'amount' => $denom_total,
            'cashier_id' => $cashier_id,
            'cash_acc_code' => $cash_acc_gl_code,
            'cash_in_hand' => $tot_cash_in_hand,
            'total_to_pay' => $total_to_pay,
            'adj' => $adjustments,
            'balance' => $balance,
            'trans_date' => db_escape(date2sql($trans_date)),
            'created_by' => $created_by,
            'denom1000' => $denom1000,
            'denom500' => $denom500,
            'denom200' => $denom200,
            'denom100' => $denom100,
            'denom50' => $denom50,
            'denom20' => $denom20,
            'denom10' => $denom10,
            'denom5' => $denom5,
            'denom1' => $denom1,
            'denom0_5' => $denom0_5,
            'denom0_25' => $denom0_25,
        ];

        db_insert('0_cash_handover_requests', $insert_data);

        $insert_id = db_insert_id();
        $ref = 'CH/' . $insert_id;

        $sql = "update 0_cash_handover_requests set reference = '$ref' WHERE id=$insert_id";
        db_query($sql);

        return AxisPro::SendResponse([
            "status" => "OK",
            "msg" => "New Cash Handover Request Placed",
            'data' => $ref
        ], $format);

    } catch (Exception $e) {
        return AxisPro::catchException($e);
    }

}


public function getAllCashHandoverRequests($format = 'json')
{
    try {
        if (!$_SESSION['wa_current_user']->can_access('SA_CASH_HANDOVER_LIST')) {
            return AxisPro::SendResponse([
                'rep' => [],
                'total_rows' => 0,
                'pagination_link' => '',
                'users' => [],
                'bl' => [],
                'aggregates' => [],
            ]);
        }

        $sql = "SELECT * FROM 0_cash_handover_requests WHERE 1=1 ORDER BY created_at DESC";

        $total_count_sql = "select count(*) as cnt from ($sql) as tmpTable";
        $total_count_exec = db_fetch_assoc(db_query($total_count_sql));
        $total_count = $total_count_exec['cnt'];

        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $perPage = 200;
        $offset = ($page * $perPage) - $perPage;


        $sql = $sql . " LIMIT $perPage OFFSET $offset";

        $result = db_query($sql);
        $report = [];
        while ($myrow = db_fetch_assoc($result)) {

            $myrow['trans_date'] = sql2date($myrow['trans_date']);

            $denoms = [];

            if (!empty($myrow['denom1000']))
                $denoms[] = [
                    "key" => '1000',
                    "val" => $myrow['denom1000']
                ];

            if (!empty($myrow['denom500']))
                $denoms[] = [
                    "key" => '500',
                    "val" => $myrow['denom500']
                ];

            if (!empty($myrow['denom200']))
                $denoms[] = [
                    "key" => '200',
                    "val" => $myrow['denom200']
                ];

            if (!empty($myrow['denom100']))
                $denoms[] = [
                    "key" => '100',
                    "val" => $myrow['denom100']
                ];

            if (!empty($myrow['denom50']))
                $denoms[] = [
                    "key" => '50',
                    "val" => $myrow['denom50']
                ];

            if (!empty($myrow['denom20']))
                $denoms[] = [
                    "key" => '20',
                    "val" => $myrow['denom20']
                ];

            if (!empty($myrow['denom10']))
                $denoms[] = [
                    "key" => '10',
                    "val" => $myrow['denom10']
                ];

            if (!empty($myrow['denom5']))
                $denoms[] = [
                    "key" => '5',
                    "val" => $myrow['denom5']
                ];

            if (!empty($myrow['denom1']))
                $denoms[] = [
                    "key" => '1',
                    "val" => $myrow['denom1']
                ];

            if (!empty($myrow['denom0_5']))
                $denoms[] = [
                    "key" => '0.5',
                    "val" => $myrow['denom0_5']
                ];

            if (!empty($myrow['denom0_25']))
                $denoms[] = [
                    "key" => '0.25',
                    "val" => $myrow['denom0_25']
                ];

            $myrow['denoms'] = $denoms;

            $report[] = $myrow;
        }


        $sql = "SELECT coa.account_name, coa.account_code, bank.id AS bank_acc_id FROM 0_chart_master coa 
        INNER JOIN 0_bank_accounts bank ON bank.account_code = coa.account_code";
        $result = db_query($sql);
        $bank_ledgers = [];
        while ($myrow = db_fetch_assoc($result))
            $bank_ledgers[$myrow['account_code']] = $myrow;

        return AxisPro::SendResponse(
            [
            'rep' => $report,
            'total_rows' => $total_count,
            'pagination_link' => AxisPro::paginate($total_count),
            'users' => $this->get_key_value_records('0_users', 'id', 'user_id'),
            'bl' => $bank_ledgers,
            'aggregates' => $total_count_exec,]
            );


    } catch (Exception $e) {
        return AxisPro::catchException($e);
    }

}

public function cashHandoverRequestActionHandler($format = 'json')
{
    if (!$_SESSION['wa_current_user']->can_access('SA_CASH_HANDOVER_LIST')) {
        return AxisPro::SendResponse([
            'status' => 'FAIL',
            'msg'    => 'The security settings on your account do not permit you to access this function'
        ]);
    }

    try {

        global $Refs;

        $req_id = $_POST['req_id'];
        $actionToUpdate = $_POST['action'];
        $dateTime = date(DB_DATETIME_FORMAT);

        if ($actionToUpdate == 'APPROVED') {
            //Post gl after approval
            //Todo

            $sql = "select * from 0_cash_handover_requests where id=$req_id";
            $get = db_query($sql);
            $req_info = db_fetch($get);

            $user_handing_over_cash = $req_info['cashier_id'];
            $cash_handover_requested_on = $req_info['trans_date'];
            $cash_in_hand = $req_info['cash_in_hand'];
            $adjustments = $req_info['adj'];
            $totalToPay = $req_info['total_to_pay'];
            $credit_account = $req_info['cash_acc_code'];
            $trans_date = sql2date($req_info['trans_date']);
            $credit_account2 = get_company_pref('cash_handover_round_off_adj_act');
            if (empty(trim($credit_account2))) {
                return AxisPro::SendResponse([
                    'status' => 'FAIL',
                    'msg' => "Cash handover round off adjustment account is not set"
                ]);
            }

            $cash_acc_id = db_query(
                "select cashier_account from 0_users where id = {$_SESSION['wa_current_user']->user}"
            )->fetch_row()[0];
            if (empty($cash_acc_id)) {
                return AxisPro::SendResponse(["status" => "FAIL",
                    "msg" => "No Cashier A/C is set for this user."
                ], $format);
            }

            // $hasMultipleUsersWithSameAccount = db_query(
            //     "SELECT COUNT(1) FROM 0_users WHERE cashier_account = $cash_acc_id AND id != {$_SESSION['wa_current_user']->user}"
            // )->fetch_row()[0];
            // if ($hasMultipleUsersWithSameAccount) {
            //     return AxisPro::SendResponse(["status" => "FAIL",
            //         "msg" => "This user must have a seperate cashier A/C"
            //     ]);
            // }

            $debit_account = get_bank_gl_account($cash_acc_id);
            if (empty($debit_account)) {
                return AxisPro::SendResponse(["status" => "FAIL",
                    "msg" => "No bank account is configured for this A/C."
                ], $format);
            }

            /** Check if the user transfering the cash and the user approving the request use the same cashier account */
            if ($credit_account == $debit_account) {
                return AxisPro::SendResponse(["status" => "FAIL",
                    "msg" => "Cannot transfer cash between users: having the same cash account"
                ], $format);
            }

            /** Verify the balance in the cashier's account */
            $_GET['user_id']    = $user_handing_over_cash;
            $_GET['trans_date'] = DateTime::createFromFormat(DB_DATE_FORMAT, $cash_handover_requested_on)->format(getDateFormatInNativeFormat());
            $_GET['cash_acc']   = db_query("SELECT id from 0_bank_accounts where account_code = '{$credit_account}'")->fetch_assoc()['id'];
            $balance_in_cashier_account = round2((float)$this->getBalanceCashCollection('array')['data'], 2);
            if (floatcmp($balance_in_cashier_account, $cash_in_hand) != 0) {
                return AxisPro::SendResponse(["status" => "FAIL",
                    "msg" => "Amount does not match the balance in their account!"
                ], $format);
            }

            if (check_bank_account_history(-$cash_in_hand, is_bank_account($credit_account), $trans_date)) {
                return AxisPro::SendResponse(["status" => "FAIL",
                    "msg" => "This request would result in negative balance of the cashier's account. Please verify the account balance and add any missing entries or reject the request."
                ], $format);
            }

            //Pass Journal Entry
            begin_transaction();
            $cart = new items_cart(ST_JOURNAL);
            $cart->tran_date = $cart->doc_date = $cart->event_date = $trans_date;
            $cart->reference = $Refs->get_next(ST_JOURNAL, null, $cart->tran_date, true);
            $cart->memo_ = "Cash Handover Request";
            
            $cart->add_gl_item($debit_account, 0, 0, $totalToPay, $cart->memo_);
            $cart->add_gl_item($credit_account, 0, 0, -$cash_in_hand, $cart->memo_);
            $cart->add_gl_item($credit_account2, 0, 0, -$adjustments, $cart->memo_);
            
            $trans_id = write_journal_entries($cart);

            db_update('0_cash_handover_requests', [
                'trans_no' => $trans_id,
                'source_ref' => db_escape($cart->reference),
                'handovered_on' => db_escape($cash_handover_requested_on),
                'status' => db_escape($actionToUpdate),
                'approve_rejected_by' => $_SESSION['wa_current_user']->user,
                'approve_rejected_at' => "'{$dateTime}'"
            ], ["id=$req_id"]);
            commit_transaction();
        } else {
            begin_transaction();
            db_update('0_cash_handover_requests', [
                'status' => db_escape($actionToUpdate),
                'approve_rejected_by' => $_SESSION['wa_current_user']->user,
                'approve_rejected_at' => "'{$dateTime}'"
            ], ["id=$req_id"]);
            commit_transaction();
        }

        $msg = "Cash Handover Request is APPROVED";

        if ($actionToUpdate == 'REJECTED')
            $msg = "Cash Handover Request is REJECTED";

        return AxisPro::SendResponse(["status" => "SUCCESS",
            "msg" => $msg
            ], $format);


        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }

    function place_srv_request($format = 'json')
    {
        try {

            begin_transaction();

            $is_editing = !empty($_POST['edit_id']);
            if (!user_check_access($is_editing
                ? 'SA_EDITSERVICEREQ'
                : 'SA_SERVICE_REQUEST'
            )) {
                return AxisPro::ValidationError(
                    'You dont have permission to access this function. Please contact your admin for permission',
                    403
                );
            }

            if ($is_editing) {
                $service_req_items = db_query(
                    "SELECT
                        req.id,
                        item.id as item_id,
                        item.invoiced_at as item_invoiced_at
                    FROM `0_service_requests` as req
                    LEFT JOIN `0_service_request_items` as item ON
                        item.req_id = req.id
                    WHERE req.id = " . db_escape($_POST['edit_id']) ."
                        AND req.active_status = 'ACTIVE'
                    FOR UPDATE",
                    'Could not query for service request'
                )->fetch_all(MYSQLI_ASSOC);

                if (empty($service_req_items)) {
                    return AxisPro::ValidationError('Could not find the service request');
                }

                if (!empty(array_filter($service_req_items, function ($i) {return !empty($i['item_invoiced_at']);}))) {
                    return AxisPro::ValidationError('Some or all of the items in this service request is already invoiced');
                }
            }

            $inputs = $_POST;

            $curr_user_id = $_SESSION['wa_current_user']->user;
            $user_info = get_user($curr_user_id);

            $errors = [];
            if (empty($_POST['display_customer']))
                $errors['display_customer'] = "Please enter display customer";

            if (empty($_POST['customer']))
                $errors['customer'] = "Please choose a customer";

            if (empty($_POST['mobile']) || !preg_match(UAE_MOBILE_NO_PATTERN, $_POST['mobile']))
                $errors['mobile'] = "Please enter customer mobile";
//            else if (strlen($_POST['mobile']) <> 10)
//                $errors['mobile'] = "Mobile number must have 10 digits Eg: 0512345678";

            // if (empty($_POST['token_no']))
            //     $errors['token_no'] = "Please enter token number";

            if (empty($_POST['contact_person']) && pref('axispro.is_contact_person_mandatory'))
                $errors['contact_person'] = "Please enter a contact person";

            if (
                empty($_POST['cost_center_id'])
                || (
                    $_POST['cost_center_id'] != $user_info['dflt_dimension_id']
                    && !in_array($_POST['cost_center_id'], explode(",", $user_info['allowed_dims']))
                ) 
            ) {
                $errors['cost_center_id'] = "Please select a department";
            }

//            if (empty($_POST['iban_number']))
//                $errors['iban_number'] = "Please enter an iban number";


            if (!empty($errors))
                return AxisPro::SendResponse(['status' => 'FAIL', 'msg' => 'VALIDATION_FAILED', 'data' => $errors]);


            $edit_id = isset($_POST['edit_id']) ? $_POST['edit_id'] : null;

            $array = [
                'customer_id' => $inputs['customer'],
                'payment_method' => db_escape($inputs['payment_method'] ?? ''),
                'cost_center_id' => db_escape($_POST['cost_center_id']),
                'mobile' => db_escape(preg_replace(UAE_MOBILE_NO_PATTERN, "+971$2", $inputs['mobile'])),
                'email' => db_escape($inputs['email']),
                'iban' => db_escape($inputs['iban_number']),
                'display_customer' => db_escape($inputs['display_customer']),
                'contact_person' => db_escape($inputs['contact_person']),
                'memo' => db_escape($inputs['memo']),
                'active_status' => db_escape($inputs['active_status'])
            ];
            $current_time = now()->toDateTimeString();

            if (empty($edit_id)) {

                $return_msg = "Service Request added";

                $sql = "select count(*) cnt from 0_service_requests 
                where token_number=" . db_escape($inputs['token_no']) . " and date(created_at) = " . db_escape(date2sql(Today()));
                $get = db_query($sql);
                $data = db_fetch($get);
                // if ($data['cnt'] > 0)
                //     return AxisPro::SendResponse(['status' => 'FAIL', 'msg' => 'This TOKEN NUMBER is already used today', 'data' => $errors]);

                $barcode = AxisPro::GenerateBarCode(12, '0_service_requests', 'barcode');

                $array['barcode'] = db_escape($barcode);
                $array['token_number'] = db_escape($inputs['token_no']);
                $array['created_by'] = $curr_user_id;
                $array['created_at'] = db_escape($current_time);
                $array['updated_at'] = db_escape($current_time);

                db_insert('0_service_requests', $array);
                $service_request_id = db_insert_id();
            } else {//EDIT FUNCTIONALITY

                $return_msg = "Service Request updated";

                $service_request_id = $edit_id;

                $sql = "DELETE FROM 0_service_request_items WHERE req_id=$service_request_id";
                db_query($sql);

                $array['updated_by'] = $curr_user_id;
                $array['updated_at'] = db_escape($current_time);

                db_update('0_service_requests', $array,["id=$service_request_id"]);
            }


            if (empty($edit_id)) {

                $year = date("Y");
                $month = date("m");
                $day = date("d");

                $date_part = $year.$month.$day;

                $sql = "select COUNT(*) as cnt from 0_service_requests where
                token_number = ".db_escape($inputs['token_no'])." and date(created_at) = ".db_escape(date2sql(Today()));

                $get = db_query($sql);
                $res = db_fetch($get);

                $next_cnt = $res['cnt'];

                $cost_center = get_dimension($_POST['cost_center_id']);
                if (empty($cost_center)) {
                    $cost_center = get_dimensions($user_info['dflt_dimension_id']);
                }
                $reference = "SRQ/" . $cost_center['invoice_prefix'] . "/$date_part/".$inputs['token_no']."/".$next_cnt;

                db_update('0_service_requests',['reference'=>db_escape($reference)],["id=$service_request_id"]);

            }


            // db_insert('0_service_requests', $insert_array);

            $dflt_bank_chrgs = $this->getDefaultBankChargesForServiceRequest($inputs['items']);

            // $service_request_id = db_insert_id();
            $items_batch = [];
            foreach ($inputs['items'] as $row) {

                if(empty($row['discount']))
                   $row['discount'] = 0;

                $tmp_array = [
                    'req_id' => $service_request_id,
                    'stock_id' => db_escape($row['stock_id']),
                    'description' => db_escape($row['description']),
                    'qty' => $row['qty'],
                    'govt_fee' => $row['govt_fee'],
                    'bank_service_charge' => $row['bank_charge'],
                    'price' => $row['service_charge'],
                    'pf_amount' => $row['pf_amount'],
                    'discount' => $row['discount'],
                    'unit_tax' => $row['tax'],
                    'application_id' => db_escape($row['application_id']),
                    'transaction_id' => db_escape($row['transaction_id']),
                    'ref_name' => db_escape($row['ref_name'])
                ];

                if ($tmp_array['govt_fee'] > 0) {
                    if ($tmp_array['bank_service_charge'] <= 0) {
                        $tmp_array['bank_service_charge'] = ($dflt_bank_chrgs[$row['stock_id']] ?? 0);
                    }
                } else {
                    $tmp_array['bank_service_charge'] = 0;
                }

                array_push($items_batch, $tmp_array);
            }

            db_insert_batch('0_service_request_items', $items_batch);

            commit_transaction();;

            return AxisPro::SendResponse(['status' => 'OK', 'msg' => $return_msg, 'print_url' => $this->generateUrlForServiceRequestPrint($service_request_id)]);


        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }

    public function generateUrlForServiceRequestPrint(int $id)
    {
        return $GLOBALS['SysPrefs']->project_url . "ERP/service_request/print.php?id=$id";
    }

    public function getDefaultBankChargesForServiceRequest($requestItems) {
        if(empty($requestItems)) {
            return [];
        }

        $db_escape = function($value) {
            return db_escape($value);
        };

        $stock_ids = implode(
            ',',
            array_map(
                $db_escape, 
                array_column(
                    $requestItems,
                    'stock_id'
                )
            )
        );

        $dflt_bank_chrgs = db_query(
            "SELECT 
                sm.stock_id,
                ba.dflt_bank_chrg
            FROM 
                0_stock_master sm
            LEFT JOIN 0_bank_accounts ba on ba.account_code = sm.govt_bank_account
            WHERE sm.stock_id in ({$stock_ids})"
        )->fetch_all(MYSQLI_ASSOC);

        $dflt_bank_chrgs = array_column($dflt_bank_chrgs, 'dflt_bank_chrg', 'stock_id');

        return  $dflt_bank_chrgs;
    }

    public function get_token_info($format = 'json')
    {

        try {

            $token = $_GET['token'];

            $token = db_escape($token);

            $sql = (
                "select"
                    . " req.*,"
                    . " concat(debtor.debtor_ref, ' - ', debtor.name) as formatted_name"
                . " from 0_axis_front_desk req"
                . " left join 0_debtors_master debtor on debtor.debtor_no = req.customer_id"
                . " where req.token = $token"
                . " and date(req.created_at) = ".db_escape(date(DB_DATE_FORMAT))
                . " order by req.id desc"
            );
            $get = db_query($sql);
            $return_result = db_fetch($get);

            return AxisPro::SendResponse(['status' => 'OK', 'data' => $return_result], $format);

        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }

    public function getTopTenCustomerTransaction($format = 'json')
    {

        try {


            $cat_id = $_GET['cat_id'];
            $from_date = $_GET['from_date'];
            $to_date = $_GET['to_date'];

            $where = "";

            if (!empty($from_date) && !empty($to_date)) {
                $from_date = date2sql($from_date);
                $to_date = date2sql($to_date);

                $where .= " and trans.tran_date >= " . db_escape($from_date);
                $where .= " and trans.tran_date <= " . db_escape($to_date);
            }

            if (!empty($cat_id))
                $where .= " and cat.category_id=$cat_id";

            $sql = "SELECT 

            cust.name AS customer_name, SUM(detail.quantity) AS qty 
            
            FROM 0_debtor_trans_details detail 
            
            LEFT JOIN 0_debtor_trans trans ON trans.trans_no = detail.debtor_trans_no AND trans.`type` = detail.debtor_trans_type 
            
            LEFT JOIN 0_stock_master stock ON stock.stock_id = detail.stock_id 
            
            LEFT JOIN 0_stock_category cat ON cat.category_id = stock.category_id 
            
            LEFT JOIN 0_debtors_master cust ON cust.debtor_no=trans.debtor_no
            
            WHERE detail.debtor_trans_type = 10 AND detail.quantity <> 0 $where GROUP BY cust.debtor_no ORDER BY qty DESC LIMIT 20";


            $return_result = [];
            $get = db_query($sql);
            while ($myrow = db_fetch($get))
                $return_result[] = $myrow;

            return AxisPro::SendResponse($return_result, $format);


        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }

    /**
     * Process refund
     * Creates a payment voucher and allocates automatically
     * @param string $format
     * @return array|mixed
     */
    public function process_refund($format = 'json')
    {
        try {
            $authUser = authUser();
            $dec = user_price_dec();
            $customer_id = get_post('customer_id');
            $tran_date = get_post('tran_date');
            $branch_id = data_get(get_default_branch($customer_id), 'branch_code') ?: -1;
            $bank_account = get_post('bank_account');
            
            $total_refund = input_num('total_refund');
            $discount = input_num('discount');
            $commission = input_num('commission');
            $round_off = input_num('round_off');

            $allocs = collect($_POST['alloc'] ?? [])->where('this_alloc', '>', 0)->all();

            if (sql2date(date2sql($tran_date)) != $tran_date) {
                return AxisPro::ValidationError("The date '".e($tran_date)."' is not valid");
            }

            if (!is_date_in_fiscalyear($tran_date)) {
                return AxisPro::ValidationError("The date '".e($tran_date)."' is not in Fiscalyear or is closed for further entry");
            }

            if (empty($customer_id) || blank($customer = Customer::find($customer_id))) {
                return AxisPro::ValidationError("Customer cannot be empty. Please choose the customer");
            }

            if (empty($allocs)) {
                return AxisPro::ValidationError("Please choose at least one transaction to refund");
            }

            if (empty($bank_account) || !BankAccount::whereId($bank_account)->exists()) {
                return AxisPro::ValidationError("Bank account cannot be empty!");
            }

            begin_transaction();

            if ($trans_nos = array_filter(array_column($allocs, 'trans_no'))) {
                $payments = db_query(
                    "SELECT
                        dt.`type`,
                        dt.trans_no,
                        dt.reference,
                        dt.debtor_no,
                        dt.tran_date,
                        round(abs(dt.alloc), $dec) as alloc,
                        round(abs(dt.ov_amount + dt.ov_gst + dt.ov_freight + dt.ov_freight_tax + dt.ov_discount), $dec) as total
                    FROM 0_debtor_trans as dt
                    WHERE
                        (dt.ov_amount + dt.ov_gst + dt.ov_freight + dt.ov_freight_tax + dt.ov_discount) <> 0
                        AND dt.type = ".db_escape(ST_CUSTPAYMENT)."
                        AND dt.trans_no in (". implode(',', array_map('db_escape', $trans_nos)) .")
                    FOR UPDATE",
                    "Could not query the payment details for update"
                )->fetch_all(MYSQLI_ASSOC);

                $payments = collect($payments)->keyBy('trans_no');
                foreach ($allocs as $alloc) {
                    if (empty($pmt = $payments->get($alloc['trans_no']))) {
                        return AxisPro::ValidationError(
                            sprintf("Allocation Error: Payment %s has been voided. Please refresh the page and redo the refund again", $alloc['reference'])
                        );
                    }

                    if (floatcmp($alloc['this_alloc'] + $pmt['alloc'], $pmt['total']) > 0) {
                        return AxisPro::ValidationError(sprintf("Allocation Error: This request will result in an over allocation against payment %s", $alloc['reference']));
                    }
                }
            }

            $trans_type = ST_CUSTREFUND;
            $updated_by = $authUser->id;
            $created_by = $updated_by;
            $transacted_at = now()->toDateTimeString();
            $dimension_id = $authUser->dflt_dimension_id;
            $refund_no = write_customer_payment(
                0, 
                $customer_id,
                $branch_id,
                $bank_account, 
                $tran_date, 
                MetaReference::getNext(
                    $trans_type,
                    null,
                    array(
                        'customer' => $customer_id,
                        'date' => $tran_date,
                        'dimension' => $dimension_id
                    ),
                    true
                ),
                $total_refund - $discount, 
                $discount, 
                '', 
                0, 
                0, 
                0, 
                null, 
                $dimension_id, 
                0, 
                $round_off,
                null,
                null,
                $created_by,
                0,
                $transacted_at,
                $customer->name,
                $customer->tax_id,
                $customer->mobile,
                $customer->debtor_email,
                null,
                (new Cart($trans_type))->randomNumber(),
                '',
                $commission,
                $trans_type
            );

            foreach ($allocs as $alloc) {
                add_cust_allocation($alloc['this_alloc'], $alloc['type'], $alloc['trans_no'], $trans_type, $refund_no, $customer_id, $alloc['tran_date'], $tran_date);
                update_debtor_trans_allocation($alloc['type'], $alloc['trans_no'], $customer_id, $alloc['tran_date']);
            }

            update_debtor_trans_allocation($trans_type, $refund_no, $customer_id, $tran_date);

            commit_transaction();

            runAutomaticAllocation($customer_id);

            $customerRefund = CustomerTransaction::where('type', $trans_type)
                ->where('trans_no', $refund_no)
                ->where('debtor_no', $customer_id)
                ->first();

            event(new CustomerRefunded($customerRefund));

            return AxisPro::SendResponse(["status" => "OK", "msg" => "Refund Processed", 'refund_no' => $refund_no], $format);
        }
        
        catch (Exception $e) {
            return AxisPro::catchException($e);
        }
    }



    public function generateUrlForRefundPrint($id)
    {
        return $GLOBALS['SysPrefs']->project_url . "/ERP/voucher_print/?voucher_id=$id-1";
    }


    public function list_refunds($format = 'json')
    {
        $customer_id = $_GET['customer_id'];
        $inv_no = $_GET['inv_no'];

        $sql = "SELECT a.ref,abs(a.amount) as amount,a.trans_date,a.person_id,b.trans_no,d.name
                FROM 0_bank_trans AS a
                INNER JOIN 0_debtor_trans AS b ON a.trans_no=b.trans_no
                INNER JOIN 0_debtors_master AS d ON d.debtor_no=a.person_id
                WHERE a.refund_process='1'  ";
        if(!empty($customer_id))
        {
            $sql .= " AND a.person_id='" . $customer_id . "'";
        }

        if ($inv_no != '') {
            $sql .= " AND b.reference='" . $inv_no . "'";
        }
        $sql .= " GROUP BY a.trans_no";

        if(empty($customer_id))
        {
            $sql .= " LIMIT 10";
        }

        $result = db_query($sql);

        $return_result = [];

        while ($myrow = db_fetch_assoc($result)) {

            /*  $qry="select display_customer,contact_person from 0_debtor_trans
                    where trans_no='".$myrow['trans_no']."' and `type`='10'";
              $customer_data=db_fetch(db_query($qry));*/


            $myrow['print_url'] = $this->generateUrlForRefundPrint($myrow['trans_no']);

            $return_result[] = $myrow;
        }

        return AxisPro::SendResponse($return_result, $format);


    }



    public function get_customer_advances($format = 'json')
    {
        try {
            $from_date = begin_fiscalyear();
            
            if (user_check_access('SA_MULTIFISCALYEARS')) {
                $prev_fiscal_year_begin = FiscalYear::where('end', '<', date2sql($from_date))->orderBy('end', 'desc')->value('begin');
                if ($prev_fiscal_year_begin) {
                    $from_date = sql2date($prev_fiscal_year_begin);
                }
            }

            $to_date = end_fiscalyear();
            $customer_id = $_GET['customer_id'];
            $rcpt_no = trim($_GET['rcpt_no']);
            $inv_no = trim($_GET['inv_no']);

            if (empty($customer_id) && empty($rcpt_no) && empty($inv_no)) {
                return AxisPro::SendResponse([], $format);
            }

            $sql = get_sql_for_customer_allocation_inquiry($from_date, $to_date, $customer_id, 3, false);

            if (!empty($rcpt_no))
                $sql .= " AND trans.reference = " . db_escape($rcpt_no) . " ";

            $result = db_query($sql);

            $getAllocatedInvoices = function($row) {
                $allocatedInvoices = db_query(
                    "SELECT
                        GROUP_CONCAT(invoice.`reference` SEPARATOR ', ') AS invoices
                    FROM `0_cust_allocations` alloc
                    LEFT JOIN `0_debtor_trans` invoice ON
                        alloc.trans_type_to = invoice.type
                        AND alloc.trans_no_to = invoice.trans_no
                        AND alloc.person_id = invoice.debtor_no
                    WHERE
                        alloc.person_id = {$row['debtor_no']}
                        AND alloc.trans_no_from = {$row['trans_no']}
                        AND invoice.type = ".db_escape(ST_SALESINVOICE),
                    "Could not get invoice numbers"
                )->fetch_assoc();

                $allocatedInvoices = $allocatedInvoices ? $allocatedInvoices['invoices'] : "";

                return $allocatedInvoices;
            };

            $data = [];
            while ($myrow = db_fetch_assoc($result)) {
                $invoiceNumbers = $getAllocatedInvoices($myrow);
                $allocated_invoices = explode(", ", $invoiceNumbers);

                if (!empty($inv_no) && !in_array($inv_no, $allocated_invoices)) {
                    continue;
                }

                $myrow['invoice_numbers'] = $invoiceNumbers;
                $myrow['tran_date'] = sql2date($myrow['tran_date']);
                $data[] = $myrow;
            }

            $customer = count($data) ? collect($data[0])->only(['debtor_no', 'formatted_name']) : null;

            return AxisPro::SendResponse(compact('customer', 'data'), $format);
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }
    }

    public function getAllCompanies($format = 'json')
    {

        try {

            $return_array = [
            [
            'value' => 'Direct Axis Tech',
            'id' => 1,
            ],
            [
            'value' => 'Daxis',
            'id' => 2,
            ]
            ];

            return AxisPro::SendResponse($return_array, $format);


        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }
    }

    public function get_next_customer_id()
    {

        global $SysPrefs;

        $customer_id_prefix = $SysPrefs->prefs['customer_id_prefix'];

        if (empty($customer_id_prefix)) $customer_id_prefix = "";

        $sql = "select 
        LPAD(debtor_ref+1, 4, '0') as cust_id 
        from 0_debtors_master order by debtor_no desc limit 1 ";


        // display_error($sql);
        $res = db_fetch(db_query($sql));
        return $res['cust_id'] ?: $customer_id_prefix . '0001';

    }

    public function addCustomerBasicInfo($format = 'json')
    {

        try {

            begin_transaction();
            $cust_ref = $this->get_next_customer_id();

            $CustName = $_POST['cust_name'];
            $cust_mobile = $_POST['cust_mobile'];
            $cust_email = $_POST['cust_email'];


            $sql = "select count(*) as cnt from 0_debtors_master where mobile=" . db_escape($cust_mobile);
            $get = db_query($sql);
            $mobile_duplicate = db_fetch($get);

            $errors = [];
            if (empty($CustName))
                $errors['cust_name'] = "Please enter customer name";

            if (empty($cust_mobile))
                $errors['cust_mobile'] = "Please enter customer mobile";
            else if (strlen($cust_mobile) <> 10)
                $errors['cust_mobile'] = "Mobile number must have 10 digits Eg: 0512345678";
            else if ($mobile_duplicate['cnt'] > 0)
                $errors['cust_mobile'] = "This mobile number is already exists";

            if (!empty($errors))
                return AxisPro::SendResponse(['status' => 'FAIL', 'msg' => 'VALIDATION_FAILED', 'data' => $errors]);

            add_customer($CustName, $cust_ref, $address = "", $tax_id = 1, $curr_code = "AED",
                $dimension_id = 0, $dimension2_id = 0, $credit_status = 1, $payment_terms = 4, $discount = 0, $pymt_discount = 0,
                $credit_limit = 1000, $sales_type = 1, $notes = '', $cust_mobile, $cust_email);

            $selected_id = db_insert_id();

            add_branch($selected_id, $CustName, $cust_ref,
                $address = "", 0, 2, 1, '',
                get_company_pref('default_sales_discount_act'), get_company_pref('debtors_act'), get_company_pref('default_prompt_payment_act'),
                'DEF', '', 0, 1, '', null);

            $selected_branch = db_insert_id();

            add_crm_person($cust_ref, $CustName, '', '',
                $cust_mobile, '', '', $cust_email, '', '');

            $pers_id = db_insert_id();
            add_crm_contact('cust_branch', 'general', $selected_branch, $pers_id);

            add_crm_contact('customer', 'general', $selected_id, $pers_id);

            commit_transaction();

            return AxisPro::SendResponse(["status" => "OK", "msg" => "New Customer Added"], $format);


        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }
    }


    public function addSubCustomer($format = 'json')
    {
        try {

            begin_transaction();
            $cust_id = $_POST['comp_cust_id'];
            $comp_name = $_POST['comp_name'];
            $comp_email = $_POST['comp_email'];
            $comp_trn = $_POST['comp_trn'];
            $comp_iban = $_POST['comp_iban'];
            $created_by = $_SESSION['wa_current_user']->user;

            $errors = [];
            if (empty($cust_id))
                $errors['cust_id'] = "Please select a customer";

            if (empty(trim($comp_name)))
                $errors['comp_name'] = "Please select a company name";

            if (!empty($errors))
                return AxisPro::SendResponse(['status' => 'FAIL', 'msg' => 'VALIDATION_FAILED', 'data' => $errors]);

            $sql = (
                "INSERT INTO
                    `0_sub_customers` (
                        customer_id,
                        `name`,
                        created_by,
                        mobile,
                        email,
                        trn,
                        iban
                    ) 
                VALUES (
                    " . db_escape($cust_id) . ",
                    " . db_escape($comp_name) . ",
                    $created_by,
                    NULL,
                    " . db_escape($comp_email) . ",
                    " . db_escape($comp_trn) . ",
                    " . db_escape($comp_iban) . "
                )"
            );
            db_query($sql, "Could not add subcustomer");
            commit_transaction();

            return AxisPro::SendResponse(["status" => "OK", "msg" => "New Customer Company Added"], $format);


        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }
    }

    public function get_sub_customers($format = 'json')
    {
        try {
            $customer_id = db_escape($_GET['customer_id']);

            $subCustomers = db_query(
                "SELECT
                    * FROM
                `0_sub_customers`
                WHERE customer_id = {$customer_id}",
                "Could not retrieve the list of sub customers"
            )->fetch_all(MYSQLI_ASSOC);

            return AxisPro::SendResponse(["data" => $subCustomers], $format);
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }

    public function get_sub_ledgers($format = 'json') 
    {
        if (empty($_GET['ledger']) || !($sl_type = is_subledger_account($_GET['ledger']))) {
            return AxisPro::SendResponse([
                "status"    => "FAIL",
                "msg"       => "Please provide a parent ledger code"
            ]);
        }

        $account = $_GET['ledger'];
        $type = get_subledger_person_type($sl_type);

        if ($type == PT_SUPPLIER) {
            $sql = "SELECT supplier_id as id, CONCAT(supp_ref, ' - ', supp_name) as name
            FROM "
            .TB_PREF."suppliers s
            WHERE NOT s.inactive AND s.payable_account=".db_escape($account);
        }
        
        else if ($type == PT_EMPLOYEE) {
            $sql = "SELECT id, CONCAT(emp_ref, ' - ', name) as name FROM 0_employees WHERE status = 1";
        }
        
        else if ($type == PT_USER) {
            $sql = "SELECT id, CONCAT(user_id, ' - ', real_name) as name FROM 0_users WHERE inactive = 0";
        }

        else if ($type == PT_SALESMAN) {
            $sql = "SELECT salesman_code as id, salesman_name as name FROM 0_salesman WHERE 1=1";
        }
        
        else if ($type == PT_SUBLEDGER) {
            $sql = "SELECT code as id, CONCAT(code, ' - ', name) as name FROM 0_sub_ledgers WHERE ledger_id = ".db_escape($account);
        }

        $result = ($type == PT_CUSTOMER)
            ? []
            : db_query($sql, "Could not query for subledgers")->fetch_all(MYSQLI_ASSOC);

        AxisPro::SendResponse([
            'data' => $result,
            'person_type' => $type,
            'subledger_type' => $sl_type
        ], $format);
    }

    public function getCustomersByMobile($format = 'json')
    {
        try {
            $mobile = $_REQUEST['mobile'];
            if (preg_match(UAE_MOBILE_NO_PATTERN, $mobile)) {
                $mobile = preg_replace(UAE_MOBILE_NO_PATTERN, "$2", $mobile);
                $sql = (
                    "SELECT 
                        debtor_no,
                        debtor_ref,
                        `name`,
                        tax_id,
                        debtor_email,
                        mobile,
                        contact_person,
                        iban_no
                    FROM 0_debtors_master
                    WHERE
                        mobile LIKE '%{$mobile}'
                        AND inactive = 0"
                );

                $customers = db_query(
                    $sql,
                    "Could not get the list of customers"
                )->fetch_all(MYSQLI_ASSOC);

                return AxisPro::SendResponse([
                    "status" => 200,
                    "data" => $customers
                ], $format);
            } else {
                return AxisPro::SendResponse([
                    "status" => 422,
                    "message" => "The mobile number is not a valid UAE number.",
                    "data" => []
                ], $format);
            }

        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }
    }

    public function getPendingServiceRequests($format = "json")
    {

        try {

            $cost_center_id = $_GET['cost_center_id'];

            $where = "";

            if(!empty($cost_center_id))
                $where .= " AND req.cost_center_id=$cost_center_id";

            $sql = (
                "SELECT
                    req.id,
                    req.display_customer,
                    req.mobile,
                    req.token_number,
                    ifnull(req.reference,'') reference,
                    SUM(
                        (
                            if(dim.is_invoice_tax_included, 0, item.unit_tax)
                            + item.price
                            + item.govt_fee
                            + item.bank_service_charge
                            - item.discount
                        ) * item.qty
                    ) AS amount,
                    usr.real_name AS staff_name  
                FROM 0_service_requests req 
                LEFT JOIN 0_service_request_items item ON item.req_id = req.id 
                LEFT JOIN 0_users usr ON usr.id=req.created_by 
                LEFT JOIN 0_dimensions dim ON dim.id=req.cost_center_id
                WHERE
                    date(req.created_at) = " . db_escape(date2sql(Today())) . "
                    AND req.active_status = 'ACTIVE' $where
                GROUP BY  item.req_id
                HAVING SUM(!isnull(item.invoiced_at)) != count(item.id)"
            );

            $result = db_query($sql);

            $return_result = [];
            while ($myrow = db_fetch($result)) {
                $return_result[] = $myrow;
            }

            return AxisPro::SendResponse($return_result, $format);


        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }

    public function getPaymentSummaryByMethod($format = 'json')
    {
        $user_date_format = getDateFormatInNativeFormat();
        if (
            !($dt = DateTime::createFromFormat($user_date_format, $_GET['trans_date']))
            || $dt->format($user_date_format) != $_GET['trans_date']
        ) {
            return AxisPro::SendResponse(["status" => "FAIL", "msg" => "The date is not valid"], $format);
        } else {
            $trans_date = $dt->format(DB_DATE_FORMAT);
        }

        if (!preg_match('/^[1-9][0-9]{0,15}$/', $_GET['user_id'])) {
            return AxisPro::SendResponse(["status" => "FAIL", "msg" => "The user id is not valid"], $format);
        }

        $sql = (
            "SELECT 
                SUM(
                    ROUND(dt.ov_amount + dt.credit_card_charge + dt.round_of_amount, 2)
                ) AS total,
                dt.payment_method 
            FROM 0_debtor_trans dt
            WHERE dt.type = 12 
                AND (dt.ov_amount + dt.ov_gst + dt.ov_freight + dt.ov_freight_tax + dt.ov_discount) <> 0 
                AND dt.tran_date = '$trans_date'
                AND dt.created_by = {$_GET['user_id']}
            GROUP BY dt.payment_method"
        );

        $summary = db_query($sql)->fetch_all(MYSQLI_ASSOC);
        if(!empty($summary)) {
            $summary = array_column($summary, 'total', 'payment_method');
        }

        if (empty($summary['Cash'])){
            $summary['Cash'] = 0.00;
        }
        if (empty($summary['CreditCard'])){
            $summary['CreditCard'] = 0.00;
        }
        if (empty($summary['BankTransfer'])){
            $summary['BankTransfer'] = 0.00;
        }
        if (empty($summary['OnlinePayment'])) {
            $summary['OnlinePayment'] = 0.00;
        }

        return AxisPro::SendResponse([
            "status" => "OK",
            "data"   => $summary
        ], $format);
    }

    public function getServiceRequests()
    {    
        $authUser = authUser();
        $havingClause = 'TRUE';
        $conditions = "req.active_status = 'ACTIVE'";
        $salesInvoice = ST_SALESINVOICE;

        if (!empty($_POST['fl_start_date'])) {
            $conditions .= " AND DATE(req.created_at) >= " . db_escape(date2sql($_POST['fl_start_date']));
        }

        if (!empty($_POST['fl_end_date'])) {
            $conditions .= " AND DATE(req.created_at) <= " . db_escape(date2sql($_POST['fl_end_date']));
        }

        if (!user_check_access('SA_SRVREQLSTALL')) {
            $conditions .= " AND req.cost_center_id = " . $authUser->dflt_dimension_id;
            $conditions .= " AND req.created_by = " . $authUser->id;
        }

        if (!empty($_POST['fl_status'])) {
            switch ($_POST['fl_status']) {
                case 'NOT_FULLY_COMPLETED':
                    $havingClause .= " AND sum(!isnull(item.invoiced_at)) != count(item.id)";
                    break;

                case 'PENDING':
                    $havingClause .= " AND sum(!isnull(item.invoiced_at)) = 0";
                    break;
                    
                case 'PARTIALLY_COMPLETED':
                    $havingClause .= " AND "
                        . "("
                            . "sum(!isnull(item.invoiced_at)) != 0"
                            . " AND sum(!isnull(item.invoiced_at)) != count(item.id)"
                        . ")";
                    break;
                
                case 'COMPLETED':
                    $havingClause .= " AND "
                        . "("
                            . "sum(!isnull(item.invoiced_at)) != 0"
                            . " AND sum(!isnull(item.invoiced_at)) = count(item.id)"
                        . ")";
                    break;
                
                case 'WITHOUT_TRANS_COMPLETED':
                    $havingClause .= " AND "
                        . "("
                            . "sum(!isnull(item.invoiced_at)) != 0"
                            . " AND sum(detail.transaction_id <> '') != count(item.id)"
                            . " AND sum(!isnull(item.invoiced_at)) = count(item.id)"
                        . ")";
                    break;
                
                case 'TRANS_COMPLETED':
                    $havingClause .= " AND "
                        . "("
                            . "sum(!isnull(item.invoiced_at)) != 0"
                            . " AND sum(detail.transaction_id <> '') = count(item.id)"
                            . " AND sum(!isnull(item.invoiced_at)) = count(item.id)"
                        . ")";
                    break;
            }
        }

        $sql = (
            "SELECT
                req.id,
                ifnull(req.reference, '') reference,
                ifnull(req.memo, '') memo,
                req.barcode,
                req.token_number,
                sum(!isnull(item.invoiced_at)) != 0 as is_invoiced_once,
                sum(!isnull(item.invoiced_at)) = count(item.id) as is_fully_invoiced,
                req.customer_id,
                req.display_customer,
                ifnull(trans.reference, '') invoice_number,
                ifnull(GROUP_CONCAT(nullif(detail.transaction_id, '')), '') transaction_ids,
                req.contact_person,
                req.iban,
                req.mobile,
                req.email,
                req.cost_center_id,
                req.created_by,
                req.created_at,
                cust.name customer_name,
                usr.user_id,
                usr.real_name user_real_name,
                CASE
                    WHEN sum(!isnull(item.invoiced_at)) = 0 THEN 'Pending'
                    WHEN (
                        sum(!isnull(item.invoiced_at)) != 0
                        AND sum(!isnull(item.invoiced_at)) != count(item.id)
                    ) THEN 'Partially Completed'
                    WHEN (
                        sum(!isnull(item.invoiced_at)) = count(item.id)
                        AND sum(detail.transaction_id <> '') != count(item.id)
                    ) THEN 'Completed'
                    WHEN (
                        sum(!isnull(item.invoiced_at)) = count(item.id)
                        AND sum(detail.transaction_id <> '') = count(item.id)
                    ) THEN 'Completed With Transaction'
                END as req_status
            FROM 0_service_requests req
            LEFT JOIN 0_service_request_items as item ON
                item.req_id = req.id
            LEFT JOIN 0_debtors_master cust ON
                cust.debtor_no = req.customer_id
            LEFT JOIN 0_debtor_trans_details detail ON
                detail.srv_req_line_id = item.id
                AND detail.debtor_trans_type = {$salesInvoice}
                AND detail.quantity <> 0
            LEFT JOIN 0_debtor_trans trans ON
                trans.service_req_id = req.id
                AND trans.`type` = detail.debtor_trans_type
                AND trans.trans_no = detail.debtor_trans_no
                AND (trans.ov_amount + trans.ov_gst + trans.ov_discount + trans.ov_freight + trans.ov_freight_tax) <> 0
            LEFT JOIN 0_users usr ON
                usr.id = req.created_by
            WHERE {$conditions}
            GROUP BY req.id
            HAVING {$havingClause}
            ORDER BY req.created_at DESC"
        );

        $totalCount = db_query(
            "select count(*) as cnt from ($sql) as tmpTable",
            "Couldn't query for total number of service requests"
        )->fetch_assoc()['cnt'] ?? 0;

        $page = $_GET['page'] ?? 1;
        $perPage = 50;
        $offset = ($page * $perPage) - $perPage;
        $sql .= " LIMIT $perPage OFFSET $offset";

        $report = db_query($sql, "Could not query for service requests")->fetch_all(MYSQLI_ASSOC);

        return AxisPro::SendResponse([
            'rep' => $report,
            'total_rows' => $totalCount,
            'pagination_link' => AxisPro::paginate($totalCount),
            'aggregates' => ['cnt' => $totalCount]
        ]);
    }


public function getServiceRequest($id = null, $format = 'json')
{

    try {

        if (empty($id))
            $id = $_GET['id'];

        $sql = "SELECT * FROM 0_service_requests where id = $id";
        $result = db_fetch_assoc(db_query($sql));
        $req = $result;

        $sql = "SELECT items.* 
        FROM 0_service_request_items items 
        where items.req_id = $id ";


        $result = db_query($sql);
        $items = [];
        while ($myrow = db_fetch_assoc($result)) {

            $items[] = $myrow;
        }

        return AxisPro::SendResponse(['req' => $req, 'items' => $items], $format);
    } catch (Exception $e) {
        return AxisPro::catchException($e);
    }


}


public function get_customers_list()
{
    $category_ids = array_column(
        db_query(
            "SELECT distinct item_id as item_id from 0_customer_discount_items",
            "Could not query for item categories having discount or commissions"
        )->fetch_all(MYSQLI_ASSOC),
        'item_id'
    );

    $selects = [
        'dm.*',
        "concat_ws(' - ', dm.debtor_ref, dm.name) as formatted_name",
        "ifnull(sm.salesman_name, 'NA') as salesman_name"
    ];

    foreach ($category_ids as $c_id) {
        $selects[] = "sum(if(di.item_id = {$c_id}, di.discount, 0)) as {$c_id}_discount";
        $selects[] = "sum(if(di.item_id = {$c_id}, di.customer_commission, 0)) as {$c_id}_commission";
    }

    $result = db_query(
        "SELECT
            ".implode(', ', $selects)."
        FROM 0_debtors_master as dm
        LEFT JOIN 0_salesman as sm on sm.salesman_code = dm.salesman_id
        LEFT JOIN 0_customer_discount_items as di on di.customer_id = dm.debtor_no
        GROUP BY
            dm.debtor_no,
            sm.salesman_code",
        "Could not query for customer information report"
    )->fetch_all(MYSQLI_ASSOC);

    $categories = db_query(
        "select
            category_id as id,
            `description`
        from 0_stock_category
        where
            category_id in (".(implode(',', $category_ids) ?: -1).")
        order by
            `description`",
        "Could not query for category details"
    )->fetch_all(MYSQLI_ASSOC);

    return AxisPro::SendResponse([
        "data" => $result,
        "categories" => $categories
    ]);
}


public function getPermittedSearchItemsList($format = "json")
{

    try {

        $user_id = $_SESSION['wa_current_user']->user;
        $user_info = get_user($user_id);
        $main_cat_id = $_GET['main_cat_id'] ?? 0;
        $sub_cat_id = $_GET['sub_cat_id'] ?? 0;
        $user_dimension = $user_info['dflt_dimension_id'];
        $permitted_cats = $user_info['permitted_categories'] ?? -1;

        $selected_dim = db_escape($_GET['cost_center'] ?? -1);

        if (empty($user_info['allowed_dims'])) {
            $allowed_dims = $user_dimension;
        } else {
            $allowed_dims = $user_info['allowed_dims'] . ',' . $user_dimension;
        }

        $sql = (
            "SELECT
                items.stock_id,
                cat.description category_name,
                items.description,
                items.long_description,
                IFNULL(price.price, 0) service_fee,
                ( items.govt_fee + items.bank_service_charge + items.bank_service_charge_vat ) total_govt_fee,
                ( IFNULL(price.price, 0) + items.govt_fee ) total_display_fee
            FROM 0_stock_master items 
                LEFT JOIN 0_prices price ON price.stock_id=items.stock_id 
                LEFT JOIN 0_stock_category cat ON cat.category_id=items.category_id 
                LEFT JOIN 0_subcategories subcat ON subcat.id = items.sub_category_id
            WHERE items.inactive=0
                AND items.category_id IN ({$permitted_cats})
                AND json_contains(cat.belongs_to_dep, json_quote({$selected_dim}))
                AND {$selected_dim} IN ({$allowed_dims})"
        );

        if(!empty($main_cat_id)){
            $sql .= " AND items.category_id = " . db_escape($main_cat_id);
        }

        if(!empty($sub_cat_id)){
            $sql .= " AND items.sub_category_id = " . db_escape($sub_cat_id);
        }

        $get = db_query($sql);

        $items = [];
        while ($myrow = db_fetch_assoc($get)) {

            $items[] = $myrow;
        }


        return AxisPro::SendResponse($items, $format);


    } catch (Exception $e) {
        return AxisPro::catchException($e);
    }


}


public function getPermittedCategoriesFromDepartmentForInvoicing($format = 'json')
{
    try {

        global $path_to_root;

        $user_id = $_SESSION['wa_current_user']->user;
        $user_info = get_user($user_id);
        $user_dimension = $user_info['dflt_dimension_id'];
        $permitted_cats = $user_info['permitted_categories'] ?? -1;

        $selected_dim = db_escape($_GET['cost_center'] ?? -1);

        if (empty($user_info['allowed_dims'])) {
            $allowed_dims = $user_dimension;
        } else {
            $allowed_dims = $user_info['allowed_dims'] . ',' . $user_dimension;
        }

        $sql = (
            "SELECT
                *
            FROM 0_stock_category
            WHERE 
                (NOT inactive)
                AND category_id IN ({$permitted_cats})
                AND json_contains(belongs_to_dep, json_quote({$selected_dim}))
                AND {$selected_dim} IN ({$allowed_dims})"
        );
        $result = db_query(
            $sql,
            "Could not retrieve permitted categories"
        );

        $categoreis = [];
        $logo_dir = "ERP/themes/daxis/images/";
        while ($myrow = db_fetch_assoc($result)) {
            $logo = $logo_dir . "cat_logo_" . $myrow["description"] . ".png";
            if (!file_exists($logo)) {
                $logo = "ERP/inventory/inquiry/default_category_image.png";
            }
            $myrow['category_logo'] = $logo;

            $categoreis[] = $myrow;
        }

        return AxisPro::SendResponse($categoreis, $format);

    } catch (Exception $e) {
        return AxisPro::catchException($e);
    }
}


public function getTopLevelSubcategories($format='json') {

    try {

        $category_id = $_GET['cat_id'];

        $sql = "select sub.*,cat.description category_name from 0_subcategories sub 
        LEFT JOIN 0_stock_category cat ON cat.category_id=sub.main_cat_id where main_cat_id=$category_id and parent_sub_cat_id=0";

        $get = db_query($sql);

        $return_result = [];
        $logo_dir = "ERP/themes/daxis/images/";

        while ($myrow = db_fetch_assoc($get)) {

            $logo = $logo_dir . "cat_logo_" . $myrow["category_name"] . ".png";

            if (!file_exists($logo)) {
                $logo = "ERP/inventory/inquiry/default_category_image.png";
            }

            $myrow['category_logo'] = $logo;

            $return_result[] = $myrow;
        }

        return AxisPro::SendResponse($return_result, $format);

    } catch (Exception $e) {
        return AxisPro::catchException($e);
    }

}


public function getChildLevelSubcategories($format='json') {

    try {

        $id = $_GET['id'];

        $sql = "select sub.*,cat.description category_name from 0_subcategories sub 
        LEFT JOIN 0_stock_category cat ON cat.category_id=sub.main_cat_id where parent_sub_cat_id=$id ";

//            dd($sql);

        $get = db_query($sql);

        $return_result = [];
        $logo_dir = "ERP/themes/daxis/images/";

        while ($myrow = db_fetch_assoc($get)) {

            $logo = $logo_dir . "cat_logo_" . $myrow["category_name"] . ".png";

            if (!file_exists($logo)) {
                $logo = "ERP/inventory/inquiry/default_category_image.png";
            }

            $myrow['category_logo'] = $logo;

            $return_result[] = $myrow;
        }

        return AxisPro::SendResponse($return_result, $format);

    } catch (Exception $e) {
        return AxisPro::catchException($e);
    }

}

    // function get_invoices(){
    //     $sql = "SELECT t.trans_no as trans_no ,t.reference as ref ,t.tran_date as trans_date, t.type as type FROM `0_debtor_trans` t LEFT JOIN `0_voided` v ON t.trans_no=v.id AND v.type=10 WHERE ISNULL(v.`memo_`) AND t.trans_no >= '1' AND t.trans_no <= '999999' AND t.`type` = '10' GROUP BY t.type, t.trans_no ORDER BY t.trans_no DESC";
    // }
private function get_invoice_records($params){
    // $columns = array(
    //     0 => 't.trans_no',
    //     1 => 't.reference',
    //     2 => 't.tran_date',
    //     3 => 'cust.name',
    //     4 => 't.ov_amount+t.ov_gst+t.ov_freight+t.ov_freight_tax+t.ov_discount',
    //     5 => 'total_received',
    //     6 => 'cust_pay_reference_and_method',
    //     // 7 => 'pdc_list.from_type',
    //     // 8 => 'pdc_list.is_processed',
    //     // 9 => 'created_by_table.user_id',
    //     // 10 => 'processed_by_table.user_id',
    //     );
    $where_condition = $sqlTot = $sqlRec = "";
        // if(!empty($params['search']['value']) || $params['status'] || $params['type'] ){
        //     $where_condition .= " WHERE ";
        // }
    if (!empty($params['search']['value'])) {
        $where_condition .= " AND ( t1.trans_no LIKE '%" . $params['search']['value'] . "%' ";
            $where_condition .= " OR t1.reference LIKE '%" . $params['search']['value'] . "%' ";
            $where_condition .= " OR t1.tran_date LIKE '%" . $params['search']['value'] . "%' ";
            $where_condition .= " OR cust.name LIKE '%" . $params['search']['value'] . "%' )";
            // $where_condition .= " OR cust_pay_reference_and_method LIKE '%" . $params['search']['value'] . "%' )";
}
        //add custom filter here
if ($params['reference_no']) {
    $where_condition .= " AND t1.reference = " . db_escape($params['reference_no']) . " ";
}
// if ($params['trans_date']) {
//     $where_condition .= " AND t.tran_date = " . db_escape(date2sql($params['trans_date'])) . " ";
// }
if ($params['trans_date_from'] && $params['trans_date_to']) {
    $trans_date_to = date2sql($params['trans_date_to']) ;
    $trans_date_from = date2sql($params['trans_date_from']) ;
    if($trans_date_from > $trans_date_to){
        $temp = $trans_date_to;
        $trans_date_to = $trans_date_from;
        $trans_date_from = $temp;
    }
    $trans_date_from .= " 00:00:00";
    $trans_date_to .= " 23:59:59";
    $where_condition .= " AND t1.created_at >= ".db_escape($trans_date_from)." AND t1.created_at <= ".db_escape($trans_date_to)." ";
}
if ($params['customer_id']) {
    $where_condition .= " AND t1.debtor_no = " . db_escape($params['customer_id']) . " ";
}

if ($params['customer_type']) {
    $where_condition .= " AND cust.customer_type = " . db_escape($params['customer_type']) . " ";
}

if ($params['trans_no_from'] && $params['trans_no_to']) {
    $trans_no_from = min($params['trans_no_from'],$params['trans_no_to']);
    $trans_no_to = max($params['trans_no_from'],$params['trans_no_to']);
    $where_condition .= " AND t1.trans_no >= ".db_escape($trans_no_from)." AND t1.trans_no <= ".db_escape($trans_no_to)." ";
}

$sql_query="SELECT cust.`name` AS DebtorName,cust.`customer_type` as customer_type,t1.trans_no as trans_no ,t1.reference as ref ,t1.tran_date as trans_date,t1.created_at, t1.`type` AS `type`,
ROUND(t1.ov_amount+t1.ov_gst+t1.ov_freight+t1.ov_freight_tax+t1.ov_discount,2) AS Total,
ROUND(SUM(IF(t2.payment_method = 'Cash', alloc.amt, 0)),2) AS Cash,
ROUND(SUM(IF(t2.payment_method = 'CreditCard', alloc.amt, 0)),2) AS CreditCard,
ROUND(SUM(IF(t2.payment_method = 'BankTransfer', alloc.amt, 0)),2) AS BankTransfer,
ROUND(SUM(IF(t2.payment_method NOT IN ('Cash', 'CreditCard','BankTransfer','CreditCustomer'), 0, alloc.amt)),2) AS others,
ROUND(t1.alloc,2) AS total_received,
ROUND(t1.ov_amount+t1.ov_gst+t1.ov_freight+t1.ov_freight_tax+t1.ov_discount - t1.alloc,2) AS balance,
CASE WHEN ROUND(`t1`.`alloc`) >= ROUND(`t1`.`ov_amount` + `t1`.`ov_gst` + `t1`.`ov_freight` + `t1`.`ov_freight_tax` + `t1`.`ov_discount`) THEN 'Fully Paid'
WHEN `t1`.`alloc` = 0 THEN 'Not Paid'
WHEN ROUND(`t1`.`alloc`) < ROUND(`t1`.`ov_amount` + `t1`.`ov_gst` + `t1`.`ov_freight` + `t1`.`ov_freight_tax` + `t1`.`ov_discount`) THEN 'Partially Paid'
END AS payment_status
FROM 0_debtor_trans t1 
LEFT JOIN 0_cust_allocations alloc ON  t1.trans_no = alloc.trans_no_to AND t1.`type` = alloc.trans_type_to
LEFT JOIN 0_debtor_trans t2 ON  t2.trans_no = alloc.trans_no_from AND t2.`type` = alloc.trans_type_from
LEFT JOIN 0_debtors_master cust ON t1.debtor_no = cust.debtor_no
WHERE t1.`type` = 10 ";

$sqlTot .= $sql_query;
$sqlRec .= $sql_query;
if (isset($where_condition) && $where_condition != '') {
    $sqlTot .= $where_condition;
    $sqlRec .= $where_condition;
}
$sqlTot .= " GROUP BY alloc.trans_type_to,alloc.trans_no_to";
$sqlRec .= " GROUP BY alloc.trans_type_to,alloc.trans_no_to ORDER BY ref DESC ";

if(isset($params['start']) && isset($params['length'])){
    $sqlRec .=  " LIMIT ". $params['start'] . " ," . $params['length']; 
}
// if(isset($columns[$params['order'][0]['column']]) && isset($params['order'][0]['dir']) && isset($params['start']) && isset($params['length'])){
//     $sqlRec .=  " ORDER BY " . $columns[$params['order'][0]['column']] . "   " . $params['order'][0]['dir'] . "  LIMIT " . $params['start'] . " ," . $params['length'] . " ";    
// }
$sum_query = "Select sum(s.Total) as total_sum,sum(s.Cash) as pay_cash_sum,sum(s.CreditCard) as pay_creditcard_sum,sum(s.BankTransfer) as pay_bank_sum,sum(s.others) as pay_other_sum,sum(s.total_received) as total_received_sum,sum(s.balance) as total_balance_sum FROM ($sqlTot) as s";
$sums_data = db_fetch_assoc(db_query($sum_query));
$queryTot = db_query($sqlTot);
$totalRecords = db_num_rows($queryTot);
$queryRecords = db_query($sqlRec, "Error to Get the Post details.");
return [
'data' => $queryRecords,
'total_records' => $totalRecords,
// 'sql' => $sum_query,
'sums_data' => $sums_data,
];

}
        //datatable listing method for pdc
public function get_invoice_list_for_datatable()
{
    $params = $columns = $totalRecords = $data = array();
    $params = $_REQUEST;

    $queryRecords = $this->get_invoice_records($params);
    $today = Today();
    $erp_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $erp_link = substr($erp_link, 0, strpos($erp_link, "ERP"))."ERP/"; 
    $button_style = 'vertical-align:middle;width:18px;height:18px;border:0;';
    while ($row = db_fetch_assoc($queryRecords['data'])) {
        $actions = '';
        // $total = round($row['Total'],2);
        // $received = round($row['total_received'],2);
        // $balance = $total - $received;
        $created_at = $row['created_at'];
            //doing this way because sql2date function cannot return time and changing in helper page doesnt change here
        $created_date = sql2date($row['created_at']);
        $created_time = date('h:i:s A', strtotime($row['created_at']));
        $created_at = $created_date . " " . $created_time;     
        $print_params = "PARAM_0=".$row['trans_no']."-10&PARAM_1=".$row['trans_no']."-10&PARAM_2=&PARAM_3=0&PARAM_4=&PARAM_5=&PARAM_6=&PARAM_7=0&REP_ID=107";
        $print_link = $erp_link."invoice_print/index.php?".$print_params;
        $print_invoice = "<a id='inv_print' target='_blank' href='".$print_link."'><img src='".$erp_link."themes/daxis/images/print.png' style='".$button_style."' title='Print Invoice'></a>";
        // $actions .= "<a class='btn btn-primary btn-sm' target='_blank' href='".$print_link."' title='Print Invoice'><i class='fa fa-print'></i></a>";
        $gl_link = "<a target='_blank' href='".$erp_link."gl/view/gl_trans_view.php?type_id=10&amp;trans_no=".$row['trans_no']."' onclick='javascript:openWindow(this.href,this.target); return false;'><img src='".$erp_link."themes/daxis/images/gl.png' style='".$button_style."' title='GL'></a>";
        $data[] = array(
            // $row['trans_no'],
            $row['ref'],
            $created_date,
            $created_time,
            $row['DebtorName'],
            $row['payment_status'],
            $row['customer_type'],
            $row['Total'] ? $row['Total'] : '0.00',
            $row['Cash'] ? $row['Cash'] : '0.00',
            $row['CreditCard'] ? $row['CreditCard'] : '0.00',
            $row['BankTransfer'] ? $row['BankTransfer'] : '0.00',
            $row['others'] ? $row['others'] : '0.00',
            $row['total_received'] ? $row['total_received'] : '0.00',
            // $received,
            $row['balance'] ? $row['balance'] : '0.00',
            // $this->get_cust_payment_receipts_and_method_for_invoice_report($row['cust_pay_reference_and_method'],$return_gl_and_print_link=true),
            $gl_link,
            $print_invoice
            );
    }
    $json_data = array(
        "draw"            => intval($params['draw']),
        "recordsTotal"    => intval($queryRecords['total_records']),
        "recordsFiltered" => intval($queryRecords['total_records']),
        "data"            => $data,
        "params"          => $params,
        // "sql"             => $queryRecords['sql'],
        "total_sum"       => $queryRecords['sums_data']['total_sum'] ? round($queryRecords['sums_data']['total_sum'],2) : '0.00',
        "pay_cash_sum"       => $queryRecords['sums_data']['pay_cash_sum'] ? round($queryRecords['sums_data']['pay_cash_sum'],2) : '0.00',
        "pay_creditcard_sum"       => $queryRecords['sums_data']['pay_creditcard_sum'] ? round($queryRecords['sums_data']['pay_creditcard_sum'],2) : '0.00',
        "pay_bank_sum"       => $queryRecords['sums_data']['pay_bank_sum'] ? round($queryRecords['sums_data']['pay_bank_sum'],2) : '0.00',
        "pay_other_sum"       => $queryRecords['sums_data']['pay_other_sum'] ? round($queryRecords['sums_data']['pay_other_sum'],2) : '0.00',
        "total_balance_sum"       => $queryRecords['sums_data']['total_balance_sum'] ? round($queryRecords['sums_data']['total_balance_sum'],2) : '0.00',
        "total_received_sum"       => $queryRecords['sums_data']['total_received_sum'] ? round($queryRecords['sums_data']['total_received_sum'],2) : '0.00',
        );
    echo json_encode($json_data);
}
public function export_invoice_report()
{
    try {


        $trans_date_from = date2sql($_GET['trans_date_from']);
        $trans_date_to = date2sql($_GET['trans_date_to']);
        $reference_no = (isset($_GET['reference_no']) && $_GET['reference_no']!='null') ? $_GET['reference_no'] : '';
        $customer_id = (isset($_GET['customer_id']) && $_GET['customer_id']!='null') ? $_GET['customer_id'] : '';
        $trans_no_from = (isset($_GET['trans_no_from']) && $_GET['trans_no_from']!='null') ? $_GET['trans_no_from'] : '';
        $trans_no_to = (isset($_GET['trans_no_to']) && $_GET['trans_no_to']!='null') ? $_GET['trans_no_to'] : '';
        $export_type = (isset($_GET['export_type'])) ? $_GET['export_type'] : 'excel';
        $params = [
        'reference_no'=>$reference_no,
        'trans_date_from'=>$_GET['trans_date_from'],
        'trans_date_to'=>$_GET['trans_date_to'],
        'customer_id'=>$customer_id,
        'trans_no_from'=>$trans_no_from,
        'trans_no_to'=>$trans_no_to,
        ];

        $reference_no = $reference_no == '' ? 'All' : $reference_no;
        $trans_no_from = $trans_no_from == '' ? 'All' : $trans_no_from;
        $trans_no_to = $trans_no_to == '' ? 'All' : $trans_no_to; 
        if($trans_date_from > $trans_date_to){
            $temp = $trans_date_to;
            $trans_date_to = $trans_date_from;
            $trans_date_from = $temp;
        }


        if((abs(round((strtotime($trans_date_to) - strtotime($trans_date_from)) / 86400))<=31) && $_GET['trans_date_from']!='' && $_GET['trans_date_to']!=''){

            $customer_name = '';
            if ($customer_id) {
                $customer = "SELECT name,debtor_no FROM 0_debtors_master where debtor_no='" . $customer_id . "'";
                $customer_data = db_fetch_assoc(db_query($customer));
                $customer_name = $customer_data['name'];
            } 


            set_time_limit(0);
            $queryRecords = $this->get_invoice_records($params);
            $invoice_report_data = $queryRecords['data']->fetch_all(MYSQLI_ASSOC);
            $filename = "Invoices_Report";
            global $path_to_root;
            $page = 'A4';
            $orientation = 'L';
            if ($export_type == 'pdf') {
                include_once($path_to_root . "/reporting/includes/pdf_report.inc");
            } else {
                include_once($path_to_root . "/reporting/includes/excel_report.inc");
                // In excel columns are too much congested 
                $page = 'A3';
                $orientation = 'L';
            }
            if (!empty($invoice_report_data)) {

                $columns = [
                // [
                // "key"   => "trans_no",
                // "title" => _('#'),
                // "align" => "left",
                // "width" => 30
                // ],
                [
                "key"   => "si_no",
                "title" => _('SI'),
                "align" => "left",
                "width" => 20
                ],
                [
                "key"   => "reference_no",
                "title" => _('Reference'),
                "align" => "center",
                "width" => 40
                ],
                [
                "key"   => "tran_date",
                "title" => _('Date'),
                "align" => "left",
                "width" => 50
                ],
                [
                "key"   => "tran_time",
                "title" => _('Time'),
                "align" => "left",
                "width" => 50
                ],
                [
                "key"   => "customer",
                "title" => _('Customer'),
                "align" => "left",
                "width" => 60
                ],
                [
                "key"   => "amount",
                "title" => _('Total Amount'),
                "align" => "center",
                "width" => 40
                ],
                [
                "key"   => "Cash",
                "title" => _('Cash'),
                "align" => "center",
                "width" => 35
                ],
                [
                "key"   => "CreditCard",
                "title" => _('Debit/Credit Card'),
                "align" => "center",
                "width" => 50
                ],
                [
                "key"   => "BankTransfer",
                "title" => _('Bank Transfer'),
                "align" => "center",
                "width" => 50
                ],
                [
                "key"   => "others",
                "title" => _('Others'),
                "align" => "center",
                "width" => 30
                ],
                [
                "key"   => "total_received",
                "title" => _('Amount Received'),
                "align" => "center",
                "width" => 50
                ],
                [
                "key"   => "balance_amount",
                "title" => _('Balance Amount'),
                "align" => "center",
                "width" => 40
                ],
                // [
                // "key"   => "receipts_and_payment_methods",
                // "title" => _('Receipts & Payment Methods'),
                // "align" => "left",
                // "width" => 80
                // ],

                ];

                $colInfo = new ColumnInfo($columns,$page,$orientation);

                    /**
                     * 0th parameter is Comment.
                     * Can pass any comment if needed and it will show up in the header of the report
                     */ 
                    $param[] = "";  
                    /**
                     * additional parameters are provided in a two column 
                     * format with seperater '-'
                     * [
                     *    "text" => the attribute name,
                     *    "from" => first column
                     *    "to"   => second column
                     * ]
                     */
                    $param[] = [
                    "text" => _("Reference No."),
                    "from" => $reference_no,
                    "to" => ''
                    ];
                    $param[] = [
                    "text" => _("#"),
                    "from" => $trans_no_from,
                    "to" => $trans_no_to
                    ];
                    $param[] = [
                    "text" => _("Date"),
                    "from" => sql2date($trans_date_from),
                    "to" => sql2date($trans_date_to)
                    ];
                    $param[] = [
                    "text" => _("Customer"),
                    "from" => $customer_name,
                    "to" => ''
                    ];

                    $rep = new FrontReport(_("Invoices Report"), $filename,$page,9, $orientation);
                    $rep->Font();
                    $rep->Info(
                        $param,
                        $colInfo->cols(),
                        $colInfo->headers(),
                        $colInfo->aligns()
                    );
                    $rep->NewPage();
                    $count = 0;
                    foreach ($invoice_report_data as $row){
                        $count++;
                        // $total = round($row['Total'],2);
                        // $received = round($row['total_received'],2);
                        // $balance = $total - $received;     
                        $created_date = sql2date($row['created_at']);
                        $created_time = date('h:i:s A', strtotime($row['created_at']));
                        $created_at = $created_date . " " . $created_time;  

                        // $rep->TextCol(
                        //     $colInfo->x1('trans_no'),
                        //     $colInfo->x2('trans_no'),
                        //     $row['trans_no']
                        //     );
                        
                        $rep->TextCol(
                            $colInfo->x1('si_no'),
                            $colInfo->x2('si_no'),
                            $count
                            );
                        $rep->TextCol(
                            $colInfo->x1('reference_no'),
                            $colInfo->x2('reference_no'),
                            $row['ref']
                            );
                        $rep->DateCol(
                            $colInfo->x1('tran_date'),
                            $colInfo->x2('tran_date'),
                            $created_date
                            );
                        $rep->TextCol(
                            $colInfo->x1('tran_time'),
                            $colInfo->x2('tran_time'),
                            $created_time
                            );
                        $rep->TextCol(
                            $colInfo->x1('customer'),
                            $colInfo->x2('customer'),
                            $row['DebtorName']
                            );
                        $rep->TextCol(
                            $colInfo->x1('amount'),
                            $colInfo->x2('amount'),
                            $row['Total'] ? $row['Total'] : 0
                            );
                        $rep->TextCol(
                            $colInfo->x1('Cash'),
                            $colInfo->x2('Cash'),
                            $row['Cash'] ? $row['Cash'] : 0
                            );
                        $rep->TextCol(
                            $colInfo->x1('CreditCard'),
                            $colInfo->x2('CreditCard'),
                            $row['CreditCard'] ? $row['CreditCard'] : 0
                            );
                        $rep->TextCol(
                            $colInfo->x1('BankTransfer'),
                            $colInfo->x2('BankTransfer'),
                            $row['BankTransfer'] ? $row['BankTransfer'] : 0
                            );
                        $rep->TextCol(
                            $colInfo->x1('others'),
                            $colInfo->x2('others'),
                            $row['others'] ? $row['others'] : 0
                            );
                         $rep->TextCol(
                            $colInfo->x1('total_received'),
                            $colInfo->x2('total_received'),
                            $row['total_received'] ? $row['total_received'] : 0
                            );
                        $rep->TextCol(
                            $colInfo->x1('balance_amount'),
                            $colInfo->x2('balance_amount'),
                             $row['balance'] ? $row['balance'] : 0
                            );
                        // $rep->TextCol(
                        //     $colInfo->x1('receipts_and_payment_methods'),
                        //     $colInfo->x2('receipts_and_payment_methods'),
                        //     $this->get_cust_payment_receipts_and_method_for_invoice_report($row['cust_pay_reference_and_method'],$return_gl_and_print_link=false)
                        //     );

                        $rep->NewLine();

                        if ($rep->row < $rep->bottomMargin + $rep->lineHeight) {
                            $rep->Line($rep->row - 2);
                            $rep->NewPage();
                        }
                    }
                    $rep->End();
                }
            }else{
                display_error("Wrong Date Range Given for Invoice Report Export!!!");
            // echo "Wrong Date Range Given for Invoice Report Export!!!";
            } 
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }
    }
    private function get_cust_payment_receipts_and_method_for_invoice_report($cust_pay_reference_and_method,$return_gl_and_print_link=false){
        $erp_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $erp_link = substr($erp_link, 0, strpos($erp_link, "ERP"))."ERP/"; 
        $button_style = 'vertical-align:middle;width:18px;height:18px;border:0;';
        $cust_pay_reference_and_method_arr = explode(' , ',$cust_pay_reference_and_method);
        $cust_ref_payment_method = [];
        foreach($cust_pay_reference_and_method_arr as $val){
            $var2 = explode(' - ',$val);
            if($var2[0] !='' && $var2[1] !=''){
                $receipts_and_method_string = $var2[0]."(".$var2[1].")";
                $print_params = "PARAM_0=".$var2[2]."-".$var2[3]."&PARAM_1=".$var2[2]."-".$var2[3]."&PARAM_2=&PARAM_3=&PARAM_4=0&PARAM_5=0&REP_ID=112";
                $print_receipt = "<a id='inv_print' target='_blank' href='".$erp_link."reporting/prn_redirect.php?".$print_params."'><img src='".$erp_link."themes/daxis/images/print.png' style='".$button_style."' title='Print Receipt'></a>";
                $gl_link = "<a target='_blank' href='".$erp_link."gl/view/gl_trans_view.php?type_id=".$var2[3]."&amp;trans_no=".$var2[2]."' onclick='javascript:openWindow(this.href,this.target); return false;'><img src='".$erp_link."themes/daxis/images/gl.png' style='".$button_style."' title='GL'></a>";
                $cust_ref_payment_method[] = $return_gl_and_print_link==false ? $receipts_and_method_string : "<span>".$gl_link." ".$print_receipt." ".$receipts_and_method_string."</span>";
            }else{
                $cust_ref_payment_method[] = '-';
            }
        }
        return implode($return_gl_and_print_link==false ? ' , ' : '<br>', $cust_ref_payment_method);

    }

// function get_customers_for_select2(){

// $sql = "SELECT debtor_no, concat(debtor_ref,' - ',name) as custname FROM `0_debtors_master` 
//         WHERE name LIKE '%".$_GET['q']."%' OR debtor_ref LIKE '%" . $_GET['q'] . "%' 
//         LIMIT 10";
//     $result = db_query($sql);

//     $return_result = [];
//     while ($myrow = db_fetch($result)) {
//         $return_result[] = ['id'=>$myrow['debtor_no'], 'text'=>$myrow['custname']];
//     }

//     return AxisPro::SendResponse($return_result, 'json');
// }

    public function fetchTodaysServiceRequestsByToken($format='json') {

        try {

            $token=$_GET['token'];

            $sql = (
                "select
                    req.id,
                    req.display_customer,
                    req.mobile,
                    req.token_number,
                    ifnull(req.reference,'') reference,
                    SUM((item.unit_tax+item.price+item.govt_fee+item.bank_service_charge-item.discount)*item.qty) as amount 
                from 0_service_requests req
                left join 0_service_request_items item on item.req_id = req.id 
                where
                    date(req.created_at) = " . quote(date(DB_DATE_FORMAT)) . "
                    and active_status = 'ACTIVE'
                    and token_number=".db_escape($token)."
                group by req.id
                having sum(!isnull(item.invoiced_at)) = 0"
            );

            $get = db_query($sql);

            $return_result = [];
            while ($myrow = db_fetch_assoc($get)) {
                $return_result[] = $myrow;
            }

            return AxisPro::SendResponse($return_result, $format);

        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }


    public function getInvoicedApplicationIDs($format='json') {

        try {
            $conditions = '1 = 1 AND debtor_trans_type = 10 AND quantity <> 0';
            if (!empty($_GET['applicationIds']) && is_array($_GET['applicationIds'])) {
                $applicationIds = array_map('db_escape', $_GET['applicationIds']);
                $applicationIds = implode(",", $applicationIds);

                $conditions .= " AND application_id in ({$applicationIds})";
            }

            $sql = (
                "SELECT TRIM(detail.application_id) application_id 
                FROM 0_debtor_trans_details detail
                WHERE {$conditions}"
            );

            $sql .= " ORDER BY detail.id DESC LIMIT 800";

            $result = db_query($sql);

            $return_result = [];
            while ($myrow = db_fetch($result)) {
                $return_result[] = trim($myrow['application_id']," ");
            }

            return AxisPro::SendResponse($return_result, $format);

        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }


    public function getServiceRequestApplicationIDs($format='json') {

        try {

            $sql = "SELECT TRIM(application_id) application_id 
                    FROM 0_service_request_items
                   ";

            $sql .= " ORDER BY id DESC LIMIT 800";


            $result = db_query($sql);

            $return_result = [];
            while ($myrow = db_fetch($result)) {
                $return_result[] = trim($myrow['application_id']," ");
            }

            return AxisPro::SendResponse($return_result, $format);

        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }

    public function getAutoFetchedItems($format = 'json'){
        try  {
            return AxisPro::SendResponse([
                'status' => 200,
                'items' => getAutoFetchedItems($_GET['selectedIds'])
            ], $format);
        }

        catch (BusinessLogicException $e) {
            return Axispro::ValidationError($e->getMessage(), 400);
        }
        
        catch (Exception $e) {
            return AxisPro::catchException($e);
        }
    }

     /**
     * Returns the reception report
     *
     * @param string $format
     * @return void
     */
    public function getReceptionReport($format = 'json')
    {
        $validate = function () {
            $errors = [];
            // validate customer id
            if(
                !empty($_GET['cust_id']) 
                && !preg_match('/^[1-9][0-9]*$/', $_GET['cust_id'])
            ) {
                $errors['cust_id'] = 'Customer id is invalid!';
            }

            // validate customer filter
            if(
                !empty($_GET['cust_filter']) 
                && !preg_match('/[a-zA-Z_ 0-9]*$/', $_GET['cust_filter'])
            ) {
                $errors['cust_filter'] = 'Filter can only contain letters (a-z, A-Z), numbers (0-9), underscore (_) and space (<space>)';
            }

            // validate date from
            if(
                !empty($_GET['dt_from']) 
                && (
                    !($dt_from = DateTime::createFromFormat('d/m/Y', $_GET['dt_from'])) 
                    || $dt_from->format('d/m/Y') != $_GET['dt_from']
                )
            ) {
                $errors['dt_from'] = 'From date is invalid';
            } else {
                $dt_from && $_GET['_dt_from'] = $dt_from->format('Y-m-d');
            }

            // validate date to
            if(
                !empty($_GET['dt_to']) 
                && (
                    !($dt_to = DateTime::createFromFormat('d/m/Y', $_GET['dt_to'])) 
                    || $dt_to->format('d/m/Y') != $_GET['dt_to']
                )
            ) {
                $errors['dt_to'] = 'To date is invalid';
            } else {
                $dt_to && $_GET['_dt_to'] = $dt_to->format('Y-m-d');
            }

            return $errors;
        };

        if (!empty($errors = $validate())) {
            return AxisPro::SendResponse(['status' => 'FAIL', 'errors' => $errors], $format);
        }

        $buildWhere = function () {
            $where = '';
            !empty($_GET['cust_id'])     && $where .= " AND r.customer_id = {$_GET['cust_id']}";
            !empty($_GET['cust_filter']) && $where .= " AND (r.display_customer LIKE '%{$_GET['cust_filter']}%' OR r.customer_mobile LIKE '%{$_GET['cust_filter']}%')";
            !empty($_GET['_dt_from'])     && $where .= " AND DATE_FORMAT(r.created_at, '%Y-%m-%d') >= '{$_GET['_dt_from']}'";
            !empty($_GET['_dt_to'])       && $where .= " AND DATE_FORMAT(r.created_at, '%Y-%m-%d') <= '{$_GET['_dt_to']}'";

            return $where;
        };
        $where = $buildWhere();

        $sql = (
            "SELECT 
                r.token,
                r.customer_id cust_id,
                r.display_customer `display_name`,
                d.name `real_name`,
                r.customer_mobile mobile_no,
                r.customer_email email,
                r.created_at `date`
            FROM 0_axis_front_desk r 
                LEFT JOIN 0_debtors_master d ON d.debtor_no = r.customer_id
            WHERE 1=1 $where
            ORDER BY r.created_at DESC"
        );

        $total_count = db_query("SELECT COUNT(1) cnt FROM ($sql) t1")->fetch_row()[0];
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $perPage = 50;
        $offset = ($page * $perPage) - $perPage;
        $sql .= " LIMIT $perPage OFFSET $offset";
        
        $report = db_query($sql)->fetch_all(MYSQLI_ASSOC);

        return AxisPro::SendResponse([
            'data' => $report,
            'pagination_links' => AxisPro::paginate($total_count, $perPage)
        ], $format);
    }

    public function addAutoBatchItems($format="json") {

        try  {

            $items = $_POST['items'];


            foreach ($items as $index => $myrow) {

                $_SESSION['Items']->line_items[$index] = new line_details(
                    $myrow["stock_id"], 1,
                    $myrow["srv_amt"], 0,
                    1, 0,
                    $myrow["description"], 0,
                    0,
                    0,
                    $myrow['tot'],
                    0,
                    0,
                    $myrow['transaction_id'],//TR ID
                    0,//DISC AMT
                    null,
                    $myrow["application_id"],
                    1,//govt_bank
                    null,
                    $myrow['transaction_id']

                );
            }

        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }

    }

    function validate($rules, $data)
    {
        $valid = [];
        $invalid = [];
        $_rules = [];
        $_data = [];
        $fieldsToValidate = array_keys($rules);

        $validators = [
            "date" => function ($date, $format = 'Y-m-d') {
                $dt = DateTime::createFromFormat($format, $date);
                return $dt && $dt->format($format) == $date;
            },
            "p_intiger" => function ($value) {
                return preg_match('/^[1-9][0-9]{0,15}$/', $value) === 1;
            },
            "p_intigerArray" => function ($values){
                if (!is_array($values))
                    return false;
                $isint = true;
                foreach ($values as $value)
                    $isint = $isint && (preg_match('/^[1-9][0-9]{0,15}$/', $value)  === 1);
                return $isint;
            },
            "inArray" => function ($value, $array) {
                return array_search($value, $array) !== false;
            },
            "subArray" => function ($values, $array) {
                if (!is_array($values))
                    return false;
                
                return empty(array_diff($values, $array));
            },
            "spaced_alpha" => function ($value) {
                $value = trim($value);
                return preg_match('/^[a-zA-Z][a-zA-Z ]*$/', $value) === 1;
            },
            "alphaNum" => function ($value) {
                return preg_match('/^[0-9a-zA-Z][0-9a-zA-Z]*$/', $value) === 1;
            },
            "time" => function ($time, $format = 'H:i:s') {
                $dt = DateTime::createFromFormat($format, $time);
                return $dt && $dt->format($format) == $time;
            },
            "description" => function($value) {
                return preg_match('/^[\w _\-\.\:\,\?]*$/', trim($value)) === 1;
            },
            "email" => function($value) {
                return filter_var(trim($value), FILTER_VALIDATE_EMAIL) !== false;
            },
            "whole_number" => function ($value) {
                return preg_match('/^[0-9]{1,15}$/', $value) === 1;
            },
            "reference" => function ($value) {
                return preg_match('/^[a-zA-Z0-9]+\/[0-9]+$/', $value) === 1;
            }
        ];

        foreach ($fieldsToValidate as $field) {
            $_rules = $rules[$field];
            
            $firstRule = is_array($_rules[0])
                ? $_rules[0]["name"]
                : $_rules[0];
            $nullable = $firstRule === "nullable";
            $empty = empty($data[$field]);
            
            if ($nullable) {
                if ($empty) {
                    $valid[] = $field;
                    continue;
                } else if (!array_key_exists(1, $_rules)) {
                    continue;
                }
                $_rules = array_slice($_rules, 1);
            } else if ($empty) {
                $invalid[] = $field;
                continue;
            }

            $_valid = true;
            $value = $data[$field];
            foreach ($_rules as $rule){
                $_valid = is_array($rule) 
                    ? $validators[$rule["name"]]($value, ...$rule["param"]) 
                    : $validators[$rule]($value);
                if (!$_valid) break;
            }

            if ($_valid) {
                $valid[] = $field;
                $_data[$field] = $value;
            } else {
                $invalid[] = $field;
            }
        }

        return (object)[
            "valid" => $valid,
            "invalid" => $invalid,
            "fails" => !empty($invalid),
            "validated" => $fieldsToValidate,
            "data" => $_data
        ];
    }

    function buildFilters($data, $masterFilters, $rules)
    {
        $where = "";
        $activeFilters = [];

        foreach (array_keys($data) as $filter) {
            $_where[] = $masterFilters[$filter]($data[$filter]);
        };

        $where = implode(" AND ", $_where);

        if (!empty($where))
            $where = "AND $where";

        $activeFilters = $data;

        return [
            "where" => $where,
            "active_filters" => $activeFilters
        ];
    }

    /**
     * Retrieves the list of all pay elements
     * 
     * @param 'json'|'array' $format The response format required
     * @return void|array
     */
    public function getPayElements($format = 'json') {
        require_once $GLOBALS['path_to_root'] . '/hrm/db/pay_elements_db.php';
        $payElements = getPayElements()->fetch_all(MYSQLI_ASSOC);
        AxisPro::SendResponse(
            [
                "status" => 200,
                "data" => $payElements
            ],
            $format
        );
    }

    /**
     * Retrieves the list of all gl accounts excluding the bank accounts
     * 
     * @param 'json'|'array' $format The response format required
     * @return void|array
     */
    public function getGLAccountsWithoutBankAccounts($format = 'json') {
        $sql = (
            "SELECT
                coa.*
            FROM `0_chart_master` coa
            LEFT JOIN `0_bank_accounts` ba ON
                ba.account_code = coa.account_code
            WHERE ba.id IS NULL
            AND coa.inactive = 0"
        );

        $accounts = db_query($sql, "Could not retrieve the list of Ledgers")->fetch_all(MYSQLI_ASSOC);

        return AxisPro::SendResponse(["data" => $accounts], $format);
    }

    /**
     * Retrieves the list of all configurations for processing payroll
     *
     * @param 'json'|'array' $format The response format required
     * @return void|array
     */
    public function getConfigurationsForProcessingPayroll($format = 'json') {
        $payslipElements = [
            "basic_pay"             => $GLOBALS['SysPrefs']->prefs['basic_pay_el'],
            "housing_alw"           => $GLOBALS['SysPrefs']->prefs['housing_alw_el'],
            "minutes_overtime"      => $GLOBALS['SysPrefs']->prefs['overtime_el'],
            "weekends_worked"       => $GLOBALS['SysPrefs']->prefs['weekendsworked_el'],
            "holidays_worked"       => $GLOBALS['SysPrefs']->prefs['holidaysworked_el'],
            "minutes_late"          => $GLOBALS['SysPrefs']->prefs['latecoming_el'],
            "minutes_short"         => $GLOBALS['SysPrefs']->prefs['earlyleaving_el'],
            "days_absent"           => $GLOBALS['SysPrefs']->prefs['absence_el'],
            "days_not_worked"       => $GLOBALS['SysPrefs']->prefs['days_not_worked_el'],
            "violations"            => $GLOBALS['SysPrefs']->prefs['violations_el'],
            "days_on_leave"         => $GLOBALS['SysPrefs']->prefs['leaves_el'],
            "pension"               => $GLOBALS['SysPrefs']->prefs['pension_el'],
            "holded_salary"           => $GLOBALS['SysPrefs']->prefs['holded_salary_el'],
            "released_holded_salary"  => $GLOBALS['SysPrefs']->prefs['released_holded_salary_el'],
            "rewards_bonus"         => $GLOBALS['SysPrefs']->prefs['rewards_bonus_el'],
            "loan_recovery"         => $GLOBALS['SysPrefs']->prefs['loan_recovery_el']
        ];

        /**
         * the user is not having access to modify some pay_elements,
         * add it to the configuration, so it will be readonly
         */
        if (!user_check_access('HRM_UPD_STFMSTK_PSLP')) {
            $payslipElements['mistakes'] = $GLOBALS['SysPrefs']->prefs['staff_mistake_el'];
        }
        if (!user_check_access('HRM_UPD_COMMISN_PSLP')) {
            $payslipElements['commission'] = $GLOBALS['SysPrefs']->prefs['commission_el'];
        }

        return AxisPro::SendResponse(
            [
                "status" => 200,
                "data" => [
                    "publicHolidayRate" => $GLOBALS['SysPrefs']->prefs['public_holiday_rate'],
                    "weekendRate"       => $GLOBALS['SysPrefs']->prefs['weekend_rate'],
                    "overtimeRate"      => $GLOBALS['SysPrefs']->prefs['overtime_rate'],
                    "lateComingRate"    => $GLOBALS['SysPrefs']->prefs['latehour_rate'],
                    "earlyGoingRate"    => $GLOBALS['SysPrefs']->prefs['earlygoing_rate'],
                    "payslipElements"   => $payslipElements
                ]
            ],
            $format
        );
    }

    /**
     * Checks if the employee ID is unique by verifying against database
     *
     * @return void returns a 400 HTTP response if already exist else 200
     */
    public function isEmployeeIdUnique() {
        $empRef = db_escape($_GET['emp']['emp_ref']);
        $employee = db_query(
            "SELECT id FROM `0_employees` WHERE emp_ref = {$empRef}",
            "Could not verify employee ref is unique"
        )->fetch_assoc();

        http_response_code(empty($employee) ? 200 : 400);
        exit();
    }

    /**
     * Checks if the employee already applied for a leave given the number of days and the commencing date.
     * 
     * @return void exits the execution by status 400 if already exists, 200 otherwise
     * 422 if input is not valid
     */
    public function isLeaveUnique() {
        $employeeId = $_POST['employee_id'];
        $userDateFormat = getDateFormatInNativeFormat(); 
        $from = DateTimeImmutable::createFromFormat($userDateFormat, $_POST['from']);
        $days = floatval($_POST['days']);
        $debitLeaveTransaction = LTT_DEBIT;

        if (
            empty($employeeId)
            || !preg_match('/^\d+$/', $employeeId)
            || $days <= 0
            || ($days > 1 && fmod($days, 1) > 0)
            || !$from
            || $from->format($userDateFormat) !== $_POST['from']
        ) {
            http_response_code(422);
            exit();
        }

        $daysToAdd = $days - 1;
        if ($daysToAdd < 1) {
            $till = $from->format(DB_DATE_FORMAT);
        } else {
            $till = $from->add(new DateInterval("P{$daysToAdd}D"))->format(DB_DATE_FORMAT);
        }
        $from = $from->format(DB_DATE_FORMAT);

        $leave = db_query(
            "SELECT
                id
            FROM `0_emp_leave_details`
            WHERE
                `date` >= '{$from}'
                AND `date` <= '{$till}'
                AND `employee_id` = '{$employeeId}'
                AND `type` = {$debitLeaveTransaction}
                AND `category_id` = ".EmployeeLeave::CATEGORY_NORMAL."
            LIMIT 1",
            "Could not retrieve the leave details"
        )->fetch_assoc();

        http_response_code(empty($leave) ? 200 : 400);
        exit();
    }

    /**
     * Checks if the employee's attendance machine ID is unique by verifying against database
     *
     * @return void returns a 400 HTTP response if already exist else 200
     */
    public function isMachineIdUnique() {
        $machineId = db_escape($_GET['emp']['machine_id']);
        $employee = db_query(
            "SELECT id FROM `0_employees` WHERE machine_id = {$machineId}",
            "Could not verify employee ref is unique"
        )->fetch_assoc();

        http_response_code(empty($employee) ? 200 : 400);
        exit();
    }

    /**
     * Updates the GL color
     *
     * @param 'json'|'array' $format
     * @return void This function terminates the request.
     */
    public function updateGLColor($format = 'json') {
        /*
         * The input is in the format color_code[counter] = hex color code;
         * We can actually have multiple inputs, However, since we are only handling
         * one update we can just get the first key and value.
         */ 
        if (
            empty($_POST['color_code'])
            || !is_array($_POST['color_code'])
            || !is_numeric(array_keys($_POST['color_code'])[0])
            || !preg_match('/^#[a-f\d]{2}[a-f\d]{2}[a-f\d]{2}$/i', array_values($_POST['color_code'])[0])
        ) {
            return AxisPro::SendResponse([
                "status" => 422,
                "message" => "Request contains invalid data"
            ], $format);
        }

        $counter = array_keys($_POST['color_code'])[0];
        $hex = array_values($_POST['color_code'])[0];
        $hex = strtolower($hex) == '#ffffff' ? 'NULL' : "'{$hex}'";
        db_query(
            "UPDATE `0_gl_trans` SET color_code = {$hex} WHERE `counter` = '{$counter}'",
            "Could not update the color"
        );
        $affected_rows = db_num_affected_rows();
        if ($affected_rows) {
            return AxisPro::SendResponse([
                "status" => 200,
                "message" => "OK"
            ], $format);
        } else {
            return AxisPro::SendResponse([
                "status" => 400,
                "message" => "Bad Request"
            ], $format);
        }
    }

    /**
     * Returns all the status for attendance metrics review
     *
     * @param 'json'|'array' $format
     * @return void
     */
    public function getAttendanceMetricsStatuses($format = 'json') {
        return AxisPro::SendResponse([
            "status" => 200,
            "data" => $GLOBALS['attendance_review_status']
        ], $format);
    }

    /**
     * Returns the employee identified by the id
     *
     * @param 'json'|'array' $format
     * @return void
     */
    public function getEmployee($format = 'json') {
        require_once $GLOBALS['path_to_root'] . '/hrm/db/employees_db.php';

        if (empty($_GET['id']) || !preg_match('/^\d{1,15}$/', $_GET['id'])) {
            return AxisPro::SendResponse([
                "status" => 422,
                "message" => "Parameter id is missing or invalid"
            ], $format);
        }

        return AxisPro::SendResponse([
            "status" => 200,
            "data" => getEmployee($_GET['id'], 'all')
        ]);
    }

    /**
     * This funtion will return data of the employee if the payroll or payslip is processed 
     * for the selected month.
     * Returns payroll identified by employee id and date
     *
     * @param 'json'|'array' $format
     * @return message 422 if not valid and 200 if valid
     */
    public function getPayroll($format = 'json') {
            
        $user_date_format = getDateFormatInNativeFormat();
        
        $dt = DateTime::createFromFormat($user_date_format, $_GET['selectedDate']);
        $selectedDate = $dt->format(DB_DATE_FORMAT);

        if (isPayslipProcessed($_GET['employeeID'], $selectedDate)) {
            return AxisPro::SendResponse([
                "status" => 422,
                "message" => "Sorry the payroll for this employee is already processed."
            ], $format);
        } else {
            return AxisPro::SendResponse([
                "status" => 200,
                "message" => "ok"
            ]);
        }
    }

    public function get_split_accounts($format = "json")
    {
        try {
            $dimension_id = $_GET['dimension_id'];
            $cash_accounts = array_map(
                function ($v) { return Arr::only($v, ['id', 'bank_account_name']); },
                array_map('get_bank_account', array_filter(get_payment_accounts('Cash', null, $dimension_id)))
            );

            $card_accounts = array_map(
                function ($v) { return Arr::only($v, ['id', 'bank_account_name']); },
                array_map('get_bank_account', array_filter(get_payment_accounts('CreditCard', null, $dimension_id)))
            );

            return AxisPro::SendResponse([
                'cash_accounts' => $cash_accounts,
                'card_accounts' => $card_accounts,
            ], $format);
        } catch (Exception $e) {
            return AxisPro::catchException($e);
        }
    }

    public function get_employees_document_details(){


        $employee_id = $_POST['employee_id'];
        $category_type = $_POST['category_type'];

        if(!empty($_POST['created_date'])){
            $created_date = date("Y-m-d", strtotime($_POST['created_date']));

        }
        $expire_on = $_POST['expire_on'];

        $where = '';
        if(!empty($employee_id)){
            $where .=" AND doc.entity_id ='{$employee_id}'";
        }
        if(!empty($category_type)){
            $where .=" AND doc.document_type ='{$category_type}'";
        }
        if(!empty($created_date)){
            $where .=" AND DATE(doc.created_at) ='{$created_date}'";
        }
        


        if(!empty($expire_on)){
            $ds = '-' .$expire_on. ' months';
            $dt = '+' .$expire_on. ' months';

       
            // $where .=" AND DATE(doc.expire_on) <= ". date('Y-m-d')." and DATE(doc.expire_on) >= ".date('Y-m-d',strtotime('+3 months',strtotime(date('Y-m-d'))));
            $where .=" AND DATE(doc.expires_on) <= ". db_escape(date('Y-m-d',strtotime($dt,strtotime(date('Y-m-d')))))." and DATE(doc.expires_on) >= ". db_escape(date('Y-m-d',strtotime($ds,strtotime(date('Y-m-d')))));

        }
        

        $sql = "SELECT doc.*,doc_typ.name as category_name,emp.name as employee_name from 0_documents doc 
                    left join 0_document_types doc_typ on doc_typ.id = doc.document_type
                    left join 0_employees emp on emp.id = doc.entity_id
                    where 1=1 $where
                    ";

        $result = db_query($sql);

        $employee_doc_data = [];
            while ($myrow = db_fetch($result)) {

                $employee_doc_data[] = $myrow;

            }

            return AxisPro::SendResponse($employee_doc_data);
        
    }

    public function deleteServiceRequest() {
        if (!user_check_access('SA_DELSERVICEREQ')) {
            return AxisPro::ValidationError(
                'You dont have permission to access this function. Please contact your admin for permission',
                403
            );
        }

        if (empty($_POST['id']) || !preg_match('/^\d+$/', $_POST['id'])) {
            return AxisPro::ValidationError('Please provide a valid request id');
        }

        begin_transaction();
        $service_req_items = db_query(
            "SELECT
                req.id,
                item.id as item_id,
                item.invoiced_at as item_invoiced_at
            FROM `0_service_requests` as req
            LEFT JOIN `0_service_request_items` as item ON
                item.req_id = req.id
            WHERE req.id = " . db_escape($_POST['id']) ."
                AND req.active_status = 'ACTIVE'
            FOR UPDATE",
            'Could not query for service request'
        )->fetch_all(MYSQLI_ASSOC);

        if (empty($service_req_items)) {
            return AxisPro::ValidationError('Could not find the service request');
        }

        if (!empty(array_filter($service_req_items, function ($i) {return !empty($i['item_invoiced_at']);}))) {
            return AxisPro::ValidationError('Some or all of the items in this service request is already invoiced');
        }

        db_query(
            "UPDATE 0_service_requests SET active_status = 'INACTIVE' WHERE id = " . db_escape($_POST['id']),
            "Could not update service request status"
        );
        commit_transaction();

        echo json_encode(['message' => 'Row deleted successfully']);
    }
    
    public function getServiceRequestItems() {
        if (empty($_POST['req_id']) || !preg_match('/^\d+$/', $_POST['req_id'])) {
            return AxisPro::ValidationError('Please provice a request id');
        }

        if (!ServiceRequest::whereId($_POST['req_id'])->exists()) {
            return AxisPro::ValidationError('Could not find the service request');
        }

        $sql = (
            "select
                req_item.id as id,
                CONCAT_WS(' - ', sm.description, nullif(sm.long_description, '')) as description,
                req_item.qty,
                !isnull(req_item.invoiced_at) as is_invoiced,
                req_item.govt_fee,
                req_item.bank_service_charge,
                req_item.price as unit_price,
                (req_item.govt_fee + req_item.bank_service_charge) as total_govt_fee,
                round((req_item.price + req_item.govt_fee + req_item.bank_service_charge - req_item.discount) * req_item.qty) as line_total,
                req_item.invoiced_at,
                trans.reference as invoice_ref,
                detail.transaction_id,
                if(isnull(req_item.invoiced_at), 'Pending', 'Invoiced') as status
            from 0_service_requests req
            left join 0_service_request_items req_item on
                req_item.req_id = req.id
            left join 0_debtor_trans_details detail ON
                detail.srv_req_line_id = req_item.id
                and detail.debtor_trans_type = ".ST_SALESINVOICE."
                and detail.quantity <> 0
            left join 0_debtor_trans trans ON
                trans.service_req_id = req.id
                and trans.`type` = detail.debtor_trans_type
                and trans.trans_no = detail.debtor_trans_no
                and (trans.ov_amount + trans.ov_gst + trans.ov_discount + trans.ov_freight + trans.ov_freight_tax) <> 0
            left join 0_stock_master sm on
                sm.stock_id = req_item.stock_id
            where
                req.id = '{$_POST['req_id']}'" 
        );
    
        $items = db_query($sql, "Could not query for service request items")->fetch_all(MYSQLI_ASSOC);
        
        return AxisPro::SendResponse(['data' => compact('items')]);
    }

    /**
     * Checks if the employee already applied for a leave for the date.
     *
     * @return void exits the execution by status 400 if already exists, 200 otherwise
     * 422 if input is not valid
     */
    public function isAdjustmentLeaveUnique() {
        $employeeId = $_POST['employee_id'];
        $leaveTypeId = $_POST['leave_type_id'];
        $userDateFormat = getDateFormatInNativeFormat();
        $adjustmentDate = DateTimeImmutable::createFromFormat($userDateFormat, $_POST['adjustment_date']);
        $debitLeaveTransaction = LTT_DEBIT;

        if (
            empty($employeeId)
            || !preg_match('/^\d+$/', $employeeId)
            || !$adjustmentDate
            || empty($leaveTypeId)
        ) {
            http_response_code(422);
            exit();
        }

        $adjustmentDate = $adjustmentDate->format(DB_DATE_FORMAT);
        $leave = db_query(
            "SELECT
                id
            FROM `0_emp_leave_details`
            WHERE
                `date` = '{$adjustmentDate}'
                AND `employee_id` = '{$employeeId}'
                AND `leave_type_id` = '{$leaveTypeId}'
                AND `type` = {$debitLeaveTransaction}
            LIMIT 1",
            "Could not retrieve the leave details"
        )->fetch_assoc();

        http_response_code(empty($leave) ? 200 : 400);
        exit();
    }

}
