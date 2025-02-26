<?php


function Top_five_invoices($type = 10, $options = null)
{
    if ($options == 'Last Month') {
        $today1 = date('Y-m-d', strtotime('last day of previous month'));
        $begin1 = date('Y-m-d', strtotime('first day of previous month'));
    } elseif ($options == 'This Month') {
        $begin1 = date("Y-m-d", strtotime("first day of this month"));
        $today1 = date("Y-m-d", strtotime("last day of this month"));
    } elseif ($options == 'Last Week') {
        $begin1 = date("Y-m-d", strtotime("last week monday"));
        $today1 = date("Y-m-d", strtotime("last week sunday"));
    } elseif ($options == 'Last Quarter Year') {

    } else {
        $f_year = kv_get_current_fiscalyear();
        $begin1 = $f_year['begin'];
        $today1 = date('Y-m-d');
    }
    $today = date('Y-m-d');

    $sql_pre = "SET sql_mode='';";
    db_query($sql_pre, "Can't Set Mode");
    $sql = "
        SELECT 
        trans.type, 
        trans.trans_no, 
        trans.order_, 
        trans.reference,
        trans.tran_date, 
        trans.due_date, 
        debtor.name, 
        branch.br_name,
        debtor.curr_code,
        debtor.debtor_no,
        (trans.ov_amount + trans.ov_gst + trans.ov_freight 
            + trans.ov_freight_tax + trans.ov_discount) AS TotalAmount, @bal := @bal+(trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount), trans.alloc AS Allocated,
        ((trans.type = 10)
            AND trans.due_date < '$today') AS OverDue ,
        Sum(line.quantity-line.qty_done) AS Outstanding,
        Sum(line.qty_done) AS HasChild
        FROM "
        . TB_PREF . "debtor_trans as trans
            LEFT JOIN " . TB_PREF . "debtor_trans_details as line
                ON trans.trans_no=line.debtor_trans_no AND trans.type=line.debtor_trans_type
            LEFT JOIN " . TB_PREF . "voided as v
                ON trans.trans_no=v.id AND trans.type=v.type
            LEFT JOIN " . TB_PREF . "cust_branch as branch ON trans.branch_code=branch.branch_code,"
        . TB_PREF . "debtors_master as debtor
        WHERE debtor.debtor_no = trans.debtor_no
            AND trans.tran_date >= '$begin1'
            AND trans.tran_date <= '$today1'
            AND trans.branch_code = branch.branch_code
            AND ISNULL(v.date_) AND ";
    if ($type == 10)
        $sql .= "(trans.type = 10) GROUP BY trans.trans_no, trans.type ORDER BY trans.tran_date DESC LIMIT 5";
    else
        $sql .= "( trans.type = 12 OR trans.type = 2 OR trans.type = 1) GROUP BY trans.trans_no, trans.type ORDER BY trans.tran_date DESC LIMIT 5";
    $result = db_query($sql, "Transactions could not be calculated");
    //print_r($result);
    $final = array();
    $total = 0;
    while ($row = db_fetch_assoc($result)) {
        //if ($row['ctype'] > 3){
        //  $total += $row['total'];
        //}
        $final[] = $row;
    }

    return $final;
}


function get_tax_reports($options = null)
{
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
    } else {
        $f_year = kv_get_current_fiscalyear();
        $begin1 = $f_year['begin'];
        $today1 = date('Y-m-d');
    }

    $sql = "SELECT tt.name as taxname, taxrec.*, taxrec.amount*ex_rate AS amount,
                taxrec.net_amount*ex_rate AS net_amount,
                IF(ISNULL(supp.supp_name), debt.name, supp.supp_name) as name,
                branch.br_name
        FROM " . TB_PREF . "trans_tax_details taxrec
        LEFT JOIN " . TB_PREF . "tax_types tt
            ON taxrec.tax_type_id=tt.id
        LEFT JOIN " . TB_PREF . "supp_trans strans
            ON taxrec.trans_no=strans.trans_no AND taxrec.trans_type=strans.type
        LEFT JOIN " . TB_PREF . "suppliers as supp ON strans.supplier_id=supp.supplier_id
        LEFT JOIN " . TB_PREF . "debtor_trans dtrans
            ON taxrec.trans_no=dtrans.trans_no AND taxrec.trans_type=dtrans.type
        LEFT JOIN " . TB_PREF . "debtors_master as debt ON dtrans.debtor_no=debt.debtor_no
        LEFT JOIN " . TB_PREF . "cust_branch as branch ON dtrans.branch_code=branch.branch_code
        WHERE (taxrec.amount <> 0 OR taxrec.net_amount <> 0)
            AND taxrec.trans_type <> 13
            AND taxrec.tran_date >= '$begin1'
            AND taxrec.tran_date <= '$today1'
        ORDER BY taxrec.trans_type, taxrec.tran_date, taxrec.trans_no, taxrec.ex_rate";
    $result = db_query($sql, "could not get Company Details");

    $res = kvgetTaxTypes();

    $taxes[0] = array('in' => 0, 'out' => 0, 'taxin' => 0, 'taxout' => 0);
    while ($tax = db_fetch($res))
        $taxes[$tax['id']] = array('in' => 0, 'out' => 0, 'taxin' => 0, 'taxout' => 0);

    $totalnet = $totaltax = 0.0;
    while ($trans = db_fetch_assoc($result)) {
        if (in_array($trans['trans_type'], array(11, 20, 0))) {
            $trans['net_amount'] *= -1;
            $trans['amount'] *= -1;
        }

        $tax_type = $trans['tax_type_id'];
        if ($trans['trans_type'] == ST_JOURNAL && $trans['amount'] < 0) {
            $taxes[$tax_type]['taxin'] += $trans['amount'];
            $taxes[$tax_type]['in'] += $trans['net_amount'];
        } elseif ($trans['trans_type'] == ST_JOURNAL && $trans['amount'] >= 0) {
            $taxes[$tax_type]['taxout'] += $trans['amount'];
            $taxes[$tax_type]['out'] += $trans['net_amount'];
        } elseif (in_array($trans['trans_type'], array(ST_BANKDEPOSIT, ST_SALESINVOICE, ST_CUSTCREDIT))) {
            $taxes[$tax_type]['taxout'] += $trans['amount'];
            $taxes[$tax_type]['out'] += $trans['net_amount'];
        } else {
            $taxes[$tax_type]['taxin'] += $trans['amount'];
            $taxes[$tax_type]['in'] += $trans['net_amount'];
        }
        $totalnet += $trans['net_amount'];
        $totaltax += $trans['amount'];
        $totalnet += $trans['net_amount'];
        $totaltax += $trans['amount'];
    }
    $taxtotal = 0;
    $Taxesarray = array();
    foreach ($taxes as $id => $sum) {
        $tax_array = array();
        if ($id) {
            $tx = kvgetTaxInfo($id);
            $tax_array['name'] = $tx['name'];
        } else {
            $tax_array['name'] = trans('Exempt');
        }
        $tax_array['out'] = $sum['taxout'];
        $tax_array['in'] = $sum['taxin'];
        $tax_array['total'] = $sum['taxout'] + $sum['taxin'];
        $taxtotal += $sum['taxout'] + $sum['taxin'];
        $Taxesarray[] = $tax_array;
    }
    $Taxesarray['grandtotal'] = $taxtotal;
    return $Taxesarray;
}

function kvgetTaxTypes()
{
    $sql = "SELECT * FROM " . TB_PREF . "tax_types ORDER BY id";
    return db_query($sql, "No transactions were returned");
}

function kvgetTaxInfo($id)
{
    $sql = "SELECT * FROM " . TB_PREF . "tax_types WHERE id=$id";
    $result = db_query($sql, "No transactions were returned");
    return db_fetch($result);
}

function kv_get_expenses_chartlists()
{

    $sql_collect_ID = "SELECT id FROM " . TB_PREF . "chart_types WHERE class_id IN (5,6,7)";
    $kv_class_ids = array();
    $ret_id = db_query($sql_collect_ID, "Can't get empl person degrees");

    while ($cont_id = db_fetch_row($ret_id))
        $kv_class_ids[] = $cont_id[0];

    $sql = "SELECT chart.account_code, chart.account_name FROM " . TB_PREF . "chart_master chart," . TB_PREF . "chart_types type WHERE chart.account_type=type.id AND type.id IN (" . implode(",", $kv_class_ids) . ")";
    $kv_expenses_list = array();
    $ret = db_query($sql, "Can't get empl person degrees");

    while ($cont = db_fetch_row($ret))
        $kv_expenses_list[] = $cont;
//    echo json_encode($cont);
    return $kv_expenses_list;
}

function Expenses($options = null)
{

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
    }

    elseif ($options == 'Today') {
        $begin1 = date("Y-m-d", strtotime("now"));
        $today1 = date("Y-m-d", strtotime("now"));
    }

    else {
        $f_year = kv_get_current_fiscalyear();
        $begin1 = $f_year['begin'];
        $today1 = date('Y-m-d');
    }

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
    return $final;
}

function get_top_suppliers($options = null)
{
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
    } else {
        $f_year = kv_get_current_fiscalyear();
        $begin1 = $f_year['begin'];
        $today1 = date('Y-m-d');
    }
    $sql = "SELECT SUM((trans.ov_amount + trans.ov_discount) * rate) AS total, s.supplier_id, s.supp_name FROM
        " . TB_PREF . "supp_trans AS trans, " . TB_PREF . "suppliers AS s WHERE trans.supplier_id=s.supplier_id
        AND (trans.type = 20 OR trans.type = 21)
        AND tran_date >= '$begin1' AND tran_date <= '$today1' GROUP by s.supplier_id ORDER BY total DESC, s.supplier_id 
        LIMIT 10";
    $result = db_query($sql, "could not get Company Details");

    $final = array();
    while ($row = db_fetch_assoc($result)) {
        $final[] = $row;
    }
    return $final;
}


function get_top_customers($options = null)
{
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
    }
    elseif ($options == 'Today') {
        $begin1 = date("Y-m-d", strtotime("now"));
        $today1 = date("Y-m-d", strtotime("now"));
    }

    else {
        $f_year = kv_get_current_fiscalyear();
        $begin1 = $f_year['begin'];
        $today1 = date('Y-m-d');
    }

    $sql = "SELECT ROUND(SUM((ov_amount + ov_discount) * rate * IF(trans.type = 11, -1, 1)),2) AS total,d.debtor_no, d.name FROM
        " . TB_PREF . "debtor_trans AS trans, " . TB_PREF . "debtors_master AS d WHERE trans.debtor_no=d.debtor_no
        AND (trans.type = 10 OR trans.type = 11)
        AND tran_date >= '$begin1' AND tran_date <= '$today1' GROUP by d.debtor_no ORDER BY total DESC, d.debtor_no 
        LIMIT 10";
    $result = db_query($sql, "could not get Company Details");

    $final = array();
    while ($row = db_fetch_assoc($result)) {
        $final[] = $row;
    }

    return $final;
}

function Top_selling_items($options = null)
{

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
    }
    elseif ($options == 'Today') {
        $begin1 = date("Y-m-d", strtotime("now"));
        $today1 = date("Y-m-d", strtotime("now"));
    }
    else {
        $f_year = kv_get_current_fiscalyear();
        $begin1 = $f_year['begin'];
        $today1 = date('Y-m-d');
    }

//    $sql = "SELECT SUM((trans.unit_price * trans.quantity) * d.rate) AS total, s.stock_id, s.description,
//            SUM(trans.quantity) AS qty, SUM((s.material_cost + s.overhead_cost + s.labour_cost) * trans.quantity) AS costs FROM
//            " . TB_PREF . "debtor_trans_details AS trans, " . TB_PREF . "stock_master AS s, " . TB_PREF . "debtor_trans AS d
//            WHERE trans.stock_id=s.stock_id AND trans.debtor_trans_type=d.type AND trans.debtor_trans_no=d.trans_no
//            AND (d.type = 10 OR d.type = 11) AND tran_date >= '$begin1' AND tran_date <= '$today1' GROUP by s.stock_id ORDER BY total DESC, s.stock_id
//        LIMIT 10";


    $sql = "select trans.tran_date, date_format(trans.tran_date, '%W') dayname,
        SUM(detail.quantity) count,
        ROUND(SUM((detail.unit_price*detail.quantity)-(detail.pf_amount*detail.quantity)),2) as service_charge  
        from 0_debtor_trans_details detail 
        left join 0_debtor_trans trans ON trans.trans_no=detail.debtor_trans_no and trans.type=10
        where detail.debtor_trans_type=10  group by tran_date order by tran_date desc limit 6";

    $result = db_query($sql) or die(trans('Error getting weekly sales data'));

    $rows = array();
    while ($r = db_fetch_assoc($result)) {
        $rows[] = $r;
    }

    return $rows;
}


function kv_get_current_balance()
{

    $f_year = kv_get_current_fiscalyear();

    $today1 = date('Y-m-d');

    $sql_item_count = "SELECT SUM(amount) FROM `" . TB_PREF . "bank_trans` WHERE trans_date >= '" . $f_year['begin'] . "' AND trans_date <= '$today1'";

    $sql_item_count_result = db_query($sql_item_count, "could not get sales type");

    $item_count = db_fetch_row($sql_item_count_result);

    return round($item_count[0], 2) + kv_get_opening_balance();
}


function kv_get_opening_balance()
{

    $f_year = kv_get_current_fiscalyear();

    $sql_item_count = "SELECT SUM(amount) FROM `" . TB_PREF . "bank_trans` WHERE trans_date < '" . $f_year['begin'] . "'";

    $sql_item_count_result = db_query($sql_item_count, "could not get sales type");

    $item_count = db_fetch_row($sql_item_count_result);

    return round($item_count[0], 2);
}

function kv_get_current_fiscalyear()
{
    $year = get_company_pref('f_year');

    $sql = "SELECT * FROM " . TB_PREF . "fiscal_year WHERE id=" . db_escape($year);

    $result = db_query($sql, "could not get current fiscal year");

    return db_fetch($result);
}

function class_balances($options = null)
{
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
    } else {
        $f_year = kv_get_current_fiscalyear();
        $begin1 = $f_year['begin'];
        $today1 = date('Y-m-d');
    }

    $sql = "SELECT SUM(amount) AS total, c.class_name, c.ctype FROM
        " . TB_PREF . "gl_trans," . TB_PREF . "chart_master AS a, " . TB_PREF . "chart_types AS t, 
        " . TB_PREF . "chart_class AS c WHERE
        account = a.account_code AND a.account_type = t.id AND t.class_id = c.cid
        AND IF(c.ctype > 3, tran_date >= '$begin1', tran_date >= '0000-00-00') 
        AND tran_date <= '$today1' GROUP BY c.cid ORDER BY c.cid";
    $result = db_query($sql, "Transactions could not be calculated");

    $final = array();
    $total = 0;
    while ($row = db_fetch_assoc($result)) {
        if ($row['ctype'] > 3) {
            $total += $row['total'];
        }
        $final[] = $row;
    }
    $final[] = array('class_name' => 'Calculated Return', 'total' => $total);
    return $final;
}


function kv_calculate_next_invoice($myrow)
{
    if ($myrow["last_sent"] == '0000-00-00')
        $next = sql2date($myrow["begin"]);
    else
        $next = sql2date($myrow["last_sent"]);
    $next = add_months($next, $myrow['monthly']);
    $next = add_days($next, $myrow['days']);
    return add_days($next, -1);
}


if (!function_exists('generate_timezone_list')) {
    function generate_timezone_list()
    {
        static $regions = array(
            DateTimeZone::AFRICA,
            DateTimeZone::AMERICA,
            DateTimeZone::ANTARCTICA,
            DateTimeZone::ASIA,
            DateTimeZone::ATLANTIC,
            DateTimeZone::AUSTRALIA,
            DateTimeZone::EUROPE,
            DateTimeZone::INDIAN,
            DateTimeZone::PACIFIC,
        );

        $timezones = array();
        foreach ($regions as $region) {
            $timezones = array_merge($timezones, DateTimeZone::listIdentifiers($region));
        }

        $timezone_offsets = array();
        foreach ($timezones as $timezone) {
            $tz = new DateTimeZone($timezone);
            $timezone_offsets[$timezone] = $tz->getOffset(new DateTime);
        }

        // sort timezone by offset
        asort($timezone_offsets);

        $timezone_list = array();
        foreach ($timezone_offsets as $timezone => $offset) {
            $offset_prefix = $offset < 0 ? '-' : '+';
            $offset_formatted = gmdate('H:i', abs($offset));
            $pretty_offset = "UTC${offset_prefix}${offset_formatted}";
            $timezone_list[$timezone] = "(${pretty_offset}) $timezone";
        }
        return $timezone_list;
    }
}

if (!function_exists('kv_image_row')) {
    function kv_image_row($label, $name, $id = "")
    {
        echo "<tr><td class='label'>$label</td>";
        if ($id != "")
            $id = "id='$id'";
        label_cells(null, "<input type='file' name='$name'  accept='image/*'' $id >");
        echo "</tr>\n";
    }
}
if (!function_exists('create_tbl_option')) {
    function create_tbl_option()
    {
        if (db_num_rows(db_query("SHOW TABLES LIKE 'saai_theme_option'", "Table Checking")) == 1) {
        } else {
            $sql = "CREATE TABLE IF NOT EXISTS `saai_theme_option` (
    			`id` int(10) NOT NULL AUTO_INCREMENT,
      			`option_name` varchar(60) NOT NULL,
      			`option_value` varchar(60) NOT NULL,
      			PRIMARY KEY (`id`)
				) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;";
            db_query($sql, 'Basic Theme Table has not Created!');
        }
    }
}
if (!function_exists('kv_get_option')) {
    function kv_get_option($option_name)
    {
        if (db_num_rows(db_query("SHOW TABLES LIKE 'saai_theme_option'", "Table Checking")) == 1) {
            $sql = "SELECT option_value FROM saai_theme_option WHERE option_name=" . db_escape($option_name) . " LIMIT 1";
            $result = db_query($sql, "could not get sales type");
            $row = db_fetch_row($result);

            return empty($row[0]) ? 'false' : $row[0];
        } else
            return 'false';
    }
}
if (!function_exists('kv_update_option')) {
    function kv_update_option($option_name, $option_value)
    {
        if (db_num_rows(db_query("SHOW TABLES LIKE 'saai_theme_option'", "Table Checking")) == 1) {
            if ($option_name == 'hide_version') {
                if (check_empty_result("SELECT * FROM saai_theme_option WHERE option_name='hide_version'")) {
                    $sql = "UPDATE saai_theme_option SET	option_value=" . db_escape($option_value) . " WHERE option_name=" . db_escape($option_name);
                    db_query($sql, "could not update option");
                } else {
                    kv_add_option($option_name, $option_value);
                }
            } elseif ($option_name == 'hide_help_link') {
                if (check_empty_result("SELECT * FROM saai_theme_option WHERE option_name='hide_help_link'")) {
                    $sql = "UPDATE saai_theme_option SET	option_value=" . db_escape($option_value) . " WHERE option_name=" . db_escape($option_name);
                    db_query($sql, "could not update option");
                } else {
                    kv_add_option($option_name, $option_value);
                }
            } elseif ($option_name == 'hide_dashboard') {
                if (check_empty_result("SELECT * FROM saai_theme_option WHERE option_name='hide_dashboard'")) {
                    $sql = "UPDATE saai_theme_option SET	option_value=" . db_escape($option_value) . " WHERE option_name=" . db_escape($option_name);
                    db_query($sql, "could not update option");
                } else {
                    kv_add_option($option_name, $option_value);
                }
            } else {
                if (kv_get_option($option_name) != 'false') {
                    $sql = "UPDATE saai_theme_option SET option_value=" . db_escape($option_value) . " WHERE option_name=" . db_escape($option_name);
                    db_query($sql, "could not update option");
                } else {
                    kv_add_option($option_name, $option_value);
                }
            }
        }
    }
}

if (!function_exists('kv_add_option')) {
    function kv_add_option($option_name, $option_value)
    {
        if (db_num_rows(db_query("SHOW TABLES LIKE 'saai_theme_option'", "Table Checking")) == 1) {
            $sql = "INSERT INTO saai_theme_option (  option_name, option_value) VALUES (" . db_escape($option_name) . ", " . db_escape($option_value) . ")";
            db_query($sql, "could not insert Option");
        }
    }
}
if (!function_exists('kv_get_current_theme')) {
    function kv_get_current_theme()
    {
        return kv_get_option('theme');
    }
}


//Supplier Over Due Invoices
function kv_supplier_trans()
{
    $today = Today();
    $today = date2sql($today);
    $sql = "SELECT trans.trans_no, trans.reference, trans.tran_date, trans.due_date, s.supplier_id, 
        s.supp_name, s.curr_code,
        (trans.ov_amount + trans.ov_gst + trans.ov_discount) AS total,  
        (trans.ov_amount + trans.ov_gst + trans.ov_discount - trans.alloc) AS remainder,
        DATEDIFF('$today', trans.due_date) AS days  
        FROM " . TB_PREF . "supp_trans as trans, " . TB_PREF . "suppliers as s 
        WHERE s.supplier_id = trans.supplier_id
            AND trans.type = " . ST_SUPPINVOICE . " AND (ABS(trans.ov_amount + trans.ov_gst + 
                trans.ov_discount) - trans.alloc) > " . FLOAT_COMP_DELTA . "
            AND DATEDIFF('$today', trans.due_date) > 0 ORDER BY days DESC";
    $result = db_query($sql);
    //$th = array("#", trans("Ref."), trans("Date"), trans("Due Date"), trans("Supplier"), trans("Currency"), trans("Total"), trans("Remainder"),   trans("Days"));
    //start_table(TABLESTYLE);
    //table_header($th);
    echo '<table class="table table-hover">
            <thead class="text-warning">
                <tr><th>Ref</th>                    
                    <th>Due Date</th>
                    <th>Supplier</th>
                    <th>Currency</th>
                    <th>Total</th>
                    <th>Days</th>
                </tr></thead>';
    $k = 0; //row colour counter
    while ($myrow = db_fetch($result)) {
        alt_table_row_color($k);
        //label_cell(get_trans_view_str(ST_SUPPINVOICE, $myrow["trans_no"]));
        label_cell($myrow['reference']);
        //label_cell(sql2date($myrow['tran_date']));
        label_cell(sql2date($myrow['due_date']));
        $name = $myrow["supplier_id"] . " " . $myrow["supp_name"];
        label_cell($name);
        label_cell($myrow['curr_code']);
        amount_cell($myrow['total']);
        //amount_cell($myrow['remainder']);
        label_cell($myrow['days'], "align='right'");
        end_row();
    }
    end_table(1);
}

//Top 10 Selling items
function kv_stock_top($limit = 10, $width = "100", $type = 0, &$pg = null)
{
    $today = Today();
    if ($type == 2)
        $sec = 'SA_ASSETSANALYTIC';
    elseif ($type == 1)
        $sec = 'SA_WORKORDERANALYTIC';
    else
        $sec = 'SA_ITEMSTRANSVIEW';
    $begin = begin_fiscalyear();
    $begin1 = date2sql($begin);
    $today1 = date2sql($today);
    if ($type == 0) {

        $sql = "SELECT SUM((trans.unit_price * trans.quantity) * d.rate) AS total, s.stock_id, s.description, 
            SUM(trans.quantity) AS qty, SUM((s.material_cost + s.overhead_cost + s.labour_cost) * trans.quantity) AS costs FROM
            " . TB_PREF . "debtor_trans_details AS trans, " . TB_PREF . "stock_master AS s, " . TB_PREF . "debtor_trans AS d 
            WHERE trans.stock_id=s.stock_id AND trans.debtor_trans_type=d.type AND trans.debtor_trans_no=d.trans_no
            AND (d.type = " . ST_SALESINVOICE . " OR d.type = " . ST_CUSTCREDIT . ") ";
    } else {
        $sql = "SELECT SUM(m.qty * (s.material_cost + s.labour_cost + s.overhead_cost)) AS total, s.stock_id, s.description, 
            SUM(qty) AS qty FROM " . TB_PREF . "stock_master AS s, " . TB_PREF . "stock_moves AS m 
            WHERE s.stock_id=m.stock_id ";
        if ($type == 1)
            $sql .= "AND s.mb_flag='".STOCK_TYPE_MANUFACTURED."' ";
        elseif ($type == 2)
            $sql .= "AND s.mb_flag='".STOCK_TYPE_FIXED_ASSET."' ";
    }
    if ($type != 2)
        $sql .= "AND tran_date >= '$begin1' ";
    $sql .= "AND tran_date <= '$today1' GROUP by s.stock_id ORDER BY total DESC, s.stock_id 
        LIMIT $limit";
    $result = db_query($sql);

    if ($type == 0)
        $th = array(trans("Item"), trans("Sales"), trans("Costs"), trans("Quantity"));
    else
        $th = array(trans("Item"), trans("Amount"), trans("Quantity"));
    //start_table(TABLESTYLE, "width='$width%'");
    echo '<table class="table table-hover">
            <thead class="text-warning">
                <tr><th>Item</th> <th style="text-align:right">Sales</th> <th style="text-align:right">Costs</th> <th style="text-align:right">Quantity</th>
                </tr> </thead>';

    //table_header($th);

    $k = 0; //row colour counter
    $i = 0;
    while ($myrow = db_fetch($result)) {
        alt_table_row_color($k);
        $name = $myrow["description"];
        label_cell($name);
        amount_cell($myrow['total']);
        if ($type == 0)
            amount_cell($myrow['costs']);
        qty_cell($myrow['qty']);
        if ($pg != NULL) {
            $pg->x[$i] = $name;
            $pg->y[$i] = $myrow['total'];
            if ($type == 0)
                $pg->z[$i] = $myrow['costs'];
        }
        $i++;
        end_row();
    }
    end_table(1);

}


function last_week_service_charge() {

    $sql = "select
date_format(d.tran_date, '%W') dayname,
d.tran_date,
sum(a.quantity*a.unit_price)-
sum(a.discount_amount * a.quantity)-
sum(a.pf_amount*a.quantity)-
sum(IfNull(e.reward_amount, 0)) as service_charge 
from 0_debtor_trans_details a 
left join 0_stock_master b on b.stock_id=a.stock_id 
left join 0_stock_category c on c.category_id=b.category_id 
left join 0_debtor_trans d on d.trans_no = a.debtor_trans_no and d.`type`=10 
LEFT JOIN 0_customer_rewards e ON e.trans_no = d.trans_no AND e.trans_type = 10 
where a.debtor_trans_type=10    
group by weekday(d.tran_date), date_format(d.tran_date, '%W') order by d.tran_date limit 6";

    $result = db_query($sql);

?>
    <table class="table table-hover" style="text-align: center">
    <thead class="text-warning">
    <tr>
        <th>Week Day</th>
        <th>Service Charge</th>
    </tr>
    </thead><?php
    while ($myrow = db_fetch($result)) {
        label_cell($myrow["dayname"]);
        label_cell($myrow["service_charge"]);
        end_row();
    }
    end_table(1);


}


function today_report_dashboard() {

    $today = Today();
    $fdate = date2sql($today);


    $sql = "select 'Invoice Count' as description,count(*) as desc_val from 0_debtor_trans where type=10 and tran_date='$fdate'  union 

select 'Service Count' as description,IFNULL(sum(a.quantity),0) as desc_val from 0_debtor_trans_details a 
left join 0_debtor_trans b on b.trans_no=a.debtor_trans_no and a.debtor_trans_type=10 and b.type=10
 where a.debtor_trans_type=10 and b.tran_date='$fdate' union 

select 'Net Service Charge' as description, IFNULL(ROUND(sum(net_service_charge),2),0) as desc_val from invoice_report_detail_view 
 where transaction_date='$fdate' union 

select 'Total Invoice Amount' as description, IFNULL(ROUND(sum(invoice_amount),2),0) as desc_val from invoice_report_detail_view 
 where transaction_date='$fdate' union 

SELECT 'Total Collection' as description,IFNULL(ROUND(SUM(ABS(0_gl_trans.amount)),2),0) as desc_val FROM 0_gl_trans 
WHERE 0_gl_trans.type = 12 AND 0_gl_trans.account = 1200 and tran_date='$fdate' ";



    $result = db_query($sql,"Error");

    return $result;

}


// Average Daily Sales
function kv_weekly_sales()
{
    $ageSales = 'SELECT week_day, dayname, (gross_output/weeks) gross, (net_output/weeks) net, (payable/weeks) tax, weeks '
        . 'FROM (SELECT weekday(trans_date) week_day,'
        . 'date_format(trans_date, \'%W\') dayname,'
        . 'sum(gross_output) gross_output,'
        . 'sum(net_output) net_output,'
        . 'sum(payable) payable,'
        . 'count(*) weeks '
        . 'FROM (SELECT bt.trans_date trans_date,'
        . 'sum((ttd.net_amount+ttd.amount)*ex_rate) gross_output,'
        . 'sum(ttd.net_amount*ex_rate) net_output,'
        . 'sum(ttd.amount*ex_rate) payable '
        . 'FROM ' . TB_PREF . 'bank_trans bt '
        . 'INNER JOIN ' . TB_PREF . 'cust_allocations ca '
        . 'ON bt.type = ca.trans_type_from '
        . 'AND bt.trans_no = ca.trans_no_from '
        . 'INNER JOIN ' . TB_PREF . 'debtor_trans dt '
        . 'ON dt.type = ca.trans_type_from '
        . 'AND dt.trans_no = ca.trans_no_from '
        . 'INNER JOIN ' . TB_PREF . 'trans_tax_details ttd '
        . 'ON ttd.trans_type = ca.trans_type_to '
        . 'AND ttd.trans_no = ca.trans_no_to '
        . 'INNER JOIN ' . TB_PREF . 'tax_types tt '
        . 'ON tt.id = ttd.tax_type_id '
        . 'GROUP BY bt.trans_date '
        . ') days  '
        . 'GROUP BY weekday(trans_date), date_format(trans_date, \'%W\') '
        . ') items order by gross_output desc';

    $result = db_query($ageSales);
    //display_error($ageSales);
    //echo  trans("Bank Account Balances");//display_title($title);
    ?>
    <table class="table table-hover">
    <thead class="text-warning">
    <tr>
        <th>Week Day</th>
        <th>Day</th>
        <th>Gross</th>
        <th>Net</th>
        <th>Tax</th>
        <th>Weeks</th>
    </tr>
    </thead><?php
    while ($myrow = db_fetch($result)) {
        label_cell($myrow["week_day"]);
        label_cell($myrow["dayname"]);
        amount_cell($myrow['gross']);
        amount_cell($myrow['net']);
        amount_cell($myrow['tax']);
        label_cell($myrow["weeks"]);
        end_row();
    }
    end_table(1);
}

// Class Balances
function kv_gl_top(&$pg = null){
    $today = Today();
    $begin = begin_fiscalyear();
    $begin1 = date2sql($begin);
    $today1 = date2sql($today);
    $sql = "SELECT SUM(amount) AS total, c.class_name, c.ctype FROM
        " . TB_PREF . "gl_trans," . TB_PREF . "chart_master AS a, " . TB_PREF . "chart_types AS t, 
        " . TB_PREF . "chart_class AS c WHERE
        account = a.account_code AND a.account_type = t.id AND t.class_id = c.cid
        AND IF(c.ctype > 3, tran_date >= '$begin1', tran_date >= '0000-00-00') 
        AND tran_date <= '$today1' GROUP BY c.cid ORDER BY c.cid";
    $result = db_query($sql, "Transactions could not be calculated"); ?>

    <table class="table table-hover">
    <?php
    $i = 0;
    $total = 0;
    $k = 0;
    while ($myrow = db_fetch($result)) {

        if ($myrow['ctype'] > 3) {
            $total += $myrow['total'];
            $myrow['total'] = -$myrow['total'];
            if ($pg != null) {
                $pg->x[$i] = $myrow['class_name'];
                $pg->y[$i] = abs($myrow['total']);
            }
            $i++;
        }
        alt_table_row_color($k);
        label_cells($myrow['class_name'], number_format2($myrow['total'], user_price_dec()),
            " style='font-weight:bold;'", "style='font-weight:bold;' align=right");
        end_row();
    }
    $calculated = trans("Calculated Return");
    label_row("&nbsp;", "");
    label_row($calculated, number_format2(-$total, user_price_dec()),
        "class='label' style='font-weight:bold;'", "style='font-weight:bold;' align=right");
    if ($pg != null) {
        $pg->x[$i] = $calculated;
        $pg->y[$i] = -$total;
    }
    end_table(1);
    //return $title;
}

//Bank Account balances Table
function kv_bank_balance(){
    $today = Today();
    $today = date2sql($today);
//    $sql = "SELECT bank_act, bank_account_name, bank_curr_code, SUM(amount) balance FROM " . TB_PREF . "bank_trans bt
//                INNER JOIN " . TB_PREF . "bank_accounts ba ON bt.bank_act = ba.id    WHERE trans_date <= '$today'  AND inactive <> 1    GROUP BY bank_act, bank_account_name    ORDER BY bank_account_name";

    $sql = "select b.account_name as bank_account_name,ROUND(sum(amount),2) balance from 0_gl_trans a 
left join 0_chart_master b on b.account_code = a.account 
 inner join 0_bank_accounts c on c.account_code=b.account_code 
 where a.tran_date <= '$today' 
group by account order by b.account_name limit 10";

    $result = db_query($sql);
    //echo  trans("Bank Account Balances");//display_title($title);
    ?>
    <table class="table table-hover">
    <thead class="text-warning">
    <tr>
        <th>Account</th>
        <th style="text-align: right;">Balance</th>
    </tr>
    </thead><?php $k = 0; //row colour counter
    while ($myrow = db_fetch($result)) {
        alt_table_row_color($k);
        label_cell($myrow["bank_account_name"]);
        amount_cell($myrow['balance']);
        end_row();
    }
    end_table(1);
}

/**
 * @param null $status
 * Get Todays invoices
 */
function getTodaysInvoices($status = null)
{
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


    if(!empty($status)) {
        $sql .= " AND payment_status = $status";
    }

    /** IF Admin / Accountant : Shows only not paid and partially paid invoices */
    if(in_array($_SESSION['wa_current_user']->access,[9,2])){
        $sql .= " AND payment_status <> 1 ";
    }
    else {
        $sql .= " AND created_by = ".$_SESSION['wa_current_user']->user;
    }




    $result = db_query($sql, "Transactions could not be calculated");
    $table_html = "";
    $i = 0;
    while ($myrow = db_fetch($result)) {

        $class = 'class="oddrow"';
        if ($i % 2 == 0)
            $class = 'class="evenrow"';

        $payment_status = "Not Paid";
        if($myrow['payment_status'] == '1') {
            $payment_status = 'Fully Paid';
        }
        if($myrow['payment_status'] == '3') {
            $payment_status = 'Partially Paid';
        }


        $update_transaction_id_link = "sales/customer_invoice.php?ModifyInvoice=".$myrow['trans_no'];


        if($myrow['payment_flag'] != 0 && $myrow['payment_flag'] != 3) {//TASHEEL
            $update_transaction_id_link .= "&is_tadbeer=1&show_items=ts";
        }

        if($myrow['payment_flag'] == 4 || $myrow['payment_flag'] == 5) {//TADBEER
            $update_transaction_id_link .= "&is_tadbeer=1&show_items=tb";
        }


        $table_html .= "<tr $class>";
        $table_html .= "<td style='text-align: center'>" . $myrow['invoice_no'] . "</td>";
        $table_html .= "<td style='text-align: center'>" . $myrow['customer_name'] . "</td>";
        $table_html .= "<td style='text-align: center'>" . $myrow['display_customer'] . "</td>";
        $table_html .= "<td style='text-align: center'>" . $myrow['invoice_amount'] . "</td>";
        $table_html .= "<td style='text-align: center'>" . $payment_status . "</td>";
        $table_html .= "<td style='text-align: center'><a target='_blank' href='$update_transaction_id_link'>

<img src='themes/daxis/images/edit.gif' style='vertical-align:middle;width:12px;height:12px;border:0;' title='Edit'/>

</a></td>";
        $table_html .= "</tr>";
        $i++;
    }
    return $table_html;



}


function kv_customer_trans()
{
    $today = Today();
    $today = date2sql($today);

    $sql = "SELECT trans.trans_no, trans.reference, trans.tran_date, trans.due_date, debtor.debtor_no, 
        debtor.name, branch.br_name, debtor.curr_code,
        (trans.ov_amount + trans.ov_gst + trans.ov_freight 
            + trans.ov_freight_tax + trans.ov_discount) AS total,  
        (trans.ov_amount + trans.ov_gst + trans.ov_freight 
            + trans.ov_freight_tax + trans.ov_discount - trans.alloc) AS remainder,
        DATEDIFF('$today', trans.due_date) AS days  
        FROM " . TB_PREF . "debtor_trans as trans, " . TB_PREF . "debtors_master as debtor, 
            " . TB_PREF . "cust_branch as branch
        WHERE debtor.debtor_no = trans.debtor_no AND trans.branch_code = branch.branch_code
            AND trans.type = " . ST_SALESINVOICE . " AND (trans.ov_amount + trans.ov_gst + trans.ov_freight 
            + trans.ov_freight_tax + trans.ov_discount - trans.alloc) > " . FLOAT_COMP_DELTA . " 
            AND DATEDIFF('$today', trans.due_date) > 0 ORDER BY days DESC";
    $result = db_query($sql); ?>

    <table class="table table-hover">
        <thead class="text-warning">
        <tr>
            <th>Ref</th>
            <th>Due Date</th>
            <th>Customer</th>
            <th>Currency</th>
            <th>Total</th>
            <th>Days</th>
        </tr>
        </thead>
    <?php $k = 0; //row colour counter
    while ($myrow = db_fetch($result)) {
        alt_table_row_color($k);
        // label_cell(get_trans_view_str(ST_SALESINVOICE, $myrow["trans_no"]));
        label_cell($myrow['reference']);
        // label_cell(sql2date($myrow['tran_date']));
        label_cell(sql2date($myrow['due_date']));
        $name = $myrow["debtor_no"] . " " . $myrow["name"];
        label_cell($name);
        //  label_cell($myrow['br_name']);
        label_cell($myrow['curr_code']);
        amount_cell($myrow['total']);
        // amount_cell($myrow['remainder']);
        label_cell($myrow['days'], "align='right'");
        end_row();
    }
    end_table(1);
}

//Over due Recurrent Invoice
function kv_get_recurrent_invoices($date = null)
{
    // last_sent stores end date of last generated invoice _plus_ 1 (or is empty)
    if ($date) {
        $date = date2sql($date);
        // we can issue invoice after or at last day of covered period
        $sql = "SELECT *, DATE_ADD(DATE_ADD(IF(`last_sent`='0000-00-00', `begin`, `last_sent`), INTERVAL `monthly` MONTH), INTERVAL `days` DAY) <= '$date'
            AND last_sent < DATE_ADD(`end`, INTERVAL 1 DAY) as overdue";
    } else
        $sql = "SELECT * ";

    $sql .= " FROM " . TB_PREF . "recurrent_invoices ORDER BY description, group_no, debtor_no";

    return db_query($sql, "could not get recurrent invoices");
}

function kv_customer_recurrent_invoices()
{
    $today = Today();
    $result = kv_get_recurrent_invoices($today);

    echo '<table class="table table-hover">
            <thead class="text-warning">
                <tr><th>Description</th>
                    <th>Template No</th>
                    <th>Customer</th>
                    <th>Branch/Group</th>
                    <th>Next invoice</th>
                </tr></thead>';
    $k = 0;
    while ($myrow = db_fetch($result)) {
        if (!$myrow['overdue'])
            continue;
        alt_table_row_color($k);

        label_cell($myrow["description"]);
        label_cell(get_customer_trans_view_str(ST_SALESORDER, $myrow["order_no"]));
        if ($myrow["debtor_no"] == 0) {
            label_cell("");

            label_cell(get_sales_group_name($myrow["group_no"]));
        } else {
            label_cell(get_customer_name($myrow["debtor_no"]));
            label_cell(get_branch_name($myrow['group_no']));
        }
        label_cell(kv_calculate_next_invoice($myrow), "align='center'");
        end_row();
    }
    end_table(1);
}

//------------------------------------------------------------
function Saai_color_schemes($label, $name, $selected_id = null, $submit_on_change = false)
{
    $hrm_empl_ref_type = array(
        'default' => trans('Default'),
        'blue' => trans('Blue'),
        'green' => trans('Green'),
        'dark' => trans('Dark')
    );

    echo "<tr><td class='label'>$label</td><td>";
    $options = array(
        'select_submit' => $submit_on_change
    );
    echo array_selector($name, $selected_id, $hrm_empl_ref_type, $options);
    echo "</td></tr>\n";
}


function getDailyReport($date = null)
{

//    $sql = "SELECT (
//SELECT COUNT(0)
//FROM `0_debtor_trans`
//WHERE ((`0_debtor_trans`.`type` = 10) AND (`0_debtor_trans`.`tran_date` = '$date'))) AS `invoice_count`,
//`b`.`tran_date` AS `tran_date`,(
//SELECT COUNT(`0_debtor_trans_details`.`id`)
//FROM (`0_debtor_trans`
//LEFT JOIN `0_debtor_trans_details` ON((`0_debtor_trans`.`trans_no` = `0_debtor_trans_details`.`debtor_trans_no`)))
//WHERE ((`0_debtor_trans_details`.`debtor_trans_type` = 10) AND
//(`0_debtor_trans`.`type` = 10) AND (`0_debtor_trans`.`tran_date` = '$date'))) AS `total_service_count`,(
//SELECT SUM((`0_debtor_trans`.`ov_amount` + `0_debtor_trans`.`ov_gst`))
//FROM `0_debtor_trans`
//WHERE ((`0_debtor_trans`.`type` = 10) AND (`0_debtor_trans`.`tran_date` = '$date'))) AS `total_invoice_amount`,(
//SELECT SUM(`0_debtor_trans`.`alloc`)
//FROM `0_debtor_trans`
//WHERE ((`0_debtor_trans`.`type` = 10) AND (`0_debtor_trans`.`tran_date` = '$date'))) AS `total_amount_recieved`,(
//SELECT (SUM((`0_debtor_trans`.`ov_amount` + `0_debtor_trans`.`ov_gst`)) - SUM(`0_debtor_trans`.`alloc`))
//FROM `0_debtor_trans`
//WHERE ((`0_debtor_trans`.`type` = 10) AND (`0_debtor_trans`.`tran_date` = '$date'))) AS `pending_amount`,(
//SELECT (SUM((`0_debtor_trans_details`.`quantity` * `0_debtor_trans_details`.`unit_price`)) -
//SUM((`0_debtor_trans_details`.`quantity` * `0_debtor_trans_details`.`discount_amount`)))
//FROM (`0_debtor_trans`
//LEFT JOIN `0_debtor_trans_details` ON((`0_debtor_trans`.`trans_no` = `0_debtor_trans_details`.`debtor_trans_no`)))
//WHERE ((`0_debtor_trans_details`.`debtor_trans_type` = 10) AND (`0_debtor_trans`.`type` = 10) AND
//(`0_debtor_trans`.`tran_date` = '$date'))) AS `total_service_charge`,(
//SELECT SUM((`0_debtor_trans_details`.`quantity` * `0_debtor_trans_details`.`user_commission`))
//FROM (`0_debtor_trans`
//LEFT JOIN `0_debtor_trans_details` ON((`0_debtor_trans`.`trans_no` = `0_debtor_trans_details`.`debtor_trans_no`)))
//WHERE ((`0_debtor_trans_details`.`debtor_trans_type` = 10) AND (`0_debtor_trans`.`type` = 10) AND
//(`0_debtor_trans`.`tran_date` = '$date'))) AS `total_commission`,(
//SELECT SUM(ABS(`0_gl_trans`.`amount`))
//FROM `0_gl_trans`
//WHERE ((`0_gl_trans`.`type` = 12) AND (`0_gl_trans`.`account` = 1200) AND
//(`b`.`tran_date` = '$date'))) AS `total_collection`
//FROM ((`0_debtor_trans` `a`
//JOIN `0_gl_trans` `b` ON((`a`.`trans_no` = `b`.`type_no`)))
//JOIN `0_debtor_trans_details` `c` ON((`a`.`trans_no` = `c`.`debtor_trans_no`)))
//WHERE ((`c`.`debtor_trans_type` = 10) AND (`a`.`type` = 10)) and `b`.`tran_date` = '$date'
//GROUP BY `b`.`tran_date`
//ORDER BY `b`.`tran_date`";
//
////    if ($date) {
////        $sql .= " and tran_date='$date' ";
////    }
////
////    $sql .= " order by tran_date desc LIMIT 10";
//
//
////    print_r($sql); die;
//
//    $result = db_query($sql, "Transactions could not be calculated");
//
//    $table_html = "";
//
//    $i = 0;
//
//    while ($myrow = db_fetch($result)) {
//
//        $class = 'class="oddrow"';
//        if ($i % 2 == 0)
//            $class = 'class="evenrow"';
//
//        $table_html .= "<tr $class>";
//        $table_html .= "<td>" . $myrow['tran_date'] . "</td>";
//        $table_html .= "<td style='text-align: center'>" . $myrow['invoice_count'] . "</td>";
//        $table_html .= "<td style='text-align: center'>" . $myrow['total_service_count'] . "</td>";
//        $table_html .= "<td style='text-align: right'>" . number_format2($myrow['total_service_charge'], user_price_dec()) . "</td>";
//        $table_html .= "<td style='text-align: right'>" . number_format2($myrow['total_invoice_amount'], user_price_dec()) . "</td>";
//        $table_html .= "<td style='text-align: right'>" . number_format2($myrow['total_collection'], user_price_dec()) . "</td>";
//        $table_html .= "</tr>";
//        $i++;
//    }
//    return $table_html;

}


function getInvCountReport($date = null)
{

    $sql = "select c.description,sum(a.quantity) as inv_count,sum(a.quantity*a.unit_price) as service_charge from 0_debtor_trans_details a 
left join 0_stock_master b on b.stock_id=a.stock_id 
left join 0_stock_category c on c.category_id=b.category_id 
left join 0_debtor_trans d on d.trans_no = a.debtor_trans_no and d.`type`=10 where a.debtor_trans_type=10";

    if ($date) {
        $sql .= " and d.tran_date='$date' ";
    }

    $sql .= " group by b.category_id";
    $result = db_query($sql, "Transactions could not be calculated");

    $table_html = "";

    $i = 0;

    while ($myrow = db_fetch($result)) {

//        print_r($myrow); die;

        $class = 'class="oddrow"';
        if ($i % 2 == 0)
            $class = 'class="evenrow"';

        $table_html .= "<tr $class>";
        $table_html .= "<td style='text-align: center'>" . $myrow['description'] . "</td>";
        $table_html .= "<td style='text-align: center'>" . $myrow['inv_count'] . "</td>";
        $table_html .= "<td style='text-align: center'>" . $myrow['service_charge'] . "</td>";
        $table_html .= "</tr>";
        $i++;
    }
    return $table_html;

}


function getCashAccounts()
{

    $sql = "select * from 0_chart_master where account_type=1 and inactive=0";
    $result = db_query($sql, "Transactions could not be calculated");
    $html = "";

    while ($myrow = db_fetch($result)) {
        $html .= "<option value='" . $myrow['account_code'] . "'>" . $myrow['account_name'] . "</option>";
    }
    return $html;

}


function getCollectionReport($date = null, $account = null)
{

    $wheres = "";
    if ($date) {
        $wheres .= " and 0_gl_trans.tran_date='$date' ";
    }
    if ($account) {
        $wheres .= " and 0_chart_master.account_code=$account";
    }

    $sql = "select  
collection_table.user_id,
0_users.user_id as user_name,
ROUND(IFNULL(sum(dr_amount),0),2) as total_dr,
ROUND(IFNULL(sum(cr_amount),0),2) as total_cr,
ROUND(IFNULL(sum(dr_amount),0)-IFNULL(sum(cr_amount),0),2) as balance from 

(SELECT 

case when `0_gl_trans`.`amount` < 0 then ABS(`0_gl_trans`.`amount`)  end AS `cr_amount`,
case when `0_gl_trans`.`amount` > 0 then ABS(`0_gl_trans`.`amount`)  end AS `dr_amount`,

(SELECT `0_audit_trail`.`user` AS `user_id`
FROM `0_audit_trail`
WHERE (`0_audit_trail`.`trans_no` = `0_gl_trans`.`type_no`)
LIMIT 1) AS `user_id`,
`0_gl_trans`.`type_no` AS `type_no`

FROM (`0_chart_master`
JOIN `0_gl_trans` ON((`0_gl_trans`.`account` = `0_chart_master`.`account_code`)))
WHERE ((`0_gl_trans`.`type` IN (12,0,10)) AND (`0_gl_trans`.`amount` <> 0)) " . $wheres . "
ORDER BY `0_gl_trans`.`counter` DESC) as collection_table 

left join 0_users on 0_users.id=collection_table.user_id ";


    $sql .= " group by collection_table.user_id";


//    echo $sql; die;

    $result = db_query($sql, "Transactions could not be calculated");

    $table_html = "";

    $i = 0;

    while ($myrow = db_fetch($result)) {

        $class = 'class="oddrow"';
        if ($i % 2 == 0)
            $class = 'class="evenrow"';

        $table_html .= "<tr $class>";
        $table_html .= "<td style='text-align: center'>" . $myrow['user_name'] . "</td>";
        $table_html .= "<td style='text-align: center'>" . number_format2($myrow['total_dr'], user_price_dec()) . "</td>";
        $table_html .= "<td style='text-align: center'>" . number_format2($myrow['total_cr'], user_price_dec()) . "</td>";
        $table_html .= "<td style='text-align: center'>" . number_format2($myrow['balance'], user_price_dec()) . "</td>";
        $table_html .= "</tr>";
        $i++;
    }
    return $table_html;

}


?>