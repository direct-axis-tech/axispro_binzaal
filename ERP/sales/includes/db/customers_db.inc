<?php

use App\Models\Sales\Customer;
use Carbon\Carbon;
use Illuminate\Support\Arr;

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

function add_customer(
	$CustName,
	$cust_ref,
	$address,
	$tax_id,
	$curr_code,
	$dimension_id,
	$dimension2_id,
	$credit_status,
	$payment_terms,
	$discount,
	$pymt_discount, 
	$credit_limit,
	$sales_type,
	$notes,
	$mobile,
	$email,
	$cr_notice_level,
	$cr_warning_level,
	$contact_person = null,
	$iban_no = null,
	$category = null,
	$credit_days = null
) {

	if (!user_check_access('SA_UPDATECRLMT')) {
		$credit_limit = $GLOBALS['SysPrefs']->default_credit_limit();
        $credit_days = null;
		$cr_notice_level = $GLOBALS['SysPrefs']->prefs['dflt_cr_lmt_notice_lvl'];
		$cr_warning_level = $GLOBALS['SysPrefs']->prefs['dflt_cr_lmt_warning_lvl'];
	}

    $show_discount = isset($_POST['show_discount']) ? $_POST['show_discount'] : 0;
    $always_use_customer_card = empty($_POST['always_use_customer_card']) ? 0 : 1;
	$sql = (
		"INSERT INTO ".TB_PREF."debtors_master 
			(
				name,
				debtor_ref,
				address,
				tax_id,
				dimension_id,
				dimension2_id,
				curr_code,
				credit_status,
				payment_terms,
				discount, 
				pymt_discount,
				credit_limit,
				credit_days,
				sales_type,
				notes,
				mobile,
				debtor_email,
				show_discount,
				always_use_customer_card,
				contact_person,
				iban_no,
				cr_lmt_notice_lvl,
				cr_lmt_warning_lvl,
				category,
                created_at,
				created_by
			) 
		VALUES 
			(
				" 	   . db_escape($CustName) 
				. ", " . db_escape($cust_ref) 
				. ", " . db_escape($address) 
				. ", " . db_escape($tax_id) 
				. ", " . db_escape($dimension_id) 
				. ", " . db_escape($dimension2_id) 
				. ", " . db_escape($curr_code) 
				. ", " . db_escape($credit_status) 
				. ", " . db_escape($payment_terms) 
				. ", " . $discount 
				. ", " . $pymt_discount 
				. ", " . $credit_limit 
				. ", " . db_escape($credit_days, true)
				. ", " . db_escape($sales_type)
				. ", " . db_escape($notes) 
				. ", " . db_escape($mobile)
				. ", " . db_escape($email)
				. ", " . $show_discount
				. ", " . $always_use_customer_card
				. ", " . db_escape($contact_person) 
				. ", " . db_escape($iban_no)
				. ", " . $cr_notice_level
				. ", " . $cr_warning_level
				. ", " . db_escape($category)
				. ", " . quote(date(DB_DATETIME_FORMAT))
                . ", " . db_escape(authUser()->id)
			.")"
	);
	db_query($sql,"The customer could not be added");
    $customer_id = db_insert_id();

    app('activityLogger')
        ->info(
            "Created customer {customer_id}",
            [
                'customer_id' => $customer_id,
                'credit_status' => $credit_status,
                'credit_limit'  => $credit_limit
            ]
        );

    return $customer_id;
}

function update_customer(
	$customer_id,
	$CustName,
	$cust_ref,
	$address,
	$tax_id,
	$curr_code,
	$dimension_id,
	$dimension2_id,
	$credit_status,
	$payment_terms,
	$discount,
	$pymt_discount,
	$credit_limit,
	$sales_type,
	$notes,
	$activated_till=null,
	$mobile=null,
	$email = '',
	$cr_notice_level = null,
	$cr_warning_level = null,
	$contact_person = null,
	$iban_no = null,
	$show_discount = 0,
	$always_use_customer_card = 0,
	$category = null,
	$credit_days = null
){
	$updates = [
		'name' => db_escape($CustName),
		'address' => db_escape($address), 
		'tax_id' => db_escape($tax_id), 
		'category' => db_escape($category), 
		'curr_code' => db_escape($curr_code), 
		'dimension_id' => db_escape($dimension_id), 
		'dimension2_id' => db_escape($dimension2_id), 
		'credit_status' => db_escape($credit_status), 
		'payment_terms' => db_escape($payment_terms), 
		'discount' => $discount, 
		'pymt_discount' => $pymt_discount,
		'sales_type' => db_escape($sales_type), 
		'notes' => db_escape($notes),
		'activated_till' => db_escape($activated_till),
		'mobile' => db_escape($mobile),
		'show_discount' => $show_discount,
		'always_use_customer_card' => $always_use_customer_card,
		'debtor_email' => db_escape($email), 
		'contact_person' => db_escape($contact_person), 
		'iban_no' => db_escape($iban_no),
        'created_at' => quote('created_at', '`'),
		'modified_at' => quote(date(DB_DATETIME_FORMAT)),
		'modified_by' => db_escape(authUser()->id)
	];

	if (user_check_access('SA_UPDATECRLMT')){
		$updates['credit_limit'] = $credit_limit;
		$updates['credit_days'] = db_escape($credit_days, true);
		$updates['cr_lmt_notice_lvl'] = $cr_notice_level;
		$updates['cr_lmt_warning_lvl'] = $cr_warning_level;
	}

	$_updates = '';
	foreach($updates as $col => $value){
		$_updates .= "{$col} = $value, ";
	}
	$_updates = rtrim($_updates, " ,");

	db_query(
        "UPDATE ".TB_PREF."debtors_master SET {$_updates} WHERE debtor_no = ".db_escape($customer_id),
        "The customer could not be updated"
    );
}

function log_customer_updates($before = [])
{
    $after = db_query(
        "SELECT * FROM 0_debtors_master WHERE debtor_no = ".db_escape($before['debtor_no']),
        "Could not query for customer"
    )->fetch_all(MYSQLI_ASSOC)[0] ?? [];

    $changes = [];
    foreach (array_keys(Arr::except($before, ['modified_at', 'modified_by', 'activated_till'])) as $key) {
        if ($before[$key] != $after[$key]) {
            $changes[$key] = sprintf('%s -> %s', $before[$key], $after[$key]);
        }
    }

    if (count($changes)) {
        app('activityLogger')
            ->info(
                "Updated customer {customer_id}",
                array_merge(
                    [
                        'customer_id' => $before['debtor_no'],
                        'credit_status' => $after['credit_status'],
                        'credit_limit'  => $after['credit_limit']
                    ],
                    $changes
                )
            );
    }
}

function delete_customer($customer_id)
{
	begin_transaction();

	delete_customer_branches($customer_id);

	delete_entity_contacts('customer', $customer_id);

	$sql = "DELETE FROM ".TB_PREF."debtors_master WHERE debtor_no=".db_escape($customer_id);
	db_query($sql,"cannot delete customer");
	commit_transaction();

    app('activityLogger')
        ->info(
            "Deleted customer {customer_id}",
            ['customer_id' => $customer_id]
        );
}


function delete_customer_branches($customer_id) {
    $sql = "DELETE FROM ".TB_PREF."cust_branch WHERE debtor_no=".db_escape($customer_id);;
    db_query($sql,"cannot delete branches");
}

/*
	This function probably should be renamed to get_customer_summary
*/
function get_customer_details($customer_id, $to=null, $all=true)
{

	if ($to == null)
		$todate = date("Y-m-d");
	else
		$todate = date2sql($to);
	$past1 = get_company_pref('past_due_days');
	$past2 = 2 * $past1;
	$past3 = 3 * $past1;
	$past4 = 730;
	// removed - debtor_trans.alloc from all summations
	$sign = "IF(trans.`type` IN(".implode(',',  array(ST_SALESINVOICE,ST_JOURNAL,ST_BANKPAYMENT)).") and trans.ov_amount > 0, 1, -1)";
	if ($all)
    	$value = "IFNULL($sign*abs(trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount),0)";
    else		
    	$value = "IFNULL($sign*(abs(trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount) - trans.alloc),0)";
	$due = "trans.tran_date";
    $sql = "SELECT debtor.name, debtor.curr_code, debtor.credit_limit,
    			credit_status.dissallow_invoices, credit_status.reason_description,
				Sum($value) AS Balance,
				Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) > 0,$value,0)) AS Due,
				Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) > $past1,$value,0)) AS Overdue1,
				Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) > $past2,$value,0)) AS Overdue2,
				Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) > $past3,$value,0)) AS Overdue3,
				Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) > $past4,$value,0)) AS Overdue4
			FROM ".TB_PREF."debtors_master debtor
				LEFT JOIN ".TB_PREF."debtor_trans trans ON trans.tran_date <= '$todate' AND debtor.debtor_no = trans.debtor_no AND trans.type <> ".ST_CUSTDELIVERY."
				JOIN ".TB_PREF."credit_status credit_status ON debtor.credit_status = credit_status.id
			WHERE (trans.`type` <> " . ST_SALESINVOICE ." OR trans.payment_flag <> ".PF_TASHEEL_CC.")";

	if ($customer_id)
		$sql .= " AND debtor.debtor_no = ".db_escape($customer_id);

	if (!$all)
		$sql .= " AND ABS(abs(trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount) - trans.alloc) > ".FLOAT_COMP_DELTA;

	$sql .= " GROUP BY
			  	debtor.debtor_no,
			  	debtor.credit_limit,
			  	credit_status.dissallow_invoices,
			  	credit_status.reason_description";
    $result = db_query($sql,"The customer details could not be retrieved");

    $customer_record = db_fetch($result);

    return $customer_record;

}


function get_customer($customer_id)
{
    $filters = [
        'inactive' => 'both',
        'debtor_no' => $customer_id
    ];

	$result = get_customers($filters);

	return db_fetch($result);
}

/**
 * Get the list of all customers
 *
 * @param array $filters
 * @return mysqli_result
 */
function get_customers($filters = []) {
    $where = "1 = 1";

    if (!isset($filters['inactive'])) {
        $where .= " AND debtor.inactive = 0";
    } else if ($filters['inactive'] != "both") {
        $filters['inactive'] = (int)(bool)$filters['inactive'];
        $where .= " AND debtor.inactive = {$filters['inactive']}";
    }

    if (!empty($filters['except_customers'])) {
        if (!is_array($filters['except_customers'])) {
            $filters['except_customers'] = explode(",", $filters['except_customers']);
        }

        $filters['except_customers'] = array_map(
            function($id) { return db_escape($id); },
            $filters['except_customers']
        );

        $filters['except_customers'] = implode(",", $filters['except_customers']);

        $where .= " AND debtor.debtor_no NOT IN ({$filters['except_customers']})";
    }

    if (!empty($filters['debtor_no'])) {
        if (!is_array($filters['debtor_no'])) {
            $filters['debtor_no'] = explode(",", $filters['debtor_no']);
        }

        $filters['debtor_no'] = array_map(
            function($id) { return db_escape($id); },
            $filters['debtor_no']
        );

        $filters['debtor_no'] = implode(",", $filters['debtor_no']);

        $where .= " AND debtor.debtor_no IN ({$filters['debtor_no']})";
    }

    return db_query(
        "SELECT
            debtor.*,
            concat(debtor.debtor_ref,' - ',debtor.name) as formatted_name
        FROM `0_debtors_master` debtor
        WHERE {$where}
        ORDER BY
            CAST(debtor.debtor_ref AS UNSIGNED) ASC",
        "Could not retrieve the list of customers"
    );
}

/**
 * Gets all the customers and key it by their id
 *
 * @param array $filters
 * @return array
 */
function get_customers_keyed_by_id($filters = []) {
    $result = get_customers($filters);

    $customers = [];
    while ($c = $result->fetch_assoc()) {
        $customers[$c['debtor_no']] = $c;
    }

    return $customers;
}

function get_customer_name($customer_id)
{
	$sql = "SELECT name FROM ".TB_PREF."debtors_master WHERE debtor_no=".db_escape($customer_id);

	$result = db_query($sql, "could not get customer");

	$row = db_fetch_row($result);

	return is_array($row) ? $row[0] : false;
}

function get_customer_habit($customer_id)
{
	$sql = "SELECT debtor.pymt_discount, credit_status.dissallow_invoices
			FROM ".TB_PREF."debtors_master debtor,"
				.TB_PREF."credit_status credit_status
			WHERE debtor.credit_status = credit_status.id
			AND debtor.debtor_no = ".db_escape($customer_id);

	$result = db_query($sql, "could not query customers");

	return db_fetch($result);
}

function get_customer_contacts($customer_id, $action=null)
{
	$results = array();
	$res = get_crm_persons('customer', $action, $customer_id);
	while($contact = db_fetch($res))
	{
		if ($contact['lang'] == 'C') // Fix for improper lang in demo sql files.
			$contact['lang'] = '';
		$results[] = $contact;
	}	
	return $results;
}

function get_current_cust_credit($customer_id)
{
	$cust = Customer::find($customer_id);

	if (!$cust) return [];

    $isCustCreditDefined = !in_array($cust->credit_days, ['', null], true);

	return [
        'credit_limit' => $cust->credit_limit - $cust->balance,
        'credit_days' => !$isCustCreditDefined ? null : (
              $cust->credit_days
            - ($cust->first_unpaid_invoice_date ? Carbon::parse($cust->first_unpaid_invoice_date)->diffInDays(null, false) : 0)
        )
    ];
}

function is_new_customer($id)
{
	$tables = array('cust_branch', 'debtor_trans', 'recurrent_invoices', 'sales_orders');

	return !key_in_foreign_table($id, $tables, 'debtor_no');
}

function get_customer_by_ref($reference)
{
	$sql = "SELECT * FROM ".TB_PREF."debtors_master WHERE debtor_ref=".db_escape($reference);

	$result = db_query($sql, "could not get customer");

	return db_fetch($result);
}

//----------------------------------------------------------------------------------

function get_customer_currency($customer_id=null, $branch_id=null)
{
    $sql = "SELECT curr_code
    	FROM ".TB_PREF."debtors_master cust
    		LEFT JOIN ".TB_PREF."cust_branch branch ON branch.debtor_no=cust.debtor_no
    	WHERE " .(isset($branch_id) ? "branch_code = ".db_escape($branch_id) : "cust.debtor_no = ".db_escape($customer_id));

	$result = db_query($sql, "Retreive currency of customer $customer_id");

	$myrow=db_fetch_row($result);
	return $myrow ? $myrow[0] : get_company_currency();
}

function get_customers_search($customer)
{
	global $SysPrefs;

	if (isset($SysPrefs->max_rows_in_search))
		$limit = $SysPrefs->max_rows_in_search;
	else
		$limit = 10;

    $sql = "SELECT debtor_no, name, debtor_ref, address, tax_id FROM ".TB_PREF."debtors_master 
	  WHERE (  name LIKE " . db_escape("%" . $customer. "%") . " OR 
    		 debtor_ref LIKE " . db_escape("%" . $customer. "%") . " OR 
	        address LIKE " . db_escape("%" . $customer. "%") . " OR 
    	     tax_id LIKE " . db_escape("%" . $customer. "%")." OR
    	     mobile LIKE " . db_escape("%" . $customer. "%").")
	  ORDER BY name LIMIT 0,".(int)($limit);
    
	return db_query($sql, "Failed in retreiving customer list.");
}

function getDuplicateCustomerByMobile($mobile, $id = '') {
    $mobile = db_escape($mobile);
    $debtorNo = empty($id) ? -1 : db_escape($id);

    $customer = db_query(
        "SELECT * FROM `0_debtors_master` WHERE mobile = {$mobile} AND debtor_no <> {$debtorNo}",
        "Could not retrieve duplicate customer by mobile number"
    )->fetch_assoc();

    return $customer;
}

function getDuplicateCustomerByEmail($email, $id = '') {
    $email = db_escape($email);
    $debtorNo = empty($id) ? -1 : db_escape($id);

    $customer = db_query(
        "SELECT * FROM `0_debtors_master` WHERE debtor_email = {$email} AND debtor_no <> {$debtorNo}",
        "Could not retrieve duplicate customer by mobile number"
    )->fetch_assoc();

    return $customer;
}