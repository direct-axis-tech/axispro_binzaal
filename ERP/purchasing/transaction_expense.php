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

use App\Exceptions\BusinessLogicException;
use App\Http\Controllers\Sales\SalesOrderDetailsController;
use App\Models\MetaReference;
use Illuminate\Http\Request;

$path_to_root = "..";
$page_security = 'SA_SUPPLIERINVOICE';

require_once __DIR__ . "/../includes/session.inc";
require_once __DIR__ . "/../purchasing/includes/purchasing_db.inc";
require_once __DIR__ . "/../includes/banking.inc";
require_once __DIR__ . "/../includes/data_checks.inc";
require_once __DIR__ . "/../purchasing/includes/purchasing_ui.inc";
require_once __DIR__ . "/../API/AxisPro.php";

global $Refs, $Ajax;

// Handle the requests
$request = request();
switch ($request->query('action')) {
    case 'getItemInfo':
        get_item_info($request);
        exit();

    case 'calculateTax':
        calculate_tax($request);
        exit();

    case 'addExpense':
        if (!user_check_access('SA_SUPPLIERINVOICE')) {
            return AxisPro::ValidationError('The security settings on your account do not permit you to access this function', 403);
        }

        add_expense($request);
        exit();
        
    case 'getLastPurchaseDetails':
        handle_get_last_purchase_details_request($request);
        exit();
    
    default:
        return AxisPro::ValidationError('Bad Request', 400);
        exit();
}

/**
 * Handles the add expense request
 *
 * @param Request $request
 * @return void
 */
function add_expense(Request $request)
{
    try {
        $inputs = validate_add_expense_request($request);

        config()->set('database.default', 'fa');
        begin_transaction();

        $supplier = get_supplier($inputs['supplier_id']);
        $total = $inputs['price'] + $inputs['govt_fee'];
        $tax = 0;

        $orderLine = app(SalesOrderDetailsController::class)
            ->getBuilder(['line_reference' => $request->input('line_reference')])
            ->first();

        if ($inputs['price'] > 0) {
            $taxFreePrice = get_tax_free_price_for_item(
                $inputs['stock_id'],
                $inputs['price'],
                $supplier['tax_group_id'],
                $supplier['tax_included']
            );
        
            $fullPrice = get_full_price_for_item(
                $inputs['stock_id'],
                $inputs['price'],
                $supplier['tax_group_id'],
                $supplier['tax_included']
            );

            $tax = ($fullPrice - $taxFreePrice);
        }

        if ($inputs['is_taxable'] && !$supplier['tax_included']) {
            $total += $tax;
        }

        if (!$inputs['is_taxable'] && $supplier['tax_included']) {
            $total -= $tax;
        }

        $total = round2($total * $orderLine->qty_not_sent, user_price_dec());

        if ($total <= 0) {
            return AxisPro::ValidationError("Total payable amount must not be empty");
        }

        if (!empty($inputs['payment_account']) && $inputs['paying_amt'] > $total) {
            return AxisPro::ValidationError("Total paying amount must not exceed the total payable amount");
        }

        $cart = new purch_order;
        $cart->trans_type = ST_SUPPINVOICE;
        $cart->order_no = 0;
        $cart->orig_order_date = $inputs['expense_date'];
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
        
        $cart->Comments = data_get($inputs, 'comments', '');
        $cart->dimension = $orderLine->dimension_id;
        $cart->dimension2 = 0;
        $cart->prep_amount = 0;
        $cart->ex_rate = null;

        $stockItem = get_item($inputs['stock_id']);
        
        $discountConfig = get_supp_commission_config($cart->supplier_id, $inputs['stock_id']);
        $discountConfig['commission'] = $inputs['supp_commission'];
        $discountInfo = get_supp_commission($cart->supplier_id, $inputs['stock_id'], $inputs['price'], $discountConfig);

        $cart->add_to_order(
            count($cart->line_items),
            $inputs['stock_id'],
            $orderLine->qty_not_sent, 
            $stockItem["description"],
            $inputs['price'] + $inputs['govt_fee'],
            $stockItem["units"], 
            '',
            0,
            0,
            null,
            $inputs['price'],
            $inputs['govt_fee'],
            $orderLine->line_reference,
            $discountInfo['supp_commission']
        );

        if (!$inputs['is_taxable'] && $tax != 0) {
            foreach (array_keys($cart->tax_group_array) as $taxTypeId) {
                $cart->tax_overrides[$taxTypeId] = 0;
            }
        }

        $_SESSION[$cart->getSessionAccessor()] = &$cart;
        $GLOBALS['PROCESSING_ITEMS'] = &$cart;

        $inv_no = add_direct_supp_trans($cart);

        if ($inputs['payment_account']) {
            $pmt_no = write_supp_payment(
                0,
                $cart->supplier_id,
                $inputs['payment_account'],
                $inputs['payment_date'],
                MetaReference::getNext(
                    ST_SUPPAYMENT,
                    null,
                    $inputs['payment_date']
                ), 
                $inputs['paying_amt'],
                0,
                trans('Payment for:').$cart->supp_ref .' ('.$GLOBALS['type_shortcuts'][ST_SUPPINVOICE].$inv_no.')' . ' ' . $cart->Comments,
                0,
                0,
                $cart->dimension,
                $cart->dimension2,
                $inputs['payment_ref']
            );
            add_supp_allocation(
                min($inputs['paying_amt'], $total),
                ST_SUPPAYMENT,
                $pmt_no,
                ST_SUPPINVOICE,
                $inv_no,
                $cart->supplier_id,
                $inputs['payment_date'],
                $cart->orig_order_date
            );
            update_supp_trans_allocation(ST_SUPPINVOICE, $inv_no, $cart->supplier_id);
            update_supp_trans_allocation(ST_SUPPAYMENT, $pmt_no, $cart->supplier_id);
        }

        if ($inputs['process'] == 'AddExpenseAndCompleteTransaction') {
            complete_transaction($request->duplicate(null, [
                'line_reference' => $inputs['line_reference'],
                'delivery_date' => $inputs['expense_date'],
                'transaction_id' => $inputs['payment_ref']
            ]));
        }

        commit_transaction();
        
        http_response_code(204);
        echo json_encode([
            'status' => 204,
            'message' => 'Expense Added Successfully'
        ]);
        exit();
    }
    catch (BusinessLogicException $e) {
        return AxisPro::ValidationError($e->getMessage());
    }
}

/**
 * Validates the request to add expense
 *
 * @param Request $request
 * @return void
 */
function validate_add_expense_request(Request $request)
{
    // Validate the request
    $validator = Validator::make(
        $request->except(['_method', '_token']),
        [
            'line_reference' => 'required|exists:0_sales_order_details,line_reference',
            'expense_date' => 'required|date_format:' . getNativeDateFormat(),
            'stock_id' => 'required|exists:0_stock_master,stock_id',
            'supp_ref' => 'nullable|regex:#^[\pL\pM\pN_\- /]*$#u',
            'supplier_id' => 'required|exists:0_suppliers,supplier_id',
            'govt_fee' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'supp_commission' => 'required|numeric|min:0',
            'is_taxable' => 'nullable|boolean',
            'comments' => 'nullable|regex:#^[\pL\pM\pN_\- .:,\'/]*$#u',
            'payment_account' => 'nullable|exists:0_bank_accounts,id',
            'payment_date' => 'nullable|required_with:payment_account|date_format:' . getNativeDateFormat(),
            'paying_amt' => 'nullable|required_with:payment_account|numeric|gt:0',
            'payment_ref'   => 'nullable|string|alpha_num',
            'process' => 'required|in:AddExpense,AddExpenseAndCompleteTransaction'       
        ],
        [
            'supp_ref.regex' => 'Special characters except [underscore], [hyphen], [space], [slash] are not allowed',
            'supp_ref.comments' => 'Special characters except [underscore], [hyphen], [space], [dot], [colon], [single quote], [slash] are not allowed'
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

    $inputs = $validator->validated();
    $inputs['is_taxable'] = !empty($inputs['is_taxable']);

    if (
        $inputs['process'] == 'AddExpenseAndCompleteTransaction' 
        && authUser()->doesntHavePermission(\App\Permissions::SA_SALESLNCMPLTWEXP)
    ) {
        return AxisPro::ValidationError("The system does not permit you to access this function. If you think you should have this permission, Please ask your IT for access.", 403);
    }

    if (!is_date_in_fiscalyear($inputs['expense_date'])) {
		return AxisPro::ValidationError("The entered date is out of fiscal year or is closed for further data entry.");
	}

    if (!empty($inputs['supp_ref']) && is_reference_already_there($inputs['supplier_id'], $inputs['supp_ref'])) {
        return AxisPro::ValidationError("This invoice number has already been entered. It cannot be entered again. Keep it empty");
    }

    return $inputs;
}

/**
 * Handles the request for getting tax information
 *
 * @param Request $request
 * @return void
 */
function calculate_tax(Request $request)
{
    // Validate the request
    $validator = Validator::make(
        $request->except(['_method', '_token']),
        [
            'supplier_id' => 'required|integer|exists:0_suppliers,supplier_id',
            'stock_id' => 'required|exists:0_stock_master,stock_id',
            'price' => 'required|numeric|gt:0'
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

    $inputs = $validator->validated();
    $supplier = get_supplier($inputs['supplier_id']);

    $taxFreePrice = get_tax_free_price_for_item(
        $inputs['stock_id'],
        $inputs['price'],
        $supplier['tax_group_id'],
        $supplier['tax_included']
    );

    $fullPrice = get_full_price_for_item(
        $inputs['stock_id'],
        $inputs['price'],
        $supplier['tax_group_id'],
        $supplier['tax_included']
    );

    http_response_code(200);
    echo json_encode([
        'status' => 200,
        'tax' => $fullPrice - $taxFreePrice
    ]);
    exit();
}

/**
 * Handles the request for getting tax information
 *
 * @param Request $request
 * @return void
 */
function get_item_info(Request $request)
{
    // Validate the request
    $validator = Validator::make(
        $request->except(['_method', '_token']),
        [
            'supplier_id' => 'required|integer|exists:0_suppliers,supplier_id',
            'stock_id' => 'required|exists:0_stock_master,stock_id',
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

    $inputs = $validator->validated();
    $supplier = get_supplier($inputs['supplier_id']);
    $taxArray = get_tax_group_items_as_array($supplier['tax_group_id']);
    $taxes = get_taxes_for_item($inputs['stock_id'], $taxArray);

    $rate = 0;
    if ($taxes) {
        foreach ($taxes as $tax) {
            $rate += $tax["rate"];
        }
    }

    $taxInfo = [
        'rate' => $rate,
        'tax_included' => boolval($supplier['tax_included']),
        'taxes' => $taxes
    ];

    $itemInfo = get_item($inputs['stock_id']);
    $itemInfo['purchase_price'] = get_purchase_price($inputs['supplier_id'], $inputs['stock_id']);
    $govtFee = get_purchase_price($inputs['supplier_id'], $inputs['stock_id'], 'govt_fee');
    
    if ($govtFee != 0) { 
        $itemInfo['purchase_price'] -= $govtFee;
        
        if ($itemInfo['purchase_price'] < 0) {
            $itemInfo['purchase_price'] = 0;
        }
    }

    http_response_code(200);
    echo json_encode([
        'status' => 200,
        'taxInfo' => $taxInfo,
        'itemInfo' => $itemInfo,
        'discountInfo' => get_supp_commission_config($inputs['supplier_id'], $inputs['stock_id'])
    ]);
    exit();
}

/**
 * Handles the request for getting latest supplier transaction
 *
 * @param Request $request
 * @return void
 */
function handle_get_last_purchase_details_request(Request $request){
    // Validate the request
    $validator = Validator::make(
        $request->except(['_method', '_token']),
        [
            'stock_id' => 'required|exists:0_stock_master,stock_id',
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

    $inputs = $validator->validated();
    $details = get_last_purchase_details($inputs['stock_id']);

    http_response_code(200);
    echo json_encode([
        'status' => 200,
        'data' => $details
    ]);
    exit();
}