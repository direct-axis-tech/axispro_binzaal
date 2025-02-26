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
$page_security = 'SA_SUPPLIERALLOC';
$path_to_root = "../..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/banking.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
//include_once($path_to_root . "/purchasing/includes/ui/supp_alloc_ui.inc");

$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);

add_js_file('allocate.js');

page(trans($help_context = "Allocate Supplier Payment or Credit Note"), false, false, "", $js);

//--------------------------------------------------------------------------------

function clear_allocations()
{
	if (isset($_SESSION['alloc']))
	{
		unset($_SESSION['alloc']->allocs);
		unset($_SESSION['alloc']);
	}
	//session_register("alloc");
}
//--------------------------------------------------------------------------------

function edit_allocations_for_transaction($type, $trans_no)
{
	global $systypes_array;

	start_form();

	$cart = $_SESSION['alloc'];

    display_heading(trans("Allocation of") . " " . $systypes_array[$cart->type] . " # " . $cart->trans_no);

	display_heading($cart->person_name);

    display_heading2(trans("Date:") . " <b>" . $cart->date_ . "</b>");

   	display_heading2(trans("Total:"). " <b>" . price_format(-$cart->bank_amount).' '.$cart->currency."</b>");

	if (floatcmp($cart->bank_amount, $cart->amount))
	{
	    $total = trans("Amount ot be settled:") . " <b>" . price_format(-$cart->amount).' '.$cart->person_curr."</b>";
		if ($cart->currency != $cart->person_curr)
    		$total .= sprintf(" (%s %s/%s)",  exrate_format($cart->bank_amount/$cart->amount), $cart->currency, $cart->person_curr);
	   	display_heading2($total);
	}
    echo "<br>";

  	div_start('alloc_tbl');
    if (count($cart->allocs) > 0)
    {
		show_allocatable(true);

     	submit_center_first('UpdateDisplay', trans("Refresh"), trans('Start again allocation of selected amount'), true);
       	submit('Process', trans("Process"), true, trans('Process allocations'), 'default');
   		submit_center_last('Cancel', trans("Back to Allocations"),
			trans('Abandon allocations and return to selection of allocatable amounts'), 'cancel');
	}
	else
	{
    	display_note(trans("There are no unsettled transactions to allocate."), 0, 1);
   		submit_center('Cancel', trans("Back to Allocations"), true,
			trans('Abandon allocations and return to selection of allocatable amounts'), 'cancel');
    }

	div_end();
	end_form();
}
//--------------------------------------------------------------------------------

if (isset($_POST['Process']))
{
	if (check_allocations())
	{
		$_SESSION['alloc']->write();
		clear_allocations();
		$_POST['Cancel'] = 1;
	}
}

//--------------------------------------------------------------------------------

if (isset($_POST['Cancel']))
{
	clear_allocations();
	meta_forward($path_to_root . "/purchasing/allocations/supplier_allocation_main.php");
}

//--------------------------------------------------------------------------------

if (isset($_GET['trans_no']) && isset($_GET['trans_type']))
{
	$_SESSION['alloc'] = new allocation($_GET['trans_type'], $_GET['trans_no'], @$_GET['supplier_id'], PT_SUPPLIER);
}

if (get_post('UpdateDisplay'))
{
	$_SESSION['alloc']->read();
	$Ajax->activate('alloc_tbl');
}

if (isset($_SESSION['alloc']))
{
	edit_allocations_for_transaction($_SESSION['alloc']->type, $_SESSION['alloc']->trans_no);
}

//--------------------------------------------------------------------------------

end_page();

