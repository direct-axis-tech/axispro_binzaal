<?php

use App\Exceptions\BusinessLogicException;

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
//----------------------------------------------------------------------------------

// add a bank transaction
// $amount is in $currency
// $date_ is display date (non-sql)

function add_bank_trans(
    $type,
    $trans_no,
    $bank_act,
    $ref,
    $date_,
	$amount,
    $person_type_id,
    $person_id,
    $currency="",
    $err_msg="",
    $rate=0,
    $pay_type=0,
    $cheq_no=0,
    $che_date='',
    $created_by = null,
	$line_reference = null
) {
	$sqlDate = date2sql($date_);

	// convert $amount to the bank's currency
	if ($currency != "")
	{
    	$bank_account_currency = get_bank_account_currency($bank_act);
    	if ($rate == 0)
    		$to_bank_currency = get_exchange_rate_from_to($currency, $bank_account_currency, $date_);
    	else
    		$to_bank_currency = 1 / $rate;
    	$amount_bank = ($amount / $to_bank_currency);
	}
	else
		$amount_bank = $amount;
	$amount_bank = round2($amount_bank, user_price_dec());

    if (!$created_by) {
        $created_by = $_SESSION['wa_current_user']->user;
    }

    if($che_date=='')
    {$che_date='0000-00-00';}
    if($pay_type=='')
    {$pay_type='0';}
    if($cheq_no=='')
    {$cheq_no='0';}

    $cheque_date=str_replace("/","-",$che_date);
    $expl=explode("-",$cheque_date);
    $date_formmetd=$expl[2].'-'.$expl[1].'-'.$expl[0];

	$sql = "INSERT INTO ".TB_PREF."bank_trans (type, trans_no, bank_act, ref,
		trans_date, amount, line_reference, person_type_id, person_id,payment_type,cheq_no,cheq_date,created_by) ";

	$sql .= "VALUES ($type, $trans_no, '$bank_act', ".db_escape($ref).", '$sqlDate',
		".db_escape($amount_bank).", ".db_escape($line_reference, true).", ".db_escape($person_type_id)
		.", ". db_escape($person_id).",'".$pay_type."','".$cheq_no."','".$date_formmetd."','{$created_by}')";

	if ($err_msg == "")
		$err_msg = "The bank transaction could not be inserted";

	db_query($sql, $err_msg);
}

//----------------------------------------------------------------------------------------

function exists_bank_trans($type, $type_no)
{
	$sql = "SELECT trans_no
		FROM ".TB_PREF."bank_trans
		WHERE type=".db_escape($type)
		." AND trans_no=".db_escape($type_no);
	$result = db_query($sql, "Cannot retreive a bank transaction");

    return (db_num_rows($result) > 0);
}

//----------------------------------------------------------------------------------------

function get_bank_trans($type, $trans_no=null, $person_type_id=null, $person_id=null,$voided=false)
{
	if($voided){
	$sql = "SELECT bt.*, act.*,
		IFNULL(abs(dt.ov_amount), IFNULL(ABS(st.ov_amount), bt.amount)) settled_amount,
		IFNULL(abs(dt.ov_amount/bt.amount), IFNULL(ABS(st.ov_amount/bt.amount), 1)) settle_rate,
		IFNULL(debtor.curr_code, IFNULL(supplier.curr_code, act.bank_curr_code)) settle_curr

		FROM ".TB_PREF."voided_bank_trans bt
				 LEFT JOIN ".TB_PREF."voided_debtor_trans dt ON dt.type=bt.type AND dt.trans_no=bt.trans_no
				 LEFT JOIN ".TB_PREF."debtors_master debtor ON debtor.debtor_no = dt.debtor_no
				 LEFT JOIN ".TB_PREF."voided_supp_trans st ON st.type=bt.type AND st.trans_no=bt.trans_no
				 LEFT JOIN ".TB_PREF."suppliers supplier ON supplier.supplier_id = st.supplier_id,
			 ".TB_PREF."bank_accounts act
		WHERE act.id=bt.bank_act ";
	}else{
	$sql = "SELECT bt.*, act.*,
		IFNULL(abs(dt.ov_amount), IFNULL(ABS(st.ov_amount), bt.amount)) settled_amount,
		IFNULL(abs(dt.ov_amount/bt.amount), IFNULL(ABS(st.ov_amount/bt.amount), 1)) settle_rate,
		IFNULL(debtor.curr_code, IFNULL(supplier.curr_code, act.bank_curr_code)) settle_curr

		FROM ".TB_PREF."bank_trans bt
				 LEFT JOIN ".TB_PREF."debtor_trans dt ON dt.type=bt.type AND dt.trans_no=bt.trans_no
				 LEFT JOIN ".TB_PREF."debtors_master debtor ON debtor.debtor_no = dt.debtor_no
				 LEFT JOIN ".TB_PREF."supp_trans st ON st.type=bt.type AND st.trans_no=bt.trans_no
				 LEFT JOIN ".TB_PREF."suppliers supplier ON supplier.supplier_id = st.supplier_id,
			 ".TB_PREF."bank_accounts act
		WHERE act.id=bt.bank_act ";
		
	}
	if (isset($type))
		$sql .= " AND bt.type=".db_escape($type);
	if (isset($trans_no))
		$sql .= " AND bt.trans_no = ".db_escape($trans_no);
	if (isset($person_type_id))
		$sql .= " AND bt.person_type_id = ".db_escape($person_type_id);
	if (isset($person_id))
		$sql .= " AND bt.person_id = ".db_escape($person_id);
	$sql .= " ORDER BY trans_date, bt.id";


	// display_error($sql);

	return db_query($sql, "query for bank transaction");
}

//----------------------------------------------------------------------------------------

function get_bank_trans_for_bank_account($bank_account, $from, $to)
{
	$from = date2sql($from);
	$to = date2sql($to);
	$sql = "SELECT t.* 
		FROM ".TB_PREF."bank_trans t 
			LEFT JOIN ".TB_PREF."voided v ON t.type=v.type AND t.trans_no=v.id
		WHERE t.bank_act = ".db_escape($bank_account) . "
			AND ISNULL(v.date_)
			AND trans_date >= '$from'
			AND trans_date <= '$to'
			AND amount != 0
		ORDER BY trans_date, t.id";

	return db_query($sql,"The transactions for '" . $bank_account . "' could not be retrieved");
}

//----------------------------------------------------------------------------------------

function get_balance_before_for_bank_account($bank_account, $from)
{

    //Modified : Bipin || 8-5-2018

	$from = date2sql($from);
//	$sql = "SELECT SUM(amount)
//		FROM ".TB_PREF."bank_trans
//		WHERE bank_act=".db_escape($bank_account) . "
//			AND trans_date < '$from'";

    $sql = "select SUM(a.amount) from 0_gl_trans a 
left join 0_chart_master b on b.account_code = a.account 
 inner join 0_bank_accounts c on c.account_code=b.account_code 
 where a.tran_date < '$from' and b.account_code in (select account_code from 0_bank_accounts) and c.id=".db_escape($bank_account);

	$before_qty = db_query($sql, "The starting balance on hand could not be calculated");
	$bfw_row = db_fetch_row($before_qty);
	return $bfw_row[0];
}
//----------------------------------------------------------------------------------------

function get_gl_trans_value($account, $type, $trans_no)
{
	$sql = "SELECT SUM(amount)
		FROM ".TB_PREF."gl_trans
		WHERE account=".db_escape($account)
			." AND type=".db_escape($type)
			." AND type_no=".db_escape($trans_no);

	$result = db_query($sql, "query for gl trans value");

	$row = db_fetch_row($result);
	return $row[0];
}

//----------------------------------------------------------------------------------------

function void_bank_trans($type, $type_no, $nested=false)
{

	if (!$nested)
		begin_transaction();

	before_void_bank_trans($type, $type_no);
    
    revoke_cash_handover($type, $type_no);

	$sql = "UPDATE ".TB_PREF."bank_trans 
			SET amount=0
			WHERE type=".db_escape($type)." AND trans_no=".db_escape($type_no);

	db_query($sql, "could not void bank transactions for type=$type and trans_no=$type_no");

	void_gl_trans($type, $type_no, true);

	// in case it's a customer trans - probably better to check first
	void_cust_allocations($type, $type_no);
	void_customer_trans($type, $type_no);

	// in case it's a supplier trans - probably better to check first
	void_supp_allocations($type, $type_no);
	void_supp_trans($type, $type_no);

	void_trans_tax_details($type, $type_no);

	if (!$nested)
		commit_transaction();
}

function revoke_cash_handover($type, $trans_no)
{
    $result = db_query(
        "SELECT id, cash_acc_code FROM 0_cash_handover_requests WHERE `type` = ".db_escape($type)." AND trans_no = ".db_escape($trans_no),
        "Could not query for cash handover request"
    )->fetch_assoc();

    if (empty($cashHandoverId = data_get($result, 'id'))) {
        return;
    }

    $r = db_query(
        "SELECT
            bt.bank_act,
            ba.account_code as bank_account_code,
            ba.bank_account_name,
            bt.trans_date,
            bt.amount
        FROM 0_bank_trans as bt
        LEFT JOIN 0_bank_accounts as ba ON
            ba.id = bt.bank_act
        WHERE
            `type` = ".db_escape($type)."
            AND trans_no = ".db_escape($trans_no)."
            AND ba.account_code != ".db_escape($result['cash_acc_code'])."
        LIMIT 1",
        "Could not query for bank transaction"
    )->fetch_assoc();

    if (
        $r
        && ($problem = check_bank_account_history(-$r['amount'], $r['bank_act'], sql2date($r['trans_date'])))
    ) {
        throw new BusinessLogicException(sprintf(
            "Cannot void this transaction because it would result in a negative balance of %s on %s at %s",
            price_format(abs($problem['amount'])),
            $r['bank_account_name'],
            sql2date($problem['trans_date'])
        ));
    }

    db_query(
        "UPDATE 0_cash_handover_requests
        SET
            `type` = NULL,
            trans_no = NULL,
            source_ref = NULL,
            `status` = 'PENDING',
            handovered_on = NULL,
            approve_rejected_at = NULL,
            approve_rejected_by = NULL
        WHERE id = ".db_escape($cashHandoverId),
        "Could not revoke cash handover request"
    );
}

/**
*	Check account history to find transaction which would exceed authorized overdraft for given account.
*	Returns null or transaction in conflict. Running balance is checked on daily basis only, to enable ID change after edition.
*	$delta_amount - tested change in bank balance at $date.
**/
function check_bank_account_history($delta_amount, $bank_account, $date=null, $user=null, $balance_offset = 0)
{
	if ($delta_amount >= 0 && isset($date))
		 return null;	// amount increase is always safe

	$balance = $date ? get_bank_account_limit($bank_account, $date, $user) : 0;

	if (!isset($balance) && isset($date))
		return null;	// unlimited account

	$balance += $balance_offset;
	if (floatcmp($balance, -$delta_amount) < 0)
		return array('amount' => $balance + $delta_amount, 'trans_date'=> date2sql($date));

	$balance += $delta_amount;

	$sql = "SELECT sum(amount) as amount, trans_date, trans_no, type
			FROM ".TB_PREF."bank_trans
			WHERE
                bank_act=".db_escape($bank_account)."
                AND amount <> 0";
	if ($date)
	{
		$date = date2sql($date);
		$sql .= " AND trans_date > '$date'";
	}
	$sql .= " GROUP BY trans_date ORDER BY trans_date ASC";



	$history = db_query($sql, "cannot retrieve cash account history");

	while ($trans = db_fetch($history)) {
		$balance += $trans['amount'];
		if (round2($balance, user_price_dec()) < 0)
		{
			$trans['amount'] = $balance;
			return $trans;
		}
	}

	return null;
}

/**
*	Check bank transfer, deposit or customer deposit before voiding.
**/
function check_void_bank_trans($type, $type_no)
{
	$moves = get_bank_trans($type, $type_no);
	while ($trans = db_fetch($moves)) {
		if ($trans['amount'] > 0) { // skip transfer input part
			return check_bank_account_history(-$trans['amount'], $trans['bank_act'], sql2date($trans['trans_date'])) == null;
		}
	}
	return true;
}

