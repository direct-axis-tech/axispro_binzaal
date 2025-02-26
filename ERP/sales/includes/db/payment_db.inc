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
/*
  Write/update customer payment.

  Warning: $rate is leaved here for extensions compatibility reasons, will be removed in 2.4
	since 2.3.17 is not used: use $bank_amount instead.

	$amount - in customer currency (ex. discount)
	$discount - in customer currency
	$bank_amount - in bank currency (before charge)
	$charge - in bank currency
*/
function write_customer_payment(
	$trans_no,
	$customer_id,
	$branch_id,
	$bank_account,
	$date_,
	$ref,
	$amount,
	$discount,
	$memo_,
	$rate=0,
	$charge=0,
	$bank_amount=0,
	$payment_method='',
	$dim1=0,
	$dim2=0,
	$round_off=0,
    $credit_card_no = null,
    $payment_ref = null,
    $created_by = null,
    $processing_fee = 0,
    $transacted_at = null,
    $customer_name = null,
    $customer_trn = null,
    $customer_mobile = null,
    $customer_email = null ,
    $customer_ref  = null,
    $barcode  = null,
    $auth_code = null,
    $commission = 0,
    $trans_type = null
) {
	global $Refs,$SysPrefs;

    if (is_null($trans_type)) {
        $trans_type = ST_CUSTPAYMENT;
    }

	begin_transaction();

    // Factor: Customer Payment 1, Customer Refund -1
    $f = $trans_type == ST_CUSTPAYMENT ? 1 : -1;

	$company_record = get_company_prefs();

    if ($trans_no != 0) {
	  	delete_comments($trans_type, $trans_no);
	  	void_bank_trans($trans_type, $trans_no, true);
	  	void_gl_trans($trans_type, $trans_no, true);
	  	void_cust_allocations($trans_type, $trans_no, $date_);
	}

	$bank = get_bank_account($bank_account);

	if (!$bank_amount) 	// backward compatibility workaround
	{
		if(!$rate)
			$rate = get_exchange_rate_from_to(get_customer_currency($customer_id),
				$bank['bank_curr_code'], $date_ );

		$bank_amount = $amount/$rate;
	}

	// Changed Bank Charge[Credit card process charge] to percentage amount
    if($charge != 0) {
	    $charge = ($bank_amount-$commission)*($charge/100);
    }
    
	// do not use $rate here: global rate stored in exrate table is always used
	$payment_no = write_customer_trans(
        $trans_type,
        $trans_no,
        $customer_id,
        $branch_id, 
		$date_,
        $ref,
        $amount,
        $discount,
        0,
        0,
        0,
        0,
        0,
        0,
        '',
        0,
        0,
        $dim1,
        0,
        null,
        0,
        0,
        0,
        false,
        null,
        $credit_card_no,
        $payment_ref,
        $created_by,
        $processing_fee,
        null,
        null,
        $transacted_at,
        $charge,
        $payment_method,
        $customer_name,
        $customer_trn,
        $customer_mobile,
        $customer_email,
        $customer_ref,
        $barcode,
        null,
        $auth_code,
        null,
        null,
        null,
        0,
        null,
        0,
        0,
        0,
        0,
        0,
        $commission,
        null,
        null,
        [],
        null,
        null,
        1,
        $round_off
    );

	$bank_gl_account = get_bank_gl_account($bank_account);
	$total = 0;

	/* Bank account entry first */
	$total += add_gl_trans(
        $trans_type,
        $payment_no,
        $date_,
		$bank_gl_account,
        $dim1,
        $dim2,
        '',
        $f * ($bank_amount + $charge + $round_off - $commission),
        $bank['bank_curr_code'],
        PT_CUSTOMER,
        $customer_id,
        "",
        0,
        $payment_ref ?? '',
        $created_by
    );

    if ($commission && ($commission_payable_act = pref('axispro.customer_commission_payable_act'))) {
        $total += add_gl_trans_customer(
            $trans_type,
            $payment_no,
            $date_,
            $commission_payable_act,
            $dim1,
            $dim2,
            $f * $commission,
            $customer_id,
            'Cannot insert GL transaction for commission paid',
            '0',
            'Commission Paid'
        );
    }

	if ($branch_id != ANY_NUMERIC) {
		$branch_data = get_branch_accounts($branch_id);

		$debtors_account = $branch_data["receivables_account"];
		$discount_account = $branch_data["payment_discount_account"];
	} else {
		$debtors_account = $company_record["debtors_act"];
		$discount_account = $company_record["default_prompt_payment_act"];
	}

	if (($discount + $amount) != 0)	{
        /* Now Credit Debtors account with receipts + discounts */
        $total += add_gl_trans_customer(
            $trans_type,
            $payment_no,
            $date_,
            $debtors_account,
            $dim1,
            $dim2,
            $f * -($discount + $amount),
            $customer_id,
            "Cannot insert a GL transaction for the debtors account credit",
            0,
            "",
            "",
            $created_by
        );
	}

	if ($discount != 0)	{
		/* Now Debit discount account with discounts allowed */
		$total += add_gl_trans_customer(
            $trans_type,
            $payment_no,
            $date_,
			$discount_account,
            $dim1,
            $dim2,
            $f * $discount,
            $customer_id,
			"Cannot insert a GL transaction for the payment discount debit",
            0,
            "",
            "",
            $created_by
        );
	}

	if ($charge != 0)	{
		/* Now Debit bank charge account with charges */
		$charge_act = get_bank_charge_account($bank_account);

        $total +=add_gl_trans(
            $trans_type,
            $payment_no,
            $date_,
            $charge_act,
            $dim1,
            $dim2,
            '',
            $f * -$charge,
            $bank['bank_curr_code'],
            PT_CUSTOMER,
            $customer_id,
            "",
            0,
            'N/A',
            $created_by
        );
	}

	if ($round_off != 0)	{
		/* if round off greater than zero then profit for company less than zero profit for customer */
		$total +=add_gl_trans(
            $trans_type,
            $payment_no,
            $date_,
            get_company_pref('exchange_diff_act'),
            $dim1,
            $dim2,
            'Round Off Amount',
            $f * -$round_off,
            $bank['bank_curr_code'],
            PT_CUSTOMER,
            $customer_id,
            "",
            0,
            'N/A',
            $created_by
        );

	}

	if ($total != 0) {
        /* Post a balance post if $total != 0 due to variance in AR and bank posted values */
		$variance_act = get_company_pref('exchange_diff_act');
		add_gl_trans(
            $trans_type,
            $payment_no,
            $date_,
            $variance_act,
            $dim1,
            $dim2,
            '',
			$f * -$total,
            null,
            PT_CUSTOMER,
            $customer_id,
            "",
            0,
            'N/A',
            null
        );
	}

    /* Now enter the bank_trans entry */
	add_bank_trans(
        $trans_type,
        $payment_no,
        $bank_account,
        $ref,
		$date_,
        $f * ($bank_amount + $charge + $round_off - $commission),
        PT_CUSTOMER,
        $customer_id,
        "",
        "",
        0,
        0,
        0,
        '',
        $created_by
    );

	add_comments($trans_type, $payment_no, $date_, $memo_);

	$Refs->save($trans_type, $payment_no, $ref);

	commit_transaction();

	return $payment_no;
}

//-------------------------------------------------------------------------------------------------

function void_customer_payment($type, $type_no)
{
	begin_transaction();

	hook_db_prevoid($type, $type_no);
	void_bank_trans($type, $type_no, true);
	void_gl_trans($type, $type_no, true);
	void_cust_allocations($type, $type_no);
	void_customer_trans($type, $type_no);


    void_reward_points($type,$type_no);

	commit_transaction();
}

/*
	Retrieve bank charge amount from GL postings for customer payment.
	. Bank charge is not stored explicitly in database as of 2.3.xx
	. Due to roundings the retrieved charge can differ from the original amount when bank_curr!=home_curr && bank_curr!=cust_curr
*/
function get_cust_bank_charge($type, $trans_no)
{

	// restore charge amount from amounts in bank currency if possible, otherwise calculate from GL posting with exchange rate used for amount posting
	$sql = "SELECT	IF(act.bank_curr_code=home_curr.value, charge.amount,
					IF(act.bank_curr_code=debtor.curr_code, -(trans.amount-ar.ov_amount+ar.ov_discount),
					IFNULL(charge.amount*trans.amount/pmt.amount, 0)))
			FROM ".TB_PREF."bank_trans trans
				LEFT JOIN ".TB_PREF."bank_accounts act ON trans.bank_act=act.id
				LEFT JOIN ".TB_PREF."sys_prefs charge_act ON charge_act.name='bank_charge_act'
				LEFT JOIN ".TB_PREF."sys_prefs home_curr ON home_curr.name='curr_default'
				LEFT JOIN ".TB_PREF."gl_trans charge ON charge.type=trans.type AND charge.type_no=trans.trans_no AND charge.account=charge_act.value
				LEFT JOIN ".TB_PREF."gl_trans pmt ON pmt.type=trans.type AND pmt.type_no=trans.trans_no AND pmt.account=act.account_code
				LEFT JOIN ".TB_PREF."debtors_master debtor ON trans.person_id=debtor.debtor_no AND trans.person_type_id=".PT_CUSTOMER."
				LEFT JOIN ".TB_PREF."debtor_trans ar ON trans.type=ar.type AND trans.trans_no=ar.trans_no
			WHERE pmt.amount!=0 AND charge.amount!=0 AND trans.amount!=0
				AND trans.type=".db_escape($type)." AND trans.trans_no=".db_escape($trans_no);

	$result = db_query($sql, "cannot retrieve bank charge");

	if (!db_num_rows($result))
		return 0;

	$myrow = db_fetch($result);
	return $myrow['0'];
}

