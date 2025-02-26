<?php
/**********************************************************************
 * Direct Axis Technology L.L.C.
 * Released under the terms of the GNU General Public License, GPL,
 * as published by the Free Software Foundation, either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
 ***********************************************************************/
function add_item_category(
    $group_id,
    $description,
    $tax_type_id,
    $sales_account,
    $cogs_account,
    $inventory_account,
    $adjustment_account,
    $wip_account,
    $units,
    $mb_flag,
    $dim1,
    $dim2,
    $no_sale,
    $no_purchase,
    $govt_bnk_editable,
    $usr_sel_ac,
    $belongs_to_dep = [],
    $deferred_sales_account = null,
    $deferred_cogs_account = null,
    $srq_app_id_required = 0,
    $srq_trans_id_required = 0,
    $inv_app_id_required = 0,
    $inv_trans_id_required = 0,
    $inv_narration_required = 0,
    $is_app_id_unique = 0,
    $is_trans_id_unique = 0,
    $govt_bank_accounts = null,
    $is_allowed_below_service_chg = 0,
    $is_allowed_below_govt_fee = 0,
    $is_govt_fee_editable = 0,
    $is_srv_chrg_editable = 0,
    $emp_comm_calc_method = null,
    $dflt_costing_method = null
)    
{
    $sql = "INSERT INTO " . TB_PREF . "stock_category (group_id, description, dflt_tax_type,
			dflt_units, dflt_mb_flag, dflt_sales_act, dflt_cogs_act, 
			dflt_inventory_act, dflt_adjustment_act, dflt_wip_act, 
			dflt_dim1, dflt_dim2, dflt_no_sale,is_tasheel, dflt_no_purchase,govt_bnk_editable,usr_sel_ac, belongs_to_dep,
            dflt_pending_sales_act, dflt_pending_cogs_act, srq_app_id_required, srq_trans_id_required, inv_app_id_required,
            inv_trans_id_required, inv_narration_required, is_app_id_unique, is_trans_id_unique, govt_bank_accounts,
            is_allowed_below_service_chg, is_allowed_below_govt_fee, is_govt_fee_editable, is_srv_chrg_editable,
            emp_comm_calc_method, dflt_costing_method)
		VALUES ("
        . db_escape($group_id) . ","
        . db_escape($description) . ","
        . db_escape($tax_type_id) . ","
        . db_escape($units) . ","
        . db_escape($mb_flag) . ","
        . db_escape($sales_account) . ","
        . db_escape($cogs_account) . ","
        . db_escape($inventory_account) . ","
        . db_escape($adjustment_account) . ","
        . db_escape($wip_account) . ","
        . db_escape($dim1) . ","
        . db_escape($dim2) . ","
        . db_escape($no_sale) . ","
        . db_escape($_POST['is_tasheel']) . ","
        . db_escape($no_purchase) . ","
        . db_escape($govt_bnk_editable) . ","
        . db_escape($usr_sel_ac) . ","
        . quote(json_encode(array_values($belongs_to_dep))) . ","
        . db_escape($deferred_sales_account) . ","
        . db_escape($deferred_cogs_account) . ","
        . db_escape($srq_app_id_required) . ","
        . db_escape($srq_trans_id_required) . ","
        . db_escape($inv_app_id_required) . ","
        . db_escape($inv_trans_id_required) . ","
        . db_escape($inv_narration_required) . ","
        . db_escape($is_app_id_unique) . ","
        . db_escape($is_trans_id_unique) . ","
        . db_escape($govt_bank_accounts) . ","
        . db_escape($is_allowed_below_service_chg) . ","
        . db_escape($is_allowed_below_govt_fee) . ","
        . db_escape($is_govt_fee_editable) . ","
        . db_escape($is_srv_chrg_editable) . ","
        . db_escape($emp_comm_calc_method, true) . ","
        . db_escape($dflt_costing_method, true) .
        ")";

    db_query($sql, "an item category could not be added");
}

function update_item_category(
    $id,
    $group_id,
    $description,
    $tax_type_id,
    $sales_account,
    $cogs_account,
    $inventory_account,
    $adjustment_account,
    $wip_account,
    $units,
    $mb_flag,
    $dim1,
    $dim2,
    $no_sale,
    $no_purchase,
    $govt_bnk_editable,
    $usr_sel_ac,
    $belongs_to_dep = [],
    $deferred_sales_account = null,
    $deferred_cogs_account = null,
    $srq_app_id_required = 0,
    $srq_trans_id_required = 0,
    $inv_app_id_required = 0,
    $inv_trans_id_required = 0,
    $inv_narration_required = 0,
    $is_app_id_unique = 0,
    $is_trans_id_unique = 0,
    $govt_bank_accounts = null,
    $is_allowed_below_service_chg = 0,
    $is_allowed_below_govt_fee = 0,
    $is_govt_fee_editable = 0,
    $is_srv_chrg_editable = 0,
    $emp_comm_calc_method = null,
    $dflt_costing_method = null
)
{
    $sql = "UPDATE " . TB_PREF . "stock_category SET "
        . "group_id = " . db_escape($group_id) . ","
        . "description = " . db_escape($description) . ","
        . "dflt_tax_type = " . db_escape($tax_type_id) . ","
        . "dflt_units = " . db_escape($units) . ","
        . "dflt_mb_flag = " . db_escape($mb_flag) . ","
        . "dflt_sales_act = " . db_escape($sales_account) . ","
        . "dflt_cogs_act = " . db_escape($cogs_account) . ","
        . "dflt_inventory_act = " . db_escape($inventory_account) . ","
        . "dflt_adjustment_act = " . db_escape($adjustment_account) . ","
        . "dflt_wip_act = " . db_escape($wip_account) . ","
        . "dflt_dim1 = " . db_escape($dim1) . ","
        . "dflt_dim2 = " . db_escape($dim2) . ","
        . "dflt_no_sale = " . db_escape($no_sale) . ","
        . "dflt_pending_sales_act = " . db_escape($deferred_sales_account) . ","
        . "dflt_pending_cogs_act = " . db_escape($deferred_cogs_account) . ","
        . "belongs_to_dep = " . quote(json_encode(array_values($belongs_to_dep))) . ","
        . "govt_bank_accounts = " . db_escape($govt_bank_accounts) . ","
        . "is_tasheel = " . db_escape($_POST['is_tasheel']) . ","
        . "dflt_no_purchase = " . db_escape($no_purchase) . ","
        . "govt_bnk_editable =" . db_escape($govt_bnk_editable) . ","
        . "usr_sel_ac =".db_escape($usr_sel_ac) . ","
        . "srq_app_id_required =".db_escape($srq_app_id_required) . ","
        . "srq_trans_id_required =".db_escape($srq_trans_id_required) . ","
        . "inv_app_id_required =".db_escape($inv_app_id_required) . ","
        . "inv_trans_id_required =".db_escape($inv_trans_id_required) . ","
        . "inv_narration_required =".db_escape($inv_narration_required) . ","
        . "is_app_id_unique =".db_escape($is_app_id_unique) . ","
        . "is_trans_id_unique =".db_escape($is_trans_id_unique) . ","
        . "is_allowed_below_service_chg =".db_escape($is_allowed_below_service_chg) . ","
        . "is_allowed_below_govt_fee =".db_escape($is_allowed_below_govt_fee). ","
        . "is_govt_fee_editable =".db_escape($is_govt_fee_editable) . ","
        . "is_srv_chrg_editable =".db_escape($is_srv_chrg_editable) . ","
        . "emp_comm_calc_method =".db_escape($emp_comm_calc_method, true) . ","
        . "dflt_costing_method =".db_escape($dflt_costing_method, true)
        . " WHERE category_id = " . db_escape($id);

    db_query($sql, "an item category could not be updated");
}

function delete_item_category($id)
{
    $sql = "DELETE FROM " . TB_PREF . "stock_category WHERE category_id=" . db_escape($id);

    db_query($sql, "an item category could not be deleted");
}

function get_item_categories($show_inactive, $fixed_asset = false)
{
    global $SysPrefs;

    $sql = "SELECT c.*, t.name as tax_name, g.desc as group_name FROM " . TB_PREF . "stock_category c "
        . "LEFT JOIN `0_category_groups` g ON g.id = c.group_id, "
        . TB_PREF . "item_tax_types t WHERE c.dflt_tax_type=t.id";
    if (!$show_inactive)
        $sql .= " AND !c.inactive";
    if ($fixed_asset)
        $sql .= " AND c.dflt_mb_flag='".STOCK_TYPE_FIXED_ASSET."'";
    else
        $sql .= " AND c.dflt_mb_flag!='".STOCK_TYPE_FIXED_ASSET."'";

    $sql .= " ORDER BY CASE category_id
  WHEN " . $SysPrefs->prefs['immigration_category'] . " THEN 1
  WHEN " . $SysPrefs->prefs['tasheel_category'] . " THEN 2
  ELSE 3 END, description";

    return db_query($sql, "could not get stock categories");
}

function get_item_categories_keyed_by_id($show_inactive, $fixed_asset = false) {
    $mysqli_result = get_item_categories($show_inactive, $fixed_asset);
    $categories = [];
    while ($c = $mysqli_result->fetch_assoc()) {
        $categories[$c['category_id']] = $c;
    }

    return $categories;
}

function get_item_category($id)
{
    $sql = "SELECT * FROM " . TB_PREF . "stock_category WHERE category_id=" . db_escape($id);

    $result = db_query($sql, "an item category could not be retrieved");

    return db_fetch($result);
}

function get_category_name($id)
{
    $sql = "SELECT description FROM " . TB_PREF . "stock_category WHERE category_id=" . db_escape($id);

    $result = db_query($sql, "could not get sales type");

    $row = db_fetch_row($result);
	return is_array($row) ? $row[0] : false;
}

function get_item_category_by_name($category_name)
{
    $sql="SELECT * FROM ".TB_PREF."stock_category WHERE description=".db_escape($category_name);

    $result = db_query($sql,"an item category could not be retrieved");

    return db_fetch($result);
}