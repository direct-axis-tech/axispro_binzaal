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

use App\Permissions;
use Axispro\Admin\HeaderOrFooter;
use Carbon\Carbon;
use Illuminate\Support\Arr;

$page_security = 'SA_SETUPCOMPANY';
$path_to_root = "..";
include($path_to_root . "/includes/session.inc");

HeaderOrFooter::handleFileOperation([
	'company_header' => [
		'viewBtn' => 'ViewHeader',
		'deleteBtn' => 'DeleteHeader',
	],
	'company_footer' => [
		'viewBtn' => 'ViewFooter',
		'deleteBtn' => 'DeleteFooter',
	],
]);

page(trans($help_context = "Company Setup"));

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");

include_once($path_to_root . "/admin/db/company_db.inc");
include_once($path_to_root . "/reporting/includes/tcpdf.php");
//-------------------------------------------------------------------------------------------------

if (isset($_POST['update']) && $_POST['update'] != "")
{
	$input_error = 0;
	if (!check_num('login_tout', 10))
	{
		display_error(trans("Login timeout must be positive number not less than 10."));
		set_focus('login_tout');
		$input_error = 1;
	}
	if (strlen($_POST['coy_name'])==0)
	{
		$input_error = 1;
		display_error(trans("The company name must be entered."));
		set_focus('coy_name');
	}
	if (!check_num('tax_prd', 1))
	{
		display_error(_("Tax Periods must be positive number."));
		set_focus('tax_prd');
		$input_error = 1;
	}
	if (!check_num('tax_last', 1))
	{
		display_error(_("Tax Last Periods must be positive number."));
		set_focus('tax_last');
		$input_error = 1;
	}
	if (!check_num('round_to', 1))
	{
		display_error(_("Round Calculated field must be a positive number."));
		set_focus('round_to');
		$input_error = 1;
	}
	if (!check_num('max_days_in_docs', 1))
	{
		display_error(_("Max day range in Documents must be a positive number."));
		set_focus('max_days_in_docs');
		$input_error = 1;
	}
	if ($_POST['add_pct'] != "" && !is_numeric($_POST['add_pct']))
	{
		display_error(_("Add Price from Std Cost field must be number."));
		set_focus('add_pct');
		$input_error = 1;
	}	

    $validator = Validator::make(
		request()->only(['company_header', 'company_footer']),
		[
			'company_header' => 'nullable|file|mimetypes:image/jpeg,image/png|max:'.$SysPrefs->max_image_size,
			'company_footer' => 'nullable|file|mimetypes:image/jpeg,image/png|max:'.$SysPrefs->max_image_size
		]
	);

	if ($validator->fails()) {
		foreach ($validator->errors()->all() as $message) {
			display_error($message);
			$input_error = 1;
		}
	}

	if (isset($_FILES['pic']) && $_FILES['pic']['name'] != '')
	{
    if ($_FILES['pic']['error'] == UPLOAD_ERR_INI_SIZE) {
			display_error(trans('The file size is over the maximum allowed.'));
			$input_error = 1;
    }
    elseif ($_FILES['pic']['error'] > 0) {
			display_error(trans('Error uploading logo file.'));
			$input_error = 1;
    }
		$result = $_FILES['pic']['error'];
		$filename = company_path()."/images";
		if (!file_exists($filename))
		{
			mkdir($filename);
		}
		$filename .= "/".clean_file_name($_FILES['pic']['name']);

		 //But check for the worst
		if (!in_array( substr($filename,-4), array('.jpg','.JPG','.png','.PNG')))
		{
			display_error(trans('Only jpg and png files are supported - a file extension of .jpg or .png is expected'));
			$input_error = 1;
		}
		elseif ( $_FILES['pic']['size'] > ($SysPrefs->max_image_size * 1024))
		{ //File Size Check
			display_error(trans('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $SysPrefs->max_image_size);
			$input_error = 1;
		}
		elseif ( $_FILES['pic']['type'] == "text/plain" )
		{  //File type Check
			display_error( trans('Only graphics files can be uploaded'));
			$input_error = 1;
		}
		elseif (file_exists($filename))
		{
			$result = unlink($filename);
			if (!$result)
			{
				display_error(trans('The existing image could not be removed'));
				$input_error = 1;
			}
		}

		if ($input_error != 1) {
			$result  =  move_uploaded_file($_FILES['pic']['tmp_name'], $filename);
			$_POST['coy_logo'] = clean_file_name($_FILES['pic']['name']);
			if(!$result) {
				display_error(trans('Error uploading logo file'));
				$input_error = 1;
			} else {
				$msg = check_image_file($filename);
				if ( $msg) {
					display_error( $msg);
					unlink($filename);
					$input_error = 1;
				}
			}
		}
	}

	if (check_value('del_coy_logo'))
	{
		$filename = company_path()."/images/".clean_file_name($_POST['coy_logo']);
		if (file_exists($filename))
		{
			$result = unlink($filename);
			if (!$result)
			{
				display_error(trans('The existing image could not be removed'));
				$input_error = 1;
			}
		}
		$_POST['coy_logo'] = "";
	}
	if ($_POST['add_pct'] == "")
		$_POST['add_pct'] = -1;
	if ($_POST['round_to'] <= 0)
		$_POST['round_to'] = 1;

	$_POST['enabled_payment_methods'] = implode(',', $_POST['enabled_payment_methods'] ?? []);
	$_POST['extra_cols_in_statement'] = implode(',', $_POST['extra_cols_in_statement'] ?? []);
	$_POST['sent_link_lifetime'] = input_num('sent_link_lifetime');
	
	$updates = get_post([
		'coy_name',
		'coy_no',
		'gst_no',
		'tax_prd',
		'tax_last',
		'postal_address',
		'phone',
		'fax',
		'email',
		'coy_logo',
		'domicile',
		'use_dimension',
		'curr_default',
		'f_year',
		'shortname_name_in_list',
		'no_item_list' => 0,
		'no_customer_list' => 0,
		'no_supplier_list' => 0,
		'base_sales',
		'ref_no_auto_increase' => 0,
		'dim_on_recurrent_invoice' => 0,
		'long_description_invoice' => 0,
		'max_days_in_docs' => 180,
		'time_zone' => 0,
		'company_logo_report' => 0,
		'barcodes_on_stock' => 0,
		'print_dialog_direct' => 0,
		'add_pct',
		'round_to',
		'login_tout',
		'auto_curr_reval',
		'bcc_email',
		'alternative_tax_include_on_docs',
		'suppress_tax_rates',
		'use_manufacturing',
		'use_fixed_assets',
		'enable_invoice_editing',
		'refresh_permissions',
		'show_alloc_in_soa' => 0,
		'service_charge_return_account',
		'gl_after_transaction_id_update',
		'extra_cols_in_statement',
		'noqodi_account',
		'immigration_category',
		'tadbeer_category',
		'tasheel_category',
		'gl_bank_account_group',
		'gl_payment_card_group',
		'opening_bal_equity_account',
		'customer_id_prefix',
		'org_ip',
        'ip_restriction',
		'enable_passport_column' => 0,
		'enable_iban_no_column' => 0,
		'is_contact_person_mandatory' => 0,
		'is_email_mandatory' => 0,
        'collect_processing_chg_frm_cust',
		'auto_register_customer',
		'show_receipt_info_in_invoice' => 0,
		'req_auth_code_4_cc_pmt' => 0,
		'show_comm_total_in_reports' => 0,
		'req_card_no_4_cr_cd_pmt' => 0,
		'req_card_no_4_cn_cd_pmt' => 0,
		'dflt_dimension',
		'enabled_payment_methods',
		'send_sms_automatically' => 0,
		'sms_template',
		'sent_link_lifetime',
		'send_email_automatically' => 0,
		'email_subject',
		'email_template',
		'email_link_lifetime',
		'max_user_limit',
		'is_leave_accrual_scheduler_enabled' => 0,
		'is_auto_alloc_scheduler_enabled' => 0,
		'is_gratuity_accrual_sched_enabled' => 0
	]);

	if ($_SESSION['wa_current_user']->is_developer_session) {
		if (!empty($_POST['developer_password'])) {
			$updates['developer_password'] = app('hash')->make($_POST['developer_password']);
		}
		
		$updates['max_user_limit'] = $_POST['max_user_limit'];
		$updates['enabled_modules'] = implode(',', $_POST['enabled_modules']);

        if (
            ($pwd4LclUpdate = pref('axispro.amc_pwd_req_for_local_update'))
            && app('hash')->check(get_post('amc_pwd_req_for_local_update'), $pwd4LclUpdate)
        ) {
            $updates['amc_last_renewed_till'] = $_POST['amc_last_renewed_till'];

            if ($updates['amc_last_renewed_till']) {
                $updates['amc_last_renewed_till'] = Carbon::createFromFormat(dateformat().' h:i A', $updates['amc_last_renewed_till'])->toDateTimeString();
            }

            $updates['amc_duration_in_months'] = $_POST['amc_duration_in_months'];
            $updates['amc_notify_to_roles'] = implode(",", $_POST['amc_notify_to_roles']);
            $updates['amc_early_notice_days'] = $_POST['amc_early_notice_days'];
            $updates['amc_late_notice_days'] = $_POST['amc_late_notice_days'];
            $updates['amc_grace_days_after_expiry'] = $_POST['amc_grace_days_after_expiry'];

            foreach ([
                'amc_last_renewed_till',
                'amc_duration_in_months',
                'amc_early_notice_days',
                'amc_late_notice_days',
                'amc_grace_days_after_expiry',
            ] as $key) {
                if ($updates[$key] != pref("axispro.{$key}")) {
                    $updates['amc_system_updated_at'] = date(DB_DATETIME_FORMAT);
                    break;
                }
            }
        }
	}

    if ($updates['email_template']) {
        $updates['email_template'] = base64_encode($updates['email_template']);
    }

	$_valid = true;
	foreach($_POST['excluded_customers'] as $cust_id) {
		if(!preg_match('/^[1-9][0-9]{0,15}$/', $cust_id)){
			$_valid = false;
			break;
		}
	}
	if($_valid) {
		$updates['excluded_customers'] = implode(',', $_POST['excluded_customers']);
	}

    $updates['bank_bal_rep_accounts'] = implode(',', $_POST['bank_bal_rep_accounts']);

	if ($input_error != 1)
	{
		update_company_prefs($updates);
		HeaderOrFooter::upload('company_header');
		HeaderOrFooter::upload('company_footer');

		$_SESSION['wa_current_user']->timeout = $_POST['login_tout'];
		display_notification_centered(trans("Company setup has been updated."));
		set_focus('coy_name');
		$Ajax->activate('_page_body');
	}
} /* end of if submit */

start_form(true);

$myrow = get_company_prefs();

$_POST['coy_name'] = $myrow["coy_name"];
$_POST['gst_no'] = $myrow["gst_no"];
$_POST['tax_prd'] = $myrow["tax_prd"];
$_POST['tax_last'] = $myrow["tax_last"];
$_POST['coy_no']  = $myrow["coy_no"];
$_POST['postal_address']  = $myrow["postal_address"];
$_POST['phone']  = $myrow["phone"];
$_POST['fax']  = $myrow["fax"];
$_POST['email']  = $myrow["email"];
$_POST['coy_logo']  = $myrow["coy_logo"];
$_POST['domicile']  = $myrow["domicile"];
$_POST['use_dimension']  = $myrow["use_dimension"];
$_POST['base_sales']  = $myrow["base_sales"];
$_POST['max_user_limit'] = $myrow['max_user_limit'];

if (!isset($myrow["shortname_name_in_list"]))
{
	set_company_pref("shortname_name_in_list", "setup.company", "tinyint", 1, '0');
	$myrow["shortname_name_in_list"] = get_company_pref("shortname_name_in_list");
}
$_POST["dflt_dimension"] = $myrow["dflt_dimension"];
$_POST['shortname_name_in_list']  = $myrow["shortname_name_in_list"];
$_POST['no_item_list']  = $myrow["no_item_list"];
$_POST['no_customer_list']  = $myrow["no_customer_list"];
$_POST['no_supplier_list']  = $myrow["no_supplier_list"];
$_POST['curr_default']  = $myrow["curr_default"];
$_POST['f_year']  = $myrow["f_year"];
$_POST['time_zone']  = $myrow["time_zone"];
if (!isset($myrow["max_days_in_docs"]))
{
	set_company_pref("max_days_in_docs", "setup.company", "smallint", 5, '180');
	$myrow["max_days_in_docs"] = get_company_pref("max_days_in_docs");
}
$_POST['max_days_in_docs']  = $myrow["max_days_in_docs"];
if (!isset($myrow["company_logo_report"]))
{
	set_company_pref("company_logo_report", "setup.company", "tinyint", 1, '0');
	$myrow["company_logo_report"] = get_company_pref("company_logo_report");
}
$_POST['company_logo_report']  = $myrow["company_logo_report"];
if (!isset($myrow["ref_no_auto_increase"]))
{
	set_company_pref("ref_no_auto_increase", "setup.company", "tinyint", 1, '0');
	$myrow["ref_no_auto_increase"] = get_company_pref("ref_no_auto_increase");
}
$_POST['ref_no_auto_increase']  = $myrow["ref_no_auto_increase"];
if (!isset($myrow["barcodes_on_stock"]))
{
	set_company_pref("barcodes_on_stock", "setup.company", "tinyint", 1, '0');
	$myrow["barcodes_on_stock"] = get_company_pref("barcodes_on_stock");
}
$_POST['barcodes_on_stock']  = $myrow["barcodes_on_stock"];
if (!isset($myrow["print_dialog_direct"]))
{
	set_company_pref("print_dialog_direct", "setup.company", "tinyint", 1, '0');
	$myrow["print_dialog_direct"] = get_company_pref("print_dialog_direct");
}
$_POST['print_dialog_direct']  = $myrow["print_dialog_direct"];
if (!isset($myrow["dim_on_recurrent_invoice"]))
{
	set_company_pref("dim_on_recurrent_invoice", "setup.company", "tinyint", 1, '0');
	$myrow["dim_on_recurrent_invoice"] = get_company_pref("dim_on_recurrent_invoice");
}
$_POST['dim_on_recurrent_invoice']  = $myrow["dim_on_recurrent_invoice"];
if (!isset($myrow["long_description_invoice"]))
{
	set_company_pref("long_description_invoice", "setup.company", "tinyint", 1, '0');
	$myrow["long_description_invoice"] = get_company_pref("long_description_invoice");
}
$_POST['long_description_invoice']  = $myrow["long_description_invoice"];
$_POST['auto_register_customer']  = $myrow["auto_register_customer"];
$_POST['show_receipt_info_in_invoice']  = $myrow["show_receipt_info_in_invoice"];
$_POST['req_auth_code_4_cc_pmt']  = $myrow["req_auth_code_4_cc_pmt"];
$_POST['show_comm_total_in_reports']  = $myrow["show_comm_total_in_reports"];
$_POST['req_card_no_4_cr_cd_pmt']  = $myrow["req_card_no_4_cr_cd_pmt"];
$_POST['req_card_no_4_cn_cd_pmt']  = $myrow["req_card_no_4_cn_cd_pmt"];
$_POST['enabled_payment_methods']  = explode(',', $myrow["enabled_payment_methods"]);
$_POST['enabled_modules']  = explode(',', $myrow["enabled_modules"]);
$_POST['dflt_dimension']  = $myrow["dflt_dimension"];
$_POST['version_id']  = $myrow["version_id"];
$_POST['add_pct'] = $myrow['add_pct'];
$_POST['login_tout'] = $myrow['login_tout'];
if ($_POST['add_pct'] == -1)
	$_POST['add_pct'] = "";
$_POST['round_to'] = $myrow['round_to'];	
$_POST['auto_curr_reval'] = $myrow['auto_curr_reval'];	
$_POST['del_coy_logo']  = 0;
$_POST['bcc_email']  = $myrow["bcc_email"];
$_POST['alternative_tax_include_on_docs']  = $myrow["alternative_tax_include_on_docs"];
$_POST['suppress_tax_rates']  = $myrow["suppress_tax_rates"];
$_POST['use_manufacturing']  = $myrow["use_manufacturing"];
$_POST['use_fixed_assets']  = $myrow["use_fixed_assets"];


$_POST['enable_invoice_editing'] = $myrow['enable_invoice_editing'];
$_POST['refresh_permissions'] = $myrow['refresh_permissions'];
$_POST['show_alloc_in_soa'] = $myrow['show_alloc_in_soa'];
$_POST['gl_after_transaction_id_update'] = $myrow['gl_after_transaction_id_update'];
$_POST['extra_cols_in_statement'] = explode(',', $myrow['extra_cols_in_statement']);
$_POST['service_charge_return_account'] = $myrow['service_charge_return_account'];

$_POST['tasheel_category'] = $myrow['tasheel_category'];
$_POST['tadbeer_category'] = $myrow['tadbeer_category'];
$_POST['immigration_category'] = $myrow['immigration_category'];
$_POST['noqodi_account'] = $myrow['noqodi_account'];
$_POST['gl_bank_account_group'] = $myrow['gl_bank_account_group'];
$_POST['gl_payment_card_group'] = $myrow['gl_payment_card_group'];
$_POST['opening_bal_equity_account'] = $myrow['opening_bal_equity_account'];
$_POST['customer_id_prefix'] = $myrow['customer_id_prefix'];
$_POST['excluded_customers'] = explode(',', $myrow['excluded_customers']);
$_POST['bank_bal_rep_accounts'] = explode(',', $myrow['bank_bal_rep_accounts']);

if (!isset($myrow["org_ip"]))
{
    set_company_pref("org_ip", "setup.axispro", "varchar", 200, '');
    $myrow["org_ip"] = get_company_pref("org_ip");
}
$_POST['org_ip'] = $myrow['org_ip'];

if (!isset($myrow["ip_restriction"]))
{
    set_company_pref("ip_restriction", "setup.axispro", "tinyint", 1, '0');
    $myrow["ip_restriction"] = get_company_pref("ip_restriction");
}
$_POST['ip_restriction'] = $myrow['ip_restriction'];
$_POST['enable_passport_column'] = $myrow['enable_passport_column'];
$_POST['enable_iban_no_column'] = $myrow['enable_iban_no_column'];
$_POST['is_contact_person_mandatory'] = $myrow['is_contact_person_mandatory'];
$_POST['is_email_mandatory'] = $myrow['is_email_mandatory'];
$_POST['collect_processing_chg_frm_cust'] = $myrow['collect_processing_chg_frm_cust'];
$_POST['send_sms_automatically'] = $myrow['send_sms_automatically'];
$_POST['sms_template'] = $myrow['sms_template'];
$_POST['sent_link_lifetime'] = $myrow['sent_link_lifetime'];
$_POST['send_email_automatically'] = $myrow['send_email_automatically'];
$_POST['email_subject'] = $myrow['email_subject'];
$_POST['email_template'] = base64_decode($myrow['email_template']);
$_POST['email_link_lifetime'] = $myrow['email_link_lifetime'];
$_POST['is_leave_accrual_scheduler_enabled'] = $myrow['is_leave_accrual_scheduler_enabled'];
$_POST['is_auto_alloc_scheduler_enabled'] = $myrow['is_auto_alloc_scheduler_enabled'];
$_POST['is_gratuity_accrual_sched_enabled'] = $myrow['is_gratuity_accrual_sched_enabled'];
$_POST['amc_last_renewed_till'] = $myrow['amc_last_renewed_till'] ? Carbon::parse($myrow['amc_last_renewed_till'])->format(dateformat().' h:i A') : '';
$_POST['amc_duration_in_months'] = $myrow['amc_duration_in_months'];
$_POST['amc_notify_to_roles'] = explode(",", $myrow['amc_notify_to_roles']);
$_POST['amc_early_notice_days'] = $myrow['amc_early_notice_days'];
$_POST['amc_late_notice_days'] = $myrow['amc_late_notice_days'];
$_POST['amc_grace_days_after_expiry'] = $myrow['amc_grace_days_after_expiry'];
$_POST['amc_last_fetched_at'] = $myrow['amc_last_fetched_at'];
$_POST['amc_last_fetch_result'] = $myrow['amc_last_fetch_result'];
$_POST['amc_system_updated_at'] = $myrow['amc_system_updated_at'];
$_POST['amc_server_updated_at'] = $myrow['amc_server_updated_at'];


start_outer_table(TABLESTYLE2);

table_section(1);
table_section_title(trans("General settings"));

text_row_ex(trans("Name (to appear on reports):"), 'coy_name', 50, 50);
textarea_row(trans("Address:"), 'postal_address', $_POST['postal_address'], 34, 5);
text_row_ex(trans("Domicile:"), 'domicile', 25, 55);

text_row_ex(trans("Phone Number:"), 'phone', 25, 55);
text_row_ex(trans("Fax Number:"), 'fax', 25);
email_row_ex(trans("Email Address:"), 'email', 50, 55);

email_row_ex(trans("BCC Address for all outgoing mails:"), 'bcc_email', 50, 55);

text_row_ex(trans("Official Company Number:"), 'coy_no', 25);
text_row_ex(trans("TRN No:"), 'gst_no', 25);
currencies_list_row(trans("Home Currency:"), 'curr_default', $_POST['curr_default']);

label_row(trans("Company Logo:"), $_POST['coy_logo']);
file_row(trans("New Company Logo (.jpg)") . ":", 'pic', 'pic');

if ($_SESSION['wa_current_user']->is_developer_session) {
	label_row(
		trans("Header") . ":",
		(
			"<div>"
			. (
				"<input type='file' name='company_header' class='me-2' accept='image/png, image/jpeg'>"
				. (HeaderOrFooter::existingFile('company_header') ? button('ViewHeader0', trans("View"), trans("View"), 'view_1.gif') : '')
				. (HeaderOrFooter::existingFile('company_header') ? button('DeleteHeader0', trans("Delete"), trans("Delete"), ICON_DELETE) : '')
			)
			. "</div>"
			. "<small class='text-muted'>Keep empty for no change</small>"
		),
		"",
		"",
		0,
		'_company_header'
	);
	
	label_row(
		trans("Footer") . ":",
		(
			"<div>"
			. (
				"<input type='file' name='company_footer' class='me-2' accept='image/png, image/jpeg'>"
				. (HeaderOrFooter::existingFile('company_footer') ? button('ViewFooter0', trans("View"), trans("View"), 'view_1.gif') : '')
				. (HeaderOrFooter::existingFile('company_footer') ? button('DeleteFooter0', trans("Delete"), trans("Delete"), ICON_DELETE) : '')
			)
			. "</div>"
			. "<small class='text-muted'>Keep empty for no change</small>"
		),
		"",
		"",
		0,
		'_company_footer'
	);
}

check_row(trans("Delete Company Logo:"), 'del_coy_logo', $_POST['del_coy_logo']);

check_row(trans("Automatic Revaluation Currency Accounts"), 'auto_curr_reval', $_POST['auto_curr_reval']);
check_row(trans("Time Zone on Reports"), 'time_zone', $_POST['time_zone']);
check_row(trans("Company Logo on Reports"), 'company_logo_report', $_POST['company_logo_report']);
check_row(trans("Use Barcodes on Stocks"), 'barcodes_on_stock', $_POST['barcodes_on_stock']);
check_row(trans("Auto Increase of Document References"), 'ref_no_auto_increase', $_POST['ref_no_auto_increase']);
check_row(trans("Use Dimensions on Recurrent Invoices"), 'dim_on_recurrent_invoice', $_POST['dim_on_recurrent_invoice']);
check_row(trans("Use Long Descriptions on Invoices"), 'long_description_invoice', $_POST['long_description_invoice']);
dimensions_list_row(trans("Default Dimension"), 'dflt_dimension', null, true, " ", false, 1);

$_SESSION['wa_current_user']->is_developer_session
    ? text_row(trans("Developer Password") . ':', 'developer_password', null, 20, 20)
	: hidden('developer_password');

	table_section_title(trans("General Ledger Settings"));
	fiscalyears_list_row(trans("Fiscal Year:"), 'f_year', $_POST['f_year']);
	text_row_ex(trans("Tax Periods:"), 'tax_prd', 10, 10, '', null, null, trans('Months.'));
	text_row_ex(trans("Tax Last Period:"), 'tax_last', 10, 10, '', null, null, trans('Months back.'));
	check_row(trans("Put alternative Tax Include on Docs"), 'alternative_tax_include_on_docs', null);
	check_row(trans("Suppress Tax Rates on Docs"), 'suppress_tax_rates', null);
	check_row(trans("Automatic Revaluation Currency Accounts"), 'auto_curr_reval', $_POST['auto_curr_reval']);
	
	table_section_title(trans("Sales Pricing"));
	sales_types_list_row(trans("Base for auto price calculations:"), 'base_sales', $_POST['base_sales'], false,
		trans('No base price list') );
	
	text_row_ex(trans("Add Price from Std Cost:"), 'add_pct', 10, 10, '', null, null, "%");
	$curr = get_currency($_POST['curr_default']);
	text_row_ex(trans("Round calculated prices to nearest:"), 'round_to', 10, 10, '', null, null, $curr['hundreds_name']);
	check_row(trans("Collect Processing Fee from Customer"), 'collect_processing_chg_frm_cust', null);
	label_row("", "&nbsp;");
	
	
	table_section_title(trans("Optional Modules"));
	check_row(trans("Manufacturing"), 'use_manufacturing', null);
	check_row(trans("Fixed Assets"), 'use_fixed_assets', null);
	number_list_row(trans("Use Dimensions:"), 'use_dimension', null, 0, 2);
	
	table_section_title(trans("User Interface Options"));
	
	check_row(trans("Short Name and Name in List"), 'shortname_name_in_list', $_POST['shortname_name_in_list']);
	check_row(trans("Open Print Dialog Direct on Reports"), 'print_dialog_direct', null);
	check_row(trans("Search Item List"), 'no_item_list', null);
	check_row(trans("Search Customer List"), 'no_customer_list', null);
	check_row(trans("Search Supplier List"), 'no_supplier_list', null);
	text_row_ex(trans("Login Timeout:"), 'login_tout', 10, 10, '', null, null, trans('seconds'));
	text_row_ex(trans("Max day range in documents"), 'max_days_in_docs', 10, 10, '', null, null, trans('days.'));

if ($_SESSION['wa_current_user']->is_developer_session) {
    table_section_title(trans("AMC Configuration"));
    password_row(trans('Password For Updating Locally'), 'amc_pwd_req_for_local_update', null);
	date_time_row(trans("AMC Last Renewed Till"), 'amc_last_renewed_till');
    text_row_ex(trans("AMC Duration"), 'amc_duration_in_months', 10, 10, '', null, null, trans('months'));
    security_roles_list_row(trans("Notify to Roles"), 'amc_notify_to_roles', null, false, false, false, false, true);
    text_row_ex(trans("Early Notice Before"), 'amc_early_notice_days', 10, 10, '', null, null, trans('days'));
    text_row_ex(trans("Late Notice Before"), 'amc_late_notice_days', 10, 10, '', null, null, trans('days'));
    text_row_ex(trans("After Expiry Grace"), 'amc_grace_days_after_expiry', 10, 10, '', null, null, trans('days'));
    label_row(trans("Local updated at"), get_post('amc_system_updated_at') ? Carbon::parse(get_post('amc_system_updated_at'))->format(dateformat().' h:i A') : '');
    label_row(trans("Server updated at"), get_post('amc_server_updated_at') ? Carbon::parse(get_post('amc_server_updated_at'))->format(dateformat().' h:i A') : '');
    label_row(trans("Last fetched at"), get_post('amc_last_fetched_at') ? Carbon::parse(get_post('amc_last_fetched_at'))->format(dateformat().' h:i A') : '');
    label_row(trans("Last fetch result"), get_post('amc_last_fetch_result'));
}

table_section(2);
table_section_title(trans("AXISPRO CONFIGURATIONS"));

check_row(trans("ENABLE IP RESTRICTION"), 'ip_restriction', $_POST['ip_restriction']);
check_row(trans("Enable Passport No Column When invoicing"), 'enable_passport_column');
check_row(trans("Enable IBAN No Column When invoicing"), 'enable_iban_no_column');
check_row(trans("Contact Person is mandatory When invoicing"), 'is_contact_person_mandatory');
check_row(trans("Email is mandatory When invoicing"), 'is_email_mandatory');
check_row(trans("Automatically register walking customer"), 'auto_register_customer');
check_row(trans("Show Receipt Info In Invoice"), 'show_receipt_info_in_invoice');
check_row(trans("Require authorization code for credit card payment"), 'req_auth_code_4_cc_pmt');
check_row(trans("Req card no. when using credit card"), 'req_card_no_4_cr_cd_pmt');
check_row(trans("Req card no. when using center card"), 'req_card_no_4_cn_cd_pmt');
check_row(trans("Show Commission Total In Reports"), 'show_comm_total_in_reports');
array_selector_row(trans('Enabled Payment Methods'), 'enabled_payment_methods', null, PAYMENT_METHODS, ["multi" => true]);
text_row_ex(trans("ORGANIZATION IP:"), 'org_ip', 50, 50);

if ($_SESSION['wa_current_user']->is_developer_session) {
	text_row_ex(trans("Max User Limit :"), 'max_user_limit', 10);
	array_selector_row(
		trans('Enabled Modules'),
		'enabled_modules',
		null,
		[
			Permissions::HEAD_MENU_SALES => 'Sales',
			Permissions::HEAD_MENU_PURCHASE => 'Purchase',
			'HEAD_MENU_INVENTORY' => 'Items And Inventory',
			Permissions::HEAD_MENU_FINANCE => 'Finance',
			Permissions::HEAD_MENU_ASSET => 'Fixed Assets',
			Permissions::HEAD_MENU_HR => 'HR',
			Permissions::HEAD_MENU_LABOUR => 'Domestic Workers',
		],
		[
			'multi' => true
		]
	);
}

else {
	hidden('max_user_limit');
	hidden('enabled_modules');
}

check_row(trans("Enable Invoice Editing:"),'enable_invoice_editing',$_POST['enable_invoice_editing']);
check_row(trans("Refresh Permissions on each request:"),'refresh_permissions',$_POST['refresh_permissions']);
check_row(trans("Show allocation col in SOA:"), 'show_alloc_in_soa');
check_row(trans("Post GL Entries Only After Updating Transaction ID:"),'gl_after_transaction_id_update',$_POST['gl_after_transaction_id_update']);

array_selector_row(
	trans("Additional Columns in Customer Statement").":",
	'extra_cols_in_statement',
	null,
	[
        'remarks' => 'Remarks',
		'description' => 'Line Description',
		'description_ar' => 'Line Description Arabic',
		'line_reference' => 'Line Reference',
		'transaction_id' => 'Transaction ID',
		'application_id' => 'Application ID',
		'passport_no' => 'Passport No',
		'narration' => 'Narration',
		'quantity' => 'Quantity',
		'line_total' => 'Line Total',
	],
	['multi' => true]
);

gl_all_accounts_list_row(
    trans('Bank Balance Report Accounts'),
    'bank_bal_rep_accounts',
    null,
    false,
    false,
    false,
    false,
    false,
    true,
    ['multi' => true]
);

stock_categories_list_row(trans("TASHEEL Category"),'tasheel_category',$_POST['tasheel_category'], '-- select --');
stock_categories_list_row(trans("TADBEER Category"),'tadbeer_category',$_POST['tadbeer_category'], '-- select --');
stock_categories_list_row(trans("IMMIGRATION Category"),'immigration_category',$_POST['immigration_category'], '-- select --');

gl_all_accounts_list_row(trans("Service Charge Return Account:"),'service_charge_return_account',
	$_POST['service_charge_return_account'], false, false, '-- select --');
gl_all_accounts_list_row(trans("NOQODI ACCOUNT:"),'noqodi_account',$_POST['noqodi_account'], false, false, '-- select --');
gl_all_accounts_list_row(trans("Opening Balance Equity Account:"), 'opening_bal_equity_account',
	$_POST['opening_bal_equity_account'], false, false, '-- select --');

gl_account_types_list_row(trans("GL - Bank Account Group:"), 'gl_bank_account_group',
	$_POST['gl_bank_account_group'], '-- select --');
gl_account_types_list_row(trans("GL - Payment Card Account Group:"), 'gl_payment_card_group',
	$_POST['gl_payment_card_group'], '-- select --');


text_row(trans("Customer ID Prefix:"),'customer_id_prefix',$_POST['customer_id_prefix'],28,28);

customer_list_row(trans("Exclude Customers From Report:"), 'excluded_customers', null,
	'--select customers--', true, false, true, '', '', true);

table_section_title(trans("SMS CONFIGURATIONS"));
check_row('Should Sent SMS Automatically', 'send_sms_automatically');
textarea_row('SMS Template', 'sms_template', null, 50, 5, 255);
small_qty_row('Sent Link LifeTime', 'sent_link_lifetime', 0, null, 'minutes (0 min means its permanent)', 0);

table_section_title(trans("EMAIL CONFIGURATIONS"));
check_row('Should Sent Email Automatically', 'send_email_automatically');
textarea_row('Email Subject', 'email_subject', null, 50, 1, 255);
textarea_row('Email Template', 'email_template', null, 50, 5, null);
small_qty_row('Sent Link LifeTime', 'email_link_lifetime', 0, null, 'minutes (0 min means its permanent)', 0);

//echo array_selector('sdfsdf',null,[0,1,2],["multi" => true]);

table_section_title(trans("Scheduler Configurations"));
check_row('Enable Leave Accrual Scheduler', 'is_leave_accrual_scheduler_enabled');
check_row('Enable Auto Allocation Scheduler', 'is_auto_alloc_scheduler_enabled');
check_row('Enable Gratuity Accrual Scheduler', 'is_gratuity_accrual_scheduler_enabled');

table_section_title(trans("Version Information"));
if ($instanceCreatedAt = config('app.instance_created_at')) {
	label_row(trans("Instance Created At"), Carbon::parse($instanceCreatedAt)->toDayDateTimeString());
}
label_row(trans("Source Version"), $GLOBALS['src_version'] ?? '--');
label_row(trans("Database Scheme Version"), $_POST['version_id']);
label_row(trans("Core Version"), config('app.version'));
label_row(trans("Core Version Updated At"), Carbon::parse(config('app.version_updated_at'))->toDayDateTimeString());

end_outer_table(1);

hidden('coy_logo', $_POST['coy_logo']);
submit_center('update', trans("Update"), true, '',  'default');

end_form(2);
//-------------------------------------------------------------------------------------------------

end_page();

