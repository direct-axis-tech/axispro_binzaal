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

use App\Contracts\Cart as CartContract;
use App\Models\Accounting\Dimension;
use App\Traits\Cart;

/* Definition of the Supplier Transactions class to hold all the information for an accounts payable invoice or credit note
*/

include_once($path_to_root . "/taxes/tax_calc.inc");

class supp_trans implements CartContract
{
	use Cart;

	/**
	 * Array of objects of class grn_item using the id as the pointer
	 * 
	 * @var grn_item[]
	 */
	var $grn_items;

	/**
	 * Array of objects of class gl_codes using a counter as the pointer
	 * @var gl_codes[]
	 */
	var $gl_codes;

	var $supplier_id;
	var $supplier_name;
	var $terms;
	var $ex_rate;
	
	var $tax_description;
	var $tax_group_id;
	var $tax_included;
	
	var $trans_type;	// invoice or credit
	var $trans_no;

	var $Comments;
	var $tran_date;
	var $due_date;
	var $src_docs = array();		// source invoice for this credit note (if any)

	var $supp_reference;
	var $reference;
	var $ov_amount;
	var $ov_discount;
	var $ov_gst;
	var $gl_codes_counter=0;
	var $credit = 0;
	var $currency;
	var $tax_overrides = array();		// array of taxes manually inserted during sales invoice entry

	var $dimension,
		$dimension2;

	function __construct($trans_type, $trans_no=0)
	{
		$this->sessionAccessor = 'supp_trans';
		$this->trans_type = $trans_type;
		$this->cart_id = uniqid();
		/*Constructor function initialises a new Supplier Transaction object */
		$this->read($trans_type, $trans_no);
	}

	function read($trans_type, $trans_no)
	{
		$this->trans_type = $trans_type;
		$this->trans_no = $trans_no;
 		$this->grn_items = array();
 		$this->gl_codes = array();
		if ($trans_no) {
			read_supp_invoice($trans_no, $trans_type, $this);
			if ($trans_type == ST_SUPPCREDIT)
			{
                $this->src_docs = find_src_invoices($this);
			}
			read_supplier_details_to_trans($this, $this->supplier_id);
		}
	}

	function add_grn_to_trans(
		$grn_item_id,
		$po_detail_item,
		$item_code,
		$item_description, 
		$qty_recd,
		$prev_quantity_inv,
		$this_quantity_inv,
		$order_price,
		$chg_price, 
		$std_cost_unit=null,
		$gl_code='',
		$maid_id=null,
		$unit_price=0,
		$govt_fee=0,
		$so_line_reference=null,
		$supp_commission=0
	)
	{
		$this->grn_items[$grn_item_id] = new grn_item(
			$grn_item_id,
			$po_detail_item, 
			$item_code,
			$item_description,
			$qty_recd,
			$prev_quantity_inv,
			$this_quantity_inv, 
			$order_price,
			$chg_price,
			$std_cost_unit,
			$gl_code,
			$this->tax_included,
			$maid_id,
			$unit_price,
			$govt_fee,
			$so_line_reference,
			$supp_commission
		);

		$this->src_docs = find_src_invoices($this);

		unset($this->tax_overrides); // cancel tax overrides after cart change
		return 1;
	}

	function add_gl_codes_to_trans($gl_code, $gl_act_name, $gl_dim, $gl_dim2, $amount, $memo_,$tax_type)
	{
		$this->gl_codes[$this->gl_codes_counter] = new gl_codes($this->gl_codes_counter, 
			$gl_code, $gl_act_name, $gl_dim, $gl_dim2, $amount, $memo_,$tax_type);
		$this->gl_codes_counter++;
		unset($this->tax_overrides); // cancel tax overrides after cart change
		return 1;
	}

	function remove_grn_from_trans($grn_item_id)
	{
		unset($this->tax_overrides); // cancel tax overrides after cart change
	    unset($this->grn_items[$grn_item_id]);
	}
	function remove_gl_codes_from_trans($gl_code_counter)
	{
		unset($this->tax_overrides); // cancel tax overrides after cart change
		unset($this->gl_codes[$gl_code_counter]);
	}

	function is_valid_trans_to_post()
	{
		return (count($this->grn_items) > 0 || count($this->gl_codes) > 0 || 
			($this->ov_amount != 0) || ($this->ov_discount > 0));
	}

	function clear_items()
	{
		unset($this->grn_items);
		unset($this->gl_codes);
		$this->ov_amount = $this->ov_discount = $this->supplier_id = 0;

		$this->grn_items = array();
		$this->gl_codes = array();
	}

    function get_taxes($tax_group_id=null, $shipping_cost=0, $gl_codes=true)
    {
    	$items = array();
    	$prices = array();
		$item_tax_types = array();

        if ($tax_group_id == null)
        	$tax_group_id = $this->tax_group_id;

		// preload the taxgroup
		$tax_group = get_tax_group_items_as_array($tax_group_id);

        foreach ($this->grn_items as $ln_itm) 
        {
        	$items[] = $ln_itm->item_code;
        	$prices[] =round( ($ln_itm->this_quantity_inv * $ln_itm->taxable_amount()),
				user_price_dec());
			$item_tax_types[] = null;
        }

		foreach ($this->gl_codes as $gl_code)
		{
			$items[] = null;
			$prices[] = round($gl_code->amount, user_price_dec());
			$item_tax_types[] = $gl_code->tax_type;
		}

        if ($tax_group_id == null)
        	$tax_group_id = $this->tax_group_id;
        $taxes = get_tax_for_items($items, $prices, $shipping_cost, $tax_group_id, $this->tax_included, null, null, $item_tax_types);

		if (isset($this->tax_overrides))
			foreach($this->tax_overrides as $id => $value) // add values entered manually
			{
				$taxes[$id]['Override'] = $value;
			}

		// Taxes included in gl_codes table have exact value, but count to overrides as well.
		// Therefore when we want to now taxes only for items (gl_codes==false),
		// we have to subtract gl_taxes from override values.
		foreach ($this->gl_codes as $gl_code)
		{
			$index = is_tax_account($gl_code->gl_code);
			if ($index !== false)
			{
				if ($gl_codes)
					$taxes[$index]['Value'] += $gl_code->amount;
				elseif (isset($this->tax_overrides))
					$taxes[$index]['Override'] -= $gl_code->amount;
			}
		}

        return $taxes;
    }
	//
	//	Returns total invoice amount without taxes.
	//
    function get_total_taxfree($tax_group_id=null)
    {
    	$total = 0;

		// preload the taxgroup !
		if ($tax_group_id != null)
			$tax_group = get_tax_group_items_as_array($tax_group_id);
		else
			$tax_group = null;

		foreach ($this->grn_items as $ln_itm)
    		$total += round(($ln_itm->this_quantity_inv * $ln_itm->taxfree_charge_price($tax_group_id, $tax_group)),
			 user_price_dec());

   		foreach ($this->gl_codes as $gl_line)
   		{
   			if (!is_tax_account($gl_line->gl_code))
   				$total += $gl_line->amount;
   		}
   		return $total;
    }
	//
	//	Returns transaction total 
	//
	function get_items_total()
	{
		$total = 0;

		foreach ($this->grn_items as $ln_itm)
			$total += round($ln_itm->this_quantity_inv * $ln_itm->chg_price, user_price_dec());

		foreach ($this->gl_codes as $gl_line)
		{
   			if (!is_tax_account($gl_line->gl_code) || $this->tax_included)
				$total += $gl_line->amount;
		}
		return $total;
	}

	function is_maid_required($dimension_id)
	{
		return Dimension::whereId($this->dimension)->value('center_type') == CENTER_TYPES['DOMESTIC_WORKER'];
	}
} /* end of class defintion */

class grn_item 
{

/* Contains relavent information from the purch_order_details as well to provide in cached form,
all the info to do the necessary entries without looking up ie additional queries of the database again */

	var $id;
	var $po_detail_item;
	var $so_line_reference;
	var $item_code;
	var $item_description;
	var $qty_recd;
	var $prev_quantity_inv;
	var $this_quantity_inv;
	var $order_price;
	var $chg_price;
	var $std_cost_unit;
	var $gl_code;
	var $tax_included;
	var $maid_id;
	var $unit_price;
	var $govt_fee;
	var $supp_commission;

	function __construct(
		$id,
		$po_detail_item,
		$item_code,
		$item_description,
		$qty_recd, 
		$prev_quantity_inv,
		$this_quantity_inv,
		$order_price,
		$chg_price,
		$std_cost_unit,
		$gl_code,
		$tax_included,
		$maid_id=null,
		$unit_price=0,
		$govt_fee=0,
		$so_line_reference=null,
		$supp_commission=0
	)
	{

		$this->id = $id;
		$this->po_detail_item = $po_detail_item;
		$this->so_line_reference = $so_line_reference;
		$this->item_code = $item_code;
		$this->item_description = $item_description;
		$this->qty_recd = $qty_recd;
		$this->prev_quantity_inv = $prev_quantity_inv;
		$this->this_quantity_inv = $this_quantity_inv;
		$this->order_price =$order_price;
		$this->chg_price = $chg_price;
		$this->std_cost_unit = $std_cost_unit;
		$this->gl_code = $gl_code;
		$this->tax_included = $tax_included;
		$this->maid_id = $maid_id;
		$this->unit_price = $unit_price;
		$this->govt_fee = $govt_fee;
		$this->supp_commission = $supp_commission;
	}

	/**
	 * Get the taxable amount from the item
	 *
	 * @return float
	 */
	function taxable_amount()
	{
		return $this->chg_price - $this->govt_fee;
	}

	/**
	 * Get the non-taxable amount from the item
	 *
	 * @return float
	 */
	function non_taxable_amount()
	{
		return $this->govt_fee;
	}

	function full_charge_price($tax_group_id, $tax_group=null, $tax_included=null)
	{
		$full_price = get_full_price_for_item(
			$this->item_code, 
		  	$this->taxable_amount(),
			$tax_group_id,
			$tax_included ?? $this->tax_included,
			$tax_group
		);

		return $full_price + $this->non_taxable_amount();
	}

	function taxfree_charge_price($tax_group_id, $tax_group=null, $tax_included=null)
	{
		$tax_free_price = get_tax_free_price_for_item(
			$this->item_code,
			$this->taxable_amount(), 
		  	$tax_group_id,
			$tax_included ?? $this->tax_included,
			$tax_group
		);

		return $tax_free_price + $this->non_taxable_amount();
	}
}


class gl_codes 
{

	var $Counter;
	var $gl_code;
	var $gl_act_name;
	var $gl_dim;
	var $gl_dim2;
	var $amount;
	var $memo_;
	var $tax_type;

	function __construct($Counter, $gl_code, $gl_act_name, $gl_dim, $gl_dim2, $amount, $memo_,$tax_type)
	{

	/* Constructor function to add a new gl_codes object with passed params */
		$this->Counter = $Counter;
		$this->gl_code = $gl_code;
		$this->gl_act_name = $gl_act_name;
		$this->gl_dim = $gl_dim;
		$this->gl_dim2 = $gl_dim2;
		$this->amount = $amount;
		$this->memo_= $memo_;
		$this->tax_type = $tax_type;
	}
}

