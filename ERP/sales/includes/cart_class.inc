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

use App\Contracts\Cart as CartContract;
use App\Exceptions\BusinessLogicException;
use App\Models\Accounting\Dimension;
use App\Models\Inventory\StockCategory;
use App\Models\Inventory\StockItem;
use App\Models\Labour\Contract;
use App\Models\MetaReference;
use App\Models\Sales\SalesOrderDetail;
use App\Traits\Cart as CartTrait;
use Carbon\Carbon;

/* Definition of the cart class
this class can hold all the information for:

i)   a sales order
ii)  an invoice
iii) a credit note
iv)  a delivery note
*/

include_once($path_to_root . "/inventory/includes/inventory_db.inc");
include_once($path_to_root . "/taxes/tax_calc.inc");
include($path_to_root . '/BarcodeGenerator/BarcodeGenerator.php');
include($path_to_root . '/BarcodeGenerator/BarcodeGeneratorPNG.php');
include($path_to_root . '/BarcodeGenerator/BarcodeGeneratorSVG.php');
include($path_to_root . '/BarcodeGenerator/BarcodeGeneratorJPG.php');
include($path_to_root . '/BarcodeGenerator/BarcodeGeneratorHTML.php');


class Cart implements JsonSerializable, CartContract
{
    use CartTrait;

    var $trans_type; // invoice, order, quotation, delivery note ...
    var $trans_no = array();// array (num1=>ver1,..) or 0 for new
    var $so_type = 0;        // for sales order: simple=0 template=1

    /** @var line_details[] */
    var $line_items;  //array of objects of class line_details

    var $src_docs = array();    // array of arrays(num1=>ver1,...) or 0 for no src
    var $src_date;            // src document date (for info only)

    var $document_date;
    var $due_date;
    var $sales_type; // set to the customer's sales type
    var $sales_type_name; // set to customer's sales type name
    var $tax_included;

    var $customer_currency; // set to the customer's currency
    var $default_discount; // set to the customer's discount %
    var $customer_name;
    var $customer_id;
    var $mistook_staff_id;          // The employee_id of the staff if this is a staff mistake.
    var $Branch;
    var $email;
    var $salesman_id;
    var $roundoff;
    var $customer_type = null;

    var $deliver_to;
    var $delivery_address;
    var $phone;

    var $cust_ref;
    var $reference;
    var $Comments;
    var $Location;
    var $location_name;
    var $order_no;        // the original order number

    var $ship_via;
    var $freight_cost = 0;

    var $tax_group_id;
    var $tax_group_name;
    var $tax_group_array = null; // saves db queries
    var $price_factor;     // ditto for price calculations

    var $govt_fee_pay_method = null;
    var $govt_fee_pay_account = null;

    var $pos;            // user assigned POS
    var $cash_account;
    var $payment_account;
    var $original_payment_account;
    var $account_name;
    var $cash_discount;    // not used as of FA 2.1
    var $dimension_id;
    var $dimension2_id;
    var $invoice_type;
    var $payment;
    var $pay_type;
    var $original_pay_type;
    var $payment_terms = array('cash_sale' => false, 'days_before_due' => 0); // cached payment terms
    var $credit;
    var $credit_days;
    // prepayment mode:
	var $prepaid = false;	// true for documents issued in prepayment mode
	var $prep_amount = 0;	// prepayment required for SO, invoiced amount for prepaiament invoice
    var $sum_paid;        // sum of all allocated prepayments both to order and related invoices
    var $alloc;        // sum of payments allocated to this document
    var $prepayments = array(); // allocation records for this document
    var $ex_rate;

    var $moh_no = null;

    var $fixed_asset = false;

    var $tax_id;
    var $contact_person;
    var $narrations;
    var $barcode;
    var $is_editing;
    var $editing_invoice_no = null;
    var $transacted_at;
    var $created_by;
    var $credit_card_no = null;
    var $payment_ref = null;
    var $token_number = null;
    var $service_req_id = null;
    var $trans_version = null;
    var $processing_fee_rate = null;
    var $processing_fee_tax_rate = null;
    var $extra_charge_for_online_payment = null;
    var $auth_code = null;
    var $is_normal_srq_conversion = false;

    /** @var Contract */
    var $contract = null;
    var $calendar_event_id = null;
    var $installment_detail_id = null;
    var $contract_id = null;
    var $period_from = null;
    var $period_till = null;
    var $credit_inv_no = null;
    var $credit_note_charge = 0;
    var $income_recovered = 0;
    var $days_income_recovered_for = null;

    /** @var Dimension */
    protected $dimension;

    /** @var Cart */
    var $old = null;
    var $is_customer_card_changes_confirmed = false;

    var $deliver_automatically = false;
    var $invoice_automatically = false;
    var $show_discount = 0;
    var $discount_taxed = null;
    
    //-------------------------------------------------------------------------
    //
    //  $trans_no==0 => open new/direct document
    //  $trans_no!=0 && $prepare_child==false => update with parent constarints for reedition
    //  $trans_no!=0 && $prepare_child==true => prepare for child doc entry
    //	$prepare_child is set to ST_SALESINVOICE for prepayment invoices
    //
    function __construct($type, $trans_no = 0, $prepare_child = false, $dimension_id = 0, $contract_id = null)
    {
        /*Constructor function initialises a new shopping cart */
        if (!is_array($trans_no)) $trans_no = array($trans_no); {
            $is_new = !$trans_no[0];
        }

        $this->sessionAccessor = 'Items';
        $this->is_editing = !$is_new && !$prepare_child;
        $this->line_items = array();
        $this->sales_type = "";
        if ($type == ST_SALESQUOTE)
            $this->trans_type = $type;
        else
            $this->trans_type = ST_SALESORDER;
        $this->dimension_id = $dimension_id;;
        $this->dimension2_id = 0;
        $this->pos = get_sales_point(user_pos());
        $this->read($type, $trans_no, $prepare_child);
        $this->cart_id = uniqid('');
        $this->processing_fee_rate = pref('company.processing_fee_rate', 0);
        $this->processing_fee_tax_rate = pref('company.processing_fee_tax_rate', 0);
        $this->extra_charge_for_online_payment = pref('company.extra_charge_for_online_payment', 0);
        $this->barcode = $this->randomNumber();

        if ($is_new && !empty($contract_id)) {
            $this->contract_id = $contract_id;
        }

        if ($contract = Contract::make()->setConnection('fa')->active()->find($this->contract_id)) {
            $this->contract = $contract;
            $this->customer_id = $contract->debtor_no;
        } else {
            $this->contract_id = null;
        }

        if ($is_new && !empty($contract)) {
            $this->contact_person = $contract->customer->contact_person;
            $this->moh_no = $contract->customer->mohre_number;
            $this->customer_name = $contract->customer->name;
            $this->phone = $contract->customer->mobile;
            $this->email = $contract->customer->debtor_email;
            $this->salesman_id = $contract->customer->salesman_id;
            $this->dimension_id = $contract->dimension_id;
            $this->cust_ref = $contract->customer->iban_no;
        }

        // Initialize the fields if the following conditions are met
        // 1. its not being edited
        // 2. its being made from contract
        // 3. its either a free hand credit note against or credit note against an invoice
        if (
            $this->trans_no == 0
            && !empty($contract)
            && ($type == ST_CUSTCREDIT || ($type == ST_SALESINVOICE && $prepare_child)) 
        ) {
            $salesType = get_sales_type($this->shouldBeTaxIncluded($this->dimension_id) ? SALES_TYPE_TAX_INCLUDED : SALES_TYPE_TAX_EXCLUDED);
			$this->set_sales_type($salesType['id'], $salesType['sales_type'], $salesType['tax_included'], $salesType['factor']);

            $this->days_income_recovered_for = $contract->guessDaysToRecoverIncomeFor($this->document_date);
            $this->calculateIncomeRecovered($this->days_income_recovered_for);
        }
    }


    /** Random number for barcode */
    function randomNumber($length = 12)
    {
        $randomNumber = '';

        for ($i = 0; $i < $length; $i++) {
            $randomNumber .= mt_rand(0, 9);
        }

        $sql = "SELECT 1 AS 'count' FROM " . TB_PREF . "debtor_trans WHERE barcode = " . $randomNumber;
        $result = db_query($sql, "can't retrieve child trans");

        if (db_num_rows($result) > 1) {
            return $this->randomNumber(12);
        } else {
            return $randomNumber;
        }
    }


    /*
        Optional sorting items by stock_id.
    */
    function _cmp_lines($a, $b)
    {
        return strcmp($a->stock_id, $b->stock_id);
    }

    /*
        Returns items array optionally sorted by item code.
    */
    function get_items()
    {
        global $SysPrefs;

        $items = $this->line_items;
        if (@$SysPrefs->sort_sales_items)
            uasort($items, array($this, '_cmp_lines'));

        return $items;
    }
    //
    //	Prepare cart to new child document entry, just after initial parent doc read.
    //
    function prepare_child($type)
    {
        global $Refs;

        if ($type === true)
            $type = get_child_type($this->trans_type);

        $this->trans_type = $type;
        $this->reference = $Refs->get_next(
            $this->trans_type,
            null,
            array(
                'date' => $this->document_date,
                'customer' => $this->customer_id,
                'branch' => $this->Branch,
                'dimension' => $this->getDimension()
            )
        );
        if ($type == ST_CUSTCREDIT) {
            $this->src_date = $this->document_date;
            $this->credit_inv_no = key($this->trans_no);
        }

        $this->document_date = new_doc_date();
        $this->created_by = null;
        $this->transacted_at = null;

        for ($line_no = 0; $line_no < count($this->line_items); $line_no++) {
            $line = &$this->line_items[$line_no];
            $line->src_id = $line->id; // save src line ids for update
            $line->qty_dispatched = $type == ST_CUSTCREDIT ? '0' :
                (($this->prepaid && $type == ST_SALESINVOICE) ? $line->quantity : $line->quantity - $line->qty_done);
            $line->qty_old = 0;
        }
        unset($line);

        if ($type == ST_CUSTDELIVERY) {
            $this->order_no = key($this->trans_no);
            $cust = get_customer($this->customer_id);
            // $this->dimension_id = $cust['dimension_id'];
            $this->dimension2_id = $cust['dimension2_id'];
        }
        if ($type == ST_SALESINVOICE) {
            $this->due_date = get_invoice_duedate($this->payment, $this->document_date);
        }

        $this->src_docs = $this->trans_no;
        $this->trans_no = 0;
    }

    //
    //	Prepares transaction for reedition updating with parent transaction data
    //
    function set_parent_constraints($sodata, $src_no)
    {

        $srcdetails = get_sales_parent_lines($this->trans_type, $src_no);
        $src_type = get_parent_type($this->trans_type);

        // calculate & save: qtys on other docs and free qtys on src doc
        $line_no = 0;
        $src_docs = array();
        // Loop speed optimisation below depends on fact
        // that child line_items contains subset of parent lines in _the_same_ order !
        while (($line_no < count($this->line_items)) && ($srcline = db_fetch($srcdetails))) {
            $line = &$this->line_items[$line_no];
            $src_docs[] = $src_type == ST_SALESORDER ? $srcline['order_no'] : $srcline['debtor_trans_no'];
            while ($srcline['id'] != $line->src_id) // Logic : This will increment the line_items array till sales_order line is matched.
            {    // Fixes Delivery note bug : Parent constraints not working if sales order line deleted after delivery
                $line_no++;
                $line = &$this->line_items[$line_no];
            }
            if ($srcline['id'] == $line->src_id) {
                if ($this->trans_type == ST_SALESINVOICE)
                    $line->src_no = $srcline['debtor_trans_no'];
                $line->qty_old = $line->qty_dispatched = $line->quantity;
                $line->quantity += $srcline['quantity'] -
                    ($src_type == ST_SALESORDER ? $srcline['qty_sent'] : $srcline['qty_done']); // add free qty on src doc
                $line_no++;
            }
        }

        if ($src_type == ST_SALESORDER || $src_type == 0) {
            $this->src_docs = array($sodata['order_no'] => $sodata['version']);
        } else {
            // get src_data from debtor_trans
            if (!empty($src_docs))
                $this->src_docs = get_customer_trans_version($src_type, array_unique($src_docs));
        }
    }
    //-------------------------------------------------------------------------
    // Reading document into cart
    //
    function read($type, $trans_no = 0, $prepare_child = false)
    {
        global $SysPrefs, $Refs;

        if (!is_array($trans_no)) $trans_no = array($trans_no);

        if ($trans_no[0]) { // read old transaction


            if ($type == ST_SALESORDER || $type == ST_SALESQUOTE) { // sales order || sales quotation
                read_sales_order($trans_no[0], $this, $type);

            } else {

                // other type of sales transaction

                read_sales_trans($type, $trans_no, $this);


                $this->prepayments = get_payments_for($trans_no[0], $type, $this->customer_id);
                $this->update_payments();
                if ($this->order_no) { // free hand credit notes have no order_no
                    $sodata = get_sales_order_header($this->order_no, ST_SALESORDER);
                    // $this->cust_ref = $sodata["customer_ref"];
                    // currently currency is hard linked to debtor account
                    $this->delivery_to = $sodata["deliver_to"];
                    $this->delivery_address = $sodata["delivery_address"];
                    // child transaction reedition - update with parent info unless it is freehand
                    if (!$this->is_prepaid() && !$prepare_child) // this is read for view/reedition
                        $this->set_parent_constraints($sodata, $trans_no[0]);
                }
            }
            // convert document into child and prepare qtys for entry
            if ($prepare_child)
                $this->prepare_child($prepare_child);

        } else { // new document
            $this->trans_type = $type;
            $this->trans_no = 0;
            $this->customer_currency = get_company_currency();
            // set new sales document defaults here
            if (get_global_customer() != ALL_TEXT)
                $this->customer_id = get_global_customer();
            else
                $this->customer_id = '';
            $this->document_date = new_doc_date();
            if (!is_date_in_fiscalyear($this->document_date))
                $this->document_date = end_fiscalyear();
            
            
            $this->reference = $Refs->get_next(
                $this->trans_type,
                null,
                array(
                    'date' => $this->document_date,
                    'customer' => $this->customer_id,
                    'dimension' => $this->getDimension()
                )
            );


            if ($type != ST_SALESORDER && $type != ST_SALESQUOTE) // Added 2.1 Joe Hunt 2008-11-12
            {
                $dim = get_company_pref('use_dimension');
                if ($dim > 0) {
                    if ($this->customer_id == '') {
                        // $this->dimension_id = 0;
                    } else {
                        $cust = get_customer($this->customer_id);
                        // $this->dimension_id = $cust['dimension_id'];
                    }
                    if ($dim > 1) {
                        if ($this->customer_id == '')
                            $this->dimension2_id = 0;
                        else
                            $this->dimension2_id = $cust['dimension2_id'];
                    }
                }
            }
            if ($type == ST_SALESINVOICE) {
                $this->due_date =
                    get_invoice_duedate($this->payment, $this->document_date);
            } else
                $this->due_date =
                    add_days($this->document_date, $SysPrefs->default_delivery_required_by());
        }

        $currentCreditInfo = $this->customer_id ? get_current_cust_credit($this->customer_id) : [];
        $this->credit = data_get($currentCreditInfo, 'credit_limit', 0);
        $this->credit_days = data_get($currentCreditInfo, 'credit_days');
    }

    //-------------------------------------------------------------------------
    // Writing new/modified sales document to database.
    // Makes parent documents for direct delivery/invoice by recurent call.
    // $policy - 0 or 1:  writeoff/return for IV, back order/cancel for DN
    function write($policy = 0)
    {
        begin_transaction();
        
        if ($this->reference != 'auto') {
            // backup the existing cart class;
            $this->backup = DeepCopy\deep_copy($this, true);
            
            if (
                $this->trans_no == 0
                && (
                    !$this->is_editing
                    || !$this->old
                    || MetaReference::willChangeReferenceDueToDateChange(
                        $this->trans_type,
                        $this->document_date,
                        $this->old->document_date
                    )
                )
                && !$this->isUniqueReference()
            ) {
                cancel_transaction();
                return -1;
            }

            if (!isset($this->discount_taxed)) {
                $this->discount_taxed = $this->isDiscountTaxable();
            }

            foreach ($this->line_items as $ln_item) {
                $discountable_amt = round2($ln_item->discountable_amt(), user_price_dec());
                $ln_item->discount_taxed = $this->discount_taxed;

                if (!$ln_item->line_reference) {
                    $ln_item->line_reference = MetaReference::getNext(
                        SalesOrderDetail::ORDER_LINE_ITEM,
                        null,
                        [
                            'date' => $this->document_date,
                            'dimension' => $this->dimension_id
                        ],
                        true
                    );
                }
                
                if ($discountable_amt == 0 || $discountable_amt < $ln_item->discount_amount) {
                    $ln_item->discount_percent = $ln_item->discount_amount = 0;
                }

                else {
                    $ln_item->discount_percent = $ln_item->discount_amount / $discountable_amt;
                }
            }
        }

        if (empty($this->transacted_at))
            $this->transacted_at = now()->toDateTimeString();

        if (empty($this->created_by))
            $this->created_by = $_SESSION['wa_current_user']->user;

        if (count($this->src_docs) == 0 && ($this->trans_type == ST_SALESINVOICE || $this->trans_type == ST_CUSTDELIVERY) && !$this->is_prepaid()) {
            // this is direct document - first add parent
            $ref = $this->reference;
            $date = $this->document_date;
            $due_date = $this->due_date;
            $dimension_id = $this->dimension_id;
            $dimension2_id = $this->dimension2_id;
            $created_by = $this->created_by;
            $transacted_at = $this->transacted_at;
            $this->trans_type = get_parent_type($this->trans_type);

            $this->reference = 'auto';


            $trans_no = $this->write(1);

            // re-read parent document converting it to child
            $this->read($this->trans_type, $trans_no, true);

            $this->document_date = $date;
            $this->reference = $ref;
            $this->due_date = $due_date;
            $this->dimension_id = $dimension_id;
            $this->dimension2_id = $dimension2_id;
            $this->created_by = $created_by;
            $this->transacted_at = $transacted_at;
        }


        $this->reference = @html_entity_decode($this->reference, ENT_QUOTES);
        $this->Comments = @html_entity_decode($this->Comments, ENT_QUOTES);
        foreach ($this->line_items as $lineno => $line) {
            $this->line_items[$lineno]->stock_id = @html_entity_decode($line->stock_id, ENT_QUOTES);
            $this->line_items[$lineno]->item_description = @html_entity_decode($line->item_description, ENT_QUOTES);

            if (
                $line->transaction_id
                && !(
                    $line->transaction_id_updated_by
                    && $line->transaction_id_updated_at
                )
            ) {
                if (!$line->transaction_id_updated_at) {
                    $line->transaction_id_updated_at = $this->document_date;
                }

                if (!$line->transaction_id_updated_by) {
                    $line->transaction_id_updated_by = $this->created_by;
                }
            }
        }


        switch ($this->trans_type) {
            case ST_SALESINVOICE:
                $ret = write_sales_invoice($this);
                break;
            case ST_CUSTCREDIT:
                $ret = write_credit_note($this, $policy);
                break;
            case ST_CUSTDELIVERY:
                $ret = write_sales_delivery($this, $policy);
                break;
            case ST_SALESORDER:
            case ST_SALESQUOTE: {
                if ($this->trans_no == 0)    // new document
                    $ret = add_sales_order($this);
                else {
                    $ret = update_sales_order($this);
                }

                if (
                    $this->trans_type == ST_SALESORDER
                    && $this->payment == PMT_TERMS_PREPAID
                ) {
                    $_order = $ret;
                    if ($this->deliver_automatically) {
                        // Do auto completion
                        $ret = $this->complete_prepaid_transactions($_order);
                    }

                    if ($this->invoice_automatically) {
                        // Do auto invoice
                        $ret = $this->make_final_invoice($_order);
                    }
                }
            }
        }

        commit_transaction();

        return $ret;
    }

    /**
     * Complete prepaid transaction against this order
     *
     * @param string $order_no
     * @return int
     */
    public function make_final_invoice($order_no)
    {
        begin_transaction();
        $cart = new cart(ST_SALESORDER, $order_no, ST_SALESINVOICE);

        /**
         * Hardcode the invoice to be delayed, so that its always
         * fully invoiced and there is no issue with partial invoices
         * using prepaid factor
         */
    
        $cart->order_no = $order_no;
        $cart->src_docs = array($order_no);
        $cart->trans_no = 0;
        $cart->trans_type = ST_SALESINVOICE;
        $cart->pay_type = 'PayLater';

        $cart->due_date = $cart->document_date = $this->document_date;
        $cart->Comments = $this->Comments ?: "Invoice against order #{$this->reference}";
        $cart->dimension_id = $this->dimension_id;

        $cart->reference = MetaReference::getNext(
            ST_SALESINVOICE,
            null,
            array(
                'date' => $cart->document_date,
                'customer' => $cart->customer_id,
                'branch' => $cart->Branch,
                'dimension' => $cart->dimension_id
            )
        );
    
        $cart->update_payments();
        $cart->prep_amount = $cart->get_cart_total();

        $ret = $cart->write(1);

        commit_transaction();

        return $ret;
    }

    /**
     * Complete prepaid transaction against this order
     *
     * @param string $order_no
     * @return int
     */
    public function complete_prepaid_transactions($order_no)
    {
        begin_transaction();
        
        $cart = new Cart(ST_SALESORDER, $order_no, true);
        $cart->ship_via = DB::table('0_shippers')->value('shipper_id');
        $cart->freight_cost = 0;
        $cart->document_date = $this->document_date;
        $cart->due_date = $cart->document_date;
        $cart->Location = DB::table('0_locations')->value('loc_code');
        $cart->Comments = $this->Comments ?: "Auto completion against order #{$this->reference}";
        $cart->dimension_id = $this->dimension_id;
        $cart->dimension2_id = 0;
        $cart->reference = MetaReference::getNext(
            ST_CUSTDELIVERY,
            null,
            array(
                'date' => $cart->document_date,
                'customer' => $cart->customer_id,
                'branch' => $cart->Branch,
                'dimension' => $cart->dimension_id
            )
        );

        $cart->line_items = array_filter($cart->line_items, function ($line) {
            return (
                $line->costing_method != COSTING_METHOD_EXPENSE
                && $line->quantity - $line->qty_done > 0
            );
        });

        array_walk($cart->line_items, function (&$line) {
            if (
                $line->govt_bank_account == ''
                && (
                    $line->govt_fee != 0
                    || $line->bank_service_charge != 0
                    || $line->bank_service_charge_vat != 0
                    || $line->pf_amount != 0
                )
            ) {
                throw new BusinessLogicException("Err: Line #{$line->line_reference} is an auto completable item. Therefor, the govt bank account cannot be empty.");
            }

            $line->qty_dispatched = $line->quantity - $line->qty_done;
        });

        $ret = $cart->write(1);

        commit_transaction();

        return $ret;
    }

    /**
     * Checks if this transactions reference is unique
     *
     * **This is an internal function only used inside a transaction**
     * 
     * The newly implemented method of storing the reference assumes that the reference is
     * not freely modifiable by user. If so we have to detect and do the checking
     * accordingly. Currently the checking is ignored altogether. 
     * 
     * As for the old method, we are not doing anything. However: it means there is a minute
     * chance that the reference number is duplicated.
     * 
     * In the future we will implement all the sales transactions in the new way so
     * that it can be assured that no two transaction have same reference number.
     * 
     * @return boolean
     */
    private function isUniqueReference() {
        global $Refs;

        if (MetaReference::canGetNextReference($this->trans_type)) {
            // New way of calculating reference - The fourth parameter persists the data
            $nextReference = $Refs->get_next(
                $this->trans_type,
                null,
                [
                    'date' => $this->document_date,
                    'dimension' => $this->getDimension()
                ],
                true
            );

            // If the reference is modified by the user this check will almost always fail
            $isUniqueReference = $this->reference == $nextReference;
        } else {
            $isUniqueReference = is_new_reference($this->reference, $this->trans_type);
        }

        return $isUniqueReference;
    }

    function get_next_kit_ref($kit_prefix)
    {
        // filter all the similar kit numbers
        $similar_kit_items = $kit_prefix
            ? array_filter(
                $this->line_items,
                function ($ln) use ($kit_prefix) {
                    return Str::startsWith($ln->kit_ref, $kit_prefix);
                }
            )
            : $this->line_items;

        // Get the most significant bit from all similar kit nos
        $kit_refs = array_map(
            function ($ln) { return explode('.', $ln->kit_ref)[0]; },
            $similar_kit_items
        );

        // Get the next kit number, 0 if empty
        $next_kit_ref = (max(array_merge([0], $kit_refs)) + 1);

        return $kit_prefix ? "$kit_prefix.$next_kit_ref" : "$next_kit_ref";
    }

    function set_customer($customer_id, $customer_name, $currency, $discount, $payment, $cdiscount = 0 , $salesman_id = null, $show_discount = 0, $customer_type = null)    {
        $this->customer_name = $customer_name;
        $this->customer_id = $customer_id;
        $this->default_discount = $discount;
        $this->customer_currency = $currency;
        $this->payment = $payment;
        $this->payment_terms = get_payment_terms($payment);
        $this->cash_discount = $cdiscount;
        $this->salesman_id = $salesman_id;
        $this->show_discount = $show_discount;
        $this->customer_type = $customer_type;

        if ($this->payment_terms['cash_sale']) {
            $this->Location = $this->pos['pos_location'];
            $this->location_name = $this->pos['location_name'];
        }

        $currentCreditInfo = get_current_cust_credit($customer_id);
        $this->credit = data_get($currentCreditInfo, 'credit_limit', 0);
        $this->credit_days = data_get($currentCreditInfo, 'credit_days');
    }

    function set_branch($branch_id, $tax_group_id, $tax_group_name, $phone = '', $email = '',
        $tax_id = '', $cust_ref = '', $contact_person = '')
    {
        $this->Branch = $branch_id;
        $this->phone = $phone;
        $this->email = $email;
        $this->tax_id = $tax_id;
        $this->cust_ref = $cust_ref;
        $this->contact_person = $contact_person;
        $this->tax_group_id = $tax_group_id;
        $this->tax_group_array = get_tax_group_items_as_array($tax_group_id);
    }

    function set_sales_type($sales_type, $sales_name, $tax_included = 0, $factor = 0)
    {
        $this->sales_type = $sales_type;
        $this->sales_type_name = $sales_name;
        $this->tax_included = $tax_included;
        $this->price_factor = $factor;
    }

    function set_location($id, $name)
    {
        $this->Location = $id;
        $this->location_name = $name;
    }

    function set_delivery($shipper, $destination, $address, $freight_cost = null)
    {
        $this->ship_via = $shipper;
        $this->deliver_to = $destination;
        $this->delivery_address = $address;
        if (isset($freight_cost))
            $this->freight_cost = $freight_cost;
    }

    function add_to_cart(
        $line_no,
        $stock_id,
        $qty,
        $price,
        $disc,
        $qty_done = 0,
        $standard_cost = 0,
        $description = null,
        $id = 0,
        $src_no = 0,
        $src_id = 0,
        $govt_fee = 0,
        $bank_service_charge = 0,
        $bank_service_charge_vat = 0,
        $transaction_id = null,
        $discount_amount = null,
        $other = null,
        $application_id = null,
        $govt_bank_account = null,
        $ref_name = null,
        $ed_transaction_id = null,
        $returnable_amt = 0,
        $returnable_to = null,
        $split_govt_fee_amt = 0,
        $split_govt_fee_acc = null,
        $pf_amount = 0,
        $passport_no = '',
        $extra_srv_chg = '0.00',
        $created_by = null,
        $employee_commission = 0.00,
        $customer_commission = 0.00,
        $cust_comm_emp_share = 0.00,
        $cust_comm_center_share = 0.00,
        $receivable_commission_amount = 0,
        $receivable_commission_account = null,
        $srv_req_line_id = null,
        $transaction_id_updated_at = null,
        $transaction_id_updated_by = null,
        $line_reference = null,
        $qty_expensed = 0,
        $qty_invoiced = 0,
        $assignee_id = null,
        $item_code = null,
        $kit_ref = 1,
        $customer_commission2 = 0.00,
        $is_customer_card_act = 0,
        $fine= 0
    ) {


        $line = new line_details(
            $stock_id,
            $qty,
            $price,
            $disc,
            $qty_done,
            $standard_cost,
            $description,
            $id,
            $src_no,
            $src_id,
            $govt_fee,
            $bank_service_charge,
            $bank_service_charge_vat,
            $transaction_id,
            $discount_amount,
            $other,
            $application_id,
            $govt_bank_account,
            $ref_name,
            $ed_transaction_id,
            $returnable_amt,
            $returnable_to,
            $split_govt_fee_amt,
            $split_govt_fee_acc,
            $pf_amount,
            $passport_no,
            $extra_srv_chg,
            $created_by,
            $employee_commission,
            $customer_commission,
            $cust_comm_emp_share,
            $cust_comm_center_share,
            $receivable_commission_amount,
            $receivable_commission_account,
            $srv_req_line_id,
            $transaction_id_updated_at,
            $transaction_id_updated_by,
            $line_reference,
            $qty_expensed,
            $qty_invoiced,
            $assignee_id,
            $item_code,
            $kit_ref,
            $customer_commission2,
            $is_customer_card_act,
            $fine
        );

        if ($line->valid) {
            $this->line_items[$line_no] = $line;

            return 1;
        } else
            display_error(trans("You have to enter valid stock code or nonempty description"));
        return 0;
    }

    function update_cart_item(
        $line_no,
        $qty,
        $price,
        $disc,
        $description = "",
        $govt_fee = 0,
        $bank_charge = 0,
        $bank_charge_vat = 0,
        $discount_amount = 0,
        $transaction_id = "",
        $other = "",
        $application_id = "",
        $govt_bank_account = "",
        $ref_name = "",
        $ed_transaction_id = "",
        $returnable_amt = 0,
        $returnable_to = null,
        $split_govt_fee_amt = 0,
        $split_govt_fee_acc = null,
        $pf_amount = 0,
        $passport_no = '',
        $extra_srv_chg = '0.00',
        $created_by = null,
        $employee_commission = 0.00,
        $customer_commission = 0.00,
        $cust_comm_emp_share = 0.00,
        $cust_comm_center_share = 0.00,
        $receivable_commission_amount = 0,
        $receivable_commission_account = null,
        $srv_req_line_id = null,
        $transaction_id_updated_at = null,
        $transaction_id_updated_by = null,
        $line_reference = null,
        $qty_expensed = 0,
        $assignee_id = null,
        $customer_commission2 = 0.00,
        $is_customer_card_act = 0,
        $fine = 0
    ) {
        if ($description != "")
            $this->line_items[$line_no]->item_description = $description;
        $this->line_items[$line_no]->quantity = $qty;
        $this->line_items[$line_no]->qty_dispatched = $qty;
        $this->line_items[$line_no]->price = $price;
        $this->line_items[$line_no]->discount_percent = $disc;

        //Modifications for AMER
        $this->line_items[$line_no]->govt_fee = $govt_fee;
        $this->line_items[$line_no]->bank_service_charge = $bank_charge;
        $this->line_items[$line_no]->bank_service_charge_vat = $bank_charge_vat;
        $this->line_items[$line_no]->discount_amount = $discount_amount;
        $this->line_items[$line_no]->returnable_amt = $returnable_amt;
        $this->line_items[$line_no]->returnable_to = $returnable_to;
        $this->line_items[$line_no]->split_govt_fee_amt = $split_govt_fee_amt;
        $this->line_items[$line_no]->split_govt_fee_acc = $split_govt_fee_acc;
        $this->line_items[$line_no]->pf_amount = $pf_amount;
        $this->line_items[$line_no]->passport_no = $passport_no;
        $this->line_items[$line_no]->extra_srv_chg = $extra_srv_chg;
        $this->line_items[$line_no]->created_by = $created_by;
        $this->line_items[$line_no]->employee_commission = $employee_commission;
        $this->line_items[$line_no]->customer_commission = $customer_commission;
        $this->line_items[$line_no]->customer_commission2 = $customer_commission2;
        $this->line_items[$line_no]->cust_comm_emp_share = $cust_comm_emp_share;
        $this->line_items[$line_no]->cust_comm_center_share = $cust_comm_center_share;
        $this->line_items[$line_no]->receivable_commission_amount = $receivable_commission_amount;
        $this->line_items[$line_no]->receivable_commission_account = $receivable_commission_account;
        $this->line_items[$line_no]->transaction_id_updated_at = $transaction_id_updated_at;
        $this->line_items[$line_no]->transaction_id_updated_by = $transaction_id_updated_by;
        $this->line_items[$line_no]->line_reference = $line_reference;
        $this->line_items[$line_no]->qty_expensed = $qty_expensed;
        $this->line_items[$line_no]->assignee_id = $assignee_id;
        $this->line_items[$line_no]->is_customer_card_act = $is_customer_card_act;
        $this->line_items[$line_no]->fine = $fine;

        if ($transaction_id != "")
            $this->line_items[$line_no]->transaction_id = $transaction_id;

        if ($application_id != "")
            $this->line_items[$line_no]->application_id = $application_id;

        if ($ref_name != "")
            $this->line_items[$line_no]->ref_name = $ref_name;

        if ($ed_transaction_id != "")
            $this->line_items[$line_no]->ed_transaction_id = $ed_transaction_id;

        if ($govt_bank_account != "")
            $this->line_items[$line_no]->govt_bank_account = $govt_bank_account;

    }

    function update_add_cart_item_qty($line_no, $qty)
    {
        $this->line_items[$line_no]->quantity += $qty;
    }

    function remove_from_cart($line_no)
    {
        array_splice($this->line_items, $line_no, 1);
    }

    function clear_items()
    {
        unset($this->line_items);
        $this->line_items = array();
        $this->sales_type = "";
        $this->trans_no = 0;
        $this->customer_id = $this->order_no = 0;
    }

    function count_items()
    {
        $counter = 0;
        foreach ($this->line_items as $line) {
            if ($line->quantity != $line->qty_done) $counter++;
        }
        return $counter;
    }

    function count_non_saved_items()
    {
        $counter = 0;
        foreach ($this->line_items as $line) {
            if (!$line->line_reference) $counter++;
        }
        return $counter;
    }

    function get_items_total($qty = 'quantity')
    {
        $dec = user_price_dec();
        $total = 0;
        $total_discount = 0;

        foreach ($this->line_items as $ln_itm) {
            /*
             * we need to calculate the price separately,
             * otherwise the rounding error is reflecting in the
             * customer statement
             */
            $total += round($ln_itm->{$qty} * $ln_itm->unit_total(), $dec);
            $total_discount += ($ln_itm->{$qty} * $ln_itm->discount_amount);
        }
        return round2($total - $total_discount, $dec);
    }

    function get_items_total_dispatch()
    {
        return $this->get_items_total('qty_dispatched');
    }

    function getTotalCreditable() {
        return $this->get_cart_total() - $this->getCustomerCardTotal();
    }

    /**
     * Calculates the total fees that is not the service charge
     *
     * @return float
     */
    function getTotalGovtFees() {
        $total = 0;
        $decimalPoints = user_price_dec();

        foreach ($this->line_items as $item) {
            $total += round2(
                (
                    $item->govt_fee +
                    $item->bank_service_charge +
                    $item->bank_service_charge_vat +
                    $item->pf_amount
                ) * $item->qty_dispatched,
                $decimalPoints
            );
        }

        return $total;
    }

    /**
     * Calculates the total fees that is being split from the total govt fees
     *
     * @return float
     */
    function getTotalSplitGovtFees() {
        $total = 0;
        $decimalPoints = user_price_dec();

        foreach ($this->line_items as $item) {
            $total += round2(
                $item->split_govt_fee_amt * $item->qty_dispatched,
                $decimalPoints
            );
        }

        return $total;
    }

    /**
     * Calculate the total amount required for the given amount to be recieved in bank
     *
     * @param double $amount
     * @param bool $isOnlinePayment
     * @return double
     */
     function _getApproximateAmount($amount, $isOnlinePayment = false) {
        /** 
         * For now we are hardcoding the tax as 5%,
         * If in the future it got changed we may need to change it here
         */
        $factor = 1 - $this->processing_fee_rate - ($this->processing_fee_rate * $this->processing_fee_rate);
        if ($isOnlinePayment) {
            $extraCharge = $this->extra_charge_for_online_payment;
            $extraChargeVat = $extraCharge * $this->processing_fee_tax_rate;

            $amount += ($extraCharge + $extraChargeVat);
        }

        return round2($amount/$factor, 2);
    }

    /**
     * Calculate the processing charges for the given amount
     *
     * @param double $amount
     * @return double
     */ 
     function _getCharges($amount) {
        $charge = round2($amount * $this->processing_fee_rate, 2);
        $chargeVat = round2($charge * $this->processing_fee_tax_rate, 2);

        return $charge + $chargeVat;
    }

    /**
     * Calculates the total processing fee needed to be collected for the given amount
     *
     * @param double $amount
     * @param bool $isOnlinePayment
     * @return double
     */
     function _getProcessingFee($amount, $isOnlinePayment = false) {
        $approxAmount = $this->_getApproximateAmount($amount, $isOnlinePayment);

        $charges = $this->_getCharges($amount);
        if ($isOnlinePayment) {
            $extraCharge = $this->extra_charge_for_online_payment;
            $extraChargeVat = $extraCharge * $this->processing_fee_tax_rate;

            $charges += ($extraCharge + $extraChargeVat);
        }

        return round2($charges, 2);
    }

    /**
     * Calculates the total amount: provided a processing fee
     *
     * @param double $processingFee
     * @param bool $isOnlinePayment
     * @return double
     */
     function _getAmountFromProcessingFee($processing_fee, $isOnlinePayment = false) {
        if ($isOnlinePayment) {
            $extraCharge = $this->extra_charge_for_online_payment;
            $extraChargeVat = $extraCharge * $this->processing_fee_tax_rate;

            $processing_fee -= ($extraCharge + $extraChargeVat);
        }

        $factor = $this->processing_fee_rate * (1 + $this->processing_fee_tax_rate);
        return round2($processing_fee/$factor, 2);
    }

    /**
     * Calculates the total processing fee needed to be collected for the given amount and payment method
     * 
     * @param double $amount
     * @param string $paymentMethod
     * @return double
     */
    function getProcessingFee($amount = null, $paymentMethod = null) {
        if ($paymentMethod === null) {
            $paymentMethod = $this->pay_type;
        }

        if (
                empty(pref('company.collect_processing_chg_frm_cust'))
            || $this->getDimension()->center_type == CENTER_TYPES['RTA']
            || $this->getDimension()->pos_type == POS_CAFETERIA
            || !in_array($paymentMethod, ['PayNoWCC', 'PayOnline', 'PayCashAndCard'])
        ) {
            return 0;
        }
        
        if ($amount === null) {
            $amount = $paymentMethod == 'PayCashAndCard' 
                ? input_num('card_amount')
                : $this->getTotalCreditable();
        }

        $charges = $this->_getProcessingFee(
            $amount,
            $paymentMethod == 'PayOnline'
        );

        return $charges;
    }


    /**
     * Calculates the total invoice amount
     *
     * @return double
     */
    function get_cart_total($exclude_round_off = false) {
        return $this->get_trans_total('qty_dispatched', $exclude_round_off);
    }

    /**
     * Calculates the total customer card amount
     *
     * @return double
     */
    function getCustomerCardTotal() {
        $total = 0;
        foreach ($this->line_items as $item) {
            $total += $item->customer_card_total();
        }

        return round2($total, user_price_dec());
    }

    /**
     * Check if the cart have customer card changes
     *
     * @return boolean
     */
    function has_customer_card_changes()
    {
        $curr_customer_card_tot = $this->getCustomerCardTotal();
        $prev_customer_card_tot = $this->old ? $this->old->getCustomerCardTotal() : 0;

        // Checks if the card had|have any customer card transactions at all
        if ($curr_customer_card_tot == 0 && (!$this->old || $prev_customer_card_tot == 0)) {
            return false;
        }

        // If the cart is not being edited then, we only need to check for existence
        if (!$this->old) {
            return $curr_customer_card_tot != 0;
        }

        if (
            $this->customer_id != $this->old->customer_id
            && ($curr_customer_card_tot != 0 || $prev_customer_card_tot != 0)
        ) {
            return true;
        }

        /*
         * If the cart is being edited then, we need to check for changes line item wise.
         * If there is any difference in total we can be sure there is change. Otherwise,
         * we need to check each item wise to see if there is any change in amount or account
         */
        if ($curr_customer_card_tot != $prev_customer_card_tot) {
            return true;
        }

        $getCustomerCardTransDetails = function (Cart $cart) {
            $arr = [];
            foreach ($cart->line_items as $i => $ln) {
                $customer_card_amt = round2($ln->customer_card_total(), user_price_dec());
                if ($customer_card_amt != 0) {
                    $arr[$i] = "{$ln->govt_bank_account}-{$customer_card_amt}";
                }
            }
            return $arr;
        };
        
        return $getCustomerCardTransDetails($this) != $getCustomerCardTransDetails($this->old);
    }

    function has_items_dispatch()
    {
        foreach ($this->line_items as $ln_itm) {
            if ($ln_itm->qty_dispatched > 0)
                return true;
        }
        return false;
    }

    function any_already_delivered()
    {
        /* Checks if there have been any line item processed */
        foreach ($this->line_items as $stock_item) {
            if ($stock_item->qty_done != 0) {
                return 1;
            }
        }
        return 0;
    }

    function some_already_delivered($line_no)
    {
        /* Checks if there have been deliveries of a specific line item */
        if (isset($this->line_items[$line_no]) &&
            $this->line_items[$line_no]->qty_done != 0) {
            return 1;
        }
        return 0;
    }

    function get_taxes($shipping_cost = null)
    {
        $items = array();
        $prices = array();
        $item_tax_types = [];
        if ($shipping_cost == null)
            $shipping_cost = $this->freight_cost;
        $taxable_items = [];
        foreach ($this->line_items as $ln_itm) {
            $qty = $this->trans_type == ST_SALESORDER ? $ln_itm->quantity : $ln_itm->qty_dispatched;
            $price = $this->getTaxableAmt($ln_itm, null);
            $item_tax_type = get_item_tax_type_for_item($ln_itm->stock_id)['id'];
            
            $items[] = null;
            $taxable_items[] = ($item_tax_type == ITT_REGULAR && $price != 0) ? $qty : 0;
            $prices[] = round2($price * $qty, user_price_dec());
            $item_tax_types[] = $item_tax_type;
        }

        if ($this->income_recovered != 0 || $this->credit_note_charge != 0) {
            $count_taxable = array_sum($taxable_items);
            if ($count_taxable == 0) {
                $items[] = null;
                $prices[] = round2(-$this->income_recovered -$this->credit_note_charge, user_price_dec());
                $item_tax_types[] = ITT_REGULAR;
            }

            // This ensures in most cases that there is minimal rounding error
            else {
                $proportionate_income = $this->income_recovered / $count_taxable;
                $proportionate_credit_note_charge = $this->credit_note_charge / $count_taxable;

                foreach ($taxable_items as $i => $qty) {
                    if (!$qty) {
                        continue;
                    }

                    $prices[$i] -= ($proportionate_income + $proportionate_credit_note_charge) * $qty;
                }
            }
        }

        $tran_date = $this->document_date;
        $tax_effective_from = $this->getDimension()->tax_effective_from
            ? sql2date($this->getDimension()->tax_effective_from)
            : null;

        $taxes = get_tax_for_items(
            $items,
            $prices,
            $shipping_cost,
            $this->tax_group_id,
            $this->tax_included,
            $this->tax_group_array,
            null,
            $item_tax_types,
            $tran_date,
            $tax_effective_from
        );

        // Adjustment for swiss franken, we always have 5 rappen = 1/20 franken
        if ($this->customer_currency == 'CHF') {
            $val = $taxes['1']['Value'];
            $val1 = (floatval((intval(round(($val * 20), 0))) / 20));
            $taxes['1']['Value'] = $val1;
        }
        return $taxes;
    }


    function get_tax_free_shipping()
    {

        if ($this->tax_included == 0)
            return $this->freight_cost;
        else
            return ($this->freight_cost - $this->get_shipping_tax());
    }

    function get_shipping_tax()
    {

        $tax_items = get_shipping_tax_as_array($this->tax_group_id);
        $tax_rate = 0;
        if ($tax_items != null) {
            foreach ($tax_items as $item_tax) {
                $index = $item_tax['tax_type_id'];
                if (isset($this->tax_group_array[$index]['rate'])) {
                    $tax_rate += $item_tax['rate'];
                }
            }
        }
        if ($this->tax_included)
            return round($this->freight_cost * $tax_rate / ($tax_rate + 100), user_price_dec());
        else
            return round($this->freight_cost * $tax_rate / 100, user_price_dec());
    }

    /**
     * Returns transaction value including all taxes
     *
     * @return float
     */
    function get_trans_total($qty = 'quantity', $exclude_round_off = false)
    {
        $dec = user_price_dec();
        
        $total = (
            + $this->get_items_total($qty)
            + $this->freight_cost
            - $this->income_recovered
            - $this->credit_note_charge
        );

        if (!$exclude_round_off) {
            $total += $this->roundoff;
        }

        if (!$this->tax_included) {
            foreach ($this->get_taxes() as $tax) {
                $total += round2($tax['Value'], $dec);
            }
        }

        return round2($total, $dec);
    }

    /*
        Checks cart quantities on document_date.
        Returns array of stock_ids which stock quantities would go negative on some day.
    */
    function check_qoh($date = null, $location = null)
    {
        $low_stock = array();
        // check only for customer delivery and direct sales invoice
        if (!($this->trans_type == ST_CUSTDELIVERY || ($this->trans_type == ST_SALESINVOICE && $this->trans_no == 0)))
            return $low_stock;

        // collect quantities by stock_id
        $qtys = array();
        foreach ($this->line_items as $line_no => $line_item) {
            if (has_stock_holding($line_item->mb_flag)) {
                if (!$this->trans_no) // new delivery
                    $qtys[$line_item->stock_id]['qty'] = $line_item->qty_dispatched + @$qtys[$line_item->stock_id]['qty'];
                else    // DN modification: check change in quantity
                    $qtys[$line_item->stock_id]['qty'] = ($line_item->qty_dispatched - $line_item->qty_old) + @$qtys[$line_item->stock_id]['qty'];
                $qtys[$line_item->stock_id]['line'] = $line_no;
            }
        }

        foreach ($qtys as $stock_id => $sum) {
            if (check_negative_stock($stock_id, -$sum['qty'], $location ? $location : $this->Location, $date ? $date : $this->document_date, null, $this->contract ? $this->contract->labour_id : null))
                $low_stock[] = $stock_id;
        }

        return $low_stock;
    }

    /*
        Returns true for documents issued in prepayment cycle.
    */
    function is_prepaid()
    {
        return $this->prepaid;
    }

    /*
        Order is ready for delivery in prepament mode.
    */
    function is_released()
    {
        return floatcmp($this->sum_paid, $this->prep_amount) >= 0;
    }

    /*
        Check whether order has been already invoiced/send or not.
    */
    function is_started()
    {
        if ($this->trans_no == 0)
            return false;
        $order_no = array_keys($this->trans_no);

        return is_sales_order_started(reset($order_no));
    }

    /*
        Check payment terms and prepayments selected for this invoice,
        and updates	partial/final invoice value respectively.
    */
    function update_payments()
    {
        $remainder = prepaid_invoice_remainder($this->order_no);

        // recalculate prepaid part from payments
        if ($this->payment_terms['days_before_due'] == -1) {    // this is partial invoice for selected prepayments made.
            $paid = 0;
            foreach ($this->prepayments as $payment)
                $paid += $payment['amt'];
            $this->prep_amount = $this->trans_no ? $paid : min($remainder, $paid);
        } else    // this is final invoice
            $this->prep_amount = $remainder;
    }

    static function is_discount_applicable($discount_amount, $total_service_charge, $category_id) {
        return $discount_amount != 0 && $total_service_charge != 0;
    }

    /**
     * Checks whether there is split payment applicable or not
     *
     * @param Dimension $dimension
     * @return boolean
     */
    function isHavingSplitGovtFee($dimension = null) {
        if (is_null($dimension))
            $dimension = $this->getDimension();

        return data_get($dimension, 'is_having_split_govt_fee') && !$this->isFromLabourContract();
    }

    /**
     * Checks whether theis invoice is from labour contract
     *
     * @return boolean
     */
    function isFromLabourContract() {
        return !empty($this->contract_id);
    }

    /**
     * Checks whether the service fee is combined with govt_fee
     * 
     * @param Dimension $dimension
     * @return boolean
     */
    function isServiceFeeCombined($dimension = null) {
        return data_get($dimension, 'is_service_fee_combined');
    }

    /**
     * Checks whether the returnable amount is editable or not
     * 
     * @param Dimension $dimension
     * @return boolean
     */
    function isReturnableAmountEditable($dimension = null) {
        return data_get($dimension, 'is_returnable_amt_editable');
    }

    /**
     * Checks whether the returnable account is editable or not
     * 
     * @param Dimension $dimension
     * @return boolean
     */
    function isReturnableAccountEditable($dimension = null) {
        return data_get($dimension, 'is_returnable_act_editable');
    }

    /**
     * Checks whether the receivable commission amount is editable or not
     * 
     * @param Dimension $dimension
     * @return boolean
     */
    function isReceivableCommissionAmountEditable($dimension = null) {
        return data_get($dimension, 'is_receivable_commission_amt_editable');
    }

    /**
     * Checks whether the receivable commission account is editable or not
     * 
     * @param Dimension $dimension
     * @return boolean
     */
    function isReceivableCommissionAccountEditable($dimension = null) {
        return data_get($dimension, 'is_receivable_commission_act_editable');
    }

    /**
     * Checks whether the other fee a.k.a pf_amount can be directly edited or not
     * 
     * @param Dimension $dimension
     * @return boolean
     */
    function isOtherFeeEditable($dimension = null) {
        return data_get($dimension, 'is_other_fee_editable');
    }

    /**
     * Checks whether the other fee a.k.a pf_amount can be directly edited or not
     * 
     * @param Dimension $dimension
     * @return boolean
     */
    function isAssigneeColEnabled($dimension=null)
    {
        return data_get($dimension, 'enable_assignee_col');
    }

    function isExtraSrvChgApplicable($payment_method)
    {
        return false;
    }

    function isFineColEnabled($dimension=null)
    {
        return data_get($dimension, 'is_fine_col_enabled');
    }

    static function isAutomaticBankChargeApplicable($category_id, $stock_id)
    {
        $applicable = (
            in_array($category_id, explode(',', pref('axispro.auto_bnk_chg_applicable_cats')))
            && !in_array($stock_id, [
                StockItem::TAS_AUTO,
                StockItem::TAS_AUTO_19,
                StockItem::TAS_AUTO_40,
                StockItem::TAS_AUTO_240,
                StockItem::TWJ_AUTO_151_2,
            ])
        );

        return pref('axispro.enable_auto_bank_charge', 0) ? $applicable : false;
    }

    static function calculateAutomaticBankCharge($govt_fee = 0)
    {
        return round2($govt_fee * pref('axispro.auto_bank_charge_rate', 0), user_price_dec());
    }

    /**
     * Calculates the credit note charge automatically
     * 
     * As per ASG Management
     * 1. They are collecting the credit note charge
     *    which is equivalent to the tax that needs to be returned
     *    Eg:
     *     - Contract Amount = 12600 for 12 months
     *       ie. (12000 + 600 (5% tax of 12000))
     *     - So here the credit note charge is 600
     *       ie. (571.43 + 28.57 (5% tax of 571.43))
     *     - When crediting this contract the customer will get 12000
     *       ie. (11428.57 + 571.43 (5% tax of 11428.57))
     * 2. Credit note against a contract is tax inclusive
     */
    public function calculateCreditNoteCharge()
    {
        if (!$this->isFromLabourContract()) {
            return 0;
        }

        $totalCreditableAmount = get_tax_free_price_for_item(
            $this->contract->stock_id,
            $this->get_items_total_dispatch(),
            $this->tax_group_id,
            $this->tax_included,
            $this->tax_group_array
        );

        $incomeRecovered = get_tax_free_price_for_item(
            $this->contract->stock_id,
            $this->income_recovered,
            $this->tax_group_id,
            $this->tax_included,
            $this->tax_group_array
        );

        $taxForCreditingAmount = get_tax_for_item(
            $this->contract->stock_id,
            round2($totalCreditableAmount - $incomeRecovered, user_price_dec()),
            $this->tax_group_id,
            0,
            $this->tax_group_array
        );

        $this->credit_note_charge =  get_tax_free_price_for_item(
            $this->contract->stock_id,
            $taxForCreditingAmount,
            $this->tax_group_id,
            !$this->tax_included,
            $this->tax_group_array
        );

        return $this->credit_note_charge;
    }

    /**
     * Calculate the income recovered for the given number of days
     * 
     * @param int $noOfDays
     * @return float
     */
    public function calculateIncomeRecovered($noOfDays)
    {
        if (!$this->isFromLabourContract()) {
            return 0;
        }

        $this->income_recovered =  get_full_price_for_item(
            $this->contract->stock_id,
            $this->contract->getAmountForDays($noOfDays),
            $this->tax_group_id,
            !$this->tax_included,
            $this->tax_group_array
        );

        return $this->income_recovered;
    }

    function shouldBeTaxIncluded($dimension_id) {
        if ($this->isFromLabourContract()) {
            if (in_array($this->trans_type, [ST_SALESINVOICE, ST_SALESORDER]) && $this->installment_detail_id) {
                return true;
            }

            return $this->contract->sales_type == SALES_TYPE_TAX_INCLUDED;
        }

        return in_array(
            $dimension_id,
            Dimension::where('is_invoice_tax_included', '=', true)->pluck('id')->toArray()
        );
    }

    /**
     * Check if round of should be automatically applied
     *
     * Note:  
     * Automatic Round off is only applicable in the following scenarios  
     * 1. When the invoice is not being edited. Because if it is being edited and  
     * we apply automatic round off. we will loose the actual amount that was there
     * 2. When the invoice is invoice. The cart class can be used to process order,  
     * delivery, invoice and credit note
     * 3. When the payment method is either cash or credit. In other scenarios  
     * it doesn't make sense to apply round off. Because like fe. if being paid by card,  
     * there is card charge say 1%. then we round off after charge the card % is not 1  
     * any more. if we round off before card charge it is not rounded off anymore. etc.  
     * Then again, why round off when you can pay exactly
     * 4. This document is not being converted from a parent document. In that case  
     * we cannot automatically add round off because it would change the total amount  
     * of the parent document. Eg: If an order of 100 AED is being invoiced two times  
     * with 50.70 and 49.30 and we apply automatic round off. it would then make 50.75  
     * and 49.50. Now, the total of the order that is invoiced is 100.25. 0.25 extra of  
     * the actual order
     * 5. This document is not being converted from labour contract. This is the same  
     * case with the order.
     * 
     * @param Dimension $dimension
     * @return boolean
     */
    function isAutomaticRoundoffApplicable(Dimension $dimension = null) {
        if (
            $dimension
            && $dimension->enable_round_off
            && !$this->is_editing
            && $this->trans_type == ST_SALESINVOICE
            && in_array($this->pay_type, ['PayNow', 'PayLater'])
            && empty($this->src_docs)
            && !$this->isFromLabourContract()
        ) {
            return true;
        }

        return false;
    }

    /**
     * Recalculate the round of that needs to be automatically applied
     *
     * @param Dimension|null $dimension
     * @return boolean
     */
    function reCalculateRoundoff($dimension = null) {
        if (is_null($dimension)) {
            $dimension = $this->getDimension();
        }

        if (!$this->isAutomaticRoundoffApplicable($dimension)) {
            return false;
        }

        $roundoffTo = $dimension->round_off_to;
        $roundoffAlgorithm = $dimension->round_off_algorithm;
        $amountTotal = round2($this->get_cart_total(true), user_price_dec());

        if ($roundoffTo > 0) {
            $divisionResult = $amountTotal / $roundoffTo;
            $floor = floor($divisionResult);
            $difference = round($divisionResult - $floor, 2);
            $this->roundoff = 0;
            
            if ($difference) {
                switch ($roundoffAlgorithm) {
                    case ROUNDOFF:
                        $factor = $floor + intval($difference >= 0.5);
                        break;
    
                    case ROUND_DOWN:
                        $factor = $floor;
                        break;
    
                    case ROUND_UP:
                    default :
                        $factor = $floor + 1;
                        break;
                }

                $this->roundoff = ($factor * $roundoffTo) - $amountTotal;
            }
        }
    }

    function shouldBePrepaid(Dimension $dimension) {
        return data_get($dimension, 'dflt_payment_term') == PMT_TERMS_PREPAID;
    }

    /**
     * Returns if the discount is taxable or not
     *
     * @return 0|1
     */
    function isDiscountTaxable()
    {
        /*
         | Check if the discount is being given at least partially
         | off of non taxable amounts. If so discount cannot be taxed
         | because it will result in inconsistency in the tax collected
         | and the service charge shown in the invoice print
         */
        foreach ($this->line_items as $itm) {
            if (
                $itm->discountable_amt() > $itm->discount_amount
                && $itm->discount_amount > $itm->taxable_amt()
            ) {
                return 0;
            }
        }

        /*
         | If the show discount option is set to false for the customer
         | then also the discount cannot be taxed due to the above mentioned
         | inconsistency
         */
        if (!$this->show_discount) {
            return 0;
        }

        /*
         | If the system is configured as such, honor the configuration
         */
        if (!$GLOBALS['SysPrefs']->sales_invoice_discount_apply_on_taxable_amount) {
            return 0;
        }

        return 1;
    }

    /**
     * Returns the dimension instance based on current dimension_id
     *
     * @return Dimension
     */
    function getDimension()
    {
        if (is_null($this->dimension) || $this->dimension->id != $this->dimension_id) {
            $this->dimension = Dimension::find($this->dimension_id) ?: Dimension::make(['id' => $this->dimension_id]);
        }

        return $this->dimension;
    }

    /**
     * Returns the taxable amount from the line item
     *
     * @param line_details $itm
     * @param string|null $qty
     * @return float
     */
    function getTaxableAmt($itm, $qty = 'qty_dispatched')
    {
        $qty = is_null($qty) ? 1 : data_get($itm, $qty, 1);
        $taxable_amt = $itm->taxable_amt() * $qty;

        if ($this->discount_taxed) {
            $taxable_amt -= ($itm->discount_amount * $qty);
        }

        return $taxable_amt;
    }

    /**
     * Checks whether the discount amount is editable or not
     * 
     * @param Dimension $dimension
     * @return boolean
     */
    function isDiscountEditable($dimension=null) {
        return data_get($dimension, 'is_discount_editable');
    }

    public function jsonSerialize()
    {
        $obj = \DeepCopy\deep_copy($this, true);

        $obj->contract = null;
        $obj->backup = null;

        array_walk($obj->line_items, function (&$item) {
            $item = (array)$item;
        });

        return (array)$obj;
    }

    /**
     * Creates a new instance from json array
     *
     * @param array $json
     * @return static
     */
    public static function fromJson($json)
    {
        if (!empty($json['contract_id'])) {
            $json['contract'] = Contract::find($json['contract_id']);
        }

        array_walk($json['line_items'], function (&$line) {
            $line = line_details::fromJson($line);
        });

        $cart = new static($json['trans_type']);
        foreach ($json as $k => $v) {
            $cart->{$k} = $v;
        }

        return $cart;
    }
    
    /**
     * getLineItemTaxPrice
     *
     * @param  mixed $line_item
     * @return int $line_tax
     */
    public function getLineItemTaxPrice($line_item)
    {
        $taxable_amt = $this->getTaxableAmt($line_item);
        $tax_effective_from = sql2date($this->getDimension()->tax_effective_from);
        $line_taxfree_price = get_tax_free_price_for_item(
            $line_item->stock_id,
            $taxable_amt,
            0,
            $this->tax_included,
            $this->tax_group_array,
            null,
            $this->document_date,
            $tax_effective_from,
        );

        $line_tax = get_full_price_for_item(
            $line_item->stock_id,
            $taxable_amt,
            0,
            $this->tax_included,
            $this->tax_group_array,
            null,
            $this->document_date,
            $tax_effective_from,
        ) - $line_taxfree_price;

        return $line_tax;
    }
    
    /**
     * line_total
     *
     * @param  mixed $line_item
     * @return int
     */
    public function line_total($line_item)
    {
        $dec = user_price_dec();
        $lineItemTaxPrice = $this->tax_included ? 0 : $this->getLineItemTaxPrice($line_item);
        return round2((($line_item->unit_total() + $lineItemTaxPrice - $line_item->discount_amount) * $line_item->qty_dispatched), $dec);
    }

    /**
     * Check if the item is already in the cart using their transaction_id or application_id
     *
     * @param string $value The current value of the key being checked
     * @param int $excludedLine If editing: The current line number
     * @param 'application_id'|'transaction_id' $key
     * @return boolean
     */
    public function doc_id_already_exists($value, $key = 'application_id', $excludedLine = -1)
    {
        foreach ($this->line_items as $lineNo => $item) {
            if ($lineNo != $excludedLine && trim($item->{$key}) == trim($value)) {
                return $lineNo + 1;
            }
        }

        return false;
    }
} /* end of class defintion */

class line_details
{
    var $id;
    var $stock_id;
    var $item_code;
    var $kit_ref;
    var $item_description;
    var $units;
    var $mb_flag;
    var $costing_method;
    var $tax_type;
    var $tax_type_name;
    var $src_no;    // number of src doc for this line
    var $src_id;
    var $price;
    var $discount_percent;
    var $discount_taxed = 1;

    var $standard_cost;
    var $descr_editable;

    var $valid; // validation in constructor
    /*
        Line quantity properties in various cart create modes:

        view:
        $quantity - quantity on current document
        $qty_done - quantity processed on all child documents
        $qty_dispatched - not used
        $qty_old - not used

        edit:
        $quantity - free parent quantity including this doc (= max allowed quantity)
        $qty_done - qty processed on child documents (= min allowed qty)
        $qty_dispatched - quantity currently selected to process
        $qty_old - quantity processed on this document before reedition

        new child entry (view parent followed by prepare_child() call):
        $quantity - max allowed quantity (get from parent)
        $qty_done - qty processed on other child documents
        $qty_dispatched - quantity currently selected to process
        $qty_old - 0; not used
    */
    var $quantity;
    var $qty_done;
    var $qty_invoiced;
    var $qty_dispatched;
    var $qty_old = 0;
    var $qty_expensed=0;


    //Additional changes

    var $pf_amount = 0;
    var $govt_fee;
    var $govt_bank_account;
    var $bank_service_charge;
    var $bank_service_charge_vat;
    var $returnable_to;
    var $returnable_amt;
    var $split_govt_fee_acc;
    var $split_govt_fee_amt;
    var $commission_loc_user;
    var $commission_non_loc_user;
    var $transaction_id;
    var $application_id;
    var $passport_no;
    var $ref_name;
    var $ed_transaction_id;
    var $discount_amount;
    var $transaction_id_updated_at;
    var $transaction_id_updated_by;
    var $category_id = null;
    var $other_fee_info_json = null;
    var $extra_srv_chg;
    var $created_by = null;
    var $employee_commission = 0;
    var $customer_commission = 0;
    var $cust_comm_emp_share = 0;
    var $cust_comm_center_share = 0;
    var $receivable_commission_amount;
    var $receivable_commission_account;
    var $srv_req_line_id = null;
    var $line_reference = null;
    var $assignee_id = null;
    var $customer_commission2 = 0;
    var $is_customer_card_act = 0;
    var $fine = 0;

    function __construct(
        $stock_id,
        $qty,
        $prc,
        $disc_percent,
        $qty_done,
        $standard_cost,
        $description,
        $id = 0,
        $src_no = 0,
        $src_id = 0,
        $govt_fee = 0,
        $bank_service_charge = 0,
        $bank_service_charge_vat = 0,
        $transaction_id = null,
        $discount_amount = 0,
        $other = null,
        $application_id = null,
        $govt_bank_account = null,
        $ref_name = null,
        $ed_transaction_id = null,
        $returnable_amt = 0.00,
        $returnable_to = null,
        $split_govt_fee_amt = 0.00,
        $split_govt_fee_acc = null,
        $pf_amount = 0.00,
        $passport_no = '',
        $extra_srv_chg = 0.00,
        $created_by = null,
        $employee_commission = 0.00,
        $customer_commission = 0.00,
        $cust_comm_emp_share = 0.00,
        $cust_comm_center_share = 0.00,
        $receivable_commission_amount = 0.00,
        $receivable_commission_account = null,
        $srv_req_line_id = null,
        $transaction_id_updated_at = null,
        $transaction_id_updated_by = null,
        $line_reference = null,
        $qty_expensed = 0,
        $qty_invoiced = 0,
        $assignee_id = null,
        $item_code = null,
        $kit_ref = 1,
        $customer_commission2 = 0.00,
        $is_customer_card_act = 0,
        $fine = 0
    ) {
        /* Constructor function to add a new LineDetail object with passed params */

        $this->id = $id;
        $this->src_no = $src_no;
        $this->src_id = $src_id;
        $item_row = get_item($stock_id);

        if (!$item_row)
            return;

        $this->category_id = $item_row['category_id'];
        $this->govt_fee = $govt_fee;
        $this->bank_service_charge = $bank_service_charge;
        $this->bank_service_charge_vat = $bank_service_charge_vat;
        $this->transaction_id = $transaction_id;
        $this->application_id = $application_id;
        $this->ref_name = $ref_name;
        $this->ed_transaction_id = $ed_transaction_id;
        $this->discount_amount = $discount_amount ? $discount_amount : 0;
        $this->govt_bank_account = $govt_bank_account;
        $this->commission_loc_user = $item_row['commission_loc_user'];
        $this->commission_non_loc_user = $item_row['commission_non_loc_user'];
        $this->pf_amount = $pf_amount;
        $this->mb_flag = $item_row["mb_flag"];
        $this->costing_method = $item_row["costing_method"];
        $this->units = $item_row["units"];
        $this->descr_editable = $item_row["editable"];
        if ($description == null || !$this->descr_editable)
            $this->item_description = $item_row['long_description'] ?
                $item_row["description"] . " - " . $item_row['long_description'] : $item_row['description'];
        else
            $this->item_description = $description;
        $this->tax_type = $item_row["tax_type_id"];
        $this->tax_type_name = $item_row["tax_type_name"];
        $this->stock_id = $stock_id;
        $this->item_code = $item_code ?: $stock_id;
        $this->kit_ref = $kit_ref;
        $this->quantity = $qty;
        $this->qty_dispatched = $qty;
        $this->price = $prc;
        $this->discount_percent = $disc_percent;
        $this->qty_done = $qty_done;
        $this->qty_invoiced = $qty_invoiced;
        $this->standard_cost = $standard_cost;
        $this->valid = true;
        $this->returnable_amt = $returnable_amt;
        $this->returnable_to = $returnable_to;
        $this->split_govt_fee_acc = $split_govt_fee_acc;
        $this->split_govt_fee_amt = $split_govt_fee_amt;
        $this->passport_no = $passport_no;
        $this->extra_srv_chg = $extra_srv_chg;
        $this->other_fee_info_json = $other;
        $this->created_by = $created_by;
        $this->employee_commission = $employee_commission;
        $this->customer_commission = $customer_commission;
        $this->customer_commission2 = $customer_commission2;
        $this->cust_comm_emp_share = $cust_comm_emp_share;
        $this->cust_comm_center_share = $cust_comm_center_share;
        $this->receivable_commission_amount = $receivable_commission_amount;
        $this->receivable_commission_account = $receivable_commission_account;
        $this->srv_req_line_id = $srv_req_line_id;
        $this->transaction_id_updated_at = $transaction_id_updated_at;
        $this->transaction_id_updated_by = $transaction_id_updated_by;
        $this->line_reference = $line_reference;
        $this->qty_expensed = $qty_expensed;
        $this->assignee_id = $assignee_id;
        $this->is_customer_card_act = $is_customer_card_act;
        $this->fine = $fine;
    }

    // get unit price as stated on document
    function line_price()
    {
        return $this->price;
    }

    function discountable_amt()
    {
        return (
              $this->line_price()
            + $this->returnable_amt
            + $this->extra_srv_chg
            + $this->receivable_commission_amount
            - $this->pf_amount
        );
    }

    function taxable_amt()
    {
        return $this->line_price() + $this->returnable_amt + $this->extra_srv_chg;
    }

    /**
     * The total price for 1 unit without discount
     *
     * @return float
     */
    function unit_total()
    {
        return (
            $this->line_price()
            + $this->govt_fee
            + $this->bank_service_charge
            + $this->bank_service_charge_vat
            + $this->extra_srv_chg
        );
    }

    /**
     * Get the total customer card amount
     *
     * @return float
     */
    function customer_card_total()
    {
        if (!$this->is_customer_card_act) {
            return 0;
        }

        return (
            (
                $this->govt_fee
                + $this->bank_service_charge
                + $this->bank_service_charge_vat
                + $this->pf_amount
                - $this->split_govt_fee_amt
            ) * $this->qty_dispatched
        );
    }

    /**
     * Creates a new instance from json array
     *
     * @param array $json
     * @return static
     */
    public static function fromJson($json)
    {
        $line = new static(null, 0, 0, 0, 0, 0, null);
        foreach ($json as $k => $v) {
            $line->{$k} = $v;
        }

        return $line;
    }
}

