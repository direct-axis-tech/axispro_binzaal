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
function add_dimension(
	$reference, 
	$name, 
	$type_, 
	$date_, 
	$due_date, 
	$memo_,
	$gst_no=null,
	$inv_prefix=null,
	$pos_type=0,
	$has_service_request=0,
	$is_service_request_required=0,
	$has_token_filter=1,
	$require_token=0,
	$is_1to1_token=0,
	$has_autofetch=0,
	$is_payment_separate=0,
	$is_invoice_tax_included=0,
	$is_returnable_amt_editable=0,
	$is_returnable_act_editable=0,
	$center_card_accounts=null,
	$cash_accounts=null,
	$credit_card_accounts=null,
	$customer_card_accounts=null,
	$bank_transfer_accounts=null,
	$online_payment_accounts=null,
	$is_having_split_govt_fee=0,
	$is_service_fee_combined=0,
	$is_govt_bank_editable=0,
	$is_other_fee_editable=0,
	$is_passport_col_enabled=0,
	$is_app_id_col_enabled=0,
	$is_trans_id_col_enabled=0,
	$is_narration_col_enabled=0,
    $tax_effective_from=null,
	$is_receivable_commission_amt_editable = 0,
	$is_receivable_commission_act_editable = 0,
	$enabled_payment_methods=null,
	$is_discount_editable=0,
	$is_line_ref_col_enabled=0,
	$center_type=null,
	$dflt_payment_method=null,
	$default_customer_id=null,
	$dflt_payment_term=null,
	$is_cost_grouped_in_inv=0,
	$govt_fee_editable_in_purch=0,
	$enable_line_ref_in_purch=0,
	$enable_assignee_col=0,
	$round_off_to=0,
	$round_off_algorithm=null,
    $enable_round_off=0,
    $enable_govt_fee_pmt_method=0,
    $require_govt_fee_pmt_method=0,
    $auto_purchase_maid=0,
	$is_fine_col_enabled=0
)
{
	global $Refs;

	begin_transaction();

	$date = date2sql($date_);
	$duedate = date2sql($due_date);
    $tax_effective_from = date2sql($tax_effective_from);

	$sql = (
		"INSERT INTO ".TB_PREF."dimensions (
			reference, 
			name, 
			type_, 
			date_, 
			due_date,
			`gst_no`, 
			`invoice_prefix`, 
			`pos_type`,
			`center_type`,
			`default_customer_id`,
			`has_service_request`, 
			`is_service_request_required`,
			`has_token_filter`,
			`require_token`, 
			`is_1to1_token`, 
			`has_autofetch`, 
			`is_payment_separate`, 
			`is_invoice_tax_included`,
			`is_cost_grouped_in_inv`,
			`is_returnable_amt_editable`,
			`is_returnable_act_editable`,
			`is_having_split_govt_fee`,
			`is_service_fee_combined`,
			`is_govt_bank_editable`,
			`is_other_fee_editable`,
			`is_passport_col_enabled`,
			`is_app_id_col_enabled`,
			`is_trans_id_col_enabled`,
			`is_narration_col_enabled`,
			`center_card_accounts`,
			`cash_accounts`,
			`credit_card_accounts`,
			`customer_card_accounts`,
			`bank_transfer_accounts`,
			`online_payment_accounts`,
			`tax_effective_from`,
			`is_receivable_commission_amt_editable`,
			`is_receivable_commission_act_editable`,
			`enabled_payment_methods`,
			`enable_govt_fee_pmt_method`,
			`require_govt_fee_pmt_method`,
			`is_discount_editable`,
			`is_line_ref_col_enabled`,
			`dflt_payment_method`,
			`dflt_payment_term`,
			`auto_purchase_maid`,
			`govt_fee_editable_in_purch`,
			`enable_line_ref_in_purch`,
			`enable_assignee_col`,
			`enable_round_off`,
			`round_off_to`,
			`is_fine_col_enabled`,
			`round_off_algorithm`
		)
		VALUES (
			".db_escape($reference).", 
			".db_escape($name).", 
			".db_escape($type_).", 
			'$date', 
			'$duedate',
			".db_escape($gst_no).",
			".db_escape($inv_prefix).",
			".db_escape($pos_type).",
			".db_escape($center_type, true).",
			".db_escape($default_customer_id, true).",
			$has_service_request,
			$is_service_request_required,
			$has_token_filter,
			$require_token,
			$is_1to1_token,
			$has_autofetch,
			$is_payment_separate,
			$is_invoice_tax_included,
			$is_cost_grouped_in_inv,
			$is_returnable_amt_editable,
			$is_returnable_act_editable,
			$is_having_split_govt_fee,
			$is_service_fee_combined,
			$is_govt_bank_editable,
			$is_other_fee_editable,
			$is_passport_col_enabled,
			$is_app_id_col_enabled,
			$is_trans_id_col_enabled,
			$is_narration_col_enabled,
			".db_escape($center_card_accounts).",
			".db_escape($cash_accounts).",
			".db_escape($credit_card_accounts).",
			".db_escape($customer_card_accounts).",
			".db_escape($bank_transfer_accounts).",
			".db_escape($online_payment_accounts).",
			'$tax_effective_from',
			$is_receivable_commission_amt_editable,
			$is_receivable_commission_act_editable,
			".db_escape($enabled_payment_methods).",
			".db_escape($enable_govt_fee_pmt_method).",
			".db_escape($require_govt_fee_pmt_method).",
			$is_discount_editable,
			$is_line_ref_col_enabled,
			".db_escape($dflt_payment_method, true).",
			".db_escape($dflt_payment_term, true).",
			".db_escape($auto_purchase_maid).",
			$govt_fee_editable_in_purch,
			$enable_line_ref_in_purch,
			$enable_assignee_col,
			".db_escape($enable_round_off).",
			".db_escape($round_off_to).",
			".db_escape($is_fine_col_enabled).",
			".db_escape($round_off_algorithm, true)."
		)"
	);
	db_query($sql, "could not add dimension");
	$id = db_insert_id();

	add_comments(ST_DIMENSION, $id, $date_, $memo_);

	$Refs->save(ST_DIMENSION, $id, $reference);

	commit_transaction();

	return $id;
}

function update_dimension(
	$id, 
	$name, 
	$type_, 
	$date_, 
	$due_date, 
	$memo_,
	$gst_no=null,
	$inv_prefix=null,
	$pos_type=0,
	$has_service_request=0,
	$is_service_request_required=0,
	$has_token_filter=1,
	$require_token=0,
	$is_1to1_token=0,
	$has_autofetch=0,
	$is_payment_separate=0,
	$is_invoice_tax_included=0,
	$is_returnable_amt_editable=0,
	$is_returnable_act_editable=0,
	$center_card_accounts=null,
	$cash_accounts=null,
	$credit_card_accounts=null,
	$customer_card_accounts=null,
	$bank_transfer_accounts=null,
	$online_payment_accounts=null,
	$is_having_split_govt_fee=0,
	$is_service_fee_combined=0,
	$is_govt_bank_editable=0,
	$is_other_fee_editable=0,
	$is_passport_col_enabled=0,
	$is_app_id_col_enabled=0,
	$is_trans_id_col_enabled=0,
	$is_narration_col_enabled=0,
    $tax_effective_from=null,
	$is_receivable_commission_amt_editable = 0,
	$is_receivable_commission_act_editable = 0,
	$enabled_payment_methods=null,
	$is_discount_editable=0,
	$is_line_ref_col_enabled=0,
	$center_type=null,
	$dflt_payment_method=null,
	$default_customer_id=null,
	$dflt_payment_term=null,
	$is_cost_grouped_in_inv=0,
	$govt_fee_editable_in_purch=0,
	$enable_line_ref_in_purch=0,
	$enable_assignee_col=0,
	$round_off_to=0,
	$round_off_algorithm=null,
    $enable_round_off=0,
    $enable_govt_fee_pmt_method=0,
    $require_govt_fee_pmt_method=0,
    $auto_purchase_maid=0,
	$is_fine_col_enabled=0
)
{
	begin_transaction();

	$date = date2sql($date_);
	$duedate = date2sql($due_date);
    $tax_effective_from = date2sql($tax_effective_from);

	$sql = (
		"UPDATE ".TB_PREF."dimensions SET name=".db_escape($name).",
			type_ = ".db_escape($type_).",
			date_='$date',
			due_date='$duedate',
			gst_no = ".db_escape($gst_no).",
			invoice_prefix=".db_escape($inv_prefix).",
			pos_type = ".db_escape($pos_type).",
			center_type = ".db_escape($center_type, true).",
			default_customer_id = ".db_escape($default_customer_id, true).",
			has_service_request = $has_service_request,
			is_service_request_required = $is_service_request_required,
			has_token_filter = $has_token_filter,
			require_token = $require_token,
			is_1to1_token = $is_1to1_token,
			has_autofetch = $has_autofetch,
			is_payment_separate = $is_payment_separate,
			is_invoice_tax_included = $is_invoice_tax_included,
			is_cost_grouped_in_inv = $is_cost_grouped_in_inv,
			is_having_split_govt_fee = $is_having_split_govt_fee,
			is_service_fee_combined = $is_service_fee_combined,
			is_govt_bank_editable = $is_govt_bank_editable,
			is_other_fee_editable = $is_other_fee_editable,
			is_passport_col_enabled = $is_passport_col_enabled,
			is_app_id_col_enabled = $is_app_id_col_enabled,
			is_trans_id_col_enabled = $is_trans_id_col_enabled,
			is_fine_col_enabled = $is_fine_col_enabled,
			is_narration_col_enabled = $is_narration_col_enabled,
			center_card_accounts=".db_escape($center_card_accounts).",
			cash_accounts=".db_escape($cash_accounts).",
			credit_card_accounts=".db_escape($credit_card_accounts).",
			customer_card_accounts=".db_escape($customer_card_accounts).",
			bank_transfer_accounts=".db_escape($bank_transfer_accounts).",
			online_payment_accounts=".db_escape($online_payment_accounts).",
			is_returnable_amt_editable = $is_returnable_amt_editable,
			is_returnable_act_editable = $is_returnable_act_editable,
			tax_effective_from='$tax_effective_from',
			is_receivable_commission_amt_editable = $is_receivable_commission_amt_editable,
			is_receivable_commission_act_editable = $is_receivable_commission_act_editable,
			enabled_payment_methods = ".db_escape($enabled_payment_methods).",
            enable_govt_fee_pmt_method = ".db_escape($enable_govt_fee_pmt_method).",
            require_govt_fee_pmt_method = ".db_escape($require_govt_fee_pmt_method).",
			is_discount_editable = $is_discount_editable,
			is_line_ref_col_enabled = $is_line_ref_col_enabled,
			dflt_payment_method = ".db_escape($dflt_payment_method, true).",
			dflt_payment_term = ".db_escape($dflt_payment_term, true).",
			auto_purchase_maid = ".db_escape($auto_purchase_maid).",
			govt_fee_editable_in_purch = $govt_fee_editable_in_purch,
			enable_line_ref_in_purch = $enable_line_ref_in_purch,
			enable_assignee_col = $enable_assignee_col,
            enable_round_off = ".db_escape($enable_round_off).",
			round_off_to = ".db_escape($round_off_to).",
			round_off_algorithm = ".db_escape($round_off_algorithm, true)."
		WHERE id = ".db_escape($id)
	);

	db_query($sql, "could not update dimension");

	update_comments(ST_DIMENSION, $id, null, $memo_);

	commit_transaction();

	return $id;
}

function delete_dimension($id)
{
	begin_transaction();

	// delete the actual dimension
	$sql="DELETE FROM ".TB_PREF."dimensions WHERE id=".db_escape($id);
	db_query($sql,"The dimension could not be deleted");

	delete_comments(ST_DIMENSION, $id);

	commit_transaction();
}

//--------------------------------------------------------------------------------------

function get_dimension($id, $allow_null=false)
{
    $sql = "SELECT * FROM ".TB_PREF."dimensions	WHERE id=".db_escape($id);

	$result = db_query($sql, "The dimension could not be retrieved");

	if (!$allow_null && db_num_rows($result) == 0)
		display_db_error("Could not find dimension $id", $sql);

	return db_fetch($result);
}

//--------------------------------------------------------------------------------------

function get_dimension_string($id, $html=false, $space=' ', $excludeRef = false)
{
	if ($id <= 0)
	{
		if ($html)
			$dim = "&nbsp;";
		else
			$dim = "";
	}
	else
	{
		$row = get_dimension($id, true);
		$dim = $excludeRef ? $row['name'] : $row['reference'] . $space . $row['name'];
	}

	return $dim;
}

//--------------------------------------------------------------------------------------

/**
 * Get the list of all dimensions
 *
 * @param array $filters
 * @return mysqli_result
 */
function get_dimensions($filters = [])
{
    $where = '1 = 1';
    if (!isset($filters['closed'])) {
        $where .= " AND closed = 0";
    } else if ($filters['closed'] != 'both') {
        $where .= " AND closed = " .(int)(bool)$filters['closed'];
    }

	$sql = "SELECT * FROM ".TB_PREF."dimensions WHERE {$where} ORDER BY date_";

	return db_query($sql, "The dimensions could not be retrieved");
}

//--------------------------------------------------------------------------------------

function dimension_has_deposits($id)
{
	return dimension_has_payments($id);
}

//--------------------------------------------------------------------------------------

function dimension_has_payments($id)
{
	$sql = "SELECT COUNT(*) FROM ".TB_PREF."gl_trans WHERE dimension_id = ".db_escape($id)
	 . " OR dimension2_id = ".db_escape($id);
	$res = db_query($sql, "Transactions could not be calculated");
	$row = db_fetch_row($res);
	return ($row[0] > 0);
}

function dimension_is_closed($id)
{
	$result = get_dimension($id);
	return ($result['closed'] == '1');
}

//--------------------------------------------------------------------------------------

function close_dimension($id)
{
	$sql = "UPDATE ".TB_PREF."dimensions SET closed='1' WHERE id = ".db_escape($id);
	db_query($sql, "could not close dimension");
}

//--------------------------------------------------------------------------------------

function reopen_dimension($id)
{
	$sql = "UPDATE ".TB_PREF."dimensions SET closed='0' WHERE id = ".db_escape($id);
	db_query($sql, "could not reopen dimension");
}

//--------------------------------------------------------------------------------------

function get_dimension_balance_all($id, $from, $to) 
{
	$from = date2sql($from);
	$to = date2sql($to);
	$sql = "SELECT account, coa.account_name, sum(amount) AS amt 
		FROM "
		.TB_PREF."gl_trans trans,"
		.TB_PREF."chart_master coa
		WHERE
			trans.account = coa.account_code
		AND	(dimension_id = ".db_escape($id)." OR dimension2_id = ".db_escape($id).")
		AND	tran_date >= '$from' AND tran_date <= '$to' GROUP BY account";
	return db_query($sql, "Transactions could not be calculated");
}

//--------------------------------------------------------------------------------------

function get_dimension_balance($id, $from, $to) 
{
	$id = db_escape($id);
	$sql = "SELECT SUM(amount)
			FROM ".TB_PREF."gl_trans 
			WHERE
				tran_date >= '" .	date2sql($from) . "' 
			AND	tran_date <= '" . date2sql($to) . "' 
			AND (dimension_id = $id OR dimension2_id = $id)";
	$res = db_query($sql, "Sum of transactions could not be calculated");
	$row = db_fetch_row($res);

	return $row[0];
}

//--------------------------------------------------------------------------------------

function get_sql_for_search_dimensions()
{
	$sql = (
		"SELECT dim.id,
			dim.reference,
			dim.name,
			dim.type_,
			dim.date_,
			dim.due_date,
			dim.closed,
			dim.gst_no, 
			dim.invoice_prefix, 
			dim.pos_type,
			dim.has_service_request, 
			dim.is_service_request_required,
			dim.has_token_filter,
			dim.require_token,
			dim.is_1to1_token, 
			dim.has_autofetch, 
			dim.is_payment_separate, 
			dim.is_invoice_tax_included,
			dim.is_returnable_amt_editable,
			dim.is_returnable_act_editable
		FROM ".TB_PREF."dimensions as dim WHERE id > 0"
	);

	return $sql;
}
