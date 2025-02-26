<?php

use App\Models\Accounting\Dimension;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Output\ConsoleOutput;

$path_to_root = "../ERP";

require_once __DIR__ . "/../ERP/includes/console_session.inc";
require_once __DIR__ . "/../ERP/includes/date_functions.inc";
require_once __DIR__ . "/../ERP/includes/data_checks.inc";
require_once __DIR__ . "/../ERP/includes/ui/ui_globals.inc";
require_once __DIR__ . "/../ERP/sales/includes/sales_db.inc";
require_once __DIR__ . "/../ERP/taxes/tax_calc.inc";

try {
    begin_transaction();

    $changes = [];
    $result = collect(get_discount_issue_for_job_order_completion())
        ->groupBy('delivery_id')
        ->map
        ->keyBy('id');

    foreach ($result as $delivery) {
        $changes[] = fix_discount_issue_for_job_order_completion($delivery);
    }

    log_changes($changes);

    commit_transaction();
}

catch (Throwable $e) {
    if (is_in_transaction()) cancel_transaction();
    (new ConsoleApplication)->renderThrowable($e, new ConsoleOutput());
}

/**
 * Get all the deliveries that have issues with discount
 *
 * @return mysqli_result
 */
function get_discount_issue_for_job_order_completion()
{
    $sql = (
        "select
            delivery.id as delivery_id,
            delivery.type as trans_type,
            delivery.trans_no,
            delivery.reference as delivery_reference,
            delivery.payment_terms,
            delivery.tax_included,
            line_item.*
        from `0_debtor_trans_details` as line_item
        left join `0_debtor_trans_details` as inv_line_item on
            inv_line_item.line_reference = line_item.line_reference
            and inv_line_item.debtor_trans_type = ".ST_SALESINVOICE."
            and inv_line_item.quantity <> 0
        left join `0_debtor_trans` as delivery on
            delivery.`type` = line_item.debtor_trans_type
            and delivery.trans_no = line_item.debtor_trans_no
        where
            line_item.quantity <> 0
            and line_item.debtor_trans_type = ".ST_CUSTDELIVERY."
            and line_item.unit_tax != inv_line_item.unit_tax
        for update"
    );

    return db_query($sql, "Could not query for erroneous")->fetch_all(MYSQLI_ASSOC);
}

/**
 * Fix one individual delivery
 *
 * @param array $delivery
 * @return void
 */
function fix_discount_issue_for_job_order_completion($delivery_lines)
{
    begin_transaction();
    
    $changes = [];
    $dec = user_price_dec();
    $delivery_data = $delivery_lines->first();
    $delivery = new Cart(ST_CUSTDELIVERY, 0);
    $dimension = $delivery->getDimension();
    $tax_effective_from = data_get($dimension, 'tax_effective_from') ? sql2date($dimension->tax_effective_from) : null;
    read_sales_trans($delivery_data['trans_type'], $delivery_data['trans_no'], $delivery);
    $delivery->discount_taxed = $delivery->isDiscountTaxable();

    // guard from unexpected edits. Currently we only know of issues: for tax included job order completions
    if (!$delivery->is_prepaid() || !$delivery->tax_included) {
        return;
    }

    // filter 
    tap($delivery_lines->keys()->all(), function ($line_ids) use ($delivery) {
        $delivery->line_items = array_filter(
            $delivery->line_items,
            function ($line_item) use ($line_ids) { return in_array($line_item->id, $line_ids); }
        );
    });

    foreach ($delivery->line_items as $delivery_line) {
        $old_data = $delivery_lines[$delivery_line->id];
        $qty = $delivery_line->qty_dispatched;
        $delivery_line->discount_taxed = $delivery->discount_taxed;
        $taxable_amt = $delivery->getTaxableAmt($delivery_line);
        $item_category = get_item_category($delivery_line->category_id);
		$stock_gl_code = get_stock_gl_code($delivery_line->stock_id);
        $line_tax_free_price = get_tax_free_price_for_item(
			$delivery_line->stock_id,
			$taxable_amt,
			0,
			$delivery->tax_included,
			$delivery->tax_group_array,
            null,
            $delivery->document_date,
            $tax_effective_from
		);

        $line_tax = get_full_price_for_item(
			$delivery_line->stock_id,
			$taxable_amt,
			0,
			$delivery->tax_included,
			$delivery->tax_group_array,
            null,
            $delivery->document_date,
            $tax_effective_from
		) - $line_tax_free_price;

        // we are sure the delivery is tax included, because we added a guard
        // clause before this code, so only those passing the test comes here.

        // split into 2 parts for clarity. 1st part is taxable and 2nd part is not
        $old_sales_amt = round2(
            (
                (
                    $old_data['unit_price']
                    + $old_data['returnable_amt']
                    + $old_data['extra_srv_chg']
                    - $old_data['unit_tax']
                ) * $old_data['quantity']
            )
            +
            (
                (
                    $old_data['govt_fee']
                    - $old_data['returnable_amt']
                    + $old_data['bank_service_charge']
                    + $old_data['bank_service_charge_vat']
                ) * $old_data['quantity']
            ),
            $dec
        );

        $sales_account = $stock_gl_code['sales_account'];
        $deferred_act = data_get($item_category, 'dflt_pending_sales_act') ?: get_company_pref('deferred_income_act');

        $gl_trans = db_query(
            "select
                *
            from 0_gl_trans
            where
                `type` = ".db_escape($delivery_data['trans_type'])."
                and type_no = ".db_escape($delivery_data['trans_no'])."
                and line_reference = ".db_escape($delivery_line->line_reference)."
                and memo_ in ('', 'Sales Amount')
                and account in (".implode(',', array_map('db_escape', [$sales_account, $deferred_act])).")
                and round(amount, $dec) = if(account = ".db_escape($sales_account).", -1, 1) * $old_sales_amt
            for update",
            "Could not query for old erroneous data"
        )->fetch_all(MYSQLI_ASSOC);

        // Check if configuration changed, If so, we don't have any other means
        // of absolutely knowing. We will be left to guess
        if (count($gl_trans) != 2) continue;

        $new_sales_amt =  round2(
            $line_tax_free_price
            + ($delivery->discount_taxed ? $delivery_line->discount_amount * $qty : 0)
            + (
                (
                    $delivery_line->govt_fee
                    - $delivery_line->returnable_amt
                    + $delivery_line->bank_service_charge
                    + $delivery_line->bank_service_charge_vat
                ) * $qty
            ),
            $dec
        );

        [$credit, $debit] = $gl_trans;

        // if the credit line is not credit line, swap credit and debit line
        if ($credit['amount'] > 0) {
            [$credit, $debit] = [$debit, $credit];
        }

        db_query(
            "update 0_gl_trans
            set amount = if (counter = {$credit['counter']}, -1, 1) * $new_sales_amt
            where counter in ({$credit['counter']}, {$debit['counter']})",
            "Could not fix the gl amount for erroneous deferred sales and sales"
        );

        db_query(
            "update 0_debtor_trans_details
            set
                unit_tax = ".db_escape($line_tax/$qty).",
                discount_taxed = ".db_escape($delivery_line->discount_taxed)."
            where id = ".db_escape($delivery_line->id),
            sprintf("Could not update line item %s of delivery %s", $delivery_line->id, $delivery->reference)
        );

        $changes[] = [
            "delivery_id" => $delivery_data['delivery_id'],
            "delivery_reference" => $delivery_data['delivery_reference'],
            "line_id" => $delivery_line->id,
            "line_reference" => $delivery_line->line_reference,
            "old_unit_tax" => $old_data['unit_tax'],
            "old_discount_taxed" => $old_data['discount_taxed'],
            "new_unit_tax" => round2($line_tax/$qty, $dec),
            "new_discount_taxed" => $delivery_line->discount_taxed,
            "old_sales_amount" => $old_sales_amt,
            "new_sales_amount" => $new_sales_amt,
            "change_in_amt" => $new_sales_amt - $old_sales_amt
        ];
    }

    db_query(
        "update 0_debtor_trans
        set version = version + 1
        where
            `type` = ".db_escape($delivery_data['trans_type'])."
            and trans_no = ".db_escape($delivery_data['trans_no']),
        "Could not update debtor trans version"
    );

    commit_transaction();

    return $changes;
}

/**
 * Log the changes to a file inside the logs folder
 *
 * @param array[] $changes
 * @return void
 */
function log_changes($changes)
{
    $changes = array_filter(Arr::flatten($changes, 1));
    $headers = array_map([\Illuminate\Support\Str::class, 'studly'], array_keys(reset($changes)));
    
    $filePath = storage_path('logs/completion_gl_discount_issues_'.date('YmdHis').'.csv');

    // Open the file for writing
    $handle = fopen($filePath, 'w');

    // Write the header row
    fputcsv($handle, $headers);

    // Write the data rows
    foreach ($changes as $row) {
        fputcsv($handle, array_values($row));
    }

    // Close the file
    fclose($handle);
}