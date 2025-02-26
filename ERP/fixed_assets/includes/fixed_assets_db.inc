<?php
/**********************************************************************
    Copyright (C) FrontAccounting, LLC.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/

function process_fixed_asset_depreciation($stock_id, $gl_rows, $refline, $memo_)
{
  global $Refs;

  $row = get_item($stock_id);
  $old_cost = $row['material_cost'];

  begin_transaction();

  $date_ = Today();
  if (!is_date_in_fiscalyear($date_))
    $date_ = end_fiscalyear();

  $stock_gl_code = get_stock_gl_code($stock_id);

  $cart = new items_cart(ST_JOURNAL);
  $cart->tran_date = $cart->doc_date = $cart->event_date = $date_;
  // $cart->tran_date = end_fiscalyear();
  $cart->reference = $Refs->get_next(ST_JOURNAL, $refline, $cart->tran_date, $date_);

  $value_of_change = 0;
  foreach ($gl_rows as $row) {
    $value_of_change += round2($row['value'], user_price_dec());

    $cart->add_gl_item($stock_gl_code["adjustment_account"],
        $stock_gl_code["dimension_id"], $stock_gl_code["dimension2_id"], -$row['value'],
        '', null, null, $row['date']);
    $cart->add_gl_item($stock_gl_code["cogs_account"], 0, 0, $row['value'],
        '', null, null, $row['date']);
  }

  $new_cost = $old_cost - $value_of_change;

  if (empty($memo_))
    $cart->memo_ = sprintf(_("Fixed asset has been deprecated by the value of %s"),
        number_format2($value_of_change, user_price_dec()));
  else
    $cart->memo_ = $memo_;

  $trans_no = write_journal_entries($cart);

  $sql = "UPDATE ".TB_PREF."stock_master SET 
    depreciation_date='".date2sql(end($gl_rows)['date'])."',
    material_cost=".db_escape($new_cost)."
    WHERE stock_id=".db_escape($stock_id);
  db_query($sql,"The depreciation start date could not be updated");

  // insert Depreciation Details
  $depreciations = [];
  foreach ($gl_rows as $rows) {
    $depreciations[] = [
      'trans_no'   => $trans_no,
      'trans_date' => date2sql($date_),
      'memo'       => $cart->memo_,
      'trans_type' => $cart->trans_type,
      'stock_id'   => $stock_id,
      'value'      => $rows['value'],
      'date'       => $rows['date'],
    ];
  }
  add_item_depreciations($depreciations);

  commit_transaction();

  return $trans_no;
}

//--------------------------------------------------------------------------------------------------

function get_fixed_asset_move($stock_id, $tran_type) {

  $sql = "SELECT * FROM ".TB_PREF."stock_moves 
	    WHERE stock_id=".db_escape($stock_id)."
	    	AND type=".db_escape($tran_type);

  $result = db_query($sql, "cannot retrieve fixed asset move");

  if (db_num_rows($result) == 0)
    return false;

  $row = db_fetch_assoc($result);
  return $row;
}

function get_fixed_asset_disposal($stock_id) {

  $sql = "SELECT * FROM ".TB_PREF."stock_moves 
    	WHERE stock_id=".db_escape($stock_id)."
    		AND (type=".ST_INVADJUST." OR type=".ST_CUSTDELIVERY.")
        ORDER BY tran_date DESC
        LIMIT 1";

  $result = db_query($sql, "cannot retrieve fixed asset move");

  if (db_num_rows($result) == 0)
    return false;

  $row = db_fetch_assoc($result);
  return $row;
}

function get_fixed_asset_purchase($stock_id) 
{
  	$sql = "SELECT * 
  		FROM ".TB_PREF."stock_master m, "
  			.TB_PREF."supp_invoice_items i, "
  			.TB_PREF."supp_trans t
    	WHERE m.stock_id=".db_escape($stock_id)."
		    AND i.supp_trans_no=t.trans_no
    		AND m.stock_id=i.stock_id
    	ORDER BY t.tran_date DESC
    	LIMIT 1";

  	$result = db_query($sql, "cannot retrieve fixed asset move");

  	if (db_num_rows($result) == 0)
    	return false;

  	$row = db_fetch_assoc($result);
  	return $row;
}

//--------------------------------------------------------------------------------------------------

function get_fixed_asset_class($id)
{
	$sql="SELECT * FROM ".TB_PREF."stock_fa_class WHERE fa_class_id=".db_escape($id);

	$result = db_query($sql,"a fixed asset class could not be retrieved");

	return db_fetch($result);
}

//--------------------------------------------------------------------------------------------------

function get_sql_for_fixed_assets($show_inactive = false)
{
  	$sql = "SELECT s.stock_id, c.description, s.units, s.description as name,
        s.depreciation_rate, s.depreciation_method, s.inactive, 
        s.material_cost, s.purchase_cost, s.depreciation_factor
    FROM ".TB_PREF."stock_master s"
    	." LEFT JOIN ".TB_PREF."stock_fa_class c ON s.fa_class_id=c.fa_class_id"
    	." WHERE s.mb_flag='".STOCK_TYPE_FIXED_ASSET."'";

  	if (!$show_inactive)
    	$sql .= " AND !s.inactive ";

  	$sql .= " ORDER BY c.description ";

	return $sql;
}
function add_item_depreciations($depreciations) {
  $insertArray = [];
  $currentTime = date('Y-m-d H:i:s');
  foreach ($depreciations as $itemData) {
    $insertArray[] = array(
      'item_id'             => db_escape($itemData['stock_id']),
      'depreciation_date'   => quote(date('Y-m-d', strtotime($itemData['date']))),
      'depreciation_amount' => db_escape($itemData['value']),
      'trans_no'            => db_escape($itemData['trans_no']),
      'trans_date'          => quote($itemData['trans_date']),
      'trans_type'          => db_escape($itemData['trans_type']),
      'description'         => db_escape($itemData['memo']),
      'created_at'          => quote($currentTime),
      'updated_at'          => quote($currentTime),
    );
  }

  // Create a list of column names and values
  $columns = implode(', ', array_keys(reset($insertArray)));
  $values  = implode(",\n", array_map(function ($item) {
    return '(' . implode(', ', array_values($item)) . ')';
  }, $insertArray));

  $sql = "INSERT INTO " . TB_PREF . "stock_depreciation_details ($columns) VALUES $values";
  return db_query($sql, "The item details could not be added");
}

function process_fixed_asset_category_depreciation($deprData, $refline, $memo_, $itemData)
{
  global $Refs;
  $transAmount = 0;
  begin_transaction();

  $date_ = Today();
  if (!is_date_in_fiscalyear($date_)) {
    $date_ = end_fiscalyear();
  }

  $cart = new items_cart(ST_JOURNAL);
  $cart->tran_date = $cart->doc_date = $cart->event_date = $date_;
  $cart->reference = $Refs->get_next(ST_JOURNAL, $refline, $cart->tran_date, $date_);

  foreach ($deprData as $key => $value) {

    foreach ($value['debit'] as $db_transactions => $db_trans) {
      $cart->add_gl_item($db_transactions, 0, 0, $db_trans['amount'], '', null, null, sql2date($key));
    }

    foreach ($value['credit'] as $cr_transactions => $cr_trans) {
      $cart->add_gl_item($cr_transactions, 0, 0, - ($cr_trans['amount']), '', null, null, sql2date($key));
      $transAmount += $cr_trans['amount'];
    }
  }

  if (empty($memo_)) {
    $cart->memo_ = sprintf(
      _("Fixed asset has been deprecated by the value of %s on %s"), number_format2($transAmount, user_price_dec()), date('d-m-Y H:i:s'));
  } else {
    $cart->memo_ = $memo_;
  }

  $trans_no = write_journal_entries($cart);

  //Update Items Last Depr Date and Material Cost
  foreach ($itemData as $items => $item) {

    $sql = "UPDATE " . TB_PREF . "stock_master SET 
    depreciation_date='" . $item['depreciationDate'] . "',
    material_cost=" . $item['materialCost'] . "
    WHERE stock_id=" . $items;
    db_query($sql, "The depreciation start date could not be updated");
  }

  // insert Depreciation Details
  $depreciations = [];
  foreach ($itemData as $itemKeys => $itemValues) {
    foreach ($itemValues['depreciationData'] as $deprKey => $deprValue) {
      $depreciations[] = [
        'trans_no'   => $trans_no,
        'trans_date' => date2sql($date_),
        'memo'       => $cart->memo_,
        'trans_type' => $cart->trans_type,
        'stock_id'   => $itemValues['details']['stock_id'],
        'value'      => $deprValue['value'],
        'date'       => $deprValue['date'],
      ];
    }
  }
  add_item_depreciations($depreciations);

  commit_transaction();

  return $trans_no;
}


function get_fixed_assets_depreciation_list($data)
{
  $monthNo = date('n', strtotime($data['to_date']));
  $year    = date('Y', strtotime($data['to_date']));

  $cond = '';
  if($data['category'] != 'All' && $data['category'] > 0) {
    $cond = " AND m.category_id = ".$data['category'];
  }

  $sql = "SELECT
            m.stock_id,
            m.description AS item_name,
            m.purchase_date,
            m.depreciation_start,
            m.depreciation_date,
            ROUND(m.purchase_cost, 2) AS purchase_cost,
            m.material_cost,
            m.depreciation_rate,
            m.category_id,
            c.description AS category_name,
            last_depr.depreciation_date AS period_last_depr_date,
            m.depreciation_method,
            ROUND(SUM(
                IFNULL(
                    CASE
                        WHEN dt.depreciation_date <= '{$data['to_date']}' THEN dt.depreciation_amount
                        ELSE 0
                    END,
                    0
                )
            ), 2) AS accumulated_depr_amount,
            ROUND(SUM(
                IFNULL(
                    CASE
                        WHEN dt.depreciation_date > last_depr.depreciation_date
                        AND MONTH(dt.depreciation_date) <=  {$monthNo}
                        AND YEAR(dt.depreciation_date) = {$year}
                        THEN dt.depreciation_amount
                        ELSE 0
                    END,
                    0
                )
            ), 2) AS period_depreciation,
            ROUND((
                ROUND(SUM(
                    IFNULL(
                        CASE
                            WHEN dt.depreciation_date <= '{$data['to_date']}' THEN dt.depreciation_amount
                            ELSE 0
                        END,
                        0
                    )
                ), 2) + ROUND(SUM(
                    IFNULL(
                        CASE
                            WHEN dt.depreciation_date > last_depr.depreciation_date
                            AND MONTH(dt.depreciation_date) <= {$monthNo}
                            AND YEAR(dt.depreciation_date) = {$year}
                            THEN dt.depreciation_amount
                            ELSE 0
                        END,
                        0
                    )
                ), 2)
            ), 2) AS total_depreciation,
            ROUND((
                ROUND(m.purchase_cost, 2)
                - ROUND(SUM(
                    IFNULL(
                        CASE
                            WHEN dt.depreciation_date <= '{$data['to_date']}' THEN dt.depreciation_amount
                            ELSE 0
                        END,
                        0
                    )
                ), 2) - ROUND(SUM(
                    IFNULL(
                        CASE
                            WHEN dt.depreciation_date > last_depr.depreciation_date
                            AND MONTH(dt.depreciation_date) <= {$monthNo}
                            AND YEAR(dt.depreciation_date) = {$year}
                            THEN dt.depreciation_amount
                            ELSE 0
                        END,
                        0
                    )
                ), 2)
            ), 2) AS net_value
          FROM 0_stock_master m
          LEFT JOIN 0_stock_depreciation_details dt ON dt.item_id = m.stock_id AND dt.is_active = 1
          LEFT JOIN (
            SELECT dt2.item_id, MAX(dt2.depreciation_date) AS depreciation_date
            FROM 0_stock_depreciation_details dt2
            WHERE dt2.is_active = 1 AND dt2.depreciation_date <= '{$data['to_date']}'
            GROUP BY dt2.item_id
          ) AS last_depr ON last_depr.item_id = m.stock_id
          LEFT JOIN 0_stock_category c ON c.category_id = m.category_id
          WHERE m.mb_flag = '".STOCK_TYPE_FIXED_ASSET."' AND m.inactive = 0 {$cond}
          GROUP BY m.stock_id
          ORDER BY m.stock_id";

  return db_query($sql, "No Transactions found")->fetch_all(MYSQLI_ASSOC);

}

function get_list_assets_to_allocate($data = array())
{
  $cond = "";
  $assign_cond = "";

  if(isset($data['itemId']) && $data['itemId'] > 0) {
    $cond .= " AND m.stock_id = ".$data['itemId'];
  }

  if(isset($data['categoryId']) && $data['categoryId'] > 0) {
    $cond .= " AND m.category_id = ".$data['categoryId'];
  }

  if(isset($data['assignStatus']) ) {
    $assign_cond = " AND a.`status` = ".$data['assignStatus'];
  }

  $sql = "SELECT m.stock_id, m.description AS stock_name, c.description AS category, a.id AS assign_id,
                a.assignee, a.is_employee, a.assigned_date, u.real_name AS assigned_by_name, a.returned_date, 
                u2.real_name AS returned_by_name, a.remarks,
                CASE 
                    WHEN a.is_employee = 1 THEN e.name
                    WHEN a.is_employee = 2 THEN d.name
                END AS allocated_to
          FROM ". TB_PREF ."stock_master m
          LEFT JOIN ". TB_PREF ."stock_category c ON c.category_id = m.category_id
          LEFT JOIN ". TB_PREF ."asset_assignments a ON a.stock_id = m.stock_id ". $assign_cond ."
          LEFT JOIN ". TB_PREF ."employees e ON e.id = a.assignee
          LEFT JOIN ". TB_PREF ."departments d ON d.id = a.assignee
          LEFT JOIN ". TB_PREF ."users u ON u.id = a.assigned_by
          LEFT JOIN ". TB_PREF ."users u2 ON u2.id = a.returned_by
          WHERE m.mb_flag = '".STOCK_TYPE_FIXED_ASSET."' AND m.inactive = 0 ". $cond ."
          ORDER BY m.stock_id, a.id ";
  
  return db_query($sql, "No Assets Found");        
}

function add_asset_allocation($insertArray)
{
  // Create a list of column names and values
  $columns = implode(', ', array_keys($insertArray));
  $values  = implode(', ', array_values($insertArray));

  $sql = "INSERT INTO " . TB_PREF . "asset_assignments ($columns) VALUES ($values)";
  db_query($sql, "The allocation could not be added");
}

function de_allocate_assets($data)
{
  $sql = "UPDATE " . TB_PREF . "asset_assignments a 
          SET a.`status` = 0, a.returned_date =". $data['returnDate'] .", 
              a.remarks = ". $data['remarks'] .", a.updated_at = '". date('Y-m-d H:i:s') ."',
              a.returned_by = ". $_SESSION["wa_current_user"]->user ."
          WHERE a.id = ".$data['assignId']." AND a.stock_id = ". $data['stockId'];
          
  db_query($sql, "The de allocation could not be processed");

}

function check_allocation_status($stock_id)
{
  $sql = "SELECT * FROM " . TB_PREF . "asset_assignments a 
          WHERE a.`status` = 1 AND a.stock_id =".$stock_id;

  $result = db_query($sql, "No Allocation Found");

  if (db_num_rows($result) == 0) {
    return true;
  } else {
    return false;
  }
}

//--------------------------------------------------------------------------------------------------

//function get_sql_for_fa_journal_inquiry($stock_id)
//{
//
//	$sql = "SELECT	IF(ISNULL(a.gl_seq),0,a.gl_seq) as gl_seq,
//		gl.tran_date,
//		gl.type as trans_type,
//		gl.type_no as trans_no,
//		IFNULL(max(supp.supp_name), max(cust.name)) as name,
//		refs.reference,
//		SUM(IF(gl.amount>0, gl.amount,0)) as amount,
//		com.memo_,
//		IF(ISNULL(u.user_id),'',u.user_id) as user_id";
//
//	$sql.= " FROM ".TB_PREF."gl_trans as gl
//		 LEFT JOIN ".TB_PREF."audit_trail as a ON
//			(gl.type=a.type AND gl.type_no=a.trans_no)
//		 LEFT JOIN ".TB_PREF."comments as com ON
//			(gl.type=com.type AND gl.type_no=com.id)
//		 LEFT JOIN ".TB_PREF."refs as refs ON
//			(gl.type=refs.type AND gl.type_no=refs.id)
//		 LEFT JOIN ".TB_PREF."users as u ON
//			a.user=u.id
//	 	 LEFT JOIN ".TB_PREF."debtor_trans dt ON dt.type=gl.type AND gl.type_no=dt.trans_no
//	 	 LEFT JOIN ".TB_PREF."debtors_master cust ON gl.person_type_id=2 AND gl.person_id=cust.debtor_no
//	 	 LEFT JOIN ".TB_PREF."supp_trans st ON st.type=gl.type AND gl.type_no=st.trans_no
//	 	 LEFT JOIN ".TB_PREF."suppliers supp ON gl.person_type_id=3 AND gl.person_id=supp.supplier_id
//		 WHERE gl.amount!=0
//     AND NOT ISNULL(a.gl_seq)";
//
////	if (!$alsoclosed) {
////		$sql .= " AND gl_seq=0";
////	}
//// 	else
//// 		$sql .= " AND NOT ISNULL(a.gl_seq)";
//
//	$sql .= " GROUP BY tran_date, gl_seq, trans_type, trans_no";
//
//	return $sql;
//}

//--------------------------------------------------------------------------------------------------
