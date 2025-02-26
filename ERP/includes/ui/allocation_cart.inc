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
	Class for supplier/customer payment/credit allocations edition
	and related helpers.
*/
//-----------------------------------------------------------------------------------

class allocation 
{

	var $trans_no; 
	var $type;
	var $person_id = '';
	var $person_name = '';
	var $person_type;	// PT_SUPPLIER/PT_CUSTOMER
	var $person_curr;
	var $date_;
	var $amount = 0; /*Total amount of the transaction in FX */
	var $currency;
	var $barcode = null;
	var $date_from = null;
	var $date_to = null;
	var $dimension_id = null;
	var $contract_id = null;
    var $order_no = null;
    var $editing = [
        "trans_no" => null,
        "person_id" => null,
        "person_name" => null,
        "amount" => null,
        "ref" => null,
        "entered_by" => null,
		"transacted_at" => null
    ];

	/** @var allocation_item[] */
	var $allocs; /*array of transactions allocated to */

    function __construct(
        $type, 
        $trans_no, 
        $person_id = null, 
        $person_type_id = null, 
        $contract_id = null, 
        $order_no = null, 
        $dimension_id = null,
        $date_from = null,
        $date_to = null
    )
	{
		$this->allocs = array();
		
		$this->trans_no = $trans_no;
		$this->type = $type;
		$this->contract_id = $contract_id;
        $this->order_no = $order_no;
        $this->dimension_id = $dimension_id;

		if ($person_id)
			$this->set_person($person_id, $person_type_id);

		$this->read($type, $trans_no, $person_id, $person_type_id, $dimension_id, $date_from, $date_to); // read payment or credit
	}

	function set_person($person_id, $person_type)
	{
		$this->person_id = $person_id;
		$this->person_type = $person_type;
		$this->person_curr = $person_type == PT_SUPPLIER ?
			get_supplier_currency($person_id) : get_customer_currency($person_id);
		return $this->person_curr;
	}

	function add_item($type, $type_no, $date_, $due_date, $amount, $amount_allocated, 
		$current_allocated, $ref)
	{
		if (floatcmp($amount, 0))
		{
			$this->allocs[count($this->allocs)] = new allocation_item($type, $type_no, 
				$date_, $due_date, $amount, $amount_allocated, $current_allocated, $ref);
			return true;
		} 
		else 
		{
			return false;
		}
	}
	
	function update_item($index, $type, $type_no, $date_, $due_date, 
		$amount, $amount_allocated, $current_allocated, $ref)
	{
		if (floatcmp($amount, 0))
		{
			$this->allocs[$index] = new allocation_item($type, $type_no, 
				$date_, $due_date, $amount, $amount_allocated, $current_allocated, $ref);
			return true;
		} 
		else 
		{
			return false;
		}
	}
	
	function add_or_update_item($type, $type_no, $date_, $due_date, 
		$amount, $amount_allocated, $current_allocated, $ref)
	{
		for ($i = 0; $i < count($this->allocs); $i++) 
		{
			$item = $this->allocs[$i];
			if (($item->type == $type) && ($item->type_no == $type_no)) 
			{
				return $this->update_item($i, $type, $type_no, $date_, $due_date, 
					$amount, $amount_allocated, $current_allocated, $ref);
			}
		}
        return $this->add_item($type, $type_no, $date_, $due_date, 
        	$amount, $amount_allocated, $current_allocated, $ref);
	}

	//
	//	Read payment or credit current/available allocations to cart.
	//
	// FIXME - read all transactions below twice seems to be suboptimal
	//
    function read(
        $type = null, 
        $trans_no = 0, 
        $person_id = null, 
        $person_type_id = null, 
        $dimension_id = null,
        $date_from = null,
        $date_to = null
    )
	{
        $this->date_from = $date_from;
        $this->date_to = $date_to;

        if ($type !== null) {	// otherwise re-read
			$type = $this->type;
			$trans_no = $this->trans_no;

			if (isset($person_type_id))
			{
				$this->person_type = $person_type_id;
				$this->person_id = $person_id;
			} else { // guess person_type_id
				if (in_array($type, array(ST_BANKPAYMENT, ST_BANKDEPOSIT)))
				{
					$bank_trans = db_fetch(get_bank_trans($type, $trans_no));
					$this->person_type = $bank_trans['person_type_id'];
				} else
					$this->person_type = in_array($type, array(ST_SUPPCREDIT, ST_SUPPAYMENT)) ? PT_SUPPLIER : PT_CUSTOMER;
			}

			if ($trans_no) {
				$trans = $this->person_type == PT_SUPPLIER ? get_supp_trans($trans_no, $type, $person_id)
					: get_customer_trans($trans_no, $type, $person_id);

				$this->person_id = $trans[$this->person_type == PT_SUPPLIER ? 'supplier_id':'debtor_no'];
                $this->_person_id = $trans[$this->person_type == PT_SUPPLIER ? 'supplier_id':'debtor_no'];
				$this->person_name = $trans[$this->person_type == PT_SUPPLIER ? "supplier_name":"DebtorName"];
				$this->date_ = sql2date($trans["tran_date"]);
				$this->person_curr = $trans['curr_code'];
				$this->currency = isset($trans['bank_curr_code']) ? $trans['bank_curr_code'] : $trans['curr_code'];
				$this->bank_amount = isset($trans["bank_amount"]) ? $trans["bank_amount"] : $trans["Total"]; // not set for journal entry
				$this->amount = $trans["Total"];
				$this->dimension_id = $this->person_type == PT_SUPPLIER ? null : ($dimension_id ?? $trans['dimension_id']);
			}
		}
		/* Now populate the array of possible (and previous actual) allocations 
		for this customer/supplier. First get the transactions that have 
		outstanding balances ie Total-alloc >0 */

		$this->allocs = array();
		if ($this->person_id)
		{
			if ($this->person_type==PT_SUPPLIER) {
                $trans_items = get_allocatable_to_supp_transactions($this->person_id);
            }
			else {
                $trans_items = get_allocatable_to_cust_transactions(
                    $this->person_id,
                    null,
                    null,
                    $this->barcode,
                    $this->date_from,
                    $this->date_to,
                    $this->dimension_id,
					false,
					$this->contract_id,
                    $this->order_no
                );
            }
			while ($myrow = db_fetch($trans_items))
			{

			    if($myrow['payment_flag'] <> 2) {

                    $this->add_item($myrow["type"], $myrow["trans_no"],
                        sql2date($myrow["tran_date"]),
                        sql2date($myrow["due_date"]),
                        $myrow["Total"], // trans total
                        $myrow["alloc"], // trans total allocated
                        0,
                        $myrow["reference"]); // this allocation
                }
			}
		}
		if ($this->trans_no == 0) return; // this is new payment

        /* Now get trans that might have previously been allocated to by this trans
        NB existing entries where still some of the trans outstanding entered from
        above logic will be overwritten with the prev alloc detail below */

		if ($this->person_type==PT_SUPPLIER)
			$trans_items = get_allocatable_to_supp_transactions($this->person_id, 
				$this->trans_no, $this->type);
		else
		 	$trans_items = get_allocatable_to_cust_transactions($this->person_id, 
				$this->trans_no, $this->type);

		$amount = $this->amount;
		while ($myrow = db_fetch($trans_items))
		{
			$amount -= $myrow["amt"];
			$this->add_or_update_item ($myrow["type"], $myrow["trans_no"],
				sql2date($myrow["tran_date"]),
				sql2date($myrow["due_date"]),
				$myrow["Total"],
				$myrow["alloc"] - $myrow["amt"], $myrow["amt"], $myrow["reference"]);
		}

		/* Finally, if there any unallocated money remaining, assign the unallocated portion to
		the earliest transactions. This makes the javascript All/None keys consistent
		with the transaction amount. */

		// if ($amount > 0) {
		// 	foreach ($this->allocs as $alloc_item) {
		// 		$allocatable = $alloc_item->amount - $alloc_item->amount_allocated;
		// 		if ($allocatable > 0) {
		// 			if ($amount >= $allocatable) {
		// 				$alloc_item->current_allocated = $allocatable;
		// 				$amount -= $allocatable;
		// 			} else {
		// 				$alloc_item->current_allocated += $amount;
		// 				break;
		// 			}
		// 		}
		// 	}
		// }
	}
	//
	//	Update allocations in database.
	//
	function write()
	{
		global 	$no_exchange_variations;

		begin_transaction();

		if ($this->person_type == PT_SUPPLIER)
			clear_supp_alloctions($this->type, $this->trans_no, $this->person_id, $this->date_);
		else
		 	clear_cust_alloctions($this->type, $this->trans_no, $this->person_id, $this->date_);

		// now add the new allocations
		$total_allocated = 0;
		$dec = user_price_dec();
		foreach ($this->allocs as $alloc_item)
		{
			if ($alloc_item->current_allocated > 0)
			{
				$amount = round($alloc_item->current_allocated, $dec);

				if ($this->person_type == PT_SUPPLIER) {
					add_supp_allocation($amount,
						$this->type, $this->trans_no,
			    	 	$alloc_item->type, $alloc_item->type_no, $this->person_id, $this->date_, $alloc_item->date_);

					update_supp_trans_allocation($alloc_item->type, $alloc_item->type_no, $this->person_id);
				} else {
	 				add_cust_allocation($amount,
						$this->type, $this->trans_no,
 			     		$alloc_item->type, $alloc_item->type_no, $this->person_id, $this->date_, $alloc_item->date_);

	 				update_debtor_trans_allocation($alloc_item->type, $alloc_item->type_no, $this->person_id, $alloc_item->date_);
				}
				// Exchange Variations Joe Hunt 2008-09-20 ////////////////////
				if ($alloc_item->type != ST_SALESORDER && !@$no_exchange_variations
					&& $alloc_item->type != ST_PURCHORDER && $alloc_item->type != ST_JOURNAL && $this->type != ST_JOURNAL)
					exchange_variation($this->type, $this->trans_no,
						$alloc_item->type, $alloc_item->type_no, $this->date_,
						$amount, $this->person_type);

				//////////////////////////////////////////////////////////////
				$total_allocated += $alloc_item->current_allocated;
			}

		}  /*end of the loop through the array of allocations made */
		if ($this->person_type == PT_SUPPLIER)
			update_supp_trans_allocation($this->type, $this->trans_no, $this->person_id);
		else
		 	update_debtor_trans_allocation($this->type,	$this->trans_no, $this->person_id, $this->date_);

		commit_transaction();

	}

} 

//-----------------------------------------------------------------------------------

class allocation_item 
{

	var $type;
	var $type_no;
	
	var $date_;
	var $due_date;
	
	var $amount_allocated;
	var $amount;
	var $ref;
	
	var $current_allocated;
	
	function __construct($type, $type_no, $date_, $due_date, $amount, 
		$amount_allocated, $current_allocated, $ref)
	{

		$this->type = $type;
		$this->type_no = $type_no;

		$this->ref = $ref;

		$this->date_ = $date_;
		$this->due_date = $due_date;
		
		$this->amount = $amount;
		$this->amount_allocated = $amount_allocated;
		$this->current_allocated = $current_allocated;
	}
}

//--------------------------------------------------------------------------------

function show_allocatable($show_totals) {

	global $systypes_array;

    $k = $total_allocated = 0;

    $cart = $_SESSION['alloc'];
	$supp_ref = in_array($cart->type, array(ST_SUPPCREDIT, ST_SUPPAYMENT, ST_BANKPAYMENT));

	if (count($cart->allocs)) 
	{
		display_heading(sprintf(trans("Allocated amounts in %s:"), $cart->person_curr));

		display_heading("<u><b><a href='javascript:void(0)' id='all_alloc'>".trans("ALLOCATE ALL")."</a></b></u>");
		display_heading("<u><b><a href='javascript:void(0)' id='none_alloc'>".trans("DE-ALLOCATE ALL")."</a></b></u>");

		start_table(TABLESTYLE, "width='60%'");
   		$th = array(trans("Transaction Type"), trans("#"), $supp_ref ? trans("Supplier Ref"): trans("Ref"), trans("Date"), trans("Amount"),
   			trans("Other Allocations"), trans("Left to Allocate"), trans("This Allocation"),'','');

	   	table_header($th);

		foreach ($cart->allocs as $id => $alloc_item)
		{
		    if (
                floatcmp(abs($alloc_item->amount), $alloc_item->amount_allocated)
                || round2($alloc_item->current_allocated, user_price_dec()) != 0
            ) {
				alt_table_row_color($k);
    			label_cell($systypes_array[$alloc_item->type]);
	   			label_cell(get_trans_view_str($alloc_item->type, $alloc_item->type_no), "nowrap align='right'");
		   		label_cell($alloc_item->ref);
    			label_cell($alloc_item->date_, "align=right");
    			//label_cell($alloc_item->due_date, "align=right");
	    		amount_cell(abs($alloc_item->amount));
				amount_cell($alloc_item->amount_allocated);
                $_POST['amount' . $id] = price_format($alloc_item->current_allocated);

	    		$un_allocated = round((abs($alloc_item->amount) - $alloc_item->amount_allocated), 6);
	    		amount_cell($un_allocated, false,'', 'maxval'.$id);
    			amount_cells(null, "amount" . $id);//, input_num('amount' . $id));
				label_cell("<a href='javascript:void(0)' name=Alloc$id onclick='allocate_all(this.name.substr(5));return true;'>"
					 . trans("All") . "</a>");
				label_cell("<a href='javascript:void(0)' name=DeAll$id onclick='allocate_none(this.name.substr(5));return true;'>"
					 . trans("None") . "</a>".hidden("un_allocated" . $id, 
					 price_format($un_allocated), false));
				end_row();

   	    		$total_allocated += input_num('amount' . $id);
		   	}
		}
		if ($show_totals) {
    	   	label_row(trans("Total Allocated"), price_format($total_allocated),
	    		"colspan=8 align=right", "align=right id='total_allocated'", 3);

			$amount = abs($cart->amount);

			if (floatcmp($amount, $total_allocated) < 0)
	        {
        		$font1 = "<font color=red>";
        		$font2 = "</font>";
    	    }
	        else
        		$font1 = $font2 = "";
			$left_to_allocate = price_format($amount - $total_allocated);
	        label_row(trans("Left to Allocate"), $font1 . $left_to_allocate . $font2, 
				"colspan=8 align=right", "nowrap align=right id='left_to_allocate'",
				 3);
		}
		end_table(1);
	}
	hidden('TotalNumberOfAllocs', count($cart->allocs));
}
//--------------------------------------------------------------------------------

function check_allocations()
{
	global $SysPrefs;

	$total_allocated = 0;

	for ($counter = 0; $counter < get_post("TotalNumberOfAllocs"); $counter++)
	{
		if (!isset($_POST['amount'.$counter])) continue;
		if (!check_num('amount' . $counter, 0))
		{
			display_error(trans("The entry for one or more amounts is invalid or negative."));
			set_focus('amount'.$counter);
			return false;
		 }

		  /* Now check to see that the AllocAmt is no greater than the
		 amount left to be allocated against the transaction under review;
		 skip check if no allocation is set to avoid deadlock on mistakenly overallocated transactions*/
		 $allocated = input_num('amount' . $counter);
		 if ($allocated && ($allocated > input_num('un_allocated' . $counter)))
		 {
			display_error(trans("At least one transaction is overallocated."));
			set_focus('amount'.$counter);
			return false;
		 }

		if ($_SESSION['alloc']->person_type == PT_CUSTOMER) {
			if ($_SESSION['alloc']->allocs[$counter]->type == ST_SALESORDER)
				$trans = get_sales_order_header($_SESSION['alloc']->allocs[$counter]->type_no, $_SESSION['alloc']->allocs[$counter]->type);
			else
				$trans = get_customer_trans($_SESSION['alloc']->allocs[$counter]->type_no, $_SESSION['alloc']->allocs[$counter]->type,$_SESSION['alloc']->person_id);

			if ($trans['debtor_no'] != $_SESSION['alloc']->person_id) {
				display_error(trans("Allocated transaction allocated is not related to company selected."));
				set_focus('amount'.$counter);
				return false;
			}
		} elseif ($_SESSION['alloc']->person_type == PT_SUPPLIER) {
			if ($_SESSION['alloc']->allocs[$counter]->type == ST_PURCHORDER)
                $trans = get_po($_SESSION['alloc']->allocs[$counter]->type_no);
            else
                $trans = get_supp_trans($_SESSION['alloc']->allocs[$counter]->type_no, $_SESSION['alloc']->allocs[$counter]->type,  $_SESSION['alloc']->person_id);			
			if ($trans['supplier_id'] != $_SESSION['alloc']->person_id) {
				display_error(trans("Allocated transaction allocated is not related to company selected."));
				set_focus('amount'.$counter);
				return false;
			}
		}

		$_SESSION['alloc']->allocs[$counter]->current_allocated = input_num('amount' . $counter);

		$total_allocated += input_num('amount' . $counter);
	}

	$amount = abs($_SESSION['alloc']->amount);

	if ($total_allocated - ($amount + input_num('discount'))  > $SysPrefs->allocation_settled_allowance())
	{
		display_error(trans("These allocations cannot be processed because the amount allocated is more than the total amount left to allocate."));
		return false;
	}

	return true;
}

//----------------------------------------------------------------------------------------
//
//	Returns sales or purchase invoice allocations to be reallocated after invoice edition.
//
function get_inv_allocations($trans_no, $trans_type, $person_id)
{
	$allocs = array();
	if ($trans_type == ST_SUPPINVOICE || $trans_type == ST_SUPPCREDIT)
		$result = get_allocatable_from_supp_transactions($person_id, $trans_no, $trans_type);
	else
		$result = get_allocatable_from_cust_transactions($person_id, $trans_no, $trans_type);

	while($dat = db_fetch($result))
	{
		$allocs[] = array('type'=> $dat['type'], 'trans_no'=> $dat['trans_no'], 'amount'=>$dat['alloc']);
	}
	return $allocs;
}

/**
 * Allocate customer's transactions automatically from top to bottom
 * 
 * @param int $customer_id optional customer filter
 */
function cust_auto_allocate($customer_id = null) {
	$get_debtor_transactions = function($customer_id = null) {
		$debtor_trans = [];
		$mysqli_results = [];

		$mysqli_results['allocators'] = db_query(get_allocatable_from_cust_sql($customer_id, false, true));
		while($row = $mysqli_results['allocators']->fetch_assoc()){
			$debtor_no = $row['debtor_no'];
			$debtor_trans[$debtor_no]['allocators'][] = $row; 
		}

		$mysqli_results['allocatees'] = get_customer_allocatee($customer_id, true);
		while($row = $mysqli_results['allocatees']->fetch_assoc()){
			$debtor_no = $row['debtor_no'];
			$debtor_trans[$debtor_no]['allocatees'][] = $row;
		}

		$mysqli_results['allocations']	= get_customer_allocatees_without_grouping($customer_id, true);
		while($row = $mysqli_results['allocations']->fetch_assoc()){
			$debtor_no = $row['debtor_no'];
			$type = $row['type'];
			$trans_no = $row['trans_no'];

			$debtor_trans[$debtor_no]['allocations'][$type][$trans_no][] = $row;
		}

		return $debtor_trans;
	};

	$allocate_automatically = function($allocators, $allocatees, $allocations){
		/** Reverse the sort order so we can use them as a stack */
		$allocators = array_reverse($allocators);
		$allocatees = array_reverse($allocatees);

		$count_allocators = count($allocators);
		$count_allocatees = count($allocatees);
		$debtor_no = $allocators[0]['debtor_no'];
		$user_price_dec = user_price_dec();
		$today = Today();

		/** Do not use foreach becasue we will be modifying the source arrays */
		while($count_allocators && $count_allocatees){
			$allocator = array_pop($allocators);
			$count_allocators--;
			
			$type_from = $allocator['type'];
			$trans_no_from = $allocator['trans_no'];
			$date_alloc = sql2date($allocator['tran_date']);
			$amount = abs($allocator['Total']) - $allocator['alloc'];

			begin_transaction();

			while($count_allocatees){
				$allocatee = array_pop($allocatees);
				$count_allocatees--;

				$type_to = $allocatee['type'];
				$trans_no_to = $allocatee['trans_no'];
				$date_alloc_to = $allocatee['tran_date'];
				$allocatable_amt = $allocatee['Total'] - $allocatee['alloc'];
				$this_allocation_amt = 0;
				$existing_allocation_amt = 0;

				if($amount > $allocatable_amt){
					$this_allocation_amt = $allocatable_amt;
					$amount -= $allocatable_amt;
				} else {
					$this_allocation_amt = $amount;
					$amount -= $amount;
				}

				if($allocatee['alloc'] > 0){
					$existing_allocation = array_filter($allocations[$type_to][$trans_no_to], function($alloc) use ($type_from, $trans_no_from){
						return $alloc['trans_no_from'] === $trans_no_from && $alloc['trans_type_from'] === $type_from;
					});

					/**
					 * Just get the first filtered value.
					 * Because we know there can only be one allocation if there is any.
					 */
					$existing_allocation = reset($existing_allocation);
				}

				if(!empty($existing_allocation)){
					$this_allocation_amt += $existing_allocation['amt'];
					$existing_allocation_amt = $existing_allocation['amt'];
					delete_cust_allocation($existing_allocation['alloc_id']);
				}

				$this_allocation_amt = round2($this_allocation_amt, $user_price_dec);
				if($this_allocation_amt > FLOAT_COMP_DELTA) {
					add_cust_allocation(
						$this_allocation_amt,
						$type_from,
						$trans_no_from,
						$type_to,
						$trans_no_to,
						$debtor_no,
						$today,
						sql2date($date_alloc_to)
					);
	
					update_debtor_trans_allocation(
						$type_to,
						$trans_no_to,
						$debtor_no,
						sql2date($date_alloc_to)
					);
				}

				if($amount == 0){
					$total_allocated = $allocatee['alloc'] + $this_allocation_amt - $existing_allocation_amt;
					$balance_allocatable_amt = $allocatee['Total'] - $total_allocated;
					if($balance_allocatable_amt > 0){
						$allocatee['alloc'] = $total_allocated;
						
						array_push($allocatees, $allocatee);
						$count_allocatees++;
					}
					break;
				}
			}

			update_debtor_trans_allocation(
				$type_from,
				$trans_no_from,
				$debtor_no,
				$date_alloc
			);

			commit_transaction();
		}
	};

	begin_transaction();
	$debtor_trans = $get_debtor_transactions($customer_id);
	foreach($debtor_trans as $trans){
		if(!empty($trans['allocators']) && !empty($trans['allocatees'])){
			if(!isset($trans['allocations'])) {
				$trans['allocations'] = [];
			}
			$allocate_automatically(
				$trans['allocators'],
				$trans['allocatees'],
				$trans['allocations']
			);
		}
	}
	commit_transaction();
}