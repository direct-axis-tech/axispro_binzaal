<?php

namespace Axispro\Sales;

use App\Contracts\Flowable;
use App\Models\Labour\Labour;
use App\Models\MetaReference;
use App\Models\TaskRecord;
use App\Traits\Flowable as FlowableTrait;
use Cart;

class CreditNoteController implements Flowable {
    use FlowableTrait;

    /**
     * Returns all the dependant frontaccounting files
     *
     * @return array
     */
    public static function dependencies()
    {
        return [
            "sales/includes/cart_class.inc",
            "includes/data_checks.inc",
            "sales/includes/sales_db.inc",
            "sales/includes/sales_ui.inc",
            "sales/includes/db/sales_types_db.inc",
            "sales/includes/ui/sales_credit_ui.inc",
            "sales/includes/ui/sales_order_ui.inc",
            "reporting/includes/reporting.inc",
        ];
    }

    /**
     * Validates Time Sensitive Data
     *
     * @param \Cart $cart
     * @return array
     */
    public static function validateTimeSensitiveData($cart)
    {
        $errors = [];

        $trans_total = $cart->get_cart_total();
        if ($cart->isFromLabourContract()) {
            $contract = $cart->contract->refresh();
        
            // Check if the contract is being over credited
            $totalInvoicedAmount = $contract->getTotalInvoicedAmount(null, null, true);
            $totalInvoicedAmountWithoutThisCredit = $contract->getTotalInvoicedAmount(
                $cart->reference,
                $cart->trans_type,
                true
            );
            $previousThisCredit = $totalInvoicedAmount - $totalInvoicedAmountWithoutThisCredit;
            $totalCreditableAmount = get_full_price_for_item(
                $contract->stock_id,
                $contract->creditable_amount - $previousThisCredit,
                $cart->tax_group_id,
                0,
                $cart->tax_group_array
            );

            if (floatcmp($trans_total, $totalCreditableAmount) == 1) {
                $errors[] = trans("The total crediting amount exceeds the total invoiced amount");
            }

            if (!Labour::isValidInventoryUpdate($contract->labour_id, date2sql($cart->document_date), 1)) {
                $errors[] = trans("The selected maid is already at the center or this will create a scheduling conflict");
            }
        }

        return compact('errors');
    }

    /**
     * The callback function to be called after being completed during the flow
     *
     * @param  \App\Models\TaskRecord  $taskRecord
     * @return void
     */
    public static function resolve(TaskRecord $taskRecord)
    {
        $cart = Cart::fromJson($taskRecord->data['cart']);

        if (count(($validationResult = static::validateTimeSensitiveData($cart))['errors'])) {
            response()->json(
                [
                    "message" => "Request contains invalid data",
                    "errors" => $validationResult['errors']
                ],
                422
            )->send();
            exit();
        }

        begin_transaction();
        $cart->reference = MetaReference::getNext(
            $cart->trans_type,
            null,
            array(
                'date' => $cart->document_date,
                'customer' => $cart->customer_id,
                'branch' => $cart->Branch,
                'dimension' => $cart->dimension_id
            )
        );
        
        $credit_no = $cart->write($taskRecord->data['writeoff_policy']);

        if (count($GLOBALS['messages'])) {
            response()->json(
                [
                    "message" => "Something went wrong!",
                    "errors" => [fmt_errors()]
                ],
                422
            )->send();
            exit();
        }

        if ($credit_no == -1) {
            response()->json(
                ["message" => "Could not acquire a new reference. Please process again"],
                422
            )->send();
            exit();
        }

        commit_transaction();
    }

    /**
     * Returns the relevant data to be shown to public
     *
     * @param  \App\Models\TaskRecord  $taskRecord
     * @return array
     */
    public static function getDataForDisplay(TaskRecord $taskRecord): array
    {
        return [
            'Contract #' => $taskRecord->data['contract_ref'],
            'Maid' => $taskRecord->data['maid_name'],
            'Customer' => $taskRecord->data['customer_name'],
            'Refundable Amount' => $taskRecord->data['total'],
        ];
    }
}