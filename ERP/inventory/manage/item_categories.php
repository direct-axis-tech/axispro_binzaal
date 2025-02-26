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
$page_security = 'SA_ITEMCATEGORY';
$path_to_root = "../..";
include($path_to_root . "/includes/session.inc");

if (isset($_GET['FixedAsset'])) {
  $page_security = 'SA_ASSETCATEGORY';
  $help_context = "Fixed Assets Categories";
  $_POST['mb_flag'] = STOCK_TYPE_FIXED_ASSET;
}
else {
  $help_context = "Item Categories";
}

$js = "";
if ($SysPrefs->use_popup_windows && $SysPrefs->use_popup_search)
	$js .= get_js_open_window(900, 500);

$additional_script = ';$("select#govt_bank_accounts").select2({placeholder: "-- select --", minimumResultsForSearch: 5});';
in_ajax() ? $GLOBALS['Ajax']->addScript(true, $additional_script) : ($js .= $additional_script);

page(trans($help_context), false, false, "", $js);

include_once($path_to_root . "/includes/ui.inc");

include_once($path_to_root . "/inventory/includes/inventory_db.inc");

simple_page_mode(true);

$fixed_asset = is_fixed_asset(get_post('mb_flag'));

//----------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM') 
{

	//initialise no input errors assumed initially before we test
	$input_error = 0;

	if (strlen($_POST['description']) == 0) 
	{
		$input_error = 1;
		display_error(trans("The item category description cannot be empty."));
		set_focus('description');
	}
	if (!$fixed_asset){
		if (strlen($_POST['group_id']) == 0)
		{
			$input_error = 1;
			display_error(trans("The category group cannot be empty."));
			set_focus('group_id');
		}

		if (empty($_POST['belongs_to_dep'])) {
			$input_error = 1;
			display_error("Please select the departments");
			set_focus('belongs_to_dep');
		}

		if (empty(get_post('emp_comm_calc_method'))) {
			$input_error = 1;
			display_error("Please select the employee commission calculation method");
			set_focus('emp_comm_calc_method');
		}
		
		if (empty(get_post('dflt_costing_method'))) {
			$input_error = 1;
			display_error("Please select the default costing method for this category");
			set_focus('dflt_costing_method');
		}

		if (
			get_post('dflt_costing_method') == COSTING_METHOD_EXPENSE
			&& empty(get_post('dflt_pending_cogs_act'))
		) {
			$input_error = 1;
			display_error(sprintf(
				"Costing method: %s requires Deferred COGS account to be not empty."
				. " Please select the Deferred COGS account",
				$GLOBALS['costing_methods'][COSTING_METHOD_EXPENSE]
			));
			set_focus('dflt_pending_cogs_act');
		}
	}



	if ($input_error !=1)
	{
		$_POST['govt_bank_accounts'] = is_array(get_post('govt_bank_accounts')) ? implode(',', get_post('govt_bank_accounts')) : '';
    	if ($selected_id != -1) 
    	{
		    update_item_category(
                $selected_id,
                $_POST['group_id'],
                $_POST['description'],
				$_POST['tax_type_id'],
                $_POST['sales_account'], 
				$_POST['cogs_account'],
                $_POST['inventory_account'], 
				$_POST['adjustment_account'],
                $_POST['wip_account'],
				$_POST['units'],
                $_POST['mb_flag'],
                $_POST['dim1'],
                $_POST['dim2'],
				check_value('no_sale'),
                check_value('no_purchase'),
				check_value('govt_bnk_editable'),
				check_value('usr_sel_ac'),
				$_POST['belongs_to_dep'],
				$_POST['dflt_pending_sales_act'],
				$_POST['dflt_pending_cogs_act'],
				check_value('srq_app_id_required'),
				check_value('srq_trans_id_required'),
				check_value('inv_app_id_required'),
				check_value('inv_trans_id_required'),
				check_value('inv_narration_required'),
				check_value('is_app_id_unique'),
				check_value('is_trans_id_unique'),
				$_POST['govt_bank_accounts'],
				check_value('is_allowed_below_service_chg'),
				check_value('is_allowed_below_govt_fee'),
				check_value('is_govt_fee_editable'),
				check_value('is_srv_chrg_editable'),
				get_post('emp_comm_calc_method'),
				get_post('dflt_costing_method')
            );
			display_notification(trans('Selected item category has been updated'));

			$id = $selected_id;
    	} 
    	else 
    	{
		    add_item_category(
                $_POST['group_id'],
                $_POST['description'],
				$_POST['tax_type_id'],
                $_POST['sales_account'],
                $_POST['cogs_account'],
                $_POST['inventory_account'],
                $_POST['adjustment_account'],
                $_POST['wip_account'],
                $_POST['units'],
                $_POST['mb_flag'],
                $_POST['dim1'],
                $_POST['dim2'],
                check_value('no_sale'),
                check_value('no_purchase'),
				check_value('govt_bnk_editable'),
				check_value('usr_sel_ac'),
				$_POST['belongs_to_dep'],
				$_POST['dflt_pending_sales_act'],
				$_POST['dflt_pending_cogs_act'],
				check_value('srq_app_id_required'),
				check_value('srq_trans_id_required'),
				check_value('inv_app_id_required'),
				check_value('inv_trans_id_required'),
				check_value('inv_narration_required'),
				check_value('is_app_id_unique'),
				check_value('is_trans_id_unique'),
				$_POST['govt_bank_accounts'],
				check_value('is_allowed_below_service_chg'),
				check_value('is_allowed_below_govt_fee'),
				check_value('is_govt_fee_editable'),
				check_value('is_srv_chrg_editable'),
				get_post('emp_comm_calc_method'),
				get_post('dflt_costing_method')
            );
			display_notification(trans('New item category has been added'));
            $id = db_insert_id();
    	}

        $dir =  $path_to_root."/themes/daxis/images";
        if(isset($_FILES['logo']) && $_FILES['logo']["size"] >0){
            $tmpname = $_FILES['logo']['tmp_name'];
            $ext = end((explode(".", $_FILES['logo']['name'])));

            if(!in_array($ext,["png","PNG"])) {
                display_warning("Logo could not updated. Only PNG file types allowed");
            }

            $filesize = $_FILES['logo']['size'];
            $filetype = $_FILES['logo']['type'];
            if (file_exists($dir."/".$dir."/cat_logo_".$_POST['description'].".$ext"))
                unlink($dir."/".$dir."/cat_logo_".$_POST['description'].".$ext");

            move_uploaded_file($tmpname, $dir."/cat_logo_".$_POST['description'].".$ext");
        }


		$Mode = 'RESET';
	}
}

//---------------------------------------------------------------------------------- 

if ($Mode == 'Delete')
{

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'stock_master'
	if (key_in_foreign_table($selected_id, 'stock_master', 'category_id'))
	{
		display_error(trans("Cannot delete this item category because items have been created using this item category."));
	} 
	else 
	{
		delete_item_category($selected_id);
		display_notification(trans('Selected item category has been deleted'));
	}
	$Mode = 'RESET';
}

if ($Mode == 'RESET')
{
	$selected_id = -1;
	$sav = get_post('show_inactive');
    $mb_flag = get_post('mb_flag');
	unset($_POST);
	$_POST['show_inactive'] = $sav;
	if (is_fixed_asset($mb_flag))
		$_POST['mb_flag'] = STOCK_TYPE_FIXED_ASSET;
}
if (list_updated('mb_flag')) {
	$Ajax->activate('details');
}

//----------------------------------------------------------------------------------

$result = get_item_categories(check_value('show_inactive'), $fixed_asset);

start_form(true);
start_table(TABLESTYLE, "width='80%'");
if ($fixed_asset) {
	$th = array(
        trans("Name"),
        trans("Group"),
        trans("Tax type"),
        trans("Units"),
        trans("Sales Act"),
		trans("Asset Account"),
        trans("Deprecation Cost Account"),
        trans("Depreciation/Disposal Account"),
        "",
        ""
    );
} else {
	$th = array(
        trans("Name"),
        trans("Group"),
        trans("Tax type"),
        trans("Units"),
        trans("Type"),
        trans("Sales Act"),
		trans("Inventory Account"),
        trans("COGS Account"),
        trans("Adjustment Account"),
		trans("Assembly Account"),
        trans("Pending Sales Act"),
        trans("Pending COGS Act"),
        "",
        ""
    );
}
inactive_control_column($th);

table_header($th);
$k = 0; //row colour counter

while ($myrow = db_fetch($result)) 
{
	alt_table_row_color($k);
	label_cell($myrow["description"]);
	label_cell($myrow["group_name"]);
	label_cell($myrow["tax_name"]);
	label_cell($myrow["dflt_units"], "align=center");
	if (!$fixed_asset)
		label_cell($stock_types[$myrow["dflt_mb_flag"]]);
	label_cell($myrow["dflt_sales_act"], "align=center");
	label_cell($myrow["dflt_inventory_act"], "align=center");
	label_cell($myrow["dflt_cogs_act"], "align=center");
	label_cell($myrow["dflt_adjustment_act"], "align=center");
	if (!$fixed_asset) {
		label_cell($myrow["dflt_wip_act"], "align=center");
		label_cell($myrow["dflt_pending_sales_act"], "align=center");
		label_cell($myrow["dflt_pending_cogs_act"], "align=center");
    }
	inactive_control_cell($myrow["category_id"], $myrow["inactive"], 'stock_category', 'category_id');
 	edit_button_cell("Edit".$myrow["category_id"], trans("Edit"));
 	delete_button_cell("Delete".$myrow["category_id"], trans("Delete"));
	end_row();
}

inactive_control_row($th);
end_table();
echo '<br>';
//----------------------------------------------------------------------------------

div_start('details');
start_table(TABLESTYLE2);

if ($selected_id != -1) 
{
 	if ($Mode == 'Edit') {
		//editing an existing item category
		$myrow = get_item_category($selected_id);

		$_POST['category_id'] = $myrow["category_id"];
        $_POST['group_id'] = $myrow['group_id'];
		$_POST['description']  = $myrow["description"];
		$_POST['tax_type_id']  = $myrow["dflt_tax_type"];
		$_POST['sales_account']  = $myrow["dflt_sales_act"];
		$_POST['cogs_account']  = $myrow["dflt_cogs_act"];
		$_POST['inventory_account']  = $myrow["dflt_inventory_act"];
		$_POST['adjustment_account']  = $myrow["dflt_adjustment_act"];
		$_POST['wip_account']  = $myrow["dflt_wip_act"];
		$_POST['units']  = $myrow["dflt_units"];
		$_POST['mb_flag']  = $myrow["dflt_mb_flag"];
		$_POST['dim1']  = $myrow["dflt_dim1"];
		$_POST['dim2']  = $myrow["dflt_dim2"];
		$_POST['no_sale']  = $myrow["dflt_no_sale"];
		$_POST['no_purchase']  = $myrow["dflt_no_purchase"];
		$_POST['dflt_costing_method']  = $myrow["dflt_costing_method"];
		$_POST['emp_comm_calc_method']  = $myrow["emp_comm_calc_method"];
		$_POST['is_tasheel']  = $myrow["is_tasheel"];
		$_POST['govt_bnk_editable']  = $myrow["govt_bnk_editable"];
		$_POST['usr_sel_ac']  = $myrow["usr_sel_ac"];
		$_POST['dflt_pending_sales_act']  = $myrow["dflt_pending_sales_act"];
		$_POST['dflt_pending_cogs_act']  = $myrow["dflt_pending_cogs_act"];
		$_POST['govt_bank_accounts'] = explode(',', $myrow['govt_bank_accounts']);
		$_POST['belongs_to_dep'] = json_decode($myrow['belongs_to_dep']);
		$_POST['srq_app_id_required'] = $myrow['srq_app_id_required'];
		$_POST['srq_trans_id_required'] = $myrow['srq_trans_id_required'];
		$_POST['inv_app_id_required'] = $myrow['inv_app_id_required'];
		$_POST['inv_trans_id_required'] = $myrow['inv_trans_id_required'];
		$_POST['inv_narration_required'] = $myrow['inv_narration_required'];
		$_POST['is_app_id_unique'] = $myrow['is_app_id_unique'];
		$_POST['is_trans_id_unique'] = $myrow['is_trans_id_unique'];
		$_POST['is_allowed_below_service_chg'] = $myrow['is_allowed_below_service_chg'];
		$_POST['is_allowed_below_govt_fee'] = $myrow['is_allowed_below_govt_fee'];
//		$_POST['dflt_dimension_id']  = $myrow["dflt_dimension_id"];
		$_POST['is_govt_fee_editable']  = $myrow["is_govt_fee_editable"];
		$_POST['is_srv_chrg_editable']  = $myrow["is_srv_chrg_editable"];
	}
	hidden('selected_id', $selected_id);
	hidden('category_id');
} else if ($Mode != 'CLONE') {
		$_POST['long_description'] = '';
		$_POST['description'] = '';
        $_POST['group_id'] = '';
		$_POST['no_sale']  = 0;
		$_POST['no_purchase']  = 0;
		$_POST['is_tasheel']  = 0;

		$company_record = get_company_prefs();

    if (get_post('inventory_account') == "")
    	$_POST['inventory_account'] = $company_record["default_inventory_act"];

    if (get_post('cogs_account') == "")
    	$_POST['cogs_account'] = $company_record["default_cogs_act"];

	if (get_post('sales_account') == "")
		$_POST['sales_account'] = $company_record["default_inv_sales_act"];

	if (get_post('adjustment_account') == "")
		$_POST['adjustment_account'] = $company_record["default_adj_act"];

	if (get_post('wip_account') == "")
		$_POST['wip_account'] = $company_record["default_wip_act"];

	// $_POST['mb_flag'] = STOCK_TYPE_SERVICE;

}

text_row(trans("Category Name:"), 'description', null, 30, 100);

if (!$fixed_asset){
	category_groups_list_row(trans("Category Group"), 'group_id', null);

	file_row(trans("Upload Logo") . ":", 'logo', 'logo');

	table_section_title(trans("Default values for new items"));
} else {
	hidden('group_id', 0);
}

item_tax_types_list_row(trans("Item Tax Type:"), 'tax_type_id', null);

//dimensions_list_row(trans('Default Cost Center'),'dflt_dimension_id',$_POST['dflt_dimension_id'],true,'-No Applicable-');


if (is_fixed_asset(get_post('mb_flag')))
	hidden('mb_flag', STOCK_TYPE_FIXED_ASSET);
else
	stock_item_types_list_row(trans("Item Type:"), 'mb_flag', null, true);

stock_units_list_row(trans("Units of Measure:"), 'units', null);

if (is_fixed_asset($_POST['mb_flag'])) 
	hidden('no_sale', 0);
else
	check_row(trans("Exclude from sales:"), 'no_sale');

check_row(trans("Exclude from purchases:"), 'no_purchase');

if (!$fixed_asset) {
	array_selector_row(
		trans("Costing Method"),
		'dflt_costing_method',
		null,
		$GLOBALS['costing_methods'],
		[
			'spec_option' => '-- select --',
			'spec_id' => '',
		]
	);

	array_selector_row(
		trans('Employee Commission Calculation Method'),
		'emp_comm_calc_method',
		null,
		commission_calculation_methods(),
		[
			'spec_option' => '-- select --',
			'spec_id' => '',
		]
	);
	check_row(trans("Is E-DIRHAM ? :"), 'is_tasheel');

	check_row(trans("is Govt Bank A/C editable:"), 'govt_bnk_editable');
	check_row(trans("User Selected A/C:"), 'usr_sel_ac');
	check_row(trans("Is govt Fee Editable:"), 'is_govt_fee_editable');
	check_row(trans("Is Service Charge Editable:"), 'is_srv_chrg_editable');
	check_row(trans("Application ID required in Service Request:"), 'srq_app_id_required');
	check_row(trans("Transaction ID required in Service Request:"), 'srq_trans_id_required');
	check_row(trans("Application ID required when invoicing:"), 'inv_app_id_required');
	check_row(trans("Transaction ID required when invoicing:"), 'inv_trans_id_required');
	check_row(trans("Narration required when invoicing:"), 'inv_narration_required');
	check_row(trans("Is Application ID unique:"), 'is_app_id_unique');
	check_row(trans("Is Transaction ID unique:"), 'is_trans_id_unique');
	check_row(trans("Allow Invoice Below Configured Service Chg.:"), 'is_allowed_below_service_chg');
	check_row(trans("Allow Invoice Below Configured Govt. Fee:"), 'is_allowed_below_govt_fee');
} else {
	hidden('dflt_costing_method', COSTING_METHOD_NORMAL);
	hidden('emp_comm_calc_method', CCM_AMOUNT);
	hidden('govt_bnk_editable', 0);
	hidden('usr_sel_ac', 0);
	hidden('srq_app_id_required', 0);
	hidden('srq_trans_id_required', 0);
	hidden('inv_app_id_required', 0);
	hidden('inv_trans_id_required', 0);
	hidden('inv_narration_required', 0);
	hidden('is_app_id_unique', 0);
	hidden('is_trans_id_unique', 0);
	hidden('is_allowed_below_service_chg', 1);
	hidden('is_allowed_below_govt_fee', 1);
	hidden('is_govt_fee_editable', 1);
	hidden('is_srv_chrg_editable', 1);
}

gl_all_accounts_list_row(trans("Sales Account:"), 'sales_account', $_POST['sales_account']);

if (is_service($_POST['mb_flag']))
{
	gl_all_accounts_list_row(trans("C.O.G.S. Account:"), 'cogs_account', $_POST['cogs_account']);
	hidden('inventory_account', $_POST['inventory_account']);
	hidden('adjustment_account', $_POST['adjustment_account']);
}
elseif (is_fixed_asset($_POST['mb_flag'])) 
{
	gl_all_accounts_list_row(trans("Asset account:"), 'inventory_account', $_POST['inventory_account']);
	gl_all_accounts_list_row(trans("Depreciation cost account:"), 'cogs_account', $_POST['cogs_account']);
	gl_all_accounts_list_row(trans("Depreciation/Disposal account:"), 'adjustment_account', $_POST['adjustment_account']);
}
else
{
	gl_all_accounts_list_row(trans("Inventory Account:"), 'inventory_account', $_POST['inventory_account']);

	gl_all_accounts_list_row(trans("C.O.G.S. Account:"), 'cogs_account', $_POST['cogs_account']);
	gl_all_accounts_list_row(trans("Inventory Adjustments Account:"), 'adjustment_account', $_POST['adjustment_account']);
}

if (is_manufactured($_POST['mb_flag']))
	gl_all_accounts_list_row(trans("Item Assembly Costs Account:"), 'wip_account', $_POST['wip_account']);
else
	hidden('wip_account', $_POST['wip_account']);

if (!$fixed_asset){
	gl_all_accounts_list_row(trans("Deferred Sales Account:"), 'dflt_pending_sales_act', null, true, false, '-- select --');
	gl_all_accounts_list_row(trans("Deferred Cogs Account:"), 'dflt_pending_cogs_act', null, true, false, '-- select --');
	gl_all_accounts_list_row(trans('Govt. Bank Accounts'), 'govt_bank_accounts', null, false, false, false, false, false, false, array('multi' => true));
	dimensions_list_row(trans("Belongs to department"), 'belongs_to_dep', null, false, "", false, 1, false, true);
} else {
	hidden('dflt_pending_sales_act', 0);
	hidden('dflt_pending_cogs_act', 0);
	hidden('govt_bank_accounts', 0);
	hidden('belongs_to_dep', 0);
}

$dim = get_company_pref('use_dimension');
if ($dim >= 1)
{
	// dimensions_list_row(trans("Dimension 1"), 'dim1', null, true, " ", false, 1);
	if ($dim > 1)
		dimensions_list_row(trans("Dimension")." 2", 'dim2', null, true, " ", false, 2);
}
// if ($dim < 1)
	hidden('dim1', 0);
if ($dim < 2)
	hidden('dim2', 0);

end_table(1);
div_end();
submit_add_or_update_center($selected_id == -1, '', 'both', true);

end_form();

end_page();

