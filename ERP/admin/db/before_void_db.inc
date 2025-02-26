<?php 
function get_table_columns($table_name,$prefix_for_column=''){
	switch ($table_name) {
		case '0_debtor_trans':
		case '0_voided_debtor_trans':
			$columns = 'trans_no,type,version,debtor_no,branch_code,tran_date,due_date,created_at,reference,barcode,tpe,order_,ov_amount,ov_gst,ov_freight,ov_freight_tax,ov_discount,alloc,prep_amount,rate,ship_via,dimension_id,dimension2_id,payment_terms,tax_included,display_customer,customer_trn,customer_mobile,customer_email,customer_ref,payment_method,credit_card_charge,show_bank_charge,payment_flag,cust_emp_id,cust_emp_name,invoice_type,inv_total,customer_card_amount,created_by,payment_type,cheq_no,cheq_date,contact_person,round_of_amount';
			break;

		case '0_bank_trans':
		case '0_voided_bank_trans':
			$columns = 'id,type,trans_no,bank_act,ref,trans_date,amount,dimension_id,dimension2_id,person_type_id,person_id,reconciled,payment_type,cheq_no,cheq_date,created_by';
			break;

		case '0_customer_rewards':
		case '0_voided_customer_rewards':
			$columns = 'id,trans_no,detail_id,trans_type,tran_date,stock_id,reward_type,customer_id,qty,conversion_rate,reward_point,reward_amount,created_by,created_at';
			break;
		case '0_cust_allocations':
		case '0_voided_cust_allocations':
			$columns = 'id,person_id,amt,date_alloc,trans_no_from,trans_type_from,date_alloc_to,trans_no_to,trans_type_to,stamp,updated_by';
			break;
		case '0_debtor_trans_details':
		case '0_voided_debtor_trans_details':
			$columns = 'id,debtor_trans_no,debtor_trans_type,stock_id,description,unit_price,unit_tax,quantity,discount_percent,discount_amount,standard_cost,qty_done,src_id,govt_fee,govt_bank_account,bank_service_charge,bank_service_charge_vat,pf_amount,transaction_id,ed_transaction_id,application_id,user_commission,created_by,updated_by,created_at,updated_at,ref_name,transaction_id_updated_at';
			break;
		case '0_gl_trans':
		case '0_voided_gl_trans':
			$columns = 'counter,type,type_no,tran_date,account,axispro_subledger_code,memo_,amount,transaction_id,dimension_id,dimension2_id,person_type_id,person_id,reconciled,created_by';
			break;
		case '0_grn_items':
		case '0_voided_grn_items':
			$columns = 'id,grn_batch_id,po_detail_item,item_code,description,qty_recd,quantity_inv';
			break;
		case '0_journal':
		case '0_voided_journal':
			$columns = 'type,trans_no,tran_date,reference,source_ref,event_date,doc_date,currency,amount,rate';
			break;
		case '0_purch_orders':
		case '0_voided_purch_orders':
			$columns = 'order_no,supplier_id,comments,ord_date,reference,requisition_no,into_stock_location,delivery_address,total,prep_amount,alloc,tax_included,pay_terms,quote_file,terms_and_cond';
			break;
		case '0_purch_order_details':
		case '0_voided_purch_order_details':
			$columns = 'po_detail_item,order_no,item_code,description,delivery_date,qty_invoiced,unit_price,act_price,std_cost_unit,quantity_ordered,quantity_received';
			break;
		case '0_sales_order_details':
		case '0_voided_sales_order_details':
			$columns = 'id,order_no,trans_type,item_code,kit_ref,line_reference,srv_req_line_id,stk_code,description,qty_sent,qty_not_sent,qty_expensed,unit_price,_unit_tax,quantity,invoiced,discount_percent,discount_amount,_discount_taxed,govt_fee,bank_service_charge,bank_service_charge_vat,pf_amount,transaction_id,ed_transaction_id,application_id,passport_no,govt_bank_account,split_govt_fee_amt,split_govt_fee_acc,returnable_amt,returnable_to,receivable_commission_amount,receivable_commission_account,ref_name,extra_srv_chg,user_commission,customer_commission,cust_comm_center_share,cust_comm_emp_share,customer_commission2,assignee_id,created_by,transaction_id_updated_at,transaction_id_updated_by';
			break;
		case '0_sales_orders':
		case '0_voided_sales_orders':
			$columns = 'id,order_no,trans_type,service_req_id,version,type,debtor_no,salesman_id,branch_code,contract_id,reference,customer_ref,comments,ord_date,period_from,period_till,order_type,ship_via,delivery_address,contact_phone,contact_email,contact_person,customer_trn,display_customer,narrations,deliver_to,freight_cost,from_stk_loc,delivery_date,payment_terms,total,_tax,_tax_included,_discount_taxed,prep_amount,alloc,created_by,updated_by,transacted_at,updated_at';
			break;
		case '0_voided_moves':
		case '0_voided_stock_moves':
			$columns = 'trans_id,trans_no,stock_id,type,loc_code,tran_date,price,reference,contract_id,maid_id,qty,standard_cost';
			break;
		case '0_supp_allocations':
		case '0_voided_supp_allocations':
			$columns = 'id,person_id,amt,date_alloc,trans_no_from,trans_type_from,date_alloc_to,trans_no_to,trans_type_to,stamp,updated_by';
			break;
		case '0_supp_invoice_items':
		case '0_voided_supp_invoice_items':
			$columns = 'id,supp_trans_no,supp_trans_type,gl_code,grn_item_id,po_detail_item_id,stock_id,description,quantity,unit_price,unit_tax,memo_,dimension_id,dimension2_id';
			break;
		case '0_supp_trans':
		case '0_voided_supp_trans':
			$columns='trans_no,type,supplier_id,reference,supp_reference,tran_date,due_date,ov_amount,ov_discount,ov_gst,rate,alloc,tax_included';
			break;
		case '0_trans_tax_details':
		case '0_voided_trans_tax_details':
			$columns = 'id,trans_type,trans_no,tran_date,tax_type_id,rate,ex_rate,included_in_price,net_amount,amount,memo,reg_type';
			break;
		
		default:
			$columns = '*';
			break;
	}
	if($prefix_for_column!=''){
		$column_array = explode(',',$columns);
		foreach ($column_array as $key => $column) {
			$column_array[$key] = $prefix_for_column . ".".$column;
		}
		$columns = implode (',', $column_array);

	}

	return $columns;
}
function before_void_journal_trans($type, $type_no){
	if(db_num_rows(db_query("SELECT * from ".TB_PREF."voided_journal WHERE type=".db_escape($type)." AND trans_no=".db_escape($type_no)))==0){
		$columns = get_table_columns('0_voided_journal');
		$sql_before_void = "INSERT INTO ".TB_PREF."voided_journal($columns) SELECT $columns from ".TB_PREF."journal WHERE type=".db_escape($type)." AND trans_no=".db_escape($type_no);
		db_query($sql_before_void, "could not void journal transaction for type=$type and trans_no=$type_no");
	}
}

function before_void_gl_trans($type, $trans_id){
	if(db_num_rows(db_query("SELECT * from ".TB_PREF."voided_gl_trans WHERE type=".db_escape($type)." AND type_no=".db_escape($trans_id)))==0){
	$columns = get_table_columns('0_voided_gl_trans'); 
		$sql_before_void = "INSERT INTO ".TB_PREF."voided_gl_trans($columns) SELECT $columns from ".TB_PREF."gl_trans WHERE type=".db_escape($type)." AND type_no=".db_escape($trans_id);
		db_query($sql_before_void, "could not void gl transactions for type=$type and trans_no=$trans_id");
	}
}

function before_delete_purch_order_details($id)
{
	if (db_num_rows(db_query("SELECT * from ".TB_PREF."voided_purch_order_details WHERE po_detail_item = " . db_escape($id)))==0){
		$columns = get_table_columns('0_purch_order_details'); 
		$sql_before_void = "INSERT INTO ".TB_PREF."voided_purch_order_details ($columns) SELECT $columns from ".TB_PREF."purch_order_details WHERE po_detail_item = " . db_escape($id);
		db_query($sql_before_void, "a purchase order details record could not be voided.");
	}
}

function before_delete_grn_items($grn_batch)
{
	if (db_num_rows(db_query("SELECT * from ".TB_PREF."voided_grn_items WHERE grn_batch_id=".db_escape($grn_batch)))==0){
		$columns = get_table_columns('0_grn_items'); 
		$sql_before_void = "INSERT INTO ".TB_PREF."voided_grn_items ($columns) SELECT $columns from ".TB_PREF."grn_items WHERE grn_batch_id = " . db_escape($grn_batch);
		db_query($sql_before_void, "A grn detail item could not be voided.");
	}
}

function before_delete_sales_order($order_no, $trans_type) {
	$columns = get_table_columns('0_sales_orders'); 
	$sql_before_void = "INSERT INTO ".TB_PREF."voided_sales_orders ($columns) SELECT $columns from ".TB_PREF."sales_orders WHERE trans_type = " . db_escape($trans_type)." AND order_no = ".db_escape($order_no);
	db_query($sql_before_void, "a sales order record could not be voided.");
}

function before_delete_sales_order_details($order_no, $trans_type, $ids_to_keep='') {
	$columns = get_table_columns('0_sales_order_details'); 
	$sql_before_void = "INSERT INTO ".TB_PREF."voided_sales_order_details ($columns) SELECT $columns from ".TB_PREF."sales_order_details WHERE trans_type = " . db_escape($trans_type)." AND order_no = ".db_escape($order_no).($ids_to_keep ? " AND id NOT in ($ids_to_keep)" : '');
	db_query($sql_before_void, "a sales order detail record could not be voided.");
}

function get_sql_before_void_sales_orders($type,$type_no,$void=false){
	$sales_orders_table = $void==false ? "0_sales_orders" : "0_voided_sales_orders";
	$cust_allocations_table = $void==false ? "0_cust_allocations" : "0_voided_cust_allocations";
	$columns = get_table_columns($sales_orders_table,'so');
	$sql = "SELECT $columns from ".$sales_orders_table." so
	LEFT JOIN ".$cust_allocations_table." ca ON ca.trans_type_to=so.trans_type AND ca.trans_no_to=so.order_no AND ca.person_id=so.debtor_no
	WHERE  ((ca.trans_type_from=".db_escape($type)." AND ca.trans_no_from=".db_escape($type_no).")
		OR (ca.trans_type_to=".db_escape($type)." AND ca.trans_no_to=".db_escape($type_no)."))";
return $sql;
}
function before_void_cust_allocations($type, $type_no) {
	before_clear_cust_alloctions($type, $type_no);
}
function before_clear_cust_alloctions($type, $type_no, $person_id = null){

	$time = date(DB_DATETIME_FORMAT);
	$user = authUser()->id;
	$columns = get_table_columns('0_voided_cust_allocations');
	$sql_before_void3 = "INSERT INTO ".TB_PREF."voided_cust_allocations($columns, _created_at, _modified_by) SELECT $columns, '$time' as _created_at, '$user' as _modified_by from ".TB_PREF."cust_allocations WHERE  ((trans_type_from=".db_escape($type)." AND trans_no_from=".db_escape($type_no).")
		OR (trans_type_to=".db_escape($type)." AND trans_no_to=".db_escape($type_no)."))";
	if ($person_id)
		$sql_before_void3 .= " AND person_id=".db_escape($person_id);
	db_query($sql_before_void3, "could not void customer allocations for type=$type and trans_no=$type_no");
}
function before_delete_cust_allocation($trans_id)
{
	$time = date(DB_DATETIME_FORMAT);
	$user = authUser()->id;
	$columns = get_table_columns('0_voided_cust_allocations');
	$sql = "INSERT INTO ".TB_PREF."voided_cust_allocations($columns, _created_at, _modified_by) SELECT $columns, '$time' as _created_at, '$user' as _modified_by from ".TB_PREF."cust_allocations WHERE id = ".db_escape($trans_id);
	return db_query($sql, "The existing allocation $trans_id could not be deleted");
}
function get_sql_before_void_cust_trans($type,$type_no,$void=false){
	$debtor_trans_table = $void==false ? "0_debtor_trans" : "0_voided_debtor_trans";
	$cust_allocations_table = $void==false ? "0_cust_allocations" : "0_voided_cust_allocations";
	$paym_columns = get_table_columns('0_voided_debtor_trans','paym');
	$dt_columns = get_table_columns('0_voided_debtor_trans','dt');
	$trans_columns = get_table_columns('0_voided_debtor_trans','trans');

	$sql = "(SELECT $dt_columns FROM ".$debtor_trans_table." dt
		LEFT JOIN ".$cust_allocations_table." ca ON ca.trans_type_to=dt.type AND ca.trans_no_to=dt.trans_no AND ca.person_id=dt.debtor_no
		WHERE  ((ca.trans_type_from=".db_escape($type)." AND ca.trans_no_from=".db_escape($type_no).")
			OR (ca.trans_type_to=".db_escape($type)." AND ca.trans_no_to=".db_escape($type_no)."))
)
UNION
(SELECT $paym_columns FROM ".$debtor_trans_table." paym
	LEFT JOIN ".$cust_allocations_table." ca ON ca.trans_type_from=paym.type AND ca.trans_no_from=paym.trans_no AND ca.person_id=paym.debtor_no
	WHERE  ((ca.trans_type_from=".db_escape($type)." AND ca.trans_no_from=".db_escape($type_no).")
		OR (ca.trans_type_to=".db_escape($type)." AND ca.trans_no_to=".db_escape($type_no).")))
UNION 
(SELECT $trans_columns FROM ".$debtor_trans_table." trans
	WHERE trans.trans_no = ".db_escape($type_no)." AND trans.type = ".db_escape($type)."
	)";

return $sql;

}
function before_void_cust_trans($type,$type_no){
	if(db_num_rows(db_query(get_sql_before_void_cust_trans($type,$type_no,true)))==0){
		$columns = get_table_columns('0_voided_debtor_trans');
		$sql_before_void = "INSERT IGNORE INTO ".TB_PREF."voided_debtor_trans($columns) ".get_sql_before_void_cust_trans($type,$type_no,false);
		db_query($sql_before_void, "could not void debtor transactions for type=$type and trans_no=$type_no");

	}
}
function get_sql_before_void_purch_orders($type,$type_no,$void=false){
	$purch_orders_table = $void==false ? "0_purch_orders" : "0_voided_purch_orders";
	$supp_allocations_table = $void==false ? "0_supp_allocations" : "0_voided_supp_allocations";
	$columns = get_table_columns($purch_orders_table,'po');

	$sql = "SELECT $columns from ".$purch_orders_table." po
	LEFT JOIN ".$supp_allocations_table." ca ON ca.trans_type_to=".ST_PURCHORDER." AND ca.trans_no_to=po.order_no AND ca.person_id=po.supplier_id
	WHERE  ((ca.trans_type_from=".db_escape($type)." AND ca.trans_no_from=".db_escape($type_no).")
		OR (ca.trans_type_to=".db_escape($type)." AND ca.trans_no_to=".db_escape($type_no)."))";
return $sql;
}
function before_delete_supp_allocation($trans_id)
{
	$columns = get_table_columns('0_voided_supp_allocations');
	$sql = "INSERT INTO ".TB_PREF."voided_supp_allocations ($columns) SELECCT $columns FROM ".TB_PREF."supp_allocations WHERE id = ".db_escape($trans_id);
	db_query($sql, "The existing allocation $trans_id could not be deleted");
}
function before_void_supp_allocations($type, $type_no){
	before_clear_cust_alloctions($type, $type_no);
}
function before_clear_supp_alloctions($type, $type_no, $person_id=null){

	$columns = get_table_columns('0_voided_supp_allocations');
	$sql_before_void3 = "INSERT INTO ".TB_PREF."voided_supp_allocations($columns) SELECT $columns from ".TB_PREF."supp_allocations WHERE  ((trans_type_from=".db_escape($type)." AND trans_no_from=".db_escape($type_no).")
		OR (trans_type_to=".db_escape($type)." AND trans_no_to=".db_escape($type_no)."))";
	if ($person_id)
		$sql_before_void3 .= " AND person_id=".db_escape($person_id);
	db_query($sql_before_void3, "could not void customer allocations for type=$type and trans_no=$type_no");
}
function get_sql_before_void_supp_trans($type,$type_no,$void=false){
	$supp_trans_table = $void==false ? "0_supp_trans" : "0_voided_supp_trans";
	$supp_allocations_table = $void==false ? "0_supp_allocations" : "0_voided_supp_allocations";
	$st_columns = get_table_columns($supp_trans_table,'st');
	$pay_columns = get_table_columns($supp_trans_table,'paym');
	$trans_columns = get_table_columns($supp_trans_table,'trans');

	$sql = "(SELECT $st_columns FROM ".$supp_trans_table." st
		LEFT JOIN ".$supp_allocations_table." ca ON ca.trans_type_to=st.type AND ca.trans_no_to=st.trans_no AND ca.person_id=st.supplier_id
		WHERE  ((ca.trans_type_from=".db_escape($type)." AND ca.trans_no_from=".db_escape($type_no).")
			OR (ca.trans_type_to=".db_escape($type)." AND ca.trans_no_to=".db_escape($type_no)."))
)
UNION
(SELECT $pay_columns FROM ".$supp_trans_table." paym
	LEFT JOIN ".$supp_allocations_table." ca ON ca.trans_type_from=paym.type AND ca.trans_no_from=paym.trans_no AND ca.person_id=paym.supplier_id
	WHERE  ((ca.trans_type_from=".db_escape($type)." AND ca.trans_no_from=".db_escape($type_no).")
		OR (ca.trans_type_to=".db_escape($type)." AND ca.trans_no_to=".db_escape($type_no).")))
UNION 
(SELECT $trans_columns FROM ".$supp_trans_table." trans
	WHERE trans.trans_no = ".db_escape($type_no)." AND trans.type = ".db_escape($type)."
	)";

return $sql;

}
function before_void_supp_trans($type, $type_no){
	if(db_num_rows(db_query(get_sql_before_void_supp_trans($type,$type_no,true)))==0){
		$columns = get_table_columns('0_voided_supp_trans');
		$sql_before_void = "INSERT IGNORE INTO ".TB_PREF."voided_supp_trans($columns) ".get_sql_before_void_supp_trans($type,$type_no,false);
		db_query($sql_before_void, "could not void supplier transactions for type=$type and trans_no=$type_no");

	}
}
function before_void_trans_tax_details($type, $type_no){
	if(db_num_rows(db_query("SELECT * from ".TB_PREF."voided_trans_tax_details WHERE trans_type=".db_escape($type)." AND trans_no=".db_escape($type_no)))==0){
		$columns = get_table_columns('0_voided_trans_tax_details');
		$sql_before_void = "INSERT INTO ".TB_PREF."voided_trans_tax_details($columns) SELECT $columns from ".TB_PREF."trans_tax_details WHERE trans_type=".db_escape($type)." AND trans_no=".db_escape($type_no);
		db_query($sql_before_void, "The transaction tax details could not be voided");
	}
}

// Related to post_void customer_trans
function before_void_reward_points($type,$type_no){
	if(db_num_rows(db_query("SELECT * from ".TB_PREF."voided_customer_rewards WHERE trans_type=".db_escape($type)." AND trans_no=".db_escape($type_no)))==0){
		$columns = get_table_columns('0_voided_customer_rewards');
		$sql_before_void = "INSERT INTO ".TB_PREF."voided_customer_rewards($columns) SELECT $columns from 0_customer_rewards WHERE trans_type=".db_escape($type)." AND trans_no=".db_escape($type_no);
		db_query($sql_before_void, "Could not delete from customer rewards table");
		
	}
}

function before_update_prepaid_so_line($line_id){
	if(db_num_rows(db_query("SELECT * from ".TB_PREF."voided_sales_order_details WHERE id=".db_escape($line_id)))==0){
		$columns = get_table_columns('0_voided_sales_order_details');
		$sql_before_void = "INSERT INTO ".TB_PREF."voided_sales_order_details($columns) SELECT $columns from ".TB_PREF."sales_order_details WHERE id=".db_escape($line_id);
		db_query($sql_before_void, "The document detail record could not be updated with invoiced qty");
	}
}

function before_update_parent_line($line_id){
	before_update_prepaid_so_line($line_id);
}

function before_void_customer_trans_details($type, $type_no){

	if(db_num_rows(db_query("SELECT * from ".TB_PREF."voided_debtor_trans_details WHERE debtor_trans_no=" . db_escape($type_no) . "
		AND debtor_trans_type=" . db_escape($type)))==0){
		$columns = get_table_columns('0_voided_debtor_trans_details');
		$sql_before_void = "INSERT INTO ".TB_PREF."voided_debtor_trans_details($columns) SELECT $columns from ".TB_PREF."debtor_trans_details WHERE debtor_trans_no=" . db_escape($type_no) . "
	AND debtor_trans_type=" . db_escape($type);
	db_query($sql_before_void, "The debtor transaction details could not be voided");
}

}

function before_void_stock_move($type, $type_no){
	if(db_num_rows(db_query("SELECT * from ".TB_PREF."voided_stock_moves WHERE trans_no=" . db_escape($type_no) . "
		AND type=" . db_escape($type)))==0){
		$columns = get_table_columns('0_voided_stock_moves');
		$sql_before_void = "INSERT INTO ".TB_PREF."voided_stock_moves($columns) SELECT $columns from ".TB_PREF."stock_moves WHERE trans_no=" . db_escape($type_no) . "
	AND type=" . db_escape($type);
	db_query($sql_before_void, "The stock movement cannot be delated");
}
}

// Related to post_void_supp_trans($type, $type_no)
function before_void_bank_trans($type, $type_no){
	if(db_num_rows(db_query("SELECT * from ".TB_PREF."voided_bank_trans WHERE trans_no=" . db_escape($type_no) . "
		AND type=" . db_escape($type)))==0){
		$columns = get_table_columns('0_voided_bank_trans');
		$sql_before_void = "INSERT INTO ".TB_PREF."voided_bank_trans($columns) SELECT $columns from ".TB_PREF."bank_trans WHERE trans_no=" . db_escape($type_no) . "
	AND type=" . db_escape($type);
	db_query($sql_before_void, "could not void bank transactions for type=$type and trans_no=$type_no");
}
}
function before_void_supp_invoice_items($type, $type_no){
	if(db_num_rows(db_query("SELECT * from ".TB_PREF."voided_supp_invoice_items WHERE supp_trans_no=" . db_escape($type_no) . "
		AND supp_trans_type=" . db_escape($type)))==0){
		$columns = get_table_columns('0_voided_supp_invoice_items');
		$sql_before_void = "INSERT INTO ".TB_PREF."voided_supp_invoice_items($columns) SELECT $columns from ".TB_PREF."supp_invoice_items WHERE supp_trans_type = ".db_escape($type)." AND supp_trans_no=".db_escape($type_no);
	db_query($sql_before_void, "could not void bank transactions for type=$type and trans_no=$type_no");
}
}