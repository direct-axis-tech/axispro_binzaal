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

use App\Models\Accounting\BankAccount;
use App\Models\Accounting\Dimension;
use App\Models\Inventory\StockCategory;
use App\Models\Labour\Labour;
use App\Models\MetaReference;
use App\Models\Sales\CreditStatus;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrderDetail;
use App\Models\System\User;
use Illuminate\Support\Arr;

include_once($path_to_root . "/sales/includes/cart_class.inc");

//--------------------------------------------------------------------------------
function add_to_order(
    &$order,
    $new_item,
    $new_item_qty,
    $price,
    $discount,
    $description = '',
    $govt_fee = 0,
    $bank_service_charge = 0,
    $bank_service_charge_vat = 0,
    $transaction_id = null,
    $discount_amount = null,
    $other = "",
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
    $assignee_id = null,
    $kit_prefix = '',
    $customer_commission2 = 0.00,
    $fine = 0
) {
    $kit_ref = $order->get_next_kit_ref($kit_prefix);
    $items = get_kit_items($new_item)->fetch_all(MYSQLI_ASSOC);
    $item_num = count($items);

    //  If not a kit item, There isn't much to do, Just add like normal
    $item = reset($items);
    if ($item_num == 1 && !is_kit_item($item['stock_id'])) {
        $order->add_to_cart(
            count($order->line_items),
            $item['stock_id'],
            $new_item_qty * $item['quantity'],
            $price,
            $discount,
            0,
            0,
            $description,
            null,
            null,
            null,
            $govt_fee,
            $bank_service_charge,
            $bank_service_charge_vat,
            $transaction_id,
            $discount_amount,
            null,
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
            null,
            null,
            null,
            null,
            0,
            0,
            $assignee_id,
            $item['stock_id'],
            $kit_ref,
            $customer_commission2,
            is_customer_card_account($govt_bank_account),
            $fine
        );

        return true;
    }

    // This is a kit item, So explode the items and re-distribute the prices
    $prices = [
        'price' => $price,
        'govt_fee' => $govt_fee,
        'bank_service_charge' => $bank_service_charge,
        'bank_service_charge_vat' => $bank_service_charge_vat,
        'discount' => $discount_amount,
        'returnable_amt' => $returnable_amt,
        'split_govt_fee_amt' => $split_govt_fee_amt,
        'pf_amount' => $pf_amount,
        'extra_srv_chg' => $extra_srv_chg,
        'receivable_commission_amount' => $receivable_commission_amount,
        'fine' => $fine
    ];

    $price_keys = array_keys($prices);
    $kit_info = get_item_kit_info($new_item, $order->customer_id);
    $kit_info['price'] = get_kit_price(
        $new_item,
        $order->customer_currency,
        $order->sales_type,
        $order->price_factor,
        get_post('OrderDate'),
        true
    );
    
    // Calculate the factor by which each individual price changed 
    // from its original value. If the prices were not edited, this should be 1
    $factors = [];
    foreach ($prices as $key => $value) {
        $factors[$key] = ($kit_info[$key] == 0) ? 0 : ($value / $kit_info[$key]);
    }

    // Validate the kit items configuration
    $items_info = [];
    foreach ($items as $i => $item) {
        $item_info = get_item_kit_info($item['stock_id'], $order->customer_id);
        $item_info['price'] = get_kit_price(
            $item['stock_id'],
            $order->customer_currency,
            $order->sales_type,
            $order->price_factor,
            get_post('OrderDate'),
            true
        );

        // Initialise kit item price
        $item_prices = array_fill_keys($price_keys, 0);

        // rounding differences are included in last price item in kit
        $item_num--;

        // if not last item, calculate the price and reduce it from original
        if ($item_num) {
            foreach ($price_keys as $price_key) {
                $prices[$price_key] -= $item['quantity'] * $item_info[$price_key] * $factors[$price_key];
                $item_prices[$price_key] = $item_info[$price_key] * $factors[$price_key];
            }
        }

        // if last item, allocate all the remaining amounts
        else {
            foreach ($price_keys as $price_key) {
                if ($item['quantity']) {
                    $prices[$price_key] = $prices[$price_key] / $item['quantity'];
                }

                $item_prices[$price_key] = $prices[$price_key];
            }
        }

        foreach ($price_keys as $price_key) {
            $item_prices[$price_key] = round($item_prices[$price_key], user_price_dec());
        }

        $item_prices['discountable_amount'] = (
            + $item_prices['price']
            + $item_prices['returnable_amt']
            + $item_prices['receivable_commission_amount']
            - $item_prices['pf_amount']
        );

        if ($item_prices['discountable_amount'] == 0) {
            $item_prices['discount'] = 0;
        }
        
        $item_prices['discount_percent'] = $item_prices['discountable_amount'] != 0
            ? ($item_prices['discount'] / $item_prices['discountable_amount'])
            : 0;

        $item_info['prices'] = $item_prices;
        $items_info[$i] = $item_info;

        if (is_kit_item($item['stock_id'])) {
            continue;
        }

        if (
            empty($item_info['govt_bank_account'])
            && (
                $item_prices['govt_fee'] != 0
                || $item_prices['bank_service_charge'] != 0
                || $item_prices['bank_service_charge_vat'] != 0
                || $item_prices['pf_amount'] != 0
            )
        ) {
            display_error("The govt bank account for the kit item {$item_info['stock_id']} is not defined");
            return false;
        }

        if (
            empty($item_info['returnable_to'])
            && $item_prices['returnable_amt'] != 0
        ) {
            display_error("The returnable account for the kit item {$item_info['stock_id']} is not defined");
            return false;
        }
        
        if (
            empty($item_info['split_govt_fee_acc'])
            && $item_prices['split_govt_fee_amt'] != 0
        ) {
            display_error("The noqudi (split govt fee) account for the kit item {$item_info['stock_id']} is not defined");
            return false;
        }
        
        if (
            empty($item_info['receivable_commission_account'])
            && $item_prices['receivable_commission_amount'] != 0
        ) {
            display_error("The receivable commission account for the kit item {$item_info['stock_id']} is not defined");
            return false;
        }
    }

    // If everything is good, add to the cart
    foreach ($items as $i => $item) {
        $item_info = $items_info[$i];
        $item_prices = $item_info['prices'];

        // If this is a sales kit item inside a sales kit - so, recurse
        if (is_kit_item($item['stock_id'])) {
            $ret = add_to_order(
                $order,
                $item['stock_id'],
                $new_item_qty * $item['quantity'],
                $item_prices['price'],
                $item_prices['discount_percent'],
                $item['description'],
                $item_prices['govt_fee'],
                $item_prices['bank_service_charge'],
                $item_prices['bank_service_charge_vat'],
                $transaction_id,
                $item_prices['discount'],
                $other,
                $application_id,
                null,
                $ref_name,
                $ed_transaction_id,
                $item_prices['returnable_amt'],
                null,
                $item_prices['split_govt_fee_amt'],
                null,
                $item_prices['pf_amount'],
                $passport_no,
                $item_prices['extra_srv_chg'],
                $created_by,
                0,
                0,
                0,
                0,
                $item_prices['receivable_commission_amount'],
                null,
                $assignee_id,
                $kit_ref
            );
        }
        
        // This is a stock item
        else {
            // check duplicate stock item
            foreach ($order->line_items as $order_item) {
                if (strcasecmp($order_item->stock_id, $item['stock_id']) == 0) {
                    display_warning(trans("For Part :") . $item['stock_id'] . " "
                        . trans("This item is already on this document. You have been warned."));
                    break;
                }
            }

            set_commission_amounts(
                $order->customer_id,
                $item_info,
                $transaction_id,
                $created_by,
                $item_prices['discountable_amount']
            );

            $ret = $order->add_to_cart(
                count($order->line_items),
                $item['stock_id'],
                $new_item_qty * $item['quantity'],
                $item_prices['price'],
                $item_prices['discount_percent'],
                0,
                0,
                $item_info['description'],
                null,
                null,
                null,
                $item_prices['govt_fee'],
                $item_prices['bank_service_charge'],
                $item_prices['bank_service_charge_vat'],
                $transaction_id,
                $item_prices['discount'],
                null,
                $application_id,
                $item_info['govt_bank_account'],
                $ref_name,
                $ed_transaction_id,
                $item_prices['returnable_amt'],
                $item_info['returnable_to'],
                $item_prices['split_govt_fee_amt'],
                $item_info['split_govt_fee_acc'],
                $item_prices['pf_amount'],
                $passport_no,
                $item_prices['extra_srv_chg'],
                $created_by,
                input_num('employee_commission'),
                input_num('customer_commission'),
                input_num('cust_comm_emp_share'),
                input_num('cust_comm_center_share'),
                $item_prices['receivable_commission_amount'],
                $item_info['receivable_commission_account'],
                null,
                null,
                null,
                null,
                0,
                0,
                $assignee_id,
                $item['item_code'],
                $kit_ref,
                input_num('customer_commission2'),
                is_customer_card_account($item_info['govt_bank_account']),
                $item_info['fine']
            );
        }

        if (!$ret) {
            return false;
        }
    };

    return true;
}

//---------------------------------------------------------------------------------

/**
 * Sets the customer details
 *
 * @param Cart $order
 * @param string $customer_id
 * @param string $branch_id
 */
function get_customer_details_to_order(&$order, $customer_id, $branch_id,
    $name = null, $phone = null, $email = null, $tax_id = null,
    $cust_ref = null, $contact_person = null) {

    global $SysPrefs;
    $ret_error = "";
    $myrow = get_customer_to_order($customer_id);

    $customer_email = $email ?? ($myrow['debtor_email'] ?: '');
    $customer_mobile = $phone ?? ($myrow['mobile'] ?: '');
    $customer_name = $name ?? ($myrow['name'] ?: '');
    $customer_ref = $cust_ref ?? ($myrow['iban_no'] ?: '');
    $customer_trn = $tax_id ?? ($myrow['tax_id'] ?: '');
    $contact_person = $contact_person ?? ($myrow['contact_person'] ?: '');

    if ($myrow['dissallow_invoices'] == 1)
        $ret_error = trans("The selected customer account is currently on hold. Please contact the credit control personnel to discuss.");

    $deliver = $myrow['address']; // in case no branch address use company address

    // For tasheel, tawjeeh, tadbeer and meenalabs hardcode the sales type to be tax included.
    if ($order->shouldBePrepaid($order->getDimension())) {
        $myrow['payment_terms'] = PMT_TERMS_PREPAID;
    }

    $order->set_customer(
        $customer_id,
        $customer_name,
        $myrow['curr_code'],
        $myrow['discount'],
        $myrow['payment_terms'],
        $myrow['pymt_discount'],
        $myrow['salesman_id'],
        $myrow['show_discount'],
        $myrow['customer_type']
    );

    // For tasheel, tawjeeh, tadbeer and meenalabs hardcode the sales type to be tax included.
    if ($order->shouldBeTaxIncluded($order->dimension_id)) {
        $salesType = get_sales_type(SALES_TYPE_TAX_INCLUDED);
        $myrow['salestype'] = $salesType['id'];
        $myrow['sales_type'] = $salesType['sales_type'];
        $myrow['tax_included'] = $salesType['tax_included'];
        $myrow['factor'] = $salesType['factor'];
    }

    // the sales type determines the price list to be used by default
    $order->set_sales_type(
        $myrow['salestype'],
        $myrow['sales_type'],
        $myrow['tax_included'],
        $myrow['factor']
    );

    $order->credit = $myrow['cur_credit'];
    $order->credit_days = $myrow['cur_credit_days'];

    if ($order->trans_type != ST_SALESQUOTE) {
        // $order->dimension_id = $myrow['dimension_id'];
        $order->dimension2_id = $myrow['dimension2_id'];
    }
    $result = get_branch_to_order($customer_id, $branch_id);

    if (db_num_rows($result) == 0) {
        return trans("The selected customer and branch are not valid, or the customer does not have any branches.");
    }

    $myrow = db_fetch($result);

    // FIX - implement editable contact selector in sales order
    $contact = get_branch_contacts($branch_id, 'order', $customer_id);

    $order->set_branch($branch_id, $myrow["tax_group_id"],
        $myrow["tax_group_name"], $customer_mobile, $customer_email,
        $customer_trn, $customer_ref, $contact_person
    );

    $address = trim($myrow["br_post_address"]) != '' ? $myrow["br_post_address"]
        : (trim($myrow["br_address"]) != '' ? $myrow["br_address"] : $deliver);

    $order->set_delivery($myrow["default_ship_via"], $myrow["br_name"],
        $address);
    if ($order->trans_type == ST_SALESINVOICE) {
        $order->due_date = get_invoice_duedate($order->payment, $order->document_date);
    } elseif ($order->trans_type == ST_SALESORDER)
        $order->due_date = add_days($order->document_date, $SysPrefs->default_delivery_required_by());
    elseif ($order->trans_type == ST_SALESQUOTE)
        $order->due_date = add_days($order->document_date, $SysPrefs->default_quote_valid_days());

    if ($order->payment_terms['cash_sale']) {
        $order->set_location($order->pos["pos_location"], $order->pos["location_name"]);
    } else
        $order->set_location($myrow["default_location"], $myrow["location_name"]);

    return $ret_error;
}

//---------------------------------------------------------------------------------

function display_order_summary($title, &$order, $editable_items = false)
{

    display_heading($title);

    div_start('items_table');
    start_table(TABLESTYLE, "width='90%'");

    if (!$order->dimension_id) {
        start_row();
        label_cell("Please select the dimension to proceed", 'class="text-center h-200px fs-2 text-danger"');
        end_row();
        set_focus('dimension_id');
        end_table();
        div_end();
        return;
    }

    $th = [];

    $dimension = $order->getDimension();
    $isHavingSplitGovtFee = $order->isHavingSplitGovtFee($dimension);
    $isOtherFeeEditable = $order->isOtherFeeEditable($dimension);
    $isServiceFeeCombined = $order->isServiceFeeCombined($dimension);
    $isReturnableAmountEditable = $order->isReturnableAmountEditable($dimension);
    $isReturnableAccountEditable = $order->isReturnableAccountEditable($dimension);
    $isDiscountEditable = $order->isDiscountEditable($dimension);
    $isGovtBankEditable = $dimension->is_govt_bank_editable;
    $isFromLabourContract = $order->isFromLabourContract();
    $isReceivableCommissionAmountEditable = $order->isReceivableCommissionAmountEditable($dimension);
    $isReceivableCommissionAccountEditable = $order->isReceivableCommissionAccountEditable($dimension);
    $isLineRefColEnabled = $dimension->is_line_ref_col_enabled;
    $isAssigneeColEnabled = $order->isAssigneeColEnabled($dimension);
    $isFineColEnabled = $order->isFineColEnabled($dimension);
    
    $th[] = "#";
    if ($isLineRefColEnabled) {
        $th[] = trans("Line #");
    }
    $th[] = trans("Item Code");
    $th[] = trans("Item Description");
    $th[] = trans("QTY");
    $th[] = trans("Delivered");
    if (!$isFromLabourContract) {
        if ($isGovtBankEditable) {
            $th[] = trans("Govt. A/C");
        }
        $th[] = $isHavingSplitGovtFee ? trans("Edirham Chg") : trans("Govt. Fee");
        if ($isHavingSplitGovtFee) {
            $th[] = trans("Noqudi Chrg.");
        }
        if ($isFineColEnabled) {
            $th[] = trans("Fine");
        }
        if (!$isServiceFeeCombined) {
            $th[] = $order->tax_included ? trans("Price after Tax") : trans("Service Charge");
        }
        if ($isOtherFeeEditable) {
            $th[] = trans("Other Chrg.");
        }
        $th[] = trans("Bank Charge");

        if ($isReturnableAmountEditable) {
            $th[] = trans("Returnable Amount");
        }

        if ($isReturnableAccountEditable) {
            $th[] = trans("Returnable Account");
        }

        if ($isReceivableCommissionAmountEditable) {
            $th[] = trans("Receivable Comm. Amt");
        }

        if ($isReceivableCommissionAccountEditable) {
            $th[] = trans("Receivable Comm. A/c");
        }

        if ($isDiscountEditable) {
            $th[] = trans("Discount");
        }

        if ($isAssigneeColEnabled) {
            $th[] = trans("Assigned to");
        }

    } else {
        $th[] = trans("Price");
    }
   
    if ($dimension->is_trans_id_col_enabled) {
        $th[] = trans("Transaction ID");
    }

    if ($dimension->is_app_id_col_enabled) {
        $th[] = trans("Application ID");
    }
    
    if (!$isFromLabourContract && $dimension->is_passport_col_enabled) {
        $th[] = trans("Passport No");
    }

    if ($dimension->is_narration_col_enabled) {
        $th[] = trans("Narration");
    }

    $th[] = trans("Total");
    $th[] = "";

    if ($order->trans_no == 0) {
        unset($th[4 + intval($isLineRefColEnabled)]);
    }

    if (count($order->line_items))
        $th[] = '';

    table_header($th);

    $total = 0;
    $k = 0;  //row colour counter

    $discount_total  = 0;
    $referenceOffset = 0;

    $id = find_submit('Edit');

    $low_stock = $order->check_qoh($_POST['OrderDate'], $_POST['Location']);
    $lb_span = count($th);
    $kit_ref = null;
    foreach ($order->get_items() as $line_no => $stock_item) {
        if (
            $stock_item->item_code != $stock_item->stock_id
            && $stock_item->kit_ref != $kit_ref
        ) {
            $kit_ref = $stock_item->kit_ref;
            $kit_desc = data_get(get_kit_props($stock_item->item_code), 'description');
            label_cell("$kit_ref - $kit_desc", "colspan='{$lb_span}'");
        }

        $line_total = round(
            (
                $stock_item->price
                + $stock_item->govt_fee
                + $stock_item->bank_service_charge
                + $stock_item->bank_service_charge_vat
                + $stock_item->extra_srv_chg
            ) * $stock_item->qty_dispatched,
            user_price_dec()
        );
        $discount_total += ($stock_item->discount_amount * $stock_item->qty_dispatched);

        $qoh_msg = '';
        if (!$editable_items || $id != $line_no) {

            if (in_array($stock_item->stock_id, $low_stock))
                start_row("class='stockmankobg'");    // notice low stock status
            else
                alt_table_row_color($k);

            $price = $stock_item->price;
            $govt_fee = $stock_item->govt_fee;
            $fine = $stock_item->fine;
            
            if ($isOtherFeeEditable) {
                $price -= $stock_item->pf_amount;
            }
            
            if ($isHavingSplitGovtFee) {
                $govt_fee -= $stock_item->split_govt_fee_amt;
            }
            
            if ($isServiceFeeCombined) {
                $govt_fee += $price;
            }

            if ($isReturnableAmountEditable) {
                $govt_fee -= $stock_item->returnable_amt;
            }

            if ($fine) {
                $govt_fee -= $fine;
            }

            label_cell($line_no + 1);

            if ($isLineRefColEnabled) {
                $line_ref = $stock_item->line_reference ?: MetaReference::getNext(
                    SalesOrderDetail::ORDER_LINE_ITEM,
                    null,
                    [
                        'date' => $order->document_date,
                        'dimension' => $order->dimension_id
                    ],
                    false,
                    $referenceOffset++
                );
                label_cell($line_ref);
            }

            view_stock_status_cell($stock_item->stock_id);

            label_cell($stock_item->item_description);
            $dec = get_qty_dec($stock_item->stock_id);
            qty_cell($stock_item->qty_dispatched, false, $dec);

            if ($order->trans_no != 0)
                qty_cell($stock_item->qty_done, false, $dec);

            if (!$isFromLabourContract) {
                if ($isGovtBankEditable) {
                    label_cell(empty($stock_item->govt_bank_account) ? 'N/A' : get_gl_account_name($stock_item->govt_bank_account));
                }
    
                amount_cell($govt_fee);
            
                if ($isHavingSplitGovtFee) {
                    amount_cell($stock_item->split_govt_fee_amt);
                }

                if ($isFineColEnabled) {
                    label_cell($fine);
                }

                if (!$isServiceFeeCombined) {
                    amount_cell($price);
                }

                if ($isOtherFeeEditable) {
                    amount_cell($stock_item->pf_amount);
                }

                amount_cell($stock_item->bank_service_charge + $stock_item->bank_service_charge_vat);

                if($isReturnableAmountEditable){
                    label_cell($stock_item->returnable_amt);
                }
                
                if ($isReturnableAccountEditable) {
                    label_cell($stock_item->returnable_to ? get_gl_account_name($stock_item->returnable_to) : 'N/A');
                }

                if ($isReceivableCommissionAmountEditable) {
                    label_cell($stock_item->receivable_commission_amount);
                }
                
                if ($isReceivableCommissionAccountEditable) {
                    label_cell($stock_item->receivable_commission_account ? get_gl_account_name($stock_item->receivable_commission_account) : 'N/A');
                }

                if ($isDiscountEditable) {
                    label_cell($stock_item->discount_amount);
                }
            
                if ($isAssigneeColEnabled) {
                    label_cell(data_get(User::find($stock_item->assignee_id), 'name', '--'));
                }
            } else {
                amount_cell($price);
            }

            if ($dimension->is_trans_id_col_enabled) {
                label_cell($stock_item->transaction_id);
            }

            if ($dimension->is_app_id_col_enabled) {
                label_cell($stock_item->application_id);
            }

            if (!$isFromLabourContract && $dimension->is_passport_col_enabled) {
                label_cell($stock_item->passport_no);
            }
            
            if ($dimension->is_narration_col_enabled) {
                label_cell($stock_item->ref_name);
            }

            amount_cell($line_total);

            if ($editable_items) {
                edit_button_cell("Edit$line_no", trans("Edit"), trans('Edit document line'));

                if ($stock_item->qty_expensed == 0) {
                    delete_button_cell("Delete$line_no", trans("Delete"), trans('Remove line from document'));
                }
            }
            end_row();
        } else {
            sales_order_item_controls($order, $k, $line_no);
        }

        $total += $line_total;
    }


    if ($id == -1 && $editable_items)
        sales_order_item_controls($order, $k);

    $colspan = count($th) - 2 - intval(count($order->line_items) > 0);
    if ($order->trans_no != 0)
        ++$colspan;
    start_row("style='display:none'");
    label_cell(trans("Shipping Charge"), "colspan=$colspan align=right");
    small_amount_cells(null, 'freight_cost', price_format(get_post('freight_cost', 0)));
    label_cell('', 'colspan=2');
    end_row();
    $display_sub_total = price_format($total + input_num('freight_cost'));
    label_row(trans("Sub-total"), $display_sub_total, "colspan=$colspan align=right", "align=right", 2);
    $taxes = $order->get_taxes(input_num('freight_cost'));

    $tax_total = display_edit_tax_items($taxes, $colspan, $order->tax_included, 2);

    // Show discount when making invoice
    if (user_check_access('SA_HIDEDISCOUNT')) {
        $discount_total = 0;
    }
    
    label_row(trans("Total Discount"), price_format($discount_total), "colspan=$colspan align=right", "align=right", 2);

    $grandTotal = round2($total + input_num('freight_cost') + $tax_total - $discount_total + $order->roundoff, user_price_dec());

    if (
        $GLOBALS['SysPrefs']->prefs['collect_processing_chg_frm_cust']
        && in_array($order->pay_type, ['PayNoWCC', 'PayCashAndCard', 'PayOnline'])
    ) {
        $creditCardCharge = $order->getProcessingFee();

        $grandTotal += $creditCardCharge;
        label_row(trans("Other Services"), price_format($creditCardCharge), "colspan=$colspan align=right",  "align=right", 2);
    }

    if (
        !isset($_POST['roundoff'])
        || ($GLOBALS['Ajax']->is_activated('roundoff') && !$order->is_editing)
    ) {
        $_POST['roundoff'] = price_format($order->roundoff);
    }

    $dimension->enable_round_off
        ? amount_row(trans("Round Off"), 'roundoff', null, "colspan=$colspan align=right", null, user_price_dec(), false, true)
        : hidden('roundoff');

    start_row();
    label_cells(trans("Amount Total"), price_format($grandTotal), "colspan=$colspan align=right", "align=right");
    //submit_cells('update', trans("Update"), "colspan=2 align='center'", trans("Refresh"), true);
    end_row();

    end_table();
    if ($low_stock)
        display_note(trans("Marked items have insufficient quantities in stock as on day of delivery."), 0, 1, "class='stockmankofg'");

    div_end();

    div_start();
    start_table(TABLESTYLE, "width='90%'");
    textarea_row(trans("Comments:"), "Comments", $order->Comments, 31, 5, false);
    end_table();
    div_end();


    set_focus('transaction_id');

}

// ------------------------------------------------------------------------------

/**
 * Display the header part of the UI
 *
 * @param Cart $order
 * @param boolean $editable
 * @param string $date_text
 * @return string
 */
function display_order_header(&$order, $editable, $date_text)
{
    global $Ajax, $SysPrefs, $path_to_root;

    if ($editable && empty($_POST['OrderDate'])) {
        $_POST['OrderDate'] = $order->document_date;
    }

    start_outer_table(TABLESTYLE2, "width='80%' id='header_table'");

    $table_section = 1;
    table_section($table_section++);

    $customer_error = "";
    $other_errors = "";
    $change_prices = 0;

    $dimension = $order->getDimension();
    $is_token_required = $dimension->isTokenRequired();
    $is_fields_readonly = !$order->is_editing && (
        $is_token_required
        || (
            trim($_POST['token_no']) !== ''
            && !preg_match(UAE_MOBILE_NO_PATTERN, $_POST['token_no'])
        )
        || !$order->dimension_id
    );

    // Validate the token
    if (true !== ($msg = $dimension->validateToken($_POST['token_no']))) {
        $other_errors = $msg;
    }

    $token_check_url = "$path_to_root/sales/sales_order_entry.php?NewInvoice=0&dim_id=" . $order->dimension_id;
    $token_after_html = "";
    $token_params = "";

    if($dimension->has_service_request) {
        $token_after_html = "<a id='srv_req_list_btn' type='button' "
            . "class='btn btn-sm btn-success' style='color: white' href='#' data-href='$token_check_url'>FIND</a>";
    }

    if ($order->sales_type != $_POST['sales_type']) {
        $myrow = get_sales_type($_POST['sales_type']);
        $order->set_sales_type(
            $myrow['id'],
            $myrow['sales_type'],
            $myrow['tax_included'],
            $myrow['factor']
        );
        $Ajax->activate('sales_type');
        $change_prices = 1;
    }

    if (isset($order) && !$editable) {
        label_row(null, $order->customer_name . " - " . $order->deliver_to);
        hidden('customer_id', $order->customer_id);
        hidden('branch_id', $order->Branch);
        hidden('sales_type', $order->sales_type);
        if ($order->trans_type != ST_SALESQUOTE) {
            hidden('dimension_id', $order->dimension_id); // 2008-11-12 Joe Hunt
            hidden('dimension2_id', $order->dimension2_id);
        }
    } else {
        if(!empty($_POST['token_no']) && !$order->is_editing) {
            $token_customer = get_customer_by_token($_POST['token_no'], ($_POST['OrderDate'] ?? Today()), !$is_token_required, false);
            if (!empty($token_customer)) {
                $_POST['customer_id'] = $token_customer['customer_id'];
            } else if (!$is_token_required) {
                display_warning("Cant find this Token/Mobile, Please continue...");
                $order->token_number = $_POST['token_no'] = '';
                $Ajax->activate('token_no');
            }
        }

        // Select the default branch of the customer.
        // The user don't need to know about this feature. We are no longer using this field
        $defaultBranch = get_default_branch($_POST['customer_id']);
        $_POST['branch_id'] = $defaultBranch['branch_code'];

        // check if the customer has changed and update the UI for
        // related fields
        if ($order->customer_id != get_post('customer_id', -1)) {
            $Ajax->activate('branch_id');
            $Ajax->activate('customer_email');
            $Ajax->activate('customer_mobile');
            $Ajax->activate('contact_person');
            $Ajax->activate('customer_trn');
            $Ajax->activate('customer_ref');
            $Ajax->activate('mistook_staff_id');
        }

        // Update the relavent fields if
        // a. the user reselects the customer,
        // b. the user reselects the branch
        // c. the user changes the token
        if (
            ($order->customer_id != get_post('customer_id', -1))
            || ($order->Branch != get_post('branch_id', -1))
            || list_updated('customer_id')
            || input_changed('token_no')
        ) {
            // Check if there is a valid branch
            if (empty($_POST['branch_id'])) {
                // ignore errors on customer search box call
                if ($_POST['customer_id'] == '')
                    $customer_error = trans("No customer found for entered text.");
                else
                    $customer_error = trans("The selected customer does not have any branches. Please create at least one branch.");
                unset($_POST['branch_id']);
                $order->Branch = 0;
            } else {
                $old_order = (PHP_VERSION < 5) ? $order : clone $order;

                $is_walkin_customer = $_POST['customer_id'] == Customer::WALK_IN_CUSTOMER;
                if ((($is_token_required && $dimension->is_service_request_required) || !empty($order->service_req_id)) && !$order->is_editing) {
                    // Dont't allow for any change
                    reset_display_inputs($order->customer_name, $order->phone, 
                        $order->email,$order->tax_id, $order->cust_ref, $order->contact_person);
                } else if (!empty($token_customer) && !$order->is_editing) {
                    // Dont't allow for any change
                    reset_display_inputs($token_customer['display_customer'], $token_customer['customer_mobile'],
                        $token_customer['customer_email'], $token_customer['customer_trn'],
                        $token_customer['customer_ref'], $token_customer['contact_person']);
                } else if ($is_walkin_customer && !$order->is_editing) {
                    // reset
                    reset_display_inputs();
                } else if ($is_walkin_customer) {
                    // reset to the current values
                    reset_display_inputs($order->customer_name, $order->phone, 
                        $order->email,$order->tax_id, $order->cust_ref, $order->contact_person);
                } else {
                    $customer = get_customer_to_order($_POST['customer_id']);

                    reset_display_inputs($customer['name'], $customer['mobile'],
                        $customer['debtor_email'], $customer['tax_id'], $customer['iban_no'],
                        $customer['contact_person']);
                }

                $customer_error = get_customer_details_to_order(
                    $order,
                    $_POST['customer_id'],
                    $_POST['branch_id'],
                    $_POST['display_customer'],
                    $_POST['customer_mobile'],
                    $_POST['customer_email'],
                    $_POST['customer_trn'],
                    $_POST['customer_ref'],
                    $_POST['contact_person'],
                );
                
                $_POST['Location'] = $order->Location;
                $_POST['deliver_to'] = $order->deliver_to;
                $_POST['delivery_address'] = $order->delivery_address;
                $_POST['phone'] = $order->phone;
                $_POST['delivery_date'] = $order->due_date;

                if (!in_array($order->trans_type, array(ST_SALESQUOTE, ST_SALESORDER))
                    && ($order->pos['cash_sale'] != $order->pos['credit_sale'])
                    && (($order->payment_terms['cash_sale'] && !$order->pos['cash_sale']) ||
                        (!$order->payment_terms['cash_sale'] && !$order->pos['credit_sale']))) {
                    // force payment terms refresh if terms are editable
                    // and pos have no permitions for terms selected in customer record.
                    // Terms are set to first terms in allowed category below.
                    display_warning(
                        sprintf(trans("Customer's payment terms '%s' cannot be selected on this POS"),
                            $order->payment_terms['terms']));
                    $order->payment = '';
                } elseif (get_post('payment') !== $order->payment) {
                    $_POST['payment'] = $order->payment;
                    $Ajax->activate('delivery');
                    $Ajax->activate('payment');
                } else {
                    if ($order->trans_type == ST_SALESINVOICE) {
                        $_POST['delivery_date'] = $order->due_date;
                        $Ajax->activate('delivery_date');
                    }
                    $Ajax->activate('Location');
                    $Ajax->activate('deliver_to');
                    $Ajax->activate('phone');
                    $Ajax->activate('delivery_address');
                }
                // change prices if necessary
                // what about discount in template case?
                if ($old_order->customer_currency != $order->customer_currency) {
                    $change_prices = 1;
                }
                if ($old_order->sales_type != $order->sales_type) {
                    //  || $old_order->default_discount!=$order->default_discount
                    $_POST['sales_type'] = $order->sales_type;
                    $Ajax->activate('sales_type');
                    $change_prices = 1;
                }
                if ($old_order->dimension_id != $order->dimension_id) {
                    $_POST['dimension_id'] = $order->dimension_id;
                    $Ajax->activate('dimension_id');
                }
                if ($old_order->dimension2_id != $order->dimension2_id) {
                    $_POST['dimension2_id'] = $order->dimension2_id;
                    $Ajax->activate('dimension2_id');
                }

                /**
                 * If there is no display customer set,
                 * then set display customer to the first subcustomer by default,
                 * 
                 * If there is no subcustomer,
                 * then set the customer name itself as display_customer.
                 */
                if(
                    empty($order->customer_name)
                    && !empty($order->customer_id)
                    && $order->customer_id != Customer::WALK_IN_CUSTOMER
                ) {
                    $_subCustomers = array_slice(get_sub_customers($order->customer_id), 1);

                    $_POST['display_customer']
                        = $order->customer_name
                        = empty($_subCustomers) ? get_customer_name($order->customer_id) : reset($_subCustomers);

                    $Ajax->activate('display_customer');
                }

                unset($old_order);
            }

            $Ajax->activate('discount_amount');
            set_global_customer($_POST['customer_id']);

        }
        
        // Make sure the customer is not on hold
        $row = get_customer_to_order($_POST['customer_id']);
        if (!empty($row)) {
            if ($row['dissallow_invoices'] == 1)
                $customer_error = trans("The selected customer account is currently on hold. Please contact the credit control personnel to discuss.");
            else if ($row['credit_status'] == CreditStatus::IN_LIQUIDATION)
                $_POST['credit_status'] = "Selected Customer is not allowed for credit invoices";
        }
            

        // If this is a staff mistake and staff is selected, update his name
        if (list_updated('mistook_staff_id')) {
            $employee = [];
            if (!empty($_POST['mistook_staff_id'])) {
                $employee = getEmployee($_POST['mistook_staff_id']);
            };

            $order->contact_person = empty($employee) ? '' : $employee['name'];
            $_POST['contact_person'] = $order->contact_person;
            $Ajax->activate('contact_person');
        }

        if (!$order->is_editing) {
            $customer_error .= $other_errors;
        }
    }

    if(list_updated('invoice_payment')) {
        $Ajax->activate('payment_account');
        $Ajax->activate('credit_card_no');
        $Ajax->activate('payment_ref');
        $Ajax->activate('auth_code');
        set_focus('invoice_payment');
    }

    if (list_updated('govt_fee_pay_method')) {
        $Ajax->activate('govt_fee_pay_account');
    }

    if (in_ajax()) {
        $Ajax->addScript(true, "togglePaymentMethod();");
    }

    if(
        !$order->isFromLabourContract() &&
        $dimension->isHavingTokenFilter() &&
        !$order->is_editing &&
        $editable
    ) {
        text_row_ex(trans("Token/Mobile:"), "token_no", 30, 30, null, null,
            "'", user_check_access('SA_MKINVFRMSRVREQ') ? $token_after_html : '', true);
    } else {
        hidden('token_no');
    }

    hidden('ST_KEY');
    hidden('ST_VALUE');
    hidden('is_dim_through_url');
    hidden('trans_type', $order->trans_type);
    hidden('is_editing_invoice', intval(boolval($order->is_editing)));

    ref_row(_("Reference").':', 'ref', _('Reference number unique for this document type'), null, '',
        $order->trans_type, array('date'=> @$_POST['OrderDate'], 'dimension' => $dimension));

    if ($editable) {
        if (user_check_access('SA_SALESINVOICEDATEMOD')) {
            date_row($date_text, 'OrderDate', trans('Date of order receive'),
                $order->trans_no == 0, 0, 0, 0, null, true);
        } else {
            start_row();
            label_cell($date_text,"class='label'");
            label_cell($_POST['OrderDate']);
            end_row();
            hidden('OrderDate', $_POST['OrderDate']);
        }

        if (isset($_POST['_OrderDate_changed']) || list_updated('payment')) {
            if (!is_company_currency($order->customer_currency)
                && (get_base_sales_type() > 0)) {
                $change_prices = 1;
            }
            $Ajax->activate('_ex_rate');
            if ($order->trans_type == ST_SALESINVOICE) {
                $_POST['delivery_date'] = get_invoice_duedate(get_post('payment'), get_post('OrderDate'));
            } else
                $_POST['delivery_date'] = add_days(get_post('OrderDate'), $SysPrefs->default_delivery_required_by());
            $Ajax->activate('items_table');
            $Ajax->activate('delivery_date');
        }
    } else {
        label_row($date_text, $order->document_date);
        hidden('OrderDate', $order->document_date);
    }

    pref('axispro.enable_iban_no_column', 0)
        ? text_row(trans("IBAN") . ":", "customer_ref", $order->cust_ref, 30, 30)
        : hidden("customer_ref", $order->cust_ref);

    if ($order->trans_type != ST_SALESQUOTE) {    // 2008-11-12 Joe Hunt added dimensions
        $dim = get_company_pref('use_dimension');
        
        $is_dim1_readonly = (
            get_post('is_dim_through_url')
            || count($_SESSION['Items']->line_items) > 0
        );
        dimensions_list_row(trans("Dimension:"), 'dimension_id', null, true, "-- Select Dimension --", false, 1, true, false, null, $is_dim1_readonly);
        if ($dim > 1)
            dimensions_list_row(trans("Dimension") . " 2:", 'dimension2_id',
                null, true, ' ', false, 2, false);
        else
            hidden('dimension2_id', 0);
    }

    // No longer used
    hidden('invoice_type', 'Cash'); // Cash | Card
    hidden('customer_type', '');

    // Do not let to change the customer if the customer is being selected by a token
    // Otherwise there would be inconsistancy between the customer who is issued token
    // and the customer who is invoiced using the token. If they want to change the
    // customer, let them reset the token first.
    if (
        $order->isFromLabourContract()
        || (
            !$order->is_editing &&
            (
                trim($_POST['token_no']) !== ''
                || ($dimension->is_service_request_required && $is_token_required)
                || !empty($order->service_req_id)
            )
        )
    ) {
        $helpTitle = "title='Selected from token/service request {$_POST['token_no']}'";
        label_row("Customer", Customer::find($_POST['customer_id'])->formatted_name, $helpTitle, $helpTitle);
        hidden('customer_id');
    } else {
        customer_list_row(trans("Customer:"), 'customer_id', null, '-Select a Customer-',
            true, false, true);
    }

    hidden('branch_id', $_POST['branch_id']);

    // If the selected customer is a staff_mistake customer,
    // Show the list of employees
    $staff_mistake = $GLOBALS['SysPrefs']->prefs['staff_mistake_customer_id'];
    if (get_post('customer_id') && get_post('customer_id') == $staff_mistake) {
        array_selector_row(
            trans('Staff'),
            'mistook_staff_id',
            null,
            array_column(
                getEmployeesKeyedById(),
                'formatted_name',
                'id'
            ),
            [
                'select_submit' => true,
                'disabled' => null,
                'id' => 'mistook_staff_id',
                'spec_option' => 'Select a staff'
            ]
        );
    }

    if (!empty($_POST['credit_status'])) {
        $Ajax->activate('credit_status');
        start_row();
            echo '<td colspan="2">';
                div_start('credit_status', null, false, 'warn_msg text-red');
                echo $_POST['credit_status'];
                div_end();
            echo '</td>';
        end_row();
    }

    if ($order->isFromLabourContract()) {
        $contract = $_SESSION['Items']->contract;

        if (empty($_POST['period_from'])) {
            [$period_from, $period_till] = $contract->getNextPeriod(); 
            $_POST['period_from'] = sql2date($period_from);
            $_POST['period_till'] = sql2date($period_till);
        }

        date_row('Period from', 'period_from', 'This invoice is for the period from');
        date_row('Period till', 'period_till', 'This invoice is for the period till');
        table_section($table_section++);
        label_row('Maid', Labour::whereId($contract->labour_id)->value('name'));
        label_row('Contract Period', sql2date($contract->contract_from) .' till '. sql2date($contract->contract_till));
        label_row('Contract Amount', price_format($contract->order->total));
        label_row('Total Invoiced', price_format($contract->getTotalInvoicedAmount()));
        label_row('Category', labour_invoice_categories()[$contract->category_id]);
    }

    if ($dimension->enable_govt_fee_pmt_method) {
        array_selector_row(
            trans('Card Type'),
            'govt_fee_pay_method',
            null,
            [
                'CustomerCard' => 'Customer Card',
                'CenterCard' => 'Center Card'
            ],
            array(
                'spec_option' => '-- select --',
                'spec_id' => '',
                'select_submit' => true,
                'disabled' => null,
                'id' => 'govt_fee_pay_method',
                'async' => true
            )
        );

        $gov_pay_accounts = build_govt_fee_pay_accounts($order, $dimension, get_post('govt_fee_pay_method'));

        if (
            list_updated('govt_fee_pay_method')
            || (
                get_post('govt_fee_pay_account')
                && !isset($gov_pay_accounts[get_post('govt_fee_pay_account')])
            )
        ) {
            $_POST['govt_fee_pay_account'] = '';
        }

        if (count($gov_pay_accounts) == 1) {
            $_POST['govt_fee_pay_account'] = array_key_first($gov_pay_accounts);
        }

        array_selector_row(
            'Card Account',
            'govt_fee_pay_account',
            null,
            $gov_pay_accounts,
            [
                'spec_option' => '-- choose --',
                'spec_id' => '',
                'select_submit' => true,
                'async' => true
            ]
        );
    }

    else {
        hidden('govt_fee_pay_method');
        hidden('govt_fee_pay_account');
    }

    table_section($table_section++);
    
    $subCustomers = DB::table('0_sub_customers')->where('name', '!=', ' ')->whereCustomerId(get_post('customer_id'))->pluck('name', 'id')->all();
    text_row(trans("Contact Person:"), "contact_person", $order->contact_person, 30, 30,
        null, '', '', '', $is_fields_readonly);
    editable_select_row(trans("Display Customer"), "display_customer", $order->customer_name, $subCustomers, 25, null, $is_fields_readonly, true);
    text_row(trans("Mobile:"), "customer_mobile", $order->phone, 30, 30,
        null, '', '', '', $is_fields_readonly);
    text_row(trans("Email:"), "customer_email", $order->email, 30, 60,
        null, '', '', '', $is_fields_readonly);
    text_row(trans("Customer TRN").":",'customer_trn',$order->tax_id,30,30,
        null, '', '', '', $is_fields_readonly);

    $systemPaymentMethods = $GLOBALS['global_pay_types_array'];
    $userRoleEnabledPaymentMethods  = explode(',', authUser()->role->enabled_payment_methods);
    $dimensionEnabledPaymentMethods = explode(',', data_get($dimension, 'enabled_payment_methods', ''));
    $enabledPaymentMethods =  array_intersect($userRoleEnabledPaymentMethods, $dimensionEnabledPaymentMethods);
    // Use array_intersect_key to keep only the common elements from $systemPaymentMethods
    $availablePaymentMethods = array_intersect_key($systemPaymentMethods, array_flip($enabledPaymentMethods));
    #Edit
    if ($_SESSION['Items']->is_editing && $_SESSION['Items']->original_pay_type && !isset($availablePaymentMethods[$_SESSION['Items']->original_pay_type])) {
        $availablePaymentMethods[$_SESSION['Items']->original_pay_type] = $systemPaymentMethods[$_SESSION['Items']->original_pay_type];
    }
    
    if(count($availablePaymentMethods) == 0 && $order->dimension_id) {
        $customer_error .= "<br> There Is No Payment Methods Configured !";
    }

    if($dimension->is_payment_separate && isset($availablePaymentMethods['PayLater'])) {
        $availablePaymentMethods['PayLater'] = "Pay at Cashier";
    }
    
    if (
        (!($paymentMethod = get_post('invoice_payment')) || !isset($availablePaymentMethods[$paymentMethod]))
        && isset($availablePaymentMethods[$dimension->dflt_payment_method])
    ) {
        $_POST['invoice_payment'] = $dimension->dflt_payment_method;
    }

    if (count($availablePaymentMethods) == 1) {
        $order->pay_type = $_POST['invoice_payment'] = array_keys($availablePaymentMethods)[0];
        hidden('invoice_payment');
    } else {
        array_selector_row(
            trans('Payment'),
            'invoice_payment',
            null,
            $availablePaymentMethods,
            array(
                'spec_option' => '**Choose a payment method**',
                'spec_id' => '',
                'select_submit' => true,
                'disabled' => null,
                'id' => 'invoice_payment',
                'async' => true
            )
        );
    }

    $paymentAccounts = [];
    $paymentMethod = $GLOBALS['global_pay_types_array'][$_POST['invoice_payment'] ?? -1] ?? -1;
    if ($paymentMethod == 'Split') {
        $paymentMethod = 'CreditCard';
    }
    if (!empty($paymentMethod) && !empty($accounts = get_payment_accounts($paymentMethod, null, $dimension))) {
        $paymentAccounts = BankAccount::whereIn('id', $accounts)->pluck('bank_account_name', 'id')->toArray();
    }

    if ($order->is_editing && $order->original_payment_account && !isset($paymentAccounts[$order->original_payment_account])) {
        $paymentAccounts[$order->original_payment_account] = BankAccount::whereId($order->original_payment_account)->value('bank_account_name');
    }

    if (count($paymentAccounts) == 1) {
        $_POST['payment_account'] = Arr::first(array_keys($paymentAccounts));
    }

    if (get_post('payment_account') && !isset($paymentAccounts[get_post('payment_account')])) {
        $_POST['payment_account'] = '';
    }

    array_selector_row(
        'Payment Account',
        'payment_account',
        null,
        $paymentAccounts,
        [
            'spec_option' => '-- Choose an account --',
            'spec_id' => '',
            'select_submit' => true,
            'async' => (
                isset($availablePaymentMethods['PayByCenterCard'])
                || isset($availablePaymentMethods['PayByCustomerCard'])
            )
        ]
    );

    text_row('Credit Card No.(Last 4 Digit)', 'credit_card_no', null, 4, 4);
    pref('axispro.req_auth_code_4_cc_pmt', 0)
        ? text_row('Authorization code', 'auth_code', null, 14, 60)
        : hidden('auth_code');
    text_row('Pmt. Ref', 'payment_ref', null, 14, 60);

    if (!is_company_currency($order->customer_currency) && in_array($order->trans_type, array(ST_SALESINVOICE, ST_CUSTDELIVERY))) {
        label_row(trans("Customer Currency:"), $order->customer_currency);
        exchange_rate_display(get_company_currency(), $order->customer_currency,
            ($editable ? $_POST['OrderDate'] : $order->document_date));
    }

    if ($_POST['customer_id'] != Customer::WALK_IN_CUSTOMER) {
        customer_credit_row($_POST['customer_id'], $order->credit);
        if (!is_null($order->credit_days)) {
            label_row(
                trans('Remaining Credit Days'),
                $order->credit_days,
                "",
                'class="'. ($order->credit_days > 0 ? 'text-primary' : 'redfg') .'"'
            );
        }
    }


    table_section($table_section++, false, $dimension->is_payment_separate ? 'class="d-none"' : '');
    start_row("style=display:none");
    if (($order->pos['cash_sale'] || $order->pos['credit_sale'])
        && !$order->is_started()) {
        // editable payment type
        if (isset($_POST['payment']) && $_POST['payment'] !== $order->payment) {
            $order->payment = get_post('payment');
            $order->payment_terms = get_payment_terms($order->payment);
            $order->due_date = get_invoice_duedate($order->payment, $order->document_date);
            if ($order->payment_terms['cash_sale']) {
                $_POST['Location'] = $order->Location = $order->pos['pos_location'];
                $order->location_name = $order->pos['location_name'];
            }
            $Ajax->activate('items_table');
            $Ajax->activate('delivery');
        }

        $paymcat = !$order->pos['cash_sale'] ? PM_CREDIT :
            (!$order->pos['credit_sale'] ? PM_CASH : PM_ANY);
        // all terms are available for SO
        $is_prepayment_allowed = (
            in_array($order->trans_type, array(ST_SALESQUOTE, ST_SALESORDER))
            || ($order->trans_type == ST_SALESINVOICE && $order->isFromLabourContract())
        );
        sale_payment_list_cells(trans('Payment:'), 'payment', $is_prepayment_allowed ? PM_ANY : $paymcat, null, true, $is_prepayment_allowed);
    } else {
        label_cells(trans('Payment:'), $order->payment_terms['terms'], "class='label'");
    }
    end_row();

    if ($editable) {
        $str = sales_types_list_row(trans("Price List:"), 'sales_type', null, true);
    } else {
        label_row(trans("Price List:"), $order->sales_type_name);
    }

    small_amount_row(trans("Cash Amount") . ":", "cash_amount", null,"class='cash_amount w-100 label'");
    small_amount_row(trans("Card Amount") . ":", "card_amount", null,"class='cash_amount w-100 label'");
    if (get_company_pref('collect_processing_chg_frm_cust')) {
        small_amount_row(
            trans("Overall Card Amount") . ":",
            "ov_card_amount",
            null,
            null,
            null,
            null,
            true
        );
    }

    $_POST['total_amount'] = $order->get_cart_total();
    $Ajax->activate('total_amount');

    small_amount_row(
        trans("Total Amount") . ":",
        "total_amount",
        $_POST['total_amount']
    );
    if (get_company_pref('collect_processing_chg_frm_cust')) {
        $_POST['total_to_collect'] = $_POST['total_amount'] + $order->getProcessingFee();
        $Ajax->activate('total_to_collect');
        small_amount_row(
            trans("Total Amount to be collected") . ":",
            'total_to_collect',
            $_POST['total_to_collect'],
            'style="width: 200px; display:block;"',
            null,
            null,
            true
        );
    }
    small_amount_row(trans("Round of Amount") . ":", "round_of_amount", null);
    small_amount_row(trans("Given Amount") . ":", "given_amount", null);
    small_amount_row(trans("Balance") . ":", "balance_amount", null);

    end_outer_table(1); // outer table


    if ($change_prices != 0) {
        // foreach ($order->line_items as $line_no => $item) {
        //     $line = &$order->line_items[$line_no];
        //     $line->price = get_kit_price($line->stock_id, $order->customer_currency,
        //         $order->sales_type, $order->price_factor, get_post('OrderDate'));
        // }
        $Ajax->activate('items_table');
    }

    return $customer_error;
}

//--------------------------------------------------------------------------------

function sales_order_item_controls(&$order, &$rowcounter, $line_no = -1)
{
    global $Ajax, $SysPrefs;


    $dimension = $order->getDimension();
    $isHavingSplitGovtFee = $order->isHavingSplitGovtFee($dimension);
    $isOtherFeeEditable = $order->isOtherFeeEditable($dimension);
    $isServiceFeeCombined = $order->isServiceFeeCombined($dimension);
    $isReturnableAmountEditable = $order->isReturnableAmountEditable($dimension);
    $isReturnableAccountEditable = $order->isReturnableAccountEditable($dimension);
    $isDiscountEditable = $order->isDiscountEditable($dimension);
    $isFromLabourContract = $order->isFromLabourContract();
    $isReceivableCommissionAmountEditable = $order->isReceivableCommissionAmountEditable($dimension);
    $isReceivableCommissionAccountEditable = $order->isReceivableCommissionAccountEditable($dimension);
    $isFineColEnabled = $order->isFineColEnabled($dimension);
    $isLineRefColEnabled = $dimension->is_line_ref_col_enabled;
    $isAlreadyExpensed = false;
    $isAlreadyInvoiced = false;
    $isAlreadyCompleted = false;
    $isAssigneeColEnabled = $order->isAssigneeColEnabled($dimension);
    $card_account = $_SESSION['wa_current_user']->card_account;
    $accountCodes = ['-1'];

    if ($card_account && ($card_account_code = get_bank_gl_account($card_account))) {
        $accountCodes[] = $card_account_code;
    }

    if (
        get_post('govt_fee_pay_method')
        && get_post('govt_fee_pay_account')
        && ($govt_fee_pay_account_code = get_bank_gl_account(get_post('govt_fee_pay_account')))
    ) {
        $accountCodes[] = $govt_fee_pay_account_code;
    }

    if (
        get_post('invoice_payment')
        && get_post('payment_account')
        && in_array(get_post('invoice_payment'), ['PayByCenterCard', 'PayByCustomerCard'])
        && ($payment_account_code = get_bank_gl_account(get_post('payment_account')))
    ) {
        $accountCodes[] = $payment_account_code;
    }

    //IF SERVICE REQUEST FLOW IS ENABLED IN THIS COST CENTER, LINE ITEM CONTROLS ARE ENABLED ELSE HIDDEN
    if ($dimension->is_service_request_required == 1 && $order->is_editing == false)
        return false;

    $id = find_submit('Edit');

    // If invoicing labour contract, Only one line item is allowed
    if ($isFromLabourContract && count($order->line_items) && ($line_no == -1 || $line_no != $id)) {
        return false;
    }
    
    alt_table_row_color($rowcounter);

    label_cell($line_no != -1 ? ($line_no + 1) : (count($order->line_items) + 1));

    if ($line_no != -1 && $line_no == $id) // edit old line
    {
        if ($order->line_items[$id]->qty_expensed != 0) {
            $isAlreadyExpensed = true;
        }
        
        if ($order->line_items[$id]->qty_invoiced != 0) {
            $isAlreadyInvoiced = true;
        }
        
        if ($order->line_items[$id]->qty_done != 0) {
            $isAlreadyCompleted = true;
        }

        $_POST['stock_id'] = $order->line_items[$id]->stock_id;
        $dec = get_qty_dec($_POST['stock_id']);
        $_POST['qty'] = number_format2($order->line_items[$id]->qty_dispatched, $dec);
        $_POST['price'] = $order->line_items[$id]->price;
        $_POST['govt_fee'] = $order->line_items[$id]->govt_fee;

        if ($isOtherFeeEditable) {
            $_POST['price'] -= $order->line_items[$id]->pf_amount;
        }

        if ($isFineColEnabled) {
            $_POST['govt_fee'] -= $order->line_items[$id]->fine;
        }

        if ($isHavingSplitGovtFee) {
            $_POST['govt_fee'] -= $order->line_items[$id]->split_govt_fee_amt;
        }

        if($isReturnableAmountEditable){
            $_POST['govt_fee'] -= $order->line_items[$id]->returnable_amt;
        }

        if ($isServiceFeeCombined) {
            $_POST['govt_fee'] += $_POST['price'];
        }

        $_POST['price'] = price_format($_POST['price']);
        $_POST['Disc'] = percent_format($order->line_items[$id]->discount_percent * 100);
        $units = $order->line_items[$id]->units;
        $_POST['item_description'] = $order->line_items[$id]->item_description;

        $item_info = get_item($_POST['stock_id']);

        $_POST['fine'] = $order->line_items[$id]->fine;
        $_POST['bank_service_charge'] = $order->line_items[$id]->bank_service_charge;
        $_POST['bank_service_charge_vat'] = $order->line_items[$id]->bank_service_charge_vat;
        $_POST['discount_amount'] = $order->line_items[$id]->discount_amount;
        $_POST['transaction_id'] = $order->line_items[$id]->transaction_id;
        $_POST['application_id'] = $order->line_items[$id]->application_id;
        $_POST['ref_name'] = $order->line_items[$id]->ref_name;
        $_POST['govt_bank_account'] = $order->line_items[$id]->govt_bank_account;
        $_POST['returnable_amt'] = $order->line_items[$id]->returnable_amt;
        $_POST['returnable_to'] = $order->line_items[$id]->returnable_to;
        $_POST['split_govt_fee_amt'] = $order->line_items[$id]->split_govt_fee_amt;
        $_POST['split_govt_fee_acc'] = $order->line_items[$id]->split_govt_fee_acc;
        $_POST['pf_amount'] = $order->line_items[$id]->pf_amount;
        $_POST['category_id'] = $item_info['category_id'];
        $_POST['passport_no'] = $order->line_items[$id]->passport_no;
        $_POST['extra_srv_chg'] = $order->line_items[$id]->extra_srv_chg;
        $_POST['receivable_commission_amount'] = $order->line_items[$id]->receivable_commission_amount;
        $_POST['receivable_commission_account'] = $order->line_items[$id]->receivable_commission_account;
        $_POST['transaction_id_updated_at'] = $order->line_items[$id]->transaction_id_updated_at;
        $_POST['transaction_id_updated_by'] = $order->line_items[$id]->transaction_id_updated_by;
        $_POST['line_reference'] = $order->line_items[$id]->line_reference;
        $_POST['qty_expensed'] = $order->line_items[$id]->qty_expensed;
        $_POST['assignee_id'] = $order->line_items[$id]->assignee_id;

        if ($isLineRefColEnabled) {
            label_cell($_POST['line_reference']);
        }

        $category = StockCategory::find($_POST['category_id']);
        hidden('stock_id', $_POST['stock_id']);
        label_cell($_POST['stock_id']);
        if ($order->line_items[$id]->descr_editable)
            text_cells(null, 'item_description', null, 45, 50);
        else {
            hidden('item_description', $_POST['item_description']);
            label_cell($_POST['item_description']);
        }
        $Ajax->activate('items_table');
    } else    // prepare new line
    {
        //Category Permission
        $user = get_user($_SESSION["wa_current_user"]->user);
        $permitted_categories = "0";
        if (!empty($user["permitted_categories"]))
        $permitted_categories = $user["permitted_categories"];
        
        $opts = [
            "where" => [
                "c.category_id in ($permitted_categories)",
                "json_contains(c.belongs_to_dep, json_quote('{$order->dimension_id}'))"
            ]
        ];

        if ($isFromLabourContract) {
            $contract = $order->contract;
            $opts['where'][] = "c.category_id = ".db_escape($order->contract->category_id);
            $opts['where'][] = "s.stock_id = ".db_escape($contract->stock_id);
        }

        if (empty($_POST['stock_id'])) {
            $_POST['stock_id'] = $isFromLabourContract
                ? $contract->stock_id
                : last_sales_order_detail($order, 'stock_id');
        }

        if ($order->trans_type == ST_SALESINVOICE) {
            $opts['where'][] = "s.costing_method != " . COSTING_METHOD_EXPENSE;
        }

        if ($isLineRefColEnabled) {
            label_cell(MetaReference::getNext(
                SalesOrderDetail::ORDER_LINE_ITEM,
                null,
                [
                    'date' => $order->document_date,
                    'dimension' => $order->dimension_id
                ],
                false,
                $order->count_non_saved_items()
            ));
        }
        
        if ($order->fixed_asset)
            stock_disposable_fa_list_cells(null, 'stock_id', null, trans('[Select item]'), true, $order->line_items);
        else {
            sales_items_list_cells(null, 'stock_id', null, "--", true, true, $opts);
        }
        
        if (list_updated('stock_id')) {
            $Ajax->activate('price');
            $Ajax->activate('units');
            $Ajax->activate('qty');
            $Ajax->activate('line_total');
            $Ajax->activate('govt_fee');
            $Ajax->activate('bank_service_charge');
            $Ajax->activate('bank_service_charge_vat');
            $Ajax->activate('Disc');
            $Ajax->activate('discount_amount');
            $Ajax->activate('total_bank_charge');
            $Ajax->activate('govt_bank_account');
            $Ajax->activate('returnable_amt');
            $Ajax->activate('returnable_to');
            $Ajax->activate('split_govt_fee_amt');
            $Ajax->activate('split_govt_fee_acc');
            $Ajax->activate('pf_amount');
            $Ajax->activate('receivable_commission_amount');
            $Ajax->activate('receivable_commission_account');
            $Ajax->activate('fine');
        }

        $item_info = get_item_kit_info($_POST['stock_id'], $_POST['customer_id']);

        if (empty($item_info)) {
            $item_info = [
                'units' => '',
                'decimals' => 2,
                'pf_amount' => '0.00',
                'govt_fee' => '0.00',
                'split_govt_fee_amt' => '0.00',
                'split_govt_fee_acc' => '',
                'bank_service_charge' => '0.00',
                'bank_service_charge_vat' => '0.00',
                'returnable_amt' => '0.00',
                'returnable_to' => '',
                'category_id' => '',
                'govt_bank_account' => '',
                'discount' => '0.00',
                'extra_srv_chg' => '0.00',
                'receivable_commission_amount' => '0.00',
                'receivable_commission_account' => '',
                'fine' => 0
            ];
        }

        $units = $item_info["units"];
        $dec = $item_info['decimals'];

        $_POST['qty'] = number_format2(1, $dec);

        $_POST['price'] = $isFromLabourContract
            ? $contract->amount - $contract->getTotalInvoicedAmount($order->reference, $order->trans_type, true)
            : get_kit_price(
                $_POST['stock_id'],
                $order->customer_currency,
                $_POST['sales_type'],
                $order->price_factor,
                get_post('OrderDate'),
                true
            );
        $_POST['govt_fee'] = $item_info['govt_fee'];
        $_POST['extra_srv_chg'] = $item_info['extra_srv_chg'];

        if ($isOtherFeeEditable) {
            $_POST['price'] -= $item_info['pf_amount'];
        }

        if ($isFineColEnabled) {
            $_POST['govt_fee'] -= $item_info['fine'];
        }
        
        if ($isHavingSplitGovtFee) {
            $_POST['govt_fee'] -= $item_info['split_govt_fee_amt'];
        }

        if ($isReturnableAmountEditable){
            $_POST['govt_fee'] -= $item_info['returnable_amt'];
        }

        if ($isServiceFeeCombined) {
            $_POST['govt_fee'] += $_POST['price'];
        }

        $_POST['price'] = price_format($_POST['price']);
        $_POST['Disc'] = percent_format($order->default_discount * 100);
        $_POST['fine'] = $item_info['fine'];
        $_POST['bank_service_charge'] = $item_info['bank_service_charge'];
        $_POST['bank_service_charge_vat'] = $item_info['bank_service_charge_vat'];
        $_POST['returnable_amt'] = $item_info['returnable_amt'];
        $_POST['returnable_to'] = $item_info['returnable_to'];
        $_POST['split_govt_fee_amt'] = $item_info['split_govt_fee_amt'];
        $_POST['split_govt_fee_acc'] = $item_info['split_govt_fee_acc'];
        $_POST['pf_amount'] = $item_info['pf_amount'];
        $_POST['category_id'] = $item_info['category_id'];
        $_POST['receivable_commission_amount'] = $item_info['receivable_commission_amount'];
        $_POST['receivable_commission_account'] = $item_info['receivable_commission_account'];
        $_POST['assignee_id'] = '';

        $_POST['discount_amount'] = $item_info['discount'];
        $_POST['transaction_id'] = "";
        $_POST['application_id'] = "";
        $_POST['passport_no'] = '';
        $_POST['ref_name'] = "";
        $_POST['line_reference'] = "";
        $_POST['qty_expensed'] = 0;
        
        if (!empty($item_info['govt_bank_account'])) {
            $accountCodes[] = $item_info['govt_bank_account'];
        }

        $category = StockCategory::find($_POST['category_id']) ?: StockCategory::make();

        if (get_post('govt_fee_pay_method') && get_post('govt_fee_pay_account')) {
            $_POST['govt_bank_account'] = $govt_fee_pay_account_code;
        }
        else if (
            get_post('invoice_payment')
            && get_post('payment_account')
            && in_array(get_post('invoice_payment'), ['PayByCenterCard', 'PayByCustomerCard'])
        ) {
            $_POST['govt_bank_account'] = $payment_account_code;
        }
        else if ($card_account && $category->usr_sel_ac) {
            $_POST['govt_bank_account'] = $card_account_code;
        }
        else {
            $_POST['govt_bank_account'] = $item_info['govt_bank_account'];
        }

        $_POST['transaction_id_updated_at'] = '';
        $_POST['transaction_id_updated_by'] = '';
        
        $serviceCharge = input_num('price') + input_num('returnable_amt') + input_num('receivable_commission_amount') - input_num('pf_amount');
        if (Cart::is_discount_applicable($item_info['discount'], $serviceCharge, $_POST['category_id'])) {
            $_POST['Disc'] = percent_format(($_POST['discount_amount'] * 100) / $serviceCharge);
        }

    }

    if ($order::isAutomaticBankChargeApplicable($_POST['category_id'], $_POST['stock_id'])) {
        $_POST['bank_service_charge'] = '0.00';
        $_POST['bank_service_charge_vat'] = '0.00';
    }

    $hasSubDocuments = $isAlreadyCompleted || $isAlreadyExpensed || $isAlreadyInvoiced;

    if ($order->fixed_asset) {
        label_cell(1, '', 'qty');
        hidden('qty', 1);
    } else
        qty_cells(null, 'qty', $_POST['qty'], null, null, $dec, $hasSubDocuments);

    if ($order->trans_no != 0) {
        qty_cell($line_no == -1 ? 0 : $order->line_items[$line_no]->qty_done, false, $dec);

    }

    $isGovtAccountEditable = $category->govt_bnk_editable || user_check_access('SA_GOVBNKACTEDITABLE');

    if ($category->govt_bank_accounts) {
        $accountCodes = array_merge($accountCodes, explode(',', $category->govt_bank_accounts));
    }

    if (!empty(get_post('govt_bank_account')) && !in_array(get_post('govt_bank_account'), $accountCodes)) {
        $accountCodes[] = get_post('govt_bank_account');
    }

    $isFromLabourContract || !$dimension->is_govt_bank_editable
        ? hidden('govt_bank_account')
        : gl_all_accounts_list_cells("", 'govt_bank_account', $_POST['govt_bank_account'], false, false,
            '-- no_govt_acc --', false, false, false, !$isGovtAccountEditable, false,
            ['where' => array("account_code in (".implode(',', array_filter($accountCodes)).")")]
        );

    $isGovtFeeEditable = (
        $category->is_govt_fee_editable
        && !$hasSubDocuments
    );

    $isFromLabourContract
        ? hidden('govt_fee')
        : small_amount_cells(null, 'govt_fee', null, null, "<a id='myBtn' class='gvt_btn' href='#'></a>", null, !$isGovtFeeEditable);
    
    ($isHavingSplitGovtFee && !$isFromLabourContract)
        ? small_amount_cells(null, 'split_govt_fee_amt', null, null, null, null, $hasSubDocuments)
        : hidden('split_govt_fee_amt');

    $isServiceChrgEditable = (
        $category->is_srv_chrg_editable
        && !$hasSubDocuments
    );

    ($isFineColEnabled && !$isFromLabourContract)
        ? small_amount_cells(null, 'fine', null, null, null, null, $hasSubDocuments)
        : hidden('fine');

    ($isServiceFeeCombined && !$isFromLabourContract)
        ? hidden('price')
        : small_amount_cells(null, 'price', null, null, null, null, !$isServiceChrgEditable);

    if (!$order->isExtraSrvChgApplicable($order->pay_type)) {
        $_POST['extra_srv_chg'] = '0.00';
    }

    $isFromLabourContract
        ? hidden('extra_srv_chg')
        : hidden('extra_srv_chg');

    ($isOtherFeeEditable && !$isFromLabourContract)
        ? small_amount_cells(null, 'pf_amount', null, null, null, null, $hasSubDocuments)
        : hidden('pf_amount');

    $_POST['total_bank_charge'] = $_POST['bank_service_charge'] + $_POST['bank_service_charge_vat'];
    $_POST['total_bank_charge'] = $_POST['total_bank_charge'] ? $_POST['total_bank_charge'] : '0.0';

    $isFromLabourContract
        ? hidden('total_bank_charge')
        : small_amount_cells(null, 'total_bank_charge', null, null, null, null, $hasSubDocuments);

    hidden('bank_service_charge', $_POST['bank_service_charge']);
    hidden('bank_service_charge_vat', $_POST['bank_service_charge_vat']);
    hidden('split_govt_fee_acc', null, true);
    hidden('category_id');
    hidden('transaction_id_updated_at');
    hidden('transaction_id_updated_by');
    hidden('line_reference');
    hidden('qty_expensed');
    hidden('Disc', percent_format($_POST['Disc']));

    $isReturnableAmountEditable
        ? small_amount_cells(null, 'returnable_amt')
        : hidden('returnable_amt', null, true);

    $isReturnableAccountEditable
        ? gl_all_accounts_list_cells("", 'returnable_to', $_POST['returnable_to'], false, false, '-- select --')
        : hidden('returnable_to', null, true);

    $isReceivableCommissionAmountEditable
        ? small_amount_cells(null, 'receivable_commission_amount')
        : hidden('receivable_commission_amount', null, true);

    $isReceivableCommissionAccountEditable
        ? gl_all_accounts_list_cells("", 'receivable_commission_account', $_POST['receivable_commission_account'], false, false, '-- select --')
        : hidden('receivable_commission_account', null, true);

    ($isDiscountEditable && !$isFromLabourContract)
        ? small_amount_cells(null, 'discount_amount')
        : hidden('discount_amount', $_POST['discount_amount']);
    
    ($isAssigneeColEnabled && !$isFromLabourContract)
        ? users_list_cells2(null, 'assignee_id', null, false, '-- select --')
        : hidden('assignee_id', $_POST['assignee_id']);

    !$dimension->is_trans_id_col_enabled
        ? hidden('transaction_id')
        : text_cells(null, 'transaction_id', null, 5, 50,
            false, "", "",
            "class='live_popover' autocomplete='off' data-placement='top'
            data-content='" . $_POST['transaction_id'] . "'  rel='next-popover'"
        );

    !$dimension->is_app_id_col_enabled
        ? hidden('application_id')
        : text_cells(null, 'application_id', null, 5, 50);
    
    !$isFromLabourContract && $dimension->is_passport_col_enabled
        ? text_cells(null, 'passport_no', null, 5, 50)
        : hidden('passport_no');

    !$dimension->is_narration_col_enabled
        ? hidden('ref_name')
        : text_cells(null, 'ref_name', null, 5, 50, false, "", "",
            "class='live_popover' autocomplete='off' data-placement='top' 
            data-content='" . $_POST['ref_name'] . "'  rel='next-popover'");

    $line_total = (
        (
            input_num('qty') * (
                input_num('price') +
                input_num('govt_fee') +
                input_num('bank_service_charge') +
                input_num('bank_service_charge_vat')
            )
        ) - (input_num('qty') * input_num('discount_amount'))
    );

    amount_cell('', false, '', 'line_total');

    if ($id != -1) {
        button_cell('UpdateItem', trans("Update"),
            trans('Confirm changes'), ICON_UPDATE);
        button_cell('CancelItemChanges', trans("Cancel"),
            trans('Cancel changes'), ICON_CANCEL);
        hidden('LineNo', $line_no);
        set_focus('qty');
    } else {
        submit_cells('AddItem', trans("Add Item"), "colspan=2 align='center'",
            trans('Add new item to document'), true);
    }

    end_row();

    set_focus('transaction_id');
}

//--------------------------------------------------------------------------------

function display_delivery_details(&$order)
{
    global $Ajax;

    div_start('delivery');

    if ($order->payment_terms['cash_sale']) {    // Direct payment sale
        $Ajax->activate('items_table');
        display_heading(trans('Cash payment'));
        start_table(TABLESTYLE2, "width='60%'");

        locations_list_row(trans("Deliver from Location:"), 'Location', null, false, true);
        if (list_updated('Location'))
            $Ajax->activate('items_table');
        label_row(trans("Cash account:"), $order->pos['bank_account_name']);
        end_table();
        hidden('delivery_date', $order->due_date);
    } else {
        if ($order->trans_type == ST_SALESINVOICE) {
            $title = trans("Delivery Details");
            $delname = trans("Due Date") . ':';
        } elseif ($order->trans_type == ST_CUSTDELIVERY) {
            $title = trans("Invoice Delivery Details");
            $delname = trans("Invoice before") . ':';
        } elseif ($order->trans_type == ST_SALESQUOTE) {
            $title = trans("Quotation Delivery Details");
            $delname = trans("Valid until") . ':';
        } else {
            $title = trans("Order Delivery Details");
            $delname = trans("Required Delivery Date") . ':';
        }
        display_heading($title);
        start_outer_table(TABLESTYLE2, "width='90%'");
        table_section(1);

        locations_list_row(trans("Deliver from Location:"), 'Location', null, false, true, $order->fixed_asset);
        if ($order->payment_terms['days_before_due'] == -1) {
            // bind to items_table update
            $Ajax->addUpdate('items_table', 'prep_amount', $order->trans_type == ST_SALESORDER ? 0 : price_format($order->get_cart_total()));
            if (!$order->is_started())
                amount_row(trans("Pre-Payment Required:"), 'prep_amount');
            else
                label_row(trans("Pre-Payment Required:"), price_format($order->prep_amount), "class='label'");
        }
        if (list_updated('Location'))
            $Ajax->activate('items_table');

        date_row($delname, 'delivery_date',
            $order->trans_type == ST_SALESORDER ? trans('Enter requested day of delivery')
                : ($order->trans_type == ST_SALESQUOTE ? trans('Enter Valid until Date') : ''));
        text_row(trans("Deliver To:"), 'deliver_to', $order->deliver_to, 50, 60,
            trans('Additional identifier for delivery e.g. name of receiving person'));

		textarea_row(trans("Address:"), 'delivery_address', $order->delivery_address, 35, 5, 255,
            trans('Delivery address. Default is address of customer branch'));

        table_section(2);

        text_row(trans("Contact Phone Number:"), 'phone', $order->phone, 25, 25,
            trans('Phone number of ordering person. Defaults to branch phone number'));
        // text_row(trans("Customer Reference:"), 'customer_ref', $order->cust_ref, 25, 25,
        //     trans('Customer reference number for this order (if any)'));

        shippers_list_row(trans("Shipping Company:"), 'ship_via', $order->ship_via);

        end_outer_table(1);
    }
    div_end();
}

function get_customer_by_token($token, $tokenDate, $searchMobile = true, $searchTransTable = true)
{
    $_token = db_escape($token);

    $tokenDate = date2sql($tokenDate);

    $customerWithToken = db_query(
        "SELECT
            customer_id,
            display_customer,
            customer_trn,
            customer_email,
            customer_mobile,
            contact_person,
            customer_iban as customer_ref
        FROM `0_axis_front_desk`
        WHERE
            DATE(created_at) = '{$tokenDate}'
            AND token = {$_token}
        ORDER BY
            id DESC
        LIMIT 1",
        "Could not get the token details"
    )->fetch_assoc();

    if (!empty($customerWithToken)) {
        return $customerWithToken;
    }

    // The customer is not registered by token - so sanitize the token to check if it is mobile number
    $_token = strtr($token, ['-' => '', ' ' => '']);
    if (preg_match(UAE_MOBILE_NO_PATTERN, $_token)) {
        $_mobileNo = preg_replace(UAE_MOBILE_NO_PATTERN, "$2", $_token);

        if ($searchMobile) {
            $registeredCustomer = db_query(
                "SELECT
                    debtor_no customer_id,
                    `name` display_customer,
                    tax_id customer_trn,
                    debtor_email customer_email,
                    mobile customer_mobile,
                    contact_person,
                    iban_no as customer_ref
                FROM 0_debtors_master
                WHERE
                    mobile = CONCAT('+971', '{$_mobileNo}')
                    AND inactive = 0
                ORDER BY created_at DESC
                LIMIT 1",
                "Could not retrieve customer by mobile number"
            )->fetch_assoc();
    
            if (!empty($registeredCustomer)) {
                return $registeredCustomer;
            }
        }

        if ($searchTransTable) {
            $anyCustomerWithSameMobileNumber = db_query(
                "SELECT
                    trans.debtor_no customer_id,
                    trans.display_customer,
                    trans.customer_trn,
                    trans.customer_email,
                    trans.customer_mobile,
                    trans.contact_person,
                    trans.customer_ref
                FROM 0_debtor_trans trans
                LEFT JOIN `0_debtors_master` debtor ON debtor.debtor_no = trans.debtor_no
                WHERE
                    trans.customer_mobile LIKE '%{$_mobileNo}'
                    AND trans.`type` = 10
                    AND trans.debtor_no = 1 
                    AND trans.display_customer != ' '
                    AND trans.display_customer != 'Walk-in Customer'
                    AND debtor.inactive = 0
                LIMIT 1",
                "Could not get walk-in customer by mobile number"
            )->fetch_assoc();
    
            if (!empty($anyCustomerWithSameMobileNumber)) {
                return $anyCustomerWithSameMobileNumber;
            }
        }
    }
    
    return false;
}

/**
* Returns the payment methods available for this dimension
*
* @return array
*/
function get_available_payment_methods($dimension, $customer_id)
{
    if ($dimension->is_payment_separate) {
        if ($dimension->id == Dimension::TYPING) {
            return ["PayLater" => "Pay at Cashier"];
        }
        
        return [
            "PayLater" => "Pay at Cashier",
            "PayByCustomerCard" => "Pay using Customer's Card"
        ];
    }

    $payment_types = $GLOBALS['global_pay_types_array'];

    switch ($dimension->id) {
        case Dimension::RTA:
            $payment_types["PayByCustomerCard"] = "Pay By Customer Card";
            unset($payment_types['PayCashAndCard']);
            unset($payment_types['PayByCenterCard']);
            break;
        case Dimension::TASHEEL:
        case Dimension::TAWJEEH:
        case Dimension::TADBEER:
        case Dimension::MOFA:
            $payment_types["PayByCustomerCard"] = "Customer's E-Dirham";
            $payment_types["PayByCenterCard"] = "Center's E-Dirham";
            $payment_types = array_merge(
                [
                    'PayByCustomerCard' => 0,
                    'PayByCenterCard' => 1,
                    'PayLater' => 2
                ],
                $payment_types
            );
            if (in_array($dimension->id, [Dimension::TADBEER])) {
                unset($payment_types['PayLater']);
            }
            unset($payment_types['PayNow']);
            unset($payment_types['PayNoWCC']);
            unset($payment_types['PayCashAndCard']);
            unset($payment_types['PayByBankTransfer']);
            unset($payment_types['PayOnline']);
            break;
        default:
            unset($payment_types['PayByCustomerCard']);
            unset($payment_types['PayByCenterCard']);
    }

    if ($customer_id == Customer::WALK_IN_CUSTOMER) {
        unset($payment_types['PayLater']);
    }

    // If multiple cash accounts are selected,
    // We can't handle the split cash account at the moment
    if (isset($payment_types['PayCashAndCard']) && count(get_payment_accounts('Cash', null, $dimension)) > 1) {
        unset($payment_types['PayCashAndCard']);
    }
    
    if (isset($_SESSION['Items']) && $_SESSION['Items']->is_editing && !isset($payment_types[$_SESSION['Items']->pay_type])) {
        $payment_types[$_SESSION['Items']->pay_type] = $GLOBALS['global_pay_types_array'][$_SESSION['Items']->pay_type];
    }

    return $payment_types;
}

/**
 * Build the payment accounts for govt fee based on the selected payment method
 *
 * @param Cart $order
 * @param Dimension $dimension
 * @param string $selected_method
 * @return array
 */
function build_govt_fee_pay_accounts($order, $dimension, $selected_method = '')
{
    $paymentAccounts = [];

    if (
        !empty($selected_method)
        && !empty($accounts = get_payment_accounts($selected_method, null, $dimension))
    ) {
        $paymentAccounts = BankAccount::query()
            ->whereIn('id', $accounts)
            ->pluck('bank_account_name', 'id')
            ->toArray();
    }

    if (
        $order->is_editing
        && !empty($selected_method)
        && $order->old->govt_fee_pay_account
        && !isset($paymentAccounts[$order->old->govt_fee_pay_account])
    ) {
        $paymentAccounts[$order->old->govt_fee_pay_account] = BankAccount::query()
            ->whereId($order->old->govt_fee_pay_account)
            ->value('bank_account_name');
    }

    return $paymentAccounts;
}