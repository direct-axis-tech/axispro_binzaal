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
$page_security = 'SA_BANKACCOUNT';
$path_to_root = "../..";
include($path_to_root . "/includes/session.inc");

page(trans($help_context = "Bank Accounts"));

include($path_to_root . "/includes/ui.inc");

simple_page_mode();
//-----------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM') 
{

	//initialise no input errors assumed initially before we test
	$input_error = 0;

	//first off validate inputs sensible
	if (strlen($_POST['bank_account_name']) == 0) 
	{
		$input_error = 1;
		display_error(trans("The bank account name cannot be empty."));
		set_focus('bank_account_name');
	} 
	if (
		gl_account_in_bank_accounts(get_post('account_code'), $selected_id) 
		|| DB::table('0_gl_trans')->whereAccount(get_post('account_code'))->where('amount', '!=', 0)->exists()
	) {
		$input_error = 1;
		display_error(trans("The GL account selected is already in use or has transactions. Select another empty GL account."));
		set_focus('account_code');
	}
	if ($input_error != 1)
	{
    	if ($selected_id != -1) 
    	{
    		
    		update_bank_account(
				$selected_id,
				$_POST['account_code'],
				$_POST['account_type'],
				$_POST['bank_account_name'], 
				$_POST['bank_name'],
				$_POST['bank_account_number'],
    			$_POST['bank_address'],
				$_POST['BankAccountCurrency'],
    			$_POST['dflt_curr_act'],
				$_POST['bank_charge_act'],
				$_POST['dflt_bank_chrg'],
				$_POST['dflt_trans_delay']
			);
			display_notification(trans('Bank account has been updated'));
    	} 
    	else 
    	{
    
    		add_bank_account(
				$_POST['account_code'],
				$_POST['account_type'], 
				$_POST['bank_account_name'],
				$_POST['bank_name'], 
    			$_POST['bank_account_number'],
				$_POST['bank_address'], 
				$_POST['BankAccountCurrency'],
				$_POST['dflt_curr_act'],
				$_POST['bank_charge_act'],
				$_POST['dflt_bank_chrg'],
				$_POST['dflt_trans_delay']
			);
			display_notification(trans('New bank account has been added'));
    	}
 		$Mode = 'RESET';
	}
} 
elseif( $Mode == 'Delete')
{
	//the link to delete a selected record was clicked instead of the submit button

	$cancel_delete = 0;
	// PREVENT DELETES IF DEPENDENT RECORDS IN 'bank_trans'

	if (key_in_foreign_table($selected_id, 'bank_trans', 'bank_act') || key_in_foreign_table(get_post('account_code'), 'gl_trans', 'account'))
	{
		$cancel_delete = 1;
		display_error(trans("Cannot delete this bank account because transactions have been created using this account."));
	}

	if (key_in_foreign_table($selected_id, 'sales_pos', 'pos_account'))
	{
		$cancel_delete = 1;
		display_error(trans("Cannot delete this bank account because POS definitions have been created using this account."));
	}
	if (!$cancel_delete) 
	{
		delete_bank_account($selected_id);
		display_notification(trans('Selected bank account has been deleted'));
	} //end if Delete bank account
	$Mode = 'RESET';
} 

if ($Mode == 'RESET')
{
 	$selected_id = -1;
	$_POST['dflt_bank_chrg'] = $_POST['dflt_bank_chrg'] = '0.00';
	$_POST['bank_name']  = 	$_POST['bank_account_name']  = '';
	$_POST['bank_account_number'] = $_POST['bank_address'] = '';
	$_POST['bank_charge_act'] = get_company_pref('bank_charge_act');
	$_POST['dflt_trans_delay'] = 10;
}
if (!isset($_POST['bank_charge_act']))
	$_POST['bank_charge_act'] = get_company_pref('bank_charge_act');

if (!isset($_POST['dflt_trans_delay'])) {
	$_POST['dflt_trans_delay'] = 10;
}
/* Always show the list of accounts */

$result = get_bank_accounts(check_value('show_inactive'));

start_form();
start_table(TABLESTYLE, "width='80%'");

$th = array(trans("Account Name"), trans("Type"), trans("Currency"), trans("GL Account"), 
	trans("Bank"), trans("Default Chrg."), trans("Number"), trans("Excel Columns For Reconciliation"),
	trans("Dflt"), trans("Trans. Delay"), '','');
inactive_control_column($th);
table_header($th);	

$k = 0; 
while ($myrow = db_fetch($result)) 
{
	
	alt_table_row_color($k);

    label_cell($myrow["bank_account_name"], "nowrap");
	label_cell($bank_account_types[$myrow["account_type"]], "nowrap");
    label_cell($myrow["bank_curr_code"], "nowrap");
    label_cell($myrow["account_code"] . " " . $myrow["account_name"], "nowrap");
    label_cell($myrow["bank_name"], "nowrap");
	label_cell($myrow["dflt_bank_chrg"], "nowrap");
    label_cell($myrow["bank_account_number"], "nowrap");
    label_cell($myrow["bank_address"]);
    if ($myrow["dflt_curr_act"])
		label_cell(trans("Yes"));
	else
		label_cell(trans("No"));

	inactive_control_cell($myrow["id"], $myrow["inactive"], 'bank_accounts', 'id');
	label_cell($myrow["dflt_trans_delay"], "nowrap");

 	edit_button_cell("Edit".$myrow["id"], trans("Edit"));
 	delete_button_cell("Delete".$myrow["id"], trans("Delete"));
    end_row(); 
}

inactive_control_row($th);
end_table(1);

$is_used = $selected_id != -1 && key_in_foreign_table($selected_id, 'bank_trans', 'bank_act');


start_table(TABLESTYLE2, "style='width:50%' id='bank_acc_table' ");
table_section_title(trans("ADD/EDIT BANK ACCOUNTS"));

if ($selected_id != -1)
{
    if ($Mode == 'Edit') {
        $myrow = get_bank_account($selected_id);

        $_POST['account_code'] = $myrow["account_code"];
        $_POST['account_type'] = $myrow["account_type"];
        $_POST['bank_name']  = $myrow["bank_name"];
        $_POST['bank_account_name']  = $myrow["bank_account_name"];
        $_POST['bank_account_number'] = $myrow["bank_account_number"];
        $_POST['bank_address'] = $myrow["bank_address"];
        $_POST['dflt_bank_chrg'] = $myrow["dflt_bank_chrg"];
        $_POST['BankAccountCurrency'] = $myrow["bank_curr_code"];
        $_POST['dflt_curr_act'] = $myrow["dflt_curr_act"];
        $_POST['bank_charge_act'] = $myrow["bank_charge_act"];
		$_POST['dflt_trans_delay'] = $myrow["dflt_trans_delay"];
    }
    hidden('selected_id', $selected_id);
    set_focus('bank_account_name');
}

text_row(trans("Bank Account Name:"), 'bank_account_name', null, 50, 100);

if ($is_used)
{
    label_row(trans("Account Type:"), $bank_account_types[$_POST['account_type']]);
    hidden('account_type');
}
else
{
    bank_account_types_list_row(trans("Account Type:"), 'account_type', null);
}
if ($is_used)
{
    label_row(trans("Bank Account Currency:"), $_POST['BankAccountCurrency']);
    hidden('BankAccountCurrency', $_POST['BankAccountCurrency']);
}
else
{
    currencies_list_row(trans("Bank Account Currency:"), 'BankAccountCurrency', null);
}

yesno_list_row(trans("Default currency account:"), 'dflt_curr_act');

if($is_used)
{
    label_row(trans("Bank Account GL Code:"), $_POST['account_code']);
    hidden('account_code');
} else
    gl_all_accounts_list_row(trans("Bank Account GL Code:"), 'account_code', null);

gl_all_accounts_list_row(trans("Bank Charges Account:"), 'bank_charge_act', null, true);
text_row(trans("Bank Name:"), 'bank_name', null, 50, 60);
amount_row(trans("Default Bank Charge for Transactions:"), 'dflt_bank_chrg', '0.00', null, null, user_price_dec());
text_row(trans("Bank Account Number:"), 'bank_account_number', null, 30, 60);
text_row(trans("Excel Columns For Reconciliation (Date,Transaction ID,Description,Amount,Bank Charge/Commission,VAT):"), 'bank_address',
    null, 40, 20,null,null,trans("Separated by Comma. Eg: A,B,C,D,E,F"));
text_row(trans("Transaction Delay:"), 'dflt_trans_delay', null, 6, 6, '', "", trans("Number of Days. Eg: 10 days"));

end_table(1);

submit_add_or_update_center($selected_id == -1, '', 'both');

end_form();

end_page();

?>


<style>

    #bank_acc_table td.label {
        text-align: center !important;
    }

</style>
