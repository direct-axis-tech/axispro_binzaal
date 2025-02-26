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
function display_bank_header(&$order)
{
	global $Ajax;
	$payment = $order->trans_type == ST_BANKPAYMENT;

	$customer_error = false;
	div_start('pmt_header');

	start_outer_table(TABLESTYLE2, "width='90%'"); // outer table

	table_section(1);

    date_row(trans("Date:"), 'date_', '', true, 0, 0, 0, null, true);

	ref_row(trans("Reference:"), 'ref', '', $order->reference, false, $order->trans_type, get_post('date_'));

	table_section(2, "33%");

	if (!isset($_POST['PayType']))
	{
		if (isset($_GET['PayType']))
			$_POST['PayType'] = $_GET['PayType'];
		else
			$_POST['PayType'] = "";
	}
	if (!isset($_POST['person_id']))
	{
		if (isset($_GET['PayPerson']))
			$_POST['person_id'] = $_GET['PayPerson'];
		else
			$_POST['person_id'] = "";
	}
	if (isset($_POST['_PayType_update'])) {
		$_POST['person_id'] = '';
		$Ajax->activate('pmt_header');
		$Ajax->activate('code_id');
		$Ajax->activate('subledger_id_div');
		$Ajax->activate('pagehelp');
		$Ajax->activate('editors');
		$Ajax->activate('footer');
	}
    payment_person_types_list_row( $payment ? trans("Pay To:"):trans("From:"),
		 'PayType', $_POST['PayType'], true);

//	hidden('PayType',$_POST['PayType']);

    switch ($_POST['PayType'])
    {

        case 4:

            users_list_cells("Employee","emp_id",null,true,false);

            break;

		case PT_MISC :
			text_row_ex(trans("Name:"), 'person_id', 40, 50);

    		// hidden('person_id',$_POST['person_id']);

    		break;
		case PT_SUPPLIER :
    		supplier_list_row(trans("Supplier:"), 'person_id', null, false, true, false, true);
    		break;
		case PT_CUSTOMER :
    		customer_list_row(trans("Customer:"), 'person_id', null, false, true, false, true);

        	if (db_customer_has_branches($_POST['person_id']))
        	{
        		customer_branches_list_row(trans("Branch:"), $_POST['person_id'],
					'PersonDetailID', null, false, true, true, true);
        	}
        	else
        	{
				$_POST['PersonDetailID'] = ANY_NUMERIC;
        		hidden('PersonDetailID');
        	}
        	$trans = get_customer_habit($_POST['person_id']); // take care of customers on hold
        	if (is_array($trans) && $trans['dissallow_invoices'] != 0)
        	{
        		if ($payment)
        		{
        			$customer_error = true;
					display_error(trans("This customer account is on hold."));
        		}
        		else
					display_warning(trans("This customer account is on hold."));
        	}
    		break;

		case PT_QUICKENTRY :
			quick_entries_list_row(trans("Type").":", 'person_id', null, ($payment ? QE_PAYMENT : QE_DEPOSIT), true);
			$qid = get_quick_entry(get_post('person_id'));
			if (list_updated('person_id')) {
				unset($_POST['totamount']); // enable default
				$Ajax->activate('footer');
				$Ajax->activate('totamount');
			}
			amount_row($qid['base_desc'].":", 'totamount', price_format($qid['base_amount']),
				 null, "&nbsp;&nbsp;".submit('go', trans("Go"), false, false, true));
			break;


    }



	if (!$order->order_id && !get_post('bank_account'))
	{
		if ($_POST['PayType'] == PT_CUSTOMER)
			$_POST['bank_account'] = get_default_customer_bank_account($_POST['person_id']);
		elseif ($_POST['PayType'] == PT_SUPPLIER)
			$_POST['bank_account'] = get_default_supplier_bank_account($_POST['person_id']);
		else
			unset($_POST['bank_account']);
	}

    bank_accounts_list_row( $payment ? trans("From:") : trans("Into:"), 'bank_account', null, true,false);
//    if ($payment)
//		bank_balance_row($_POST['bank_account']);

	$bank_currency = get_bank_account_currency($_POST['bank_account']);

	exchange_rate_display(get_company_currency(), $bank_currency, $_POST['date_']);

	//PAYMENT VOUCHER CHEQUE DETAILS

    hidden('chq_no');
    hidden('chq_date');

//    text_row(trans("Cheque No."),'chq_no',null,200,200);
//    date_row(trans("Cheque Date:"), 'chq_date', '', true, 0, 0, 0, null, false);
    text_row(trans("Description."),'desc',null,200,200);


    end_outer_table(1); // outer table

	div_end();
	if ($customer_error)
	{
		end_form();
		end_page();
		exit;
	}
}
//---------------------------------------------------------------------------------

function display_gl_items($title, &$order)
{
	global $path_to_root;

	$dim = get_company_pref('use_dimension');
	$colspan = ($dim == 2 ? 5 : ($dim == 1 ? 4 : 3));
	display_heading($title);

    div_start('items_table');
	start_table(TABLESTYLE, "width='95%'");

	if ($dim == 2)
        $th = array(_("Account Code"), _("Account Description"),_("Subledger"),_("Cost Center")." 1",
            _("Dimension")." 2", _("Amount"), _("Memo"), "");
	elseif ($dim == 1)
        $th = array(_("Account Code"), _("Account Description"),_("Subledger"), _("Cost Center"),
            _("Amount"), _("Memo"), "");
	else
        $th = array(_("Account Code"), _("Account Description"),_("Subledger"),
            _("Amount"), _("Memo"), "");



	if (count($order->gl_items)) $th[] = '';

	table_header($th);
	$k = 0;  //row colour counter

	$id = find_submit('Edit');
	foreach ($order->gl_items as $line => $item)
	{
		if ($id != $line)
		{
    		alt_table_row_color($k);

			label_cell($item->code_id);
			label_cell($item->description);
            label_cell($item->person_name);
    		if ($dim >= 1)
				label_cell(get_dimension_string($item->dimension_id, true));
    		if ($dim > 1)
				label_cell(get_dimension_string($item->dimension2_id, true));
			if ($order->trans_type == ST_BANKDEPOSIT)
				amount_cell(-$item->amount);
			else
				amount_cell($item->amount);
			label_cell($item->reference);

			edit_button_cell("Edit$line", trans("Edit"),
				trans('Edit document line'));
			delete_button_cell("Delete$line", trans("Delete"),
				trans('Remove line from document'));
    		end_row();
		}
		else
		{
			gl_edit_item_controls($order, $dim, $line);
		}
	}

	if ($id == -1 && (get_post('PayType') == PT_MISC || !$order->count_gl_items()))
		gl_edit_item_controls($order, $dim);

	if ($order->count_gl_items())
		label_row(trans("Total"), number_format2(abs($order->gl_items_total()), user_price_dec()),"colspan=" . $colspan . " align=right", "align=right",3);

    end_table();
	div_end();
}

//---------------------------------------------------------------------------------

function gl_edit_item_controls(&$order, $dim, $Index=null)
{
	global $Ajax;
	$payment = $order->trans_type == ST_BANKPAYMENT;

	start_row();
	$id = find_submit('Edit');
	if ($Index != -1 && $Index == $id)
	{
		$item = $order->gl_items[$Index];
		$_POST['code_id'] = $item->code_id;
        $_POST['subledger_id'] = $item->person_id;
		$_POST['dimension_id'] = $item->dimension_id;
		$_POST['dimension2_id'] = $item->dimension2_id;
		$_POST['amount'] = price_format(abs($item->amount));
		$_POST['description'] = $item->description;
		$_POST['LineMemo'] = $item->reference;

		hidden('Index', $id);
	    $Ajax->activate('items_table');
	}
	else
	{
		$_POST['amount'] = isset($_POST['amount']) ? $_POST['amount'] : price_format(0);
		$_POST['dimension_id'] = 0;
		$_POST['dimension2_id'] = 0;
		if(isset($_POST['_code_id_update'])) {
			$Ajax->activate('code_id');
		}
		if (list_updated('PayType') || list_updated('person_id'))
		{
			if ($_POST['PayType'] == PT_CUSTOMER)
			{
				$acc = get_branch_accounts($_POST['PersonDetailID']);
				$_POST['code_id'] = is_array($acc) ? $acc['receivables_account'] : '';
			}
			elseif ($_POST['PayType'] == PT_SUPPLIER)
			{
				$acc = get_supplier_accounts($_POST['person_id']);
				$_POST['code_id'] = is_array($acc) ? $acc['payable_account'] : '';
				$_POST['dimension_id'] = is_array($acc) ? $acc['dimension_id'] : '';
				$_POST['dimension2_id'] = is_array($acc) ? $acc['dimension2_id'] : '';
			}
			else {
				$_POST['code_id'] =
					get_company_pref($payment ? 'default_cogs_act':'default_inv_sales_act');
			}
			$Ajax->activate('dimension_id');
			$Ajax->activate('dimension2_id');
		}
	}
	echo gl_all_accounts_list('code_id', null, true, true, false, true, false, false, $_POST['PayType'] != PT_MISC);
    
	echo '<td>';
	div_start('subledger_id_div');
	echo in_array($_POST['PayType'], [PT_CUSTOMER, PT_SUPPLIER])
		? ''
		: subledger_list('subledger_id', get_post('code_id'),null,false,true);
	div_end();
	echo '</td>';

	if ($dim >= 1)
		dimensions_list_cells(null, 'dimension_id', null, true, " ", false, 1);
	else
		hidden('dimension_id', 0);
	if ($dim > 1)
		dimensions_list_cells(null, 'dimension2_id', null, true, " ", false, 2);
	else
		hidden('dimension2_id', 0);

	$person_type_id = get_subledger_person_type(is_subledger_account(get_post('code_id')));

	$sub_ledger_balance = get_gl_account_subledger_balance(
		get_post('code_id'),
		$person_type_id,
		get_post('subledger_id')
	);

	$cart_balance = $_SESSION['pay_items']->gl_items_total(
		get_post('code_id'),
		$person_type_id,
		get_post('subledger_id')
	);

	$balance = (
		$sub_ledger_balance + $cart_balance
	);

	hidden('balance', $balance);
	$class = $balance <= 0 ? 'success' : 'danger';
	amount_cells(null, 'amount', null, null, ('<span class="mx-3 float-start text-'.$class.'">Balance: <span>'.price_format(abs($balance)).'</span></span>'));
	text_cells_ex(null, 'LineMemo', 35, 255);

    if (list_updated('code_id')) {
        $Ajax->activate('code_id');
        $Ajax->activate('subledger_id');
    }
	

    if ($id != -1)
	{
		button_cell('UpdateItem', trans("Update"),
				trans('Confirm changes'), ICON_UPDATE);
		button_cell('CancelItemChanges', trans("Cancel"),
				trans('Cancel changes'), ICON_CANCEL);
 		set_focus('amount');
	}
	else
	{
		$confirmation = get_post('add_item_confirmation') == 'Yes';
		submit_cells(
			'AddItem',
			trans($confirmation ? 'Yes - Proceed' : 'Add Item'),
			"colspan=" . ($confirmation ? '1' : '2'),
		    trans('Add new item to document'),
			true
		);
		hidden('add_item_confirmation');
		if ($confirmation) {
			submit_cells('CancelConfirmation', _("Cancel"), '', '', true, 'bg-gray-600 bg-state-dark border-0');
		}
		
	}

	end_row();
}


//---------------------------------------------------------------------------------

function gl_options_controls($order)
{
	div_start('footer');
	echo "<br><table align='left'>";

	$type = get_post('PayType');
	$bank_curr = get_bank_account_currency(get_post('bank_account'));
	$person_curr = $type == PT_CUSTOMER ? get_customer_currency(get_post('person_id'))
		: ($type == PT_SUPPLIER ? get_supplier_currency(get_post('person_id')) : $bank_curr);

	if ($person_curr != $bank_curr)
	{
		$_POST['settled_amount'] =
			price_format(abs($order->gl_items_total() / get_exchange_rate_from_to($bank_curr, $person_curr, get_post('date_'))));
		amount_row($type == PT_CUSTOMER ? trans("Settled AR Amount:") :  trans("Settled AP Amount:"),
			 'settled_amount', null, null, $person_curr, user_price_dec());
	}
	textarea_row(trans("Memo"), 'memo_', null, 100, 4);

  	echo "</table>";
  	div_end();
}






//---------------------------------------------------------------------------------
?>
<style>
    textarea {
        max-width: 100% !important;
    }

    @media (min-width: 768px)
    table.tablestyle2 td:first-child, table.tablestyle2 td:nth-child(3) {
        width: 11% !important;
    }

    #footer table td:first-child {
        float: right !important;
    }

        table.tablestyle td {
            border: 1px solid #ccc !important;
        }

    #dimension_id {
        width: 150px !important;
    }

        table.tablestyle2 td:first-child, table.tablestyle2 td:nth-child(3) {
             width: 15% !important;
        }
    
    .customer-branch-list-row {
        display: none !important;
    }

</style>

