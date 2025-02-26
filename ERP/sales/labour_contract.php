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

use App\Events\Labour\ContractDelivered;
use App\Exceptions\BusinessLogicException;
use App\Http\Controllers\Labour\ContractController;
use App\Models\Inventory\StockCategory;
use App\Models\Inventory\StockItem;
use App\Models\Labour\Contract;
use Illuminate\Support\Facades\Validator;
use App\Models\MetaReference;
use App\Models\MetaTransaction;
use App\Models\Sales\CustomerTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

$path_to_root = "..";
$page_security = 'SA_LBR_CONTRACT';

require_once __DIR__ . "/../includes/session.inc";
require_once __DIR__ . "/../includes/data_checks.inc";
require_once __DIR__ . "/../sales/includes/sales_ui.inc";
require_once __DIR__ . "/../sales/includes/sales_db.inc";
require_once __DIR__ . "/../sales/includes/ui/sales_order_ui.inc";
require_once __DIR__ . "/../purchasing/includes/ui/invoice_ui.inc";
require_once __DIR__ . "/../API/AxisPro.php";

global $Refs, $Ajax;

// Handle the requests
$request = request();
switch ($request->query('action')) {
    case 'createContract':
        if (!user_check_access('SA_LBR_CONTRACT')) {
            http_response_code(403);
            echo json_encode([
                'status' => 403,
                'message' => 'The security settings on your account do not permit you to access this function'
            ]);
            exit();
        }

        create_contract($request);
        exit();
    
    case 'deliverMaid':
        if (!user_check_access('SA_LBR_CONTRACT')) {
            return AxisPro::ValidationError('The security settings on your account do not permit you to access this function', 403);
        }

        deliver_maid_against_contract($request);
        exit();
    
    case 'getSupplierInvoiceInfo':
        handle_get_supplier_invoice_info_request($request);
        exit();

    default:
        http_response_code(400);
        echo json_encode([
            'status' => 400,
            'message' => 'Bad Request'
        ]);
        exit();
}

/**
 * Handles the create contract request
 *
 * @param Request $request
 * @return void
 */
function create_contract(Request $request)
{
    $data = validate_create_contract_request($request);
    $transDate = $data['order_date'];
    begin_transaction();
    $data['contract_no'] = MetaTransaction::getNextTransNo($data['type']);
    $data['reference'] = MetaReference::getNext($data['type'], null, $data['contract_from'], true);
    $data['inactive'] = !empty($data['inactive']);
    $data['contract_from'] = $data['maid_expected_by'] = date2sql($data['contract_from']);
    $data['contract_till'] = date2sql($data['contract_till']);
    $data['order_date'] = date2sql($data['order_date']);
    $data['created_by'] = authUser()->id;
    $data['created_at'] = $data['updated_at'] = date(DB_DATETIME_FORMAT);

    // Save the contract
    $contract = Contract::make($data)->setConnection('fa');
    $contract->save();

    // Add book-keeping fields
    if (!empty($data['memo'])) {
        add_comments($data['type'], $data['contract_no'], $transDate, $data['memo']);
    }
	MetaReference::saveReference($data['type'], $data['contract_no'], $data['reference']);
	add_audit_trail($data['type'], $data['contract_no'], $transDate);

    // Automatically add sales order
    make_sales_order_against_contract($contract);
    commit_transaction();

    // Send Response
    http_response_code(201);
    echo json_encode([
        'status' => 201,
        'message' => 'Contract Created Successfully',
        'data' => $contract,
        'contract_id' => $contract->id
    ]);
    exit();
}

/**
 * Makes a sales order against the given contract
 *
 * @param Contract $contract
 * @return void
 */
function make_sales_order_against_contract(Contract $contract)
{
    $cart = new Cart(ST_SALESORDER, 0, false, 0, $contract->id);
    $cart->document_date = sql2date($contract->order_date);

    // Set the customer details
    $branch = get_default_branch($cart->customer_id);
    $cart->Branch = data_get($branch, 'branch_code');
    $customer_error = get_customer_details_to_order(
        $cart,
        $cart->customer_id,
        $cart->Branch,
        $cart->customer_name,
        $cart->phone,
        $cart->email,
        null,
        $cart->cust_ref,
        $cart->contact_person
    );

    if ($customer_error) {
        http_response_code(422);
        echo json_encode([
            'status' => 422,
            'message' => $customer_error
        ]);
        exit();
    }

    // Override the payment terms to prepaid
    $cart->payment = PMT_TERMS_PREPAID;
    $cart->payment_terms = get_payment_terms($cart->payment);
    $cart->prep_amount = $contract->prep_amount;

    // Set the locations and stuff
    $cart->payment_terms['cash_sale']
        ? $cart->set_location($cart->pos["pos_location"], $cart->pos["location_name"])
        : $cart->set_location($branch["default_location"], $branch["location_name"]);

    // Set the reference
    $cart->reference = MetaReference::getNext(
        ST_SALESORDER,
        null,
        array(
            'date' => $cart->document_date,
            'customer' => $cart->customer_id,
            'branch' => $cart->Branch,
            'dimension' => $cart->dimension_id
        )
    );

    // Add item to the cart
    $cart->add_to_cart(
        0,
        $contract->stock_id,
        1,
        $contract->amount,
        0,
        0,
        0,
        null,
        0,
        0,
        0,
        0,
        0,
        0,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        0,
        null,
        0,
        null,
        0,
        '',
        '0.00',
        user_id(),
        0.00,
        0.00,
        0.00,
        0.00,
        0,
        null,
        null
    );

    // Store the sales order
    _process_cart($cart);
}

/**
 * Validates the create contract request
 *
 * @param Request $request
 * @return array
 */
function validate_create_contract_request(Request $request)
{
    $validator = Validator::make(
        $request->except(['_method', '_token']),
        [
            'reference'         => 'required|string',
            'debtor_no'         => 'bail|required|integer|exists:0_debtors_master,debtor_no',
            'type'              => 'required|in:' . implode(',', array_keys(labour_contract_types())),
            'labour_id'         => 'bail|required|integer|exists:0_labours,id',
            'sales_type'        => 'bail|required|integer|exists:0_sales_types,id',
            'dimension_id'      => 'bail|required|integer|exists:0_dimensions,id',
            'category_id'       => 'required|in:' . implode(',', app(ContractController::class)->getAvailableCategories()),
            'stock_id'          => 'bail|required|string|exists:0_stock_master,stock_id',
            'order_date'        => 'required|date_format:' . getNativeDateFormat(),
            'contract_from'     => 'required|date_format:' . getNativeDateFormat(),
            'contract_till'     => 'required|date_format:' . getNativeDateFormat() . '|after_or_equal:contract_from',
            'amount'            => 'required|numeric',
            'prep_amount'       => 'required|numeric|min:0',
            'memo'              => 'nullable|string',
            'inactive'          => 'nullable|boolean',
            'is_delivered'      => 'nullable|boolean',
        ],
        [
            'mobile.regex' => 'The mobile number is not valid'
        ]
    );

    $validator->after(function ($validator) use ($request) {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $contractExists = Contract::active()
            ->whereLabourId($request->labour_id)
            ->whereRaw(
                '('
                    .     '(? between `contract_from` and `contract_till`)'
                    . ' or (? between `contract_from` and `contract_till`)'
                    . ' or (`contract_from` >= ? and `contract_till` <= ?)'
                .')',
                [
                    date2Sql($request->contract_from),
                    date2Sql($request->contract_till),
                    date2Sql($request->contract_from),
                    date2Sql($request->contract_till),
                ]
            )
            ->exists();

        if ($contractExists) {
            $validator->errors()->add('contract_from', 'Contract exists for maid in this date range.');
        }
    });

    if ($validator->fails()) {
        http_response_code(422);
        echo json_encode([
            'status' => 422,
            'message' => 'Request contains invalid data',
            'errors' => $validator->errors()
        ]);
        exit();
    }

    return $validator->validated();
}

/**
 * Deliver a maid against the contract
 *
 * @param Request $request
 * @return void
 */
function deliver_maid_against_contract(Request $request)
{
    config()->set('database.default', 'fa');
    if (empty($contract = Contract::active()->find($request->query('contract')))) {
        return AxisPro::ValidationError('Could not find the contract!', 404);
    }

    // Validate the request
    $validator = Validator::make(
        $request->except(['_method', '_token']),
        [
            'delivery_date' => 'required|date_format:' . getNativeDateFormat()
        ]
    );

    if ($validator->fails()) {
        http_response_code(422);
        echo json_encode([
            'status' => 422,
            'message' => 'Request contains invalid data',
            'errors' => $validator->errors()
        ]);
        exit();
    }

    if (!get_company_pref('deferred_income_act')) {
        return AxisPro::ValidationError("Please configure the deferred income account before issuing a delivery.");
    }

    // Check if the date is in fiscal year or return error.
    if (!is_date_in_fiscalyear($request->input('delivery_date'))) {
        return AxisPro::ValidationError("The entered delivery date is out of fiscal year or is closed for further data entry.");
	}

    begin_transaction();

    if ($request->input('auto_supplier_invoice')) {
        add_purchase_invoice($request, $contract);
    }

    // Check if the maid is available at the center for delivery
    if (!valid_maid_inventory_update($contract->labour_id, $request->input('delivery_date'))) {
        return AxisPro::ValidationError(
            'The maid is not available for delivery at the provided date '
                . 'or this delivery would cause a negative inventory of the maid in a future date'
        );
    }

    $cart = new Cart(ST_SALESORDER, $contract->order->order_no, true);

    if ($cart->count_items() == 0) {
        return AxisPro::ValidationError("This order is already delivered.");
    }

    if (!$cart->is_released()) {
        return AxisPro::ValidationError("This order requires a prepayment and the payment is not yet received.");
    }
    
    // Lock the contract for update.
    $lockAcquired = Contract::query()
        ->whereId($contract->id)
        ->whereNull('maid_delivered_at')
        ->lockForUpdate()
        ->value('id');

    if (!$lockAcquired) {
        return AxisPro::ValidationError("The maid is already delivered against this contract");
    }

    $deliveryDate = Carbon::createFromFormat(getNativeDateFormat(), $request->input('delivery_date'));
    $diffInDays = $contract->contract_from->diffInDays($deliveryDate);

    $contract->maid_delivered_at = $deliveryDate;
        
    if ($diffInDays > 0) {
        $contract->contract_from = $contract->contract_from->addDays($diffInDays);
        $contract->contract_till = $contract->contract_till->addDays($diffInDays);
        
        CustomerTransaction::active()
            ->ofType(CustomerTransaction::INVOICE)
            ->whereContractId($contract->id)
            ->update([
                'period_from' => DB::raw("DATE_ADD(`period_from`, INTERVAL {$diffInDays} DAY)"),
                'period_till' => DB::raw("DATE_ADD(`period_till`, INTERVAL {$diffInDays} DAY)")
            ]);
    }

    $contract->save();

    $cart->ship_via = DB::table('0_shippers')->value('shipper_id');
    $cart->freight_cost = 0;
    $cart->document_date = $request->input('delivery_date');
    $cart->due_date = $cart->document_date;
    $cart->Location = DB::table('0_locations')->value('loc_code');
    $cart->Comments = "Delivery against Contract #{$contract->reference}";
    $cart->dimension_id = $contract->dimension_id;
    $cart->dimension2_id = 0;
    $cart->reference = MetaReference::getNext(
        CustomerTransaction::DELIVERY,
        null,
        array(
            'date' => $cart->document_date,
            'customer' => $cart->customer_id,
            'branch' => $cart->Branch,
            'dimension' => $cart->dimension_id
        )
    );

    array_walk($cart->line_items, function (&$line) {
        $line->qty_dispatched = $line->quantity;
    });

    _process_cart($cart);

    commit_transaction();

    Event::dispatch(new ContractDelivered($contract));

    http_response_code(204);
    echo json_encode([
        'status' => 204,
        'message' => 'Maid Delivered Successfully'
    ]);
    exit();
}

/**
 * Make an automatic purchase invoice
 *
 * @param Request $request
 * @return void
 */
function add_purchase_invoice(Request $request, Contract $contract)
{
    global $messages;

    // Validate the request
    $validator = Validator::make(
        $request->except(['_method', '_token']),
        [
            'purchase_stock_id' => 'required|exists:0_stock_master,stock_id',
            'purchase_date' => 'required|date_format:' . getNativeDateFormat(),
            'supp_ref' => 'nullable|regex:#^[\pL\pM\pN_\- /]*$#u',
            'supplier_id' => 'required|exists:0_suppliers,supplier_id',
            'purchase_price' => 'required|numeric|min:0',
            'supp_is_taxable' => 'nullable|boolean',
            'supp_inv_comments' => 'nullable|regex:#^[\pL\pM\pN_\- .:,\'/]*$#u'
        ]
    );

    if ($validator->fails()) {
        http_response_code(422);
        echo json_encode([
            'status' => 422,
            'message' => 'Request contains invalid data',
            'errors' => $validator->errors()
        ]);
        exit();
    }

    $inputs = $request->input();

    if (!is_date_in_fiscalyear($inputs['purchase_date'])) {
		return AxisPro::ValidationError("The entered purchase date is out of fiscal year or is closed for further data entry.");
	}

    if (!empty($inputs['supp_ref']) && is_reference_already_there($inputs['supplier_id'], $inputs['supp_ref'])) {
        return AxisPro::ValidationError("This supplier invoice number has already been entered. It cannot be entered again. Keep it empty");
    }
    
    // Check if the maid is already purchased and is at the center for delivery
    if (!valid_maid_inventory_update($contract->labour_id, $inputs['purchase_date'], 1)) {
        return AxisPro::ValidationError(
            'The maid cannot be purchased at the provided date. Reason: This maid is already at the center'
                . 'or, this purchase would cause more than one maid to be available at the center in a future date'
        );
    }

    begin_transaction();

    $supplier = get_supplier($inputs['supplier_id']);
    $total = $inputs['purchase_price'];
    $tax = 0;

    if ($inputs['purchase_price'] > 0) {
        $taxFreePrice = get_tax_free_price_for_item(
            $inputs['purchase_stock_id'],
            $inputs['purchase_price'],
            $supplier['tax_group_id'],
            $supplier['tax_included']
        );
    
        $fullPrice = get_full_price_for_item(
            $inputs['purchase_stock_id'],
            $inputs['purchase_price'],
            $supplier['tax_group_id'],
            $supplier['tax_included']
        );

        $tax = ($fullPrice - $taxFreePrice);
    }

    if ($inputs['supp_is_taxable'] && !$supplier['tax_included']) {
        $total += $tax;
    }

    if (!$inputs['supp_is_taxable'] && $supplier['tax_included']) {
        $total -= $tax;
    }

    $qty = 1;
    $total = round2($total * $qty, user_price_dec());

    if ($total <= 0) {
        return AxisPro::ValidationError("Total payable amount must not be empty");
    }

    $cart = new purch_order;
    $cart->trans_type = ST_SUPPINVOICE;
    $cart->order_no = 0;
    $cart->orig_order_date = $inputs['purchase_date'];
    $cart->reference = MetaReference::getNext(
        $cart->trans_type,
        null,
        array(
            'supplier_id' => $cart->supplier_id,
            'date' => $cart->orig_order_date
        )
    );

    $cart->supplier_id = $inputs['supplier_id'];
    $cart->supp_ref = $inputs['supp_ref'] ?: $cart->reference;
    
    $supplier['credit_limit'] = data_get($supplier, 'credit_limit', 0) ?: 0;
    $cart->credit = data_get(
        db_query(
            "SELECT
                {$supplier['credit_limit']} - Sum((ov_amount + ov_gst + ov_discount)) as cur_credit
            FROM 0_supp_trans trans
            WHERE trans.supplier_id = ".db_escape($cart->supplier_id),
            "Could not query for supplier credit limit"
        )->fetch_assoc(),
        'cur_credit',
        $supplier['credit_limit']
    );
    
    $terms = get_payment_terms($supplier['payment_terms']);
    $cart->terms = [
        'description' => $terms['terms'],
        'days_before_due' => $terms['days_before_due'],
        'day_in_following_month' => $terms['day_in_following_month']
    ];

    get_duedate_from_terms($cart);

    $cart->supplier_name = $supplier["supp_name"];
    $cart->curr_code = $supplier["curr_code"];
    $cart->tax_group_id = $supplier["tax_group_id"];
    $cart->tax_included = $supplier["tax_included"];
    $cart->tax_group_array = get_tax_group_items_as_array($cart->tax_group_id);

    $location = DB::table('0_locations')->first();
    $cart->Location = $location->loc_code;
    $cart->delivery_address = $location->delivery_address;
    
    $cart->Comments = data_get($inputs, 'supp_inv_comments', '');
    $cart->dimension = $contract->dimension_id;
    $cart->dimension2 = 0;
    $cart->prep_amount = 0;
    $cart->ex_rate = null;

    $stockItem = get_item($inputs['purchase_stock_id']);
    
    $discountConfig = get_supp_commission_config($cart->supplier_id, $inputs['purchase_stock_id']);
    $discountInfo = get_supp_commission($cart->supplier_id, $inputs['purchase_stock_id'], $inputs['purchase_price'], $discountConfig);

    $cart->add_to_order(
        count($cart->line_items),
        $inputs['purchase_stock_id'],
        $qty, 
        $stockItem["description"],
        $inputs['purchase_price'],
        $stockItem["units"], 
        '',
        0,
        0,
        $contract->labour_id,
        $inputs['purchase_price'],
        0,
        null,
        $discountInfo['supp_commission']
    );

    if (!$inputs['supp_is_taxable'] && $tax != 0) {
        foreach (array_keys($cart->tax_group_array) as $taxTypeId) {
            $cart->tax_overrides[$taxTypeId] = 0;
        }
    }

    $err = null;
    $cart->setBackup(\DeepCopy\deep_copy($cart, true));
    $GLOBALS['PROCESSING_ITEMS'] = &$cart;
    try {
        add_direct_supp_trans($cart);
    }
    catch (BusinessLogicException $e) {
        $err = $e->getMessage();
    }
    unset($GLOBALS['PROCESSING_ITEMS']);

    if ($err || count($messages)) {
        AxisPro::ValidationError($err ?: fmt_errors(true));
        exit();
    }

    commit_transaction();
}

/**
 * Process the cart object
 *
 * @param Cart $cart
 * @return void
 */
function _process_cart(Cart $cart)
{
    global $messages;

    $err = null;
    $cart->setBackup(\DeepCopy\deep_copy($cart, true));
    $GLOBALS['PROCESSING_ITEMS'] = &$cart;
    try {
        $ret = $cart->write(1);
    }
    catch (BusinessLogicException $e) {
        $err = $e->getMessage();
    }
    unset($GLOBALS['PROCESSING_ITEMS']);

    if ($err || count($messages)) {
        AxisPro::ValidationError($err ?: fmt_errors(true));
        exit();
    }
}

/**
 * Handles the request to get the supplier invoice info against the contract
 *
 * @param Request $request
 * @return void
 */
function handle_get_supplier_invoice_info_request(Request $request)
{
    // Validate the request
    $validator = Validator::make(
        $request->except(['_method', '_token']),
        [
            'contract_id' => 'bail|required|integer|exists:0_labour_contracts,id',
        ]
    );

    if ($validator->fails()) {
        http_response_code(422);
        echo json_encode([
            'status' => 422,
            'message' => 'Request contains invalid data',
            'errors' => $validator->errors()
        ]);
        exit();
    }

    $contract = Contract::find($request->input('contract_id'));
    
    if (empty($agent = $contract->maid->agent)) {
        return AxisPro::ValidationError("An agent is not assigned to this maid. Please assign one before proceeding");
    }
    
    if (!$nationality = data_get($contract->maid, 'nationality')) {
        return AxisPro::ValidationError('Missing nationality of the maid. Please configure before proceeding');
    }

    $stocks = StockItem::whereCategoryId(StockCategory::DWD_PACKAGEONE)
        ->where('no_purchase', 0)
        ->where(function (Builder $query) use ($nationality) {
            $query->whereNull('nationality')
                ->orWhere('nationality', $nationality);
        })
        ->select('stock_id', 'category_id', 'nationality', 'description')
        ->get()
        ->toArray();

    if (blank($stocks)) {
        return AxisPro::ValidationError("There are no purchasable item configured for this maid that matches the category and nationality");
    }

    http_response_code(200);
    echo json_encode([
        'status' => 200,
        'data' => [
            'supplier' => $agent->append('formatted_name')->only('supplier_id', 'formatted_name'),
            'stocks' => $stocks
        ]
    ]);
    exit();
}