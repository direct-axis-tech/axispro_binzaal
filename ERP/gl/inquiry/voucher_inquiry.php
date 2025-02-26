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

$page_security = 'SA_GLANALYTIC';
$path_to_root="../..";

include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(800, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();

page(trans($help_context = "Voucher Inquiry"), false, false, "", $js);

//-----------------------------------------------------------------------------------
// Ajax updates
//
if (get_post('Search'))
{
	$Ajax->activate('journal_tbl');
}
//--------------------------------------------------------------------------------------
if (!isset($_POST['filterType']))
	$_POST['filterType'] = -1;

start_form();

start_table(TABLESTYLE_NOBORDER);
start_row();

ref_cells(trans("Reference:"), 'Ref', '',null, trans('Enter reference fragment or leave empty'));

date_cells(trans("From:"), 'FromDate');
date_cells(trans("To:"), 'ToDate');

check_cells(trans("Order by Upcoming PDC:"),'pdc_order');

end_row();
start_row();
//ref_cells(trans("Memo:"), 'Memo', '',null, trans('Enter memo fragment or leave empty'));
//users_list_cells(trans("User:"), 'userid', null, false);
//if (get_company_pref('use_dimension') && isset($_POST['dimension'])) // display dimension only, when started in dimension mode
//	dimensions_list_cells(trans('Dimension:'), 'dimension', null, true, null, true);
//check_cells( trans("Show closed:"), 'AlsoClosed', null);
submit_cells('Search', trans("Search"), '', '', 'default');
end_row();
end_table();

function journal_pos($row)
{
	return $row['gl_seq'] ? $row['gl_seq'] : '-';
}

function tran_date($row) {

    return sql2date($row['tran_date']);
}

function reference($row) {
    return $row['reference'];
}

function amount($row) {

    return $row['amount'];
}

function chq_no($row) {
    return $row['chq_no'];
}

function chq_date($row) {
    return $row['chq_date'];
}




function counter_party($row) {

    $trans_type = $row['voucher_type'] == "PV" ? 1 : 2;
    return get_counterparty_name($trans_type,$row['trans_no']);
}

function systype_name($row, $type)
{
	global $systypes_array;
	
	return $row['voucher_type'] == "PV" ? "Payment Voucher" : "Receipt Voucher";
}

function view_link($row) 
{
    $trans_type = $row['voucher_type'] == "PV" ? 1 : 2;
	return get_trans_view_str($trans_type, $row["trans_no"]);
}

function gl_link($row) 
{

    $trans_type = $row['voucher_type'] == "PV" ? 1 : 2;

    $view_link =  get_gl_view_str($trans_type, $row["trans_no"]);

    $print_link = '../../voucher_print/?voucher_id='.$row['id'];
    $view_link .= "<a href='$print_link' target='_blank'>
<img src='../../themes/daxis/images/print.png' style='vertical-align:middle;width:12px;height:12px;border:0;' title='Print'>
</a>";

	return $view_link;
}

function edit_link($row)
{

    $row['trans_type'] = $row['voucher_type'] == "PV" ? 1 : 2;
	$ok = true;
	if ($row['trans_type'] == ST_SALESINVOICE)
	{
		$myrow = get_customer_trans($row["trans_no"], $row["trans_type"]);
		if ($myrow['alloc'] != 0 || get_voided_entry(ST_SALESINVOICE, $row["trans_no"]) !== false)
			$ok = false;
	}
//	return $ok ? trans_editor_link( $row["trans_type"], $row["trans_no"]) : '';
}



function get_sql_for_voucher_inquiry() {


    $where = "";

    $from =  get_post('FromDate');
    $to =  get_post('ToDate');
    $ref = get_post('Ref');

    $pdc_order = check_value('pdc_order');

    $order_by = "";
    if($pdc_order) {
        $order_by.= "ORDER BY a.chq_date ASC";
        $where.= " AND a.chq_date >= CURDATE()";
    }

    if(isset($from) && !empty($from)) {
        $where .= " AND a.tran_date >= '".date2sql($from)."'";
    }

    if(isset($to) && !empty($to)) {
        $where .= " AND a.tran_date <= '".date2sql($to)."'";
    }

    if(isset($ref) && !empty($ref)) {
        $where .= " AND a.reference =".db_escape($ref);
    }

    $sql = "SELECT a.*
            FROM 0_vouchers a
            INNER JOIN 0_gl_trans b ON b.`type` in (1,2) AND b.type_no=a.trans_no 
               and b.type= IF(a.voucher_type='PV',1,2) 
            WHERE 1=1 AND b.amount <> 0 $where 
            GROUP BY a.voucher_type,a.trans_no $order_by";


    return $sql;
}

$sql = get_sql_for_voucher_inquiry();

$cols = array(
	trans("Date") =>array('fun'=>'tran_date'),
	trans("Voucher Type") => array('fun'=>'systype_name'),
 	trans("Counterparty") => array('fun' => 'counter_party'),
	trans("Reference") => array('fun'=>'reference'),
	trans("Amount") => array('fun' =>'amount','type' => 'amount'),
	trans("Cheque No") => array('fun' =>'chq_no','align' => 'center'),
	trans("Cheque Date") => array('fun' =>'chq_date','type' => 'date'),
	trans("View") => array('insert'=>true, 'fun'=>'gl_link'),
	array('insert'=>true, 'fun'=>'edit_link')
);


$table =& new_db_pager('journal_tbl', $sql, $cols);

$table->width = "80%";

display_db_pager($table);

end_form();
end_page();

