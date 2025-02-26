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

use Axispro\Admin\HeaderOrFooter;
use Illuminate\Support\Facades\Validator;

$page_security = 'SA_DIMENSION';
$path_to_root = "..";
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/admin/db/tags_db.inc");
include_once($path_to_root . "/dimensions/includes/dimensions_db.inc");
include_once($path_to_root . "/dimensions/includes/dimensions_ui.inc");

HeaderOrFooter::handleFileOperation([
	'dimension_header' => [
		'viewBtn' => 'ViewHeader',
		'deleteBtn' => 'DeleteHeader',
	],
	'dimension_footer' => [
		'viewBtn' => 'ViewFooter',
		'deleteBtn' => 'DeleteFooter',
	],
]);

$js = "";
if (user_use_date_picker())
	$js .= get_js_date_picker();

ob_start(); ?>
<style>
	#wrap-tables .tablestyle_inner {
		width: 100%;
	}
</style>
<?php $GLOBALS['__HEAD__'][] = ob_get_clean();

page(trans($help_context = "Cost center Entry"), false, false, "", $js);

//---------------------------------------------------------------------------------------

if (isset($_GET['trans_no']))
{
	$selected_id = $_GET['trans_no'];
} 
elseif(isset($_POST['selected_id']))
{
	$selected_id = $_POST['selected_id'];
}
else
	$selected_id = -1;
//---------------------------------------------------------------------------------------

if (isset($_GET['AddedID'])) 
{
	$id = $_GET['AddedID'];

	display_notification_centered(trans("The cost center has been entered."));

	safe_exit();
}

//---------------------------------------------------------------------------------------

if (isset($_GET['UpdatedID'])) 
{
	$id = $_GET['UpdatedID'];

	display_notification_centered(trans("The cost center has been updated."));
	safe_exit();
}

//---------------------------------------------------------------------------------------

if (isset($_GET['DeletedID'])) 
{
	$id = $_GET['DeletedID'];

	display_notification_centered(trans("The cost center has been deleted."));
	safe_exit();
}

//---------------------------------------------------------------------------------------

if (isset($_GET['ClosedID'])) 
{
	$id = $_GET['ClosedID'];

	display_notification_centered(trans("The cost center has been closed. There can be no more changes to it.") . " #$id");
	safe_exit();
}

//---------------------------------------------------------------------------------------

if (isset($_GET['ReopenedID'])) 
{
	$id = $_GET['ReopenedID'];

	display_notification_centered(trans("The cost center has been re-opened. ") . " #$id");
	safe_exit();
}

//-------------------------------------------------------------------------------------------------

function safe_exit()
{
	global $path_to_root, $id;

	hyperlink_no_params("", trans("Enter a &new Cost Center"));
	echo "<br>";
	hyperlink_no_params($path_to_root . "/dimensions/inquiry/search_dimensions.php", trans("&Select an existing cost center"));
    hyperlink_no_params($path_to_root . "/admin/attachments.php?filterType=40&trans_no=$id", trans("&Add Attachment"));

	display_footer_exit();
}

//-------------------------------------------------------------------------------------

function can_process()
{
	global $selected_id, $Refs, $SysPrefs;

	if ($selected_id == -1) 
	{
    	if (!check_reference($_POST['ref'], ST_DIMENSION))
    	{
			set_focus('ref');
    		return false;
    	}
	}

	if (strlen($_POST['name']) == 0) 
	{
		display_error( trans("The name must be entered."));
		set_focus('name');
		return false;
	}

	if (!is_date($_POST['date_']))
	{
		display_error( trans("The date entered is in an invalid format."));
		set_focus('date_');
		return false;
	}

	if (!is_date($_POST['due_date']))
	{
		display_error( trans("The required by date entered is in an invalid format."));
		set_focus('due_date');
		return false;
	}

	if ($_POST['invoice_prefix'] == '') {
		display_error('The invoice prefix cannot be empty');
		return false;
	}

	if (strlen($_POST['invoice_prefix'] > 3)) {
		display_error("The invoice prefix can only have upto 3 characters");
		return false;
	}

	$validator = Validator::make(
		request()->only(['dimension_header', 'dimension_footer']),
		[
			'dimension_header' => 'nullable|file|mimetypes:image/jpeg,image/png|max:'.$SysPrefs->max_image_size,
			'dimension_footer' => 'nullable|file|mimetypes:image/jpeg,image/png|max:'.$SysPrefs->max_image_size
		]
	);
	
	if ($validator->fails()) {
		foreach ($validator->errors() as $message) {
			display_error($message);
			return false;
		}
	}

	return true;
}

//-------------------------------------------------------------------------------------

if (isset($_POST['ADD_ITEM']) || isset($_POST['UPDATE_ITEM'])) 
{
	if (!isset($_POST['dimension_tags']))
		$_POST['dimension_tags'] = array();
		
	if (can_process()) 
	{
        if (isset($_POST['customer_card_accounts']) && !is_array($_POST['customer_card_accounts'])) {
            $_POST['customer_card_accounts'] = array_filter([(string)$_POST['customer_card_accounts']]);
        }

		$_POST['center_card_accounts'] = implode(',', $_POST['center_card_accounts']);
		$_POST['cash_accounts'] = implode(',', $_POST['cash_accounts']);
		$_POST['credit_card_accounts'] = implode(',', $_POST['credit_card_accounts']);
		$_POST['customer_card_accounts'] = implode(',', $_POST['customer_card_accounts']);
		$_POST['bank_transfer_accounts'] = implode(',', $_POST['bank_transfer_accounts']);
		$_POST['online_payment_accounts'] = implode(',', $_POST['online_payment_accounts']);
		$_POST['enabled_payment_methods'] = implode(',', $_POST['enabled_payment_methods']);

		if ($selected_id == -1) 
		{
			$id = add_dimension(
				$_POST['ref'], 
				$_POST['name'], 
				$_POST['type_'], 
				$_POST['date_'], 
				$_POST['due_date'], 
				$_POST['memo_'],
				$_POST['gst_no'],
				$_POST['invoice_prefix'],
				$_POST['pos_type'],
				check_value('has_service_request'),
				check_value('is_service_request_required'),
				check_value('has_token_filter'),
				check_value('require_token'),
				check_value('is_1to1_token'),
				check_value('has_autofetch'),
				check_value('is_payment_separate'),
				check_value('is_invoice_tax_included'),
				check_value('is_returnable_amt_editable'),
				check_value('is_returnable_act_editable'),
				$_POST['center_card_accounts'],
				$_POST['cash_accounts'],
				$_POST['credit_card_accounts'],
				$_POST['customer_card_accounts'],
				$_POST['bank_transfer_accounts'],
				$_POST['online_payment_accounts'],
				check_value('is_having_split_govt_fee'),
				check_value('is_service_fee_combined'),
				check_value('is_govt_bank_editable'),
				check_value('is_other_fee_editable'),
				check_value('is_passport_col_enabled'),
				check_value('is_app_id_col_enabled'),
				check_value('is_trans_id_col_enabled'),
				check_value('is_narration_col_enabled'),
				$_POST['tax_effective_from'],
				check_value('is_receivable_commission_amt_editable'),
				check_value('is_receivable_commission_act_editable'),
				$_POST['enabled_payment_methods'],
				check_value('is_discount_editable'),
				check_value('is_line_ref_col_enabled'),
				$_POST['center_type'],
				$_POST['dflt_payment_method'],
				$_POST['default_customer_id'],
				$_POST['dflt_payment_term'],
				check_value('is_cost_grouped_in_inv'),
				check_value('govt_fee_editable_in_purch'),
				check_value('enable_line_ref_in_purch'),
				check_value('enable_assignee_col'),
				$_POST['round_off_to'],
				$_POST['round_off_algorithm'],
                check_value('enable_round_off'),
                check_value('enable_govt_fee_pmt_method'),
                check_value('require_govt_fee_pmt_method'),
                check_value('auto_purchase_maid'),
				check_value('is_fine_col_enabled')
			);
			add_tag_associations($id, $_POST['dimension_tags']);
			HeaderOrFooter::upload('dimension_header', $id);
			HeaderOrFooter::upload('dimension_footer', $id);
			meta_forward($_SERVER['PHP_SELF'], "AddedID=$id");
		} 
		else 
		{

			update_dimension(
				$selected_id, 
				$_POST['name'], 
				$_POST['type_'], 
				$_POST['date_'], 
				$_POST['due_date'], 
				$_POST['memo_'],
				$_POST['gst_no'],
				$_POST['invoice_prefix'],
				$_POST['pos_type'],
				check_value('has_service_request'),
				check_value('is_service_request_required'),
				check_value('has_token_filter'),
				check_value('require_token'),
				check_value('is_1to1_token'),
				check_value('has_autofetch'),
				check_value('is_payment_separate'),
				check_value('is_invoice_tax_included'),
				check_value('is_returnable_amt_editable'),
				check_value('is_returnable_act_editable'),
				$_POST['center_card_accounts'],
				$_POST['cash_accounts'],
				$_POST['credit_card_accounts'],
				$_POST['customer_card_accounts'],
				$_POST['bank_transfer_accounts'],
				$_POST['online_payment_accounts'],
				check_value('is_having_split_govt_fee'),
				check_value('is_service_fee_combined'),
				check_value('is_govt_bank_editable'),
				check_value('is_other_fee_editable'),
				check_value('is_passport_col_enabled'),
				check_value('is_app_id_col_enabled'),
				check_value('is_trans_id_col_enabled'),
				check_value('is_narration_col_enabled'),
				$_POST['tax_effective_from'],
				check_value('is_receivable_commission_amt_editable'),
				check_value('is_receivable_commission_act_editable'),
				$_POST['enabled_payment_methods'],
				check_value('is_discount_editable'),
				check_value('is_line_ref_col_enabled'),
				$_POST['center_type'],
				$_POST['dflt_payment_method'],
				$_POST['default_customer_id'],
				$_POST['dflt_payment_term'],
				check_value('is_cost_grouped_in_inv'),
				check_value('govt_fee_editable_in_purch'),
				check_value('enable_line_ref_in_purch'),
				check_value('enable_assignee_col'),
				$_POST['round_off_to'],
				$_POST['round_off_algorithm'],
                check_value('enable_round_off'),
                check_value('enable_govt_fee_pmt_method'),
                check_value('require_govt_fee_pmt_method'),
                check_value('auto_purchase_maid'),
				check_value('is_fine_col_enabled')
			);
			update_tag_associations(TAG_DIMENSION, $selected_id, $_POST['dimension_tags']);
			HeaderOrFooter::upload('dimension_header', $selected_id);
			HeaderOrFooter::upload('dimension_footer', $selected_id);
			meta_forward($_SERVER['PHP_SELF'], "UpdatedID=$selected_id");
		}
	}
}

//--------------------------------------------------------------------------------------

if (isset($_POST['delete'])) {
	handle_delete_dimension($selected_id);
}

function handle_delete_dimension($selected_id)
{
	// can't delete it there are productions or issues
	if (dimension_has_payments($selected_id) || dimension_has_deposits($selected_id))
	{
		display_error(trans("This cost center cannot be deleted because it has already been processed."));
		set_focus('ref');
		return;
	}

	if (get_post('delete') != 'Confirm Delete') {
		$_POST['delete_confirmation'] = true;
		return;
	}
	
	delete_dimension($selected_id);
	delete_tag_associations(TAG_DIMENSION,$selected_id, true);
	meta_forward($_SERVER['PHP_SELF'], "DeletedID=$selected_id");
}

//-------------------------------------------------------------------------------------

if (isset($_POST['close'])) 
{

	// update the closed flag
	close_dimension($selected_id);
	meta_forward($_SERVER['PHP_SELF'], "ClosedID=$selected_id");
}

if (isset($_POST['reopen'])) 
{

	// update the closed flag
	reopen_dimension($selected_id);
	meta_forward($_SERVER['PHP_SELF'], "ReopenedID=$selected_id");
}
//-------------------------------------------------------------------------------------

start_form(true);

if (in_ajax()) {
	$Ajax->activate('dimension_delete_confirm_div');
    $Ajax->activate('processing_controls');
}

if (!in_ajax()) {
	if ($selected_id != -1)
	{
		$myrow = get_dimension($selected_id, true);

		if ($myrow === false) 
		{
			display_error(trans("The cost center sent is not valid."));
			display_footer_exit();
		}

		// if it's a closed cost center can't edit it
		//if ($myrow["closed"] == 1) 
		//{
		//	display_error(trans("This cost center is closed and cannot be edited."));
		//	display_footer_exit();
		//}

		$_POST['ref'] = $myrow["reference"];
		$_POST['closed'] = $myrow["closed"];
		$_POST['name'] = $myrow["name"];
		$_POST['type_'] = $myrow["type_"];
		$_POST['date_'] = sql2date($myrow["date_"]);
		$_POST['due_date'] = sql2date($myrow["due_date"]);
		$_POST['memo_'] = get_comments_string(ST_DIMENSION, $selected_id);
		$_POST['gst_no'] = $myrow['gst_no'];
		$_POST['invoice_prefix'] = $myrow['invoice_prefix'];
		$_POST['pos_type'] = $myrow['pos_type'];
		$_POST['center_type'] = $myrow['center_type'];
		$_POST['default_customer_id'] = $myrow['default_customer_id'];
		$_POST['has_service_request'] = $myrow['has_service_request'];
		$_POST['is_service_request_required'] = $myrow['is_service_request_required'];
		$_POST['has_token_filter'] = $myrow['has_token_filter'];
		$_POST['require_token'] = $myrow['require_token'];
		$_POST['is_1to1_token'] = $myrow['is_1to1_token'];
		$_POST['has_autofetch'] = $myrow['has_autofetch'];
		$_POST['is_payment_separate'] = $myrow['is_payment_separate'];
		$_POST['is_invoice_tax_included'] = $myrow['is_invoice_tax_included'];
		$_POST['is_cost_grouped_in_inv'] = $myrow['is_cost_grouped_in_inv'];
		$_POST['is_returnable_amt_editable'] = $myrow['is_returnable_amt_editable'];
		$_POST['is_returnable_act_editable'] = $myrow['is_returnable_act_editable'];
		$_POST['is_having_split_govt_fee'] = $myrow['is_having_split_govt_fee'];
		$_POST['is_service_fee_combined'] = $myrow['is_service_fee_combined'];
		$_POST['is_govt_bank_editable'] = $myrow['is_govt_bank_editable'];
		$_POST['is_other_fee_editable'] = $myrow['is_other_fee_editable'];
		$_POST['is_passport_col_enabled'] = $myrow['is_passport_col_enabled'];
		$_POST['is_app_id_col_enabled'] = $myrow['is_app_id_col_enabled'];
		$_POST['is_trans_id_col_enabled'] = $myrow['is_trans_id_col_enabled'];
		$_POST['is_narration_col_enabled'] = $myrow['is_narration_col_enabled'];
		$_POST['tax_effective_from'] = sql2date($myrow["tax_effective_from"]);
		$_POST['is_receivable_commission_amt_editable'] = $myrow['is_receivable_commission_amt_editable'];
		$_POST['is_receivable_commission_act_editable'] =  $myrow['is_receivable_commission_act_editable'];
		$_POST['is_discount_editable'] = $myrow['is_discount_editable'];
		$_POST['is_line_ref_col_enabled'] = $myrow['is_line_ref_col_enabled'];
		$_POST['enable_assignee_col'] = $myrow['enable_assignee_col'];

		$_POST['govt_fee_editable_in_purch'] = $myrow['govt_fee_editable_in_purch'];
		$_POST['enable_line_ref_in_purch'] = $myrow['enable_line_ref_in_purch'];
        $_POST['enable_govt_fee_pmt_method'] = $myrow['enable_govt_fee_pmt_method'];
        $_POST['require_govt_fee_pmt_method'] = $myrow['require_govt_fee_pmt_method'];
        $_POST['enable_round_off'] = $myrow['enable_round_off'];
        $_POST['round_off_to'] = $myrow['round_off_to'];
        $_POST['round_off_algorithm'] = $myrow['round_off_algorithm'];
		$_POST['is_fine_col_enabled'] = $myrow['is_fine_col_enabled'];

		$_POST['center_card_accounts'] = explode(',', $myrow['center_card_accounts']);
		$_POST['cash_accounts'] = explode(',', $myrow['cash_accounts']);
		$_POST['credit_card_accounts'] = explode(',', $myrow['credit_card_accounts']);
		$_POST['customer_card_accounts'] = explode(',', $myrow['customer_card_accounts']);
		$_POST['bank_transfer_accounts'] = explode(',', $myrow['bank_transfer_accounts']);
		$_POST['online_payment_accounts'] = explode(',', $myrow['online_payment_accounts']);
		$_POST['enabled_payment_methods'] = explode(',', $myrow['enabled_payment_methods']);
		$_POST['dflt_payment_method']   = $myrow['dflt_payment_method'];
		$_POST['dflt_payment_term'] = $myrow['dflt_payment_term'];
		$_POST['auto_purchase_maid'] = $myrow['auto_purchase_maid'];

		$tags_result = get_tags_associated_with_record(TAG_DIMENSION, $selected_id);
		$tagids = array();
		while ($tag = db_fetch($tags_result)) 
			$tagids[] = $tag['id'];
		$_POST['dimension_tags'] = $tagids;	

		
	} 
	else 
	{
		$_POST['dimension_tags'] = array();
		$_POST['center_type'] = CENTER_TYPES['OTHER'];
		$_POST['date_']=begin_fiscalyear();
		$_POST['due_date']=end_fiscalyear();
	}
}

div_start('dimension_delete_confirm_div');
    if (get_post('delete_confirmation')) {
        echo "<div class='row p-10 g-10 mw-450px mx-auto mt-5 mb-7 bg-body rounded'>";
			echo "<div class='col-12 fs-3 mb-3'>Delete Dimension ?</div>\n";
			echo "<div class='col-12 fw-normal mb-10'>You will need to reconfigure categories and user settings.</div>\n";
			echo "<div class='col-12 text-end'>\n";
				echo submit('cancel', 'No, Cancel', false, '', 'cancel', false, 'border-0 bg-hover-dark') . "\n";
				echo submit('delete', 'Confirm Delete', false, '', 'default', false, 'border-0 bg-hover-accent') . "\n";
			echo "</div>\n";
        echo "</div>";
        $Ajax->addScript(true, ';setTimeout(() => window.scrollTo(0, 0), 1);');
    }
div_end();

start_outer_table(TABLESTYLE2, 'style="width:100%; text-align:center" id="wrap-tables"');

table_section(1);

table_section_title('General Settings');

if ($selected_id != -1) {
	hidden('ref', $_POST['ref']);
	label_row(trans("Reference:"), $_POST['ref']);

	hidden('selected_id', $selected_id);
}

else {
	ref_row(trans("Reference:"), 'ref', '', $Refs->get_next(ST_DIMENSION), false, ST_DIMENSION);
}

text_row_ex(trans("Name") . ":", 'name', 50, 75);
text_row_ex(trans("GST No") . ":", 'gst_no', 50, 75);
date_cells(trans("Tax Effective From:"), 'tax_effective_from');
text_row_ex(trans("Prefix").":", 'invoice_prefix', 50, 75);
array_selector_row(trans("POS Type").":", 'pos_type', null, $GLOBALS['pos_types']);
array_selector_row(trans("Center Type").":", 'center_type', null, array_flip(CENTER_TYPES));

label_row(
	trans("Header") . ":",
	(
		"<div>"
		. (
			"<input type='file' name='dimension_header' class='me-2' accept='image/png, image/jpeg'>"
			. (HeaderOrFooter::existingFile("dimension_header", $selected_id) ? button('ViewHeader'.$selected_id, trans("View"), trans("View"), 'view_1.gif') : '')
			. (HeaderOrFooter::existingFile("dimension_header", $selected_id) ? button('DeleteHeader'.$selected_id, trans("Delete"), trans("Delete"), ICON_DELETE) : '')
		)
		. "</div>"
		. "<small class='text-muted'>Keep empty for no change</small>"
	),
	"",
	"",
	0,
	'_dimension_header'
);

label_row(
	trans("Footer") . ":",
	(
		"<div>"
		. (
			"<input type='file' name='dimension_footer' class='me-2' accept='image/png, image/jpeg'>"
			. (HeaderOrFooter::existingFile("dimension_footer", $selected_id) ? button('ViewFooter'.$selected_id, trans("View"), trans("View"), 'view_1.gif') : '')
			. (HeaderOrFooter::existingFile("dimension_footer", $selected_id) ? button('DeleteFooter'.$selected_id, trans("Delete"), trans("Delete"), ICON_DELETE) : '')
		)
		. "</div>"
		. "<small class='text-muted'>Keep empty for no change</small>"
	),
	"",
	"",
	0,
	'_dimension_footer'
);

table_section_title('Sales Configuration');

customer_list_row(trans('Default Customer'), 'default_customer_id', null, ' -- ');
check_row(trans('Has service requests').':', 'has_service_request');
check_row(trans('Is service request required').':', 'is_service_request_required');
check_row(trans('Has Token Filter').':', 'has_token_filter');
check_row(trans('Require Token').":", 'require_token');
check_row(trans('Is one to one token').":", 'is_1to1_token');
check_row(trans('Autofetch').":", 'has_autofetch');
check_row(trans('Is payment_separate').":", 'is_payment_separate');
check_row(trans('Is Invoice Tax included').":", 'is_invoice_tax_included');
check_row(trans('Is center chg. and govt. fee groupe in invoice print').":", 'is_cost_grouped_in_inv');
check_row(trans('Automatically purchase maid when delivering').":", 'auto_purchase_maid');
sale_payment_list_row(trans('Default Payment Term'), 'dflt_payment_term', PM_ANY, null, false, true, ['spec_option' => '-- select --', 'spec_id' => '']);
array_selector_row(trans('Default Payment Method'), 'dflt_payment_method', null, $GLOBALS['global_pay_types_array'],['select_submit' => false,'id' => 'dflt_payment_method','spec_option' => '-- Select Payment Method --','spec_id' => ALL_TEXT ]);
array_selector_row(trans('Enabled Payment Methods'), 'enabled_payment_methods', null, $GLOBALS['global_pay_types_array'], ["multi" => true]);
check_row(trans("Enable Center|Customer Card Type"), 'enable_govt_fee_pmt_method');
check_row(trans("Require Center|Customer Card Type"), 'require_govt_fee_pmt_method');
check_row(trans("Enable Round Off"), 'enable_round_off');
text_row_ex(trans("Round Off To Nearest") . ":", 'round_off_to', 8, 5);
array_selector_row(trans("Round Off Algorithm").":", 'round_off_algorithm', null, round_off_algorithms());

table_section(2);

start_outer_table(TABLESTYLE2, '', '2', '0', false, 'w-100 text-center tablestyle_inner');
table_section(1);
table_section_title('Invoice Line Configurations');

check_row(trans('Is Returnable Amount Editable').":", 'is_returnable_amt_editable');
check_row(trans('Is Returnable Account Editable').":", 'is_returnable_act_editable');
check_row(trans('Is Having Split Gov. Fee').":", 'is_having_split_govt_fee');
check_row(trans('Is Service Fee Combined').":", 'is_service_fee_combined');
check_row(trans('Is Govt Bank Editable').":", 'is_govt_bank_editable');
check_row(trans('Is Other Fee Editable').":", 'is_other_fee_editable');
check_row(trans('Is PassportCol Enabled').":", 'is_passport_col_enabled');
check_row(trans('Is ApplicationID Col Enabled').":", 'is_app_id_col_enabled');
check_row(trans('Is TransactionID Col Enabled').":", 'is_trans_id_col_enabled');
check_row(trans('Is Fine Col Enabled').":", 'is_fine_col_enabled');
check_row(trans('Is NarrationCol Enabled').":", 'is_narration_col_enabled');
check_row(trans('Is Receivable Commission Amount Editable').":", 'is_receivable_commission_amt_editable');
check_row(trans('Is Receivable Commission Account Editable').":", 'is_receivable_commission_act_editable');
check_row(trans('Is Discount Editable').":", 'is_discount_editable');
check_row(trans('Is Line Ref Col Enabled').":", 'is_line_ref_col_enabled');
check_row(trans('Is Assigned User Col Enabled').":", 'enable_assignee_col');

table_section(2);
table_section_title('Purchase Invoice Line Configurations');

check_row(trans('Govt Fee Amount Editable').":", 'govt_fee_editable_in_purch');
check_row(trans('Transaction select box Enabled').":", 'enable_line_ref_in_purch');

end_outer_table(0, false);

table_section_title('Payment Account Configurations');

bank_accounts_list_row(trans("Center Card Accounts:"), 'center_card_accounts', null, false, '-- select --', true);
bank_accounts_list_row(trans("Cash Accounts:"), 'cash_accounts', null, false, '-- select --', true);
bank_accounts_list_row(trans("Credit Card Accounts:"), 'credit_card_accounts', null, false, '-- select --', true);
bank_accounts_list_row(trans("Customer Card Accounts:"), 'customer_card_accounts', null, false, '-- select --');
bank_accounts_list_row(trans("Bank Transfer Accounts:"), 'bank_transfer_accounts', null, false, '-- select --', true);
bank_accounts_list_row(trans("Online Payment Accounts:"), 'online_payment_accounts', null, false, '-- select --', true);

$dim = get_company_pref('use_dimension');

hidden('type_',1);
hidden('date_',$_POST['date_']);
hidden('due_date',$_POST['due_date']);
hidden('dimension_tags', is_array($_POST['dimension_tags']) ? implode(',', $_POST['dimension_tags']) : $_POST['dimension_tags']);
hidden('memo_');

//number_list_row(trans("Type"), 'type_', null, 1, $dim);

//date_row(trans("Start Date") . ":", 'date_');

//date_row(trans("Date Required By") . ":", 'due_date', '', null, $SysPrefs->default_dimension_required_by());

//tag_list_row(trans("Tags:"), 'dimension_tags', 5, TAG_DIMENSION, true);

//textarea_row(trans("Memo:"), 'memo_', null, 40, 5);


end_outer_table(1);

if (isset($_POST['closed']) && $_POST['closed'] == 1)
	display_note(trans("This Cost Center is closed."), 0, 0, "class='currentfg'");

div_start('processing_controls');
if (!get_post('delete_confirmation')) {
	if ($selected_id != -1) 
	{
		echo "<br>";
		submit_center_first('UPDATE_ITEM', trans("Update"), trans('Save changes to cost center'), 'default');
	//	if ($_POST['closed'] == 1)
	//		submit('reopen', trans("Re-open This cost center"), true, trans('Mark this cost center as re-opened'), true);
	//	else
	//		submit('close', trans("Close This cost center"), true, trans('Mark this cost center as closed'), true);
		submit_center_last('delete', trans("Delete This cost center"), trans('Delete unused cost center'), true, false, 'border-0 bg-hover-accent');
	}
	else
	{
		submit_center('ADD_ITEM', trans("Add"), true, '', 'default');
	}
}
div_end();
end_form();

//--------------------------------------------------------------------------------------------

end_page();

