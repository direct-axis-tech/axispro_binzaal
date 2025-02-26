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

use Carbon\Carbon;

$page_security = 'SA_SALESALLOC';
$path_to_root = "../..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
//include_once($path_to_root . "/sales/includes/ui/cust_alloc_ui.inc");

include_once($path_to_root . "/reporting/includes/reporting.inc");

$js = "";

if ($SysPrefs->use_popup_windows) {
	$js .= get_js_open_window(900, 500);
}

if (user_use_date_picker()) {
    $js .= get_js_date_picker();
}

add_js_file('allocate.js');

page(trans($help_context = "Allocate Customer Payment or Credit Note"), false, false, "", $js);

//--------------------------------------------------------------------------------

if (!isset($_POST['date_from'])) {
    $_POST['date_from'] = Carbon::now()->subMonths(3)->startOfMonth()->format(dateformat());
}

if (!isset($_POST['date_to'])) {
    $_POST['date_to'] = Today();
}

function clear_allocations()
{
	if (isset($_SESSION['alloc']))
	{
		unset($_SESSION['alloc']->allocs);
		unset($_SESSION['alloc']);
	}
	//session_register('alloc');
}

//--------------------------------------------------------------------------------

function edit_allocations_for_transaction($type, $trans_no)
{
	global $systypes_array;

	$cart = $_SESSION['alloc'];
	
	if ($cart->type == ST_JOURNAL && $cart->bank_amount < 0)
	{
		$cart->bank_amount = -$cart->bank_amount;
		$cart->amount = -$cart->amount;
	}	
    display_heading(sprintf(trans("Allocation of %s # %d"), $systypes_array[$cart->type], $cart->trans_no));

    display_heading($cart->person_name);

    display_heading2(trans("Date:") . " <b>" . $cart->date_ . "</b>");
   	display_heading2(trans("Total:"). " <b>" . price_format($cart->bank_amount).' '.$cart->currency."</b>");

	if (floatcmp($cart->bank_amount, $cart->amount))
	{
	    $total = trans("Amount to be settled:") . " <b>" . price_format($cart->amount).' '.$cart->person_curr."</b>";
		if ($cart->currency != $cart->person_curr)
    		$total .= sprintf(" (%s %s/%s)",  exrate_format($cart->bank_amount/$cart->amount), $cart->currency, $cart->person_curr);
	   	display_heading2($total);
	}

    echo "<br>";

	start_form();

    div_start('filters_tbl', null, false, 'w-100 text-center');
    start_table(TABLESTYLE2, '', '0', '0', 'w-auto gx-5 gy-3');
    date_cells("From", 'date_from', 'Filter Allocs From');
    date_cells("To", 'date_to', 'Filter Allocs Till');
    submit_cells('Filter', 'Filter', '', false, true);
    end_table();
    div_end();

	div_start('alloc_tbl');
    if (count($cart->allocs) > 0)
    {
		show_allocatable(true);
       	submit_center_first('UpdateDisplay', trans("Refresh"), trans('Start again allocation of selected amount'), true);
       	submit('Process', trans("Process"), true, trans('Process allocations'), 'default');
   		submit_center_last('Cancel', trans("Back to Allocations"),trans('Abandon allocations and return to selection of allocatable amounts'), 'cancel');
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

if (isset($_POST['Filter'])) {
    $_SESSION['alloc']->read(
        $_SESSION['alloc']->type,
        $_SESSION['alloc']->trans_no,
        $_SESSION['alloc']->person_id,
        $_SESSION['alloc']->person_type_id,
        $_SESSION['alloc']->dimension_id,
        get_post('date_from'),
        get_post('date_to')
    );
	$Ajax->activate('alloc_tbl');
}

if (isset($_POST['Process']))
{
	if (check_allocations())
	{
		$_SESSION['alloc']->write();
		display_notification_centered(trans("The customer allocation has been successfully entered."));
		$_POST['Cancel'] = 1;
	}
}
//--------------------------------------------------------------------------------

if (isset($_POST['Cancel']))
{

    // $trans_no = $_SESSION['alloc']->trans_no;
    // $type = $_SESSION['alloc']->type;

    // display_notification(print_document_link($trans_no.'-'.$type, 'Print Receipt', true, 12, false, 'printlink', '', $email='', $extra=null));

	clear_allocations();
	meta_forward($path_to_root . "/sales/allocations/customer_allocation_main.php");
}

//--------------------------------------------------------------------------------

if (isset($_GET['trans_no']) && isset($_GET['trans_type']))
{
	clear_allocations();

	$_SESSION['alloc'] = new allocation(
        $_GET['trans_type'],
        $_GET['trans_no'],
        @$_GET['debtor_no'],
        PT_CUSTOMER,
        null,
        null,
        null,
        get_post('date_from'),
        get_post('date_to')
    );
}

if(get_post('UpdateDisplay'))
{
	$_SESSION['alloc']->read();
	$Ajax->activate('alloc_tbl');
}

if (isset($_SESSION['alloc']))
{
	edit_allocations_for_transaction($_SESSION['alloc']->type, $_SESSION['alloc']->trans_no);
}

ob_start();
?>
<script>
	$("#all_alloc").on("click", function(){
		$('[name^="Alloc"]').click();
	});
	$("#none_alloc").on("click", function(){
		$('[name^="DeAll"]').click();
	});
</script>
<?php $GLOBALS['__FOOT__'][] = ob_get_clean();

//--------------------------------------------------------------------------------

end_page();

