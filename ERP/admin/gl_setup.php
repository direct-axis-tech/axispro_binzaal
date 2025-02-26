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
$page_security = 'SA_GLSETUP';
$path_to_root="..";
include($path_to_root . "/includes/session.inc");

$js = "";
if ($SysPrefs->use_popup_windows && $SysPrefs->use_popup_search)
	$js .= get_js_open_window(900, 500);

page(trans($help_context = "System and General GL Setup"), false, false, "", $js);

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/admin/db/company_db.inc");

//-------------------------------------------------------------------------------------------------

function can_process()
{
	if (!check_num('po_over_receive', 0, 100))
	{
		display_error(trans("The delivery over-receive allowance must be between 0 and 100."));
		set_focus('po_over_receive');
		return false;
	}

	if (!check_num('po_over_charge', 0, 100))
	{
		display_error(trans("The invoice over-charge allowance must be between 0 and 100."));
		set_focus('po_over_charge');
		return false;
	}

	if (!check_num('past_due_days', 0, 100))
	{
		display_error(trans("The past due days interval allowance must be between 0 and 100."));
		set_focus('past_due_days');
		return false;
	}

	$grn_act = get_company_pref('grn_clearing_act');
	$post_grn_act = get_post('grn_clearing_act');
	if ($post_grn_act == null)
		$post_grn_act = 0;
	if (($post_grn_act != $grn_act) && db_num_rows(get_grn_items(0, '', true)))
	{
		display_error(trans("Before GRN Clearing Account can be changed all GRNs have to be invoiced"));
		$_POST['grn_clearing_act'] = $grn_act;
		set_focus('grn_clearing_account');
		return false;
	}
	if (!is_account_balancesheet(get_post('retained_earnings_act')) || is_account_balancesheet(get_post('profit_loss_year_act')))
	{
		display_error(trans("The Retained Earnings Account should be a Balance Account or the Profit and Loss Year Account should be an Expense Account (preferred the last one in the Expense Class)"));
		return false;
	}
	return true;
}

//-------------------------------------------------------------------------------------------------

if (isset($_POST['submit']) && can_process())
{
    if (isset($_POST['customer_card_accounts']) && !is_array($_POST['customer_card_accounts'])) {
        $_POST['customer_card_accounts'] = array_filter([(string)$_POST['customer_card_accounts']]);
    }

	$_POST['center_card_accounts'] = implode(",", array_filter($_POST['center_card_accounts'] ?? []));
	$_POST['bank_transfer_accounts'] = implode(",", array_filter($_POST['bank_transfer_accounts'] ?? []));
	$_POST['online_payment_accounts'] = implode(",", array_filter($_POST['online_payment_accounts'] ?? []));
	$_POST['credit_card_accounts'] = implode(",", array_filter($_POST['credit_card_accounts'] ?? []));
	$_POST['cash_accounts'] = implode(",", array_filter($_POST['cash_accounts'] ?? []));
	$_POST['customer_card_accounts'] = implode(",", array_filter($_POST['customer_card_accounts'] ?? []));
	$_POST['auto_bnk_chg_applicable_cats'] = implode(",", array_filter($_POST['auto_bnk_chg_applicable_cats'] ?? []));
    $_POST['processing_fee_rate'] = ($_POST['processing_fee_rate'] ?? 0) / 100;
    $_POST['processing_fee_tax_rate'] = ($_POST['processing_fee_tax_rate'] ?? 0) / 100;
	update_company_prefs( get_post( array( 
		'retained_earnings_act',
		'cash_handover_round_off_adj_act',
		'profit_loss_year_act',
		'walkin_receivable_act',
		'debtors_act',
		'pyt_discount_act',
		'enable_auto_bank_charge' => 0,
		'auto_bank_charge_rate',
		'auto_bnk_chg_applicable_cats',
		'creditors_act',
		'freight_act',
		'deferred_income_act',
		'exchange_diff_act',
		'bank_charge_act',
		'default_sales_act',
		'default_sales_discount_act',
		'default_prompt_payment_act',
		'default_inventory_act',
		'default_cogs_act',
		'depreciation_period',
		'default_loss_on_asset_disposal_act',
		'default_adj_act',
		'default_inv_sales_act',
		'default_wip_act',
		'legal_text',
		'past_due_days',
		'default_workorder_required',
		'default_dim_required',
		'default_receival_required',
		'default_delivery_required',
		'default_quote_valid_days',
		'grn_clearing_act',
		'tax_algorithm',
		'no_zero_lines_amount',
		'show_po_item_codes',
		'accounts_alpha',
		'loc_notification',
		'print_invoice_no',
		'allow_negative_prices',
		'print_item_images_on_quote',
		'allow_negative_stock'=> 0,
		'accumulate_shipping'=> 0,
		'po_over_receive' => 0.0,
		'po_over_charge' => 0.0,
		'default_credit_limit'=>0.0,
		'customer_card_accounts',
		'cash_accounts',
		'dflt_cr_lmt_warning_lvl'=>0.0,
		'dflt_cr_lmt_notice_lvl'=>0.0,
        'default_card_charge' => 0.0,
		'processing_fee_rate',
		'processing_fee_tax_rate',
		'extra_charge_for_online_payment',
		'center_card_accounts',
		'bank_transfer_accounts',
		'online_payment_accounts',
		'credit_card_accounts',
        'customer_commission_expense_act',
        'customer_commission_payable_act',
        'emp_commission_expense_act',
        'emp_commission_payable_act',
	    'sales_commission_expense_act',
        'sales_commission_payable_act',
		'credit_note_charge_acc',
		'tas_17_1_cat',
		'tas_36_cat',
		'tas_72_cat',																				
		'tas_216_cat',
		'twj_144_cat',
		'ts_auto_stock_category',
		'ts_auto_govt_bank_acc',
		'ts_auto_returnable_to',
		'leave_accrual_payable_account',
		'leave_accrual_expense_account',
		'gratuity_payable_account',
		'gratuity_expense_account',
		'supp_comm_receivable_acc',
		'supp_comm_income_acc',
		'imm_auto_stock_category',
		'imm_80_cat',
		'imm_auto_govt_bank_acc',
        'ts_pending_txn_max_visibility_age',
        'imm_pending_txn_max_visibility_age'
	)));

	display_notification(trans("The general GL setup has been updated."));

} /* end of if submit */

//-------------------------------------------------------------------------------------------------

start_form();

start_outer_table(TABLESTYLE2);

table_section(1);

$myrow = get_company_prefs();

$_POST['retained_earnings_act']  = $myrow["retained_earnings_act"];
$_POST['profit_loss_year_act']  = $myrow["profit_loss_year_act"];
$_POST['cash_handover_round_off_adj_act'] = $myrow['cash_handover_round_off_adj_act'];
$_POST['walkin_receivable_act']  = $myrow["walkin_receivable_act"];
$_POST['debtors_act']  = $myrow["debtors_act"];
$_POST['creditors_act']  = $myrow["creditors_act"];
$_POST['freight_act'] = $myrow["freight_act"];
$_POST['deferred_income_act'] = $myrow["deferred_income_act"];
$_POST['default_card_charge'] = $myrow["default_card_charge"];
$_POST['processing_fee_rate'] = $myrow["processing_fee_rate"] * 100;
$_POST['processing_fee_tax_rate'] = $myrow["processing_fee_tax_rate"] * 100;
$_POST['extra_charge_for_online_payment'] = $myrow["extra_charge_for_online_payment"];
$_POST['pyt_discount_act']  = $myrow["pyt_discount_act"];
$_POST['enable_auto_bank_charge']  = $myrow["enable_auto_bank_charge"];
$_POST['auto_bank_charge_rate']  = $myrow["auto_bank_charge_rate"];
$_POST['auto_bnk_chg_applicable_cats']  = explode(',', $myrow["auto_bnk_chg_applicable_cats"]);


$_POST['exchange_diff_act'] = $myrow["exchange_diff_act"];
$_POST['bank_charge_act'] = $myrow["bank_charge_act"];
$_POST['tax_algorithm'] = $myrow["tax_algorithm"];
$_POST['default_sales_act'] = $myrow["default_sales_act"];
$_POST['default_sales_discount_act']  = $myrow["default_sales_discount_act"];
$_POST['default_prompt_payment_act']  = $myrow["default_prompt_payment_act"];

$_POST['default_inventory_act'] = $myrow["default_inventory_act"];
$_POST['default_cogs_act'] = $myrow["default_cogs_act"];
$_POST['default_adj_act'] = $myrow["default_adj_act"];
$_POST['default_inv_sales_act'] = $myrow['default_inv_sales_act'];
$_POST['default_wip_act'] = $myrow['default_wip_act'];

$_POST['allow_negative_stock'] = $myrow['allow_negative_stock'];

$_POST['po_over_receive'] = percent_format($myrow['po_over_receive']);
$_POST['po_over_charge'] = percent_format($myrow['po_over_charge']);
$_POST['past_due_days'] = $myrow['past_due_days'];

$_POST['grn_clearing_act'] = $myrow['grn_clearing_act'];

$_POST['default_credit_limit'] = price_format($myrow['default_credit_limit']);
$_POST['legal_text'] = $myrow['legal_text'];
$_POST['accumulate_shipping'] = $myrow['accumulate_shipping'];

$_POST['default_workorder_required'] = $myrow['default_workorder_required'];
$_POST['default_dim_required'] = $myrow['default_dim_required'];
$_POST['default_delivery_required'] = $myrow['default_delivery_required'];
$_POST['default_receival_required'] = $myrow['default_receival_required'];
$_POST['default_quote_valid_days'] = $myrow['default_quote_valid_days'];
$_POST['no_zero_lines_amount'] = $myrow['no_zero_lines_amount'];
$_POST['show_po_item_codes'] = $myrow['show_po_item_codes'];
$_POST['accounts_alpha'] = $myrow['accounts_alpha'];
$_POST['loc_notification'] = $myrow['loc_notification'];
$_POST['print_invoice_no'] = $myrow['print_invoice_no'];
$_POST['allow_negative_prices'] = $myrow['allow_negative_prices'];
$_POST['print_item_images_on_quote'] = $myrow['print_item_images_on_quote'];
$_POST['default_loss_on_asset_disposal_act'] = $myrow['default_loss_on_asset_disposal_act'];
$_POST['depreciation_period'] = $myrow['depreciation_period'];
$_POST['customer_card_accounts'] = explode(",",$myrow['customer_card_accounts']);
$_POST['cash_accounts'] = explode(",",$myrow['cash_accounts']);
$_POST['dflt_cr_lmt_warning_lvl'] = price_format($myrow['dflt_cr_lmt_warning_lvl']);
$_POST['dflt_cr_lmt_notice_lvl'] = price_format($myrow['dflt_cr_lmt_notice_lvl']);
$_POST['credit_note_charge_acc'] = $myrow['credit_note_charge_acc'];
$_POST['customer_commission_expense_act'] = $myrow['customer_commission_expense_act'];
$_POST['customer_commission_payable_act'] = $myrow['customer_commission_payable_act'];
$_POST['emp_commission_expense_act'] = $myrow['emp_commission_expense_act'];
$_POST['emp_commission_payable_act'] = $myrow['emp_commission_payable_act'];
$_POST['supp_comm_receivable_acc'] = $myrow['supp_comm_receivable_acc'];
$_POST['supp_comm_income_acc'] = $myrow['supp_comm_income_acc'];
$_POST['sales_commission_expense_act'] = $myrow['sales_commission_expense_act'];
$_POST['sales_commission_payable_act'] = $myrow['sales_commission_payable_act'];
$_POST['center_card_accounts'] = explode(",",$myrow['center_card_accounts']);
$_POST['bank_transfer_accounts'] = explode(",",$myrow['bank_transfer_accounts']);
$_POST['online_payment_accounts'] = explode(",",$myrow['online_payment_accounts']);
$_POST['credit_card_accounts'] = explode(",",$myrow['credit_card_accounts']);
$_POST['tas_17_1_cat'] = $myrow['tas_17_1_cat'];
$_POST['tas_36_cat'] = $myrow['tas_36_cat'];
$_POST['tas_72_cat'] = $myrow['tas_72_cat'];
$_POST['tas_216_cat'] = $myrow['tas_216_cat'];
$_POST['twj_144_cat'] = $myrow['twj_144_cat'];
$_POST['ts_auto_stock_category'] = $myrow['ts_auto_stock_category'];
$_POST['ts_auto_govt_bank_acc'] = $myrow['ts_auto_govt_bank_acc'];
$_POST['ts_auto_returnable_to'] = $myrow['ts_auto_returnable_to'];
$_POST['imm_auto_stock_category'] = $myrow['imm_auto_stock_category'];
$_POST['imm_80_cat'] = $myrow['imm_80_cat'];
$_POST['imm_auto_govt_bank_acc'] = $myrow['imm_auto_govt_bank_acc'];
$_POST['imm_pending_txn_max_visibility_age'] = $myrow['imm_pending_txn_max_visibility_age'];
$_POST['ts_pending_txn_max_visibility_age'] = $myrow['ts_pending_txn_max_visibility_age'];
$_POST['leave_accrual_payable_account'] = $myrow['leave_accrual_payable_account'];
$_POST['leave_accrual_expense_account'] = $myrow['leave_accrual_expense_account'];
$_POST['gratuity_payable_account'] = $myrow['gratuity_payable_account'];
$_POST['gratuity_expense_account'] = $myrow['gratuity_expense_account'];

//---------------


table_section_title(trans("General GL"));

text_row(trans("Past Due Days Interval:"), 'past_due_days', $_POST['past_due_days'], 6, 6, '', "", trans("days"));

accounts_type_list_row(trans("Accounts Type:"), 'accounts_alpha', $_POST['accounts_alpha']);

gl_all_accounts_list_row(trans("Retained Earnings:"), 'retained_earnings_act', $_POST['retained_earnings_act']);

gl_all_accounts_list_row(trans("Profit/Loss Year:"), 'profit_loss_year_act', $_POST['profit_loss_year_act']);

gl_all_accounts_list_row(trans("Exchange Variances Account:"), 'exchange_diff_act', $_POST['exchange_diff_act']);

gl_all_accounts_list_row(trans("Bank Charges Account:"), 'bank_charge_act', $_POST['bank_charge_act']);

gl_all_accounts_list_row(trans("Cash Handover Round Off Adjustments Account:"), 'cash_handover_round_off_adj_act', $_POST['cash_handover_round_off_adj_act']);

tax_algorithm_list_row(trans("Tax Algorithm:"), 'tax_algorithm', $_POST['tax_algorithm']);

//---------------
table_section_title(trans("Payment Accounts"));

bank_accounts_list_row(trans('Customer Card Account:'), 'customer_card_accounts', null, false, '-- select --');
bank_accounts_list_row(trans('Default Cash Payments to'), 'cash_accounts', null, false, '-- use user\'s cashier account --', true);
bank_accounts_list_row(trans('Center Card Accounts'), 'center_card_accounts', null, false, false ,true);
bank_accounts_list_row(trans('Bank Transfer Accounts'), 'bank_transfer_accounts', null, false, false ,true);
bank_accounts_list_row(trans('Online Payment Accounts'), 'online_payment_accounts', null, false, false ,true);
bank_accounts_list_row(trans('Credit Card Accounts'), 'credit_card_accounts', null, false, false ,true);

//---------------
table_section_title(trans("Dimension Defaults"));

text_row(trans("Dimension Required By After:"), 'default_dim_required', $_POST['default_dim_required'], 6, 6, '', "", trans("days"));

//----------------

table_section_title(trans("Customers and Sales"));

amount_row(trans("Default Credit Limit:"), 'default_credit_limit', $_POST['default_credit_limit']);
amount_row(trans("Default Credit Warning Level:"), 'dflt_cr_lmt_warning_lvl', $_POST['dflt_cr_lmt_warning_lvl']);
amount_row(trans("Default Credit Notice Level:"), 'dflt_cr_lmt_notice_lvl', $_POST['dflt_cr_lmt_notice_lvl']);

yesno_list_row(trans("Invoice Identification:"), 'print_invoice_no', $_POST['print_invoice_no'], $name_yes=trans("Number"), $name_no=trans("Reference"));

check_row(trans("Accumulate batch shipping:"), 'accumulate_shipping', null);

check_row(trans("Print Item Image on Quote:"), 'print_item_images_on_quote', null);

textarea_row(trans("Legal Text on Invoice:"), 'legal_text', $_POST['legal_text'], 32, 4);

gl_all_accounts_list_row(trans("Shipping Charged Account:"), 'freight_act', $_POST['freight_act']);

gl_all_accounts_list_row(trans("Deferred Income Account:"), 'deferred_income_act', $_POST['deferred_income_act'], true, false,
	trans("Not used"), false, false, false);

amount_row(trans("Default Credit Card Charge:"), 'default_card_charge', null, null, '(0-100)%');

amount_row(trans("Processing Fee Rate:"), 'processing_fee_rate', $_POST['processing_fee_rate'], null, '(0-100)%', 6);

amount_row(trans("Processing Fee Tax Rate:"), 'processing_fee_tax_rate', $_POST['processing_fee_tax_rate'], null, '(0-100)%', 6);

amount_row(trans("Extra Charge For Online Payment:"), 'extra_charge_for_online_payment', $_POST['extra_charge_for_online_payment']);

check_row(trans("Enable Automatic Bank Charge for Mohre"), 'enable_auto_bank_charge');
amount_row(trans("Automatic Bank Charge for Mohre:"), 'auto_bank_charge_rate');
stock_categories_list_cells("Automatic Bank Charge Applicable Categories", 'auto_bnk_chg_applicable_cats', null,
	false, false, false, true);

//---------------

table_section_title(trans("Customers and Sales Defaults"));
// default for customer branch
gl_all_accounts_list_row(trans("Walkin - Receivable Account:"), 'walkin_receivable_act');
gl_all_accounts_list_row(trans("Receivable Account:"), 'debtors_act');

gl_all_accounts_list_row(trans("Sales Account:"), 'default_sales_act', null,
	false, false, true);

gl_all_accounts_list_row(trans("Sales Discount Account:"), 'default_sales_discount_act');

gl_all_accounts_list_row(trans("Prompt Payment Discount Account:"), 'default_prompt_payment_act');

text_row(trans("Quote Valid Days:"), 'default_quote_valid_days', $_POST['default_quote_valid_days'], 6, 6, '', "", trans("days"));

text_row(trans("Delivery Required By:"), 'default_delivery_required', $_POST['default_delivery_required'], 6, 6, '', "", trans("days"));

gl_all_accounts_list_row(trans("Credit Note Charge Account:"), 'credit_note_charge_acc', null, true, false, 'Use Debiting Sales Account');
//---------------

table_section(2);

table_section_title(trans("Suppliers and Purchasing"));

percent_row(trans("Delivery Over-Receive Allowance:"), 'po_over_receive');

percent_row(trans("Invoice Over-Charge Allowance:"), 'po_over_charge');

table_section_title(trans("Suppliers and Purchasing Defaults"));

gl_all_accounts_list_row(trans("Payable Account:"), 'creditors_act', $_POST['creditors_act']);

gl_all_accounts_list_row(trans("Purchase Discount Account:"), 'pyt_discount_act', $_POST['pyt_discount_act']);

gl_all_accounts_list_row(trans("GRN Clearing Account:"), 'grn_clearing_act', get_post('grn_clearing_act'), true, false, trans("No postings on GRN"));

text_row(trans("Receival Required By:"), 'default_receival_required', $_POST['default_receival_required'], 6, 6, '', "", trans("days"));

check_row(trans("Show PO item codes:"), 'show_po_item_codes', null);

table_section_title(trans("Inventory"));

check_row(trans("Allow Negative Inventory:"), 'allow_negative_stock', null);
label_row(null, trans("Warning:  This may cause a delay in GL postings"), "", "class='stockmankofg' colspan=2");

check_row(trans("No zero-amounts (Service):"), 'no_zero_lines_amount', null);

check_row(trans("Location Notifications:"), 'loc_notification', null);

check_row(trans("Allow Negative Prices:"), 'allow_negative_prices', null);

table_section_title(trans("Items Defaults"));
gl_all_accounts_list_row(trans("Sales Account:"), 'default_inv_sales_act', $_POST['default_inv_sales_act']);

gl_all_accounts_list_row(trans("Inventory Account:"), 'default_inventory_act', $_POST['default_inventory_act']);
// this one is default for items and suppliers (purchase account)
gl_all_accounts_list_row(trans("C.O.G.S. Account:"), 'default_cogs_act', $_POST['default_cogs_act']);

gl_all_accounts_list_row(trans("Inventory Adjustments Account:"), 'default_adj_act', $_POST['default_adj_act']);

gl_all_accounts_list_row(trans("WIP Account:"), 'default_wip_act', $_POST['default_wip_act']);

//----------------

table_section_title(trans("Fixed Assets Defaults"));

gl_all_accounts_list_row(trans("Loss On Asset Disposal Account:"), 'default_loss_on_asset_disposal_act', $_POST['default_loss_on_asset_disposal_act']);

array_selector_row (trans("Depreciation Period:"), 'depreciation_period', $_POST['depreciation_period'], array(FA_MONTHLY => trans("Monthly"), FA_YEARLY => trans("Yearly")));

//----------------

table_section_title(trans("Manufacturing Defaults"));

text_row(trans("Work Order Required By After:"), 'default_workorder_required', $_POST['default_workorder_required'], 6, 6, '', "", trans("days"));

//----------------
table_section_title(trans("Commission GL Accounts"));
gl_all_accounts_list_row(trans("Cust. Commission Payable Account:"), 'customer_commission_payable_act', null, false, false, '-- select --');
gl_all_accounts_list_row(trans("Cust. Commission Expense Account:"), 'customer_commission_expense_act', null, true, false, '-- select --');
gl_all_accounts_list_row(trans("Emp. Commission Payable Account:"), 'emp_commission_payable_act', null, true, false, '-- select --');
gl_all_accounts_list_row(trans("Emp. Commission Expense Account:"), 'emp_commission_expense_act', null, true, false, '-- select --');
gl_all_accounts_list_row(trans("Supp. Commission Receivable Account:"), 'supp_comm_receivable_acc', null, true, false, trans("-- select --"));
gl_all_accounts_list_row(trans("Supp. Commission Income Account:"), 'supp_comm_income_acc', null, true, false, trans("-- select --"));
gl_all_accounts_list_row(trans("Salesman Commission Payable Account:"), 'sales_commission_payable_act', null, true, false, '-- select --');
gl_all_accounts_list_row(trans("Salesman Commission Expense Account:"), 'sales_commission_expense_act', null, true, false, '-- select --');

//----------------
table_section_title(trans("Tasheel Autofetch Configuration"));
stock_categories_list_row(trans('Tasheel 17.1 Category'), 'tas_17_1_cat', null, '-- select --');
stock_categories_list_row(trans('Tasheel 36 Category'), 'tas_36_cat', null, '-- select --');
stock_categories_list_row(trans('Tasheel 72 Category'), 'tas_72_cat', null, '-- select --');
stock_categories_list_row(trans('Tasheel 216 Category'), 'tas_216_cat', null, '-- select --');
stock_categories_list_row(trans('Tawjeeh 144 Category'), 'twj_144_cat', null, '-- select --');

stock_categories_list_row(trans('Default Category'), 'ts_auto_stock_category', null, '-- select --');
gl_all_accounts_list_row(trans("Default Govt Bank Act:"), 'ts_auto_govt_bank_acc', null, false, false, '-- select --');
gl_all_accounts_list_row(trans("Default Returnable To Act:"), 'ts_auto_returnable_to', null, false, false, '-- select --');
text_row(trans("Max Age of Displayed Pending Transactions"), 'ts_pending_txn_max_visibility_age', null, 6, 6);

table_section_title(trans("Immigration Autofetch Configuration"));
stock_categories_list_row(trans('Default Category'), 'imm_auto_stock_category', null, '-- select --');
stock_categories_list_row(trans('Immigration 80 Category'), 'imm_80_cat', null, '-- select --');
gl_all_accounts_list_row(trans("Default Govt Bank Act:"), 'imm_auto_govt_bank_acc', null, false, false, '-- select --');
text_row(trans("Max Age of Displayed Pending Transactions"), 'imm_pending_txn_max_visibility_age', null, 6, 6);

//----------------
table_section_title(trans("Accrual Configuration"));
gl_all_accounts_list_row(trans("Leave Accrual Payable Account:"), 'leave_accrual_payable_account', null, false, false, '-- select --');
gl_all_accounts_list_row(trans("Leave Accrual Expense Account:"), 'leave_accrual_expense_account', null, false, false, '-- select --');
gl_all_accounts_list_row(trans("Gratuity Payable Account:"), 'gratuity_payable_account', null, false, false, '-- select --');
gl_all_accounts_list_row(trans("Gratuity Expense Account:"), 'gratuity_expense_account', null, false, false, '-- select --');
end_outer_table(1);
//----------------

end_outer_table(1);

submit_center('submit', trans("Update"), true, '', 'default');

end_form(2);

//-------------------------------------------------------------------------------------------------

end_page();

