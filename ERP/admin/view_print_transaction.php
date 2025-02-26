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

use App\Models\Labour\Contract;

$page_security = 'SA_VIEWPRINTTRANSACTION';
$path_to_root = "..";

include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/admin/db/transactions_db.inc");

include_once($path_to_root . "/reporting/includes/reporting.inc");
$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(800, 500);
if (user_use_date_picker())
    $js .= get_js_date_picker();
page(trans($help_context = "View or Print Transactions"), false, false, "", $js);

//----------------------------------------------------------------------------------------
function view_link($trans)
{
	if (!isset($trans['type']))
		$trans['type'] = $_POST['filterType'];
	return get_trans_view_str($trans["type"], $trans["trans_no"]);
}

function prt_link($row)
{
	if (!isset($row['type']))
		$row['type'] = $_POST['filterType'];
  	if ($row['type'] == ST_PURCHORDER || $row['type'] == ST_SALESORDER || $row['type'] == ST_SALESQUOTE || 
  		$row['type'] == ST_WORKORDER)
 		return print_document_link($row['trans_no'], trans("Print"), true, $row['type'], ICON_PRINT);
 	else	
		return print_document_link($row['trans_no']."-".$row['type'], trans("Print"), true, $row['type'], ICON_PRINT);
}

function gl_view($row)
{
	if (!isset($row['type']))
		$row['type'] = $_POST['filterType'];
	return get_gl_view_str($row["type"], $row["trans_no"]);
}

function date_view($row)
{
	return $row['trans_date'];
}

function ref_view($row)
{
    return $row['ref'];
}

function ref_invoices($row)
{
    $row['type'] = $_POST['filterType'];
    return get_customer_invoices($row["type"], $row["trans_no"]);
}

function viewing_controls()
{
	display_note(trans("Only documents can be printed."));

    start_table(TABLESTYLE_NOBORDER);
	start_row();

	if(!isset($_POST['filterType']) || $_POST['filterType'] == '') {
        $_POST['filterType'] = ST_SALESINVOICE;
    }

	systypes_list_cells(trans("Type:"), 'filterType', null, true, array(ST_CUSTOMER, ST_SUPPLIER, Contract::TEMPORARY_CONTRACT));


    text_cells(trans("Reference"), 'ref_no', null);

	date_cells(trans("From:"), 'TransDateFrom', '', null, -user_transaction_days());
    date_cells(trans("To:"), 'TransDateTill', '', null);

	if (!isset($_POST['FromTransNo']))
		$_POST['FromTransNo'] = "1";
	if (!isset($_POST['ToTransNo']))
		$_POST['ToTransNo'] = "9999999";

    hidden('FromTransNo');

    hidden('ToTransNo');

    submit_cells('ProcessSearch', trans("Search"), '', '', 'default');

	end_row();
    end_table(1);

}

//----------------------------------------------------------------------------------------

function check_valid_entries()
{
	if (!is_numeric($_POST['FromTransNo']) OR $_POST['FromTransNo'] <= 0)
	{
		display_error(trans("The starting transaction number is expected to be numeric and greater than zero."));
		return false;
	}

	if (!is_numeric($_POST['ToTransNo']) OR $_POST['ToTransNo'] <= 0)
	{
		display_error(trans("The ending transaction number is expected to be numeric and greater than zero."));
		return false;
	}

	return true;
}

//----------------------------------------------------------------------------------------

function handle_search()
{
	if (check_valid_entries()==true)
	{

        $trans_ref_no = "";
        if (isset($_POST['ref_no']) && !empty($_POST['ref_no']))
            $trans_ref_no = $_POST['ref_no'];

		$trans_ref = false;
		$sql = get_sql_for_view_transactions(
			get_post('filterType'),
			get_post('FromTransNo'),
			get_post('ToTransNo'),
			$trans_ref,
			$trans_ref_no,
			false,
			get_post('TransDateFrom'),
			get_post('TransDateTill')
		);
		if ($sql == "")
			return;

		$print_type = get_post('filterType');
        $print_out = in_array($print_type, [
            ST_SALESINVOICE, ST_CUSTCREDIT, ST_CUSTDELIVERY, ST_PURCHORDER, 
            ST_SALESORDER, ST_SALESQUOTE, ST_CUSTPAYMENT, ST_SUPPAYMENT, ST_WORKORDER,
            ST_CUSTREFUND
        ]);

		if($_POST['filterType']=='12')
        {
            $cols = array(
                trans("") => array('insert'=>true, 'fun'=>'view_link'),
                trans("Reference") => array('fun'=>'ref_view','align'=>'center'),
                trans("Invoices") => array('fun'=>'ref_invoices','align'=>'center'),
                trans("Date") => array('type'=>'date', 'fun'=>'date_view'),
                trans("Print") => array('insert'=>true, 'fun'=>'prt_link'),
                trans("GL") => array('insert'=>true, 'fun'=>'gl_view')
            );
        }
		else
        {
            $cols = array(
                trans("") => array('insert'=>true, 'fun'=>'view_link'),
                trans("Reference") => array('fun'=>'ref_view','align'=>'center'),
                trans("Date") => array('type'=>'date', 'fun'=>'date_view'),
                trans("Print") => array('insert'=>true, 'fun'=>'prt_link'),
                trans("GL") => array('insert'=>true, 'fun'=>'gl_view')
            );
        }




		if(!$print_out) {
			array_remove($cols, 3);
		}
		if(!$trans_ref) {
			array_remove($cols, 1);
		}

		$table =& new_db_pager('transactions', $sql, $cols);
		$table->width = "40%";
		display_db_pager($table);
	}

}

//----------------------------------------------------------------------------------------

if (isset($_POST['ProcessSearch']))
{
	if (!check_valid_entries())
		unset($_POST['ProcessSearch']);
	$Ajax->activate('transactions');
}

//----------------------------------------------------------------------------------------

start_form(false);
	viewing_controls();
	handle_search();
end_form(2);

end_page();

