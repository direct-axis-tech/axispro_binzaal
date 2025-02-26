<?php

use App\Models\Accounting\Dimension;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Output\ConsoleOutput;

$path_to_root = "../ERP";

require_once __DIR__ . "/../ERP/includes/console_session.inc";
require_once __DIR__ . "/../ERP/hrm/helpers/leaveAccrualHelpers.php";
require_once __DIR__ . "/../ERP/includes/date_functions.inc";
require_once __DIR__ . "/../ERP/includes/data_checks.inc";
require_once __DIR__ . "/../ERP/sales/includes/sales_db.inc";
require_once __DIR__ . "/../ERP/taxes/tax_calc.inc";

try {
    $cursor = db_query(
        "SELECT order_no, trans_type from 0_sales_orders WHERE trans_type = " . db_escape(ST_SALESORDER),
        "Could not query for sales orders"
    );

    while ($so = db_fetch_assoc($cursor)) {
        migrate_existing_tax_info($so['order_no'], $so['trans_type']);
    }
}

catch (Throwable $e) {
    (new ConsoleApplication)->renderThrowable($e, new ConsoleOutput());
}

/**
 * Migrate existing tax info to sales orders table
 *
 * @param string $trans_no
 * @param string $trans_type
 * @return void
 */
function migrate_existing_tax_info($trans_no, $trans_type)
{
    begin_transaction();
    $trans = get_sales_order_header($trans_no, $trans_type);
    $trans_details = get_sales_order_details($trans_no, $trans_type)->fetch_all(MYSQLI_ASSOC);
	$trans_details = array_filter(
        $trans_details,
        function($line) {
            return $line['quantity'] != 0;
        }
    );
    $trans_details = array_values($trans_details);
    $tax_group_array = get_tax_group_items_as_array($trans['tax_group_id']);
    $dec = user_price_dec();

    $tran_date = sql2date($trans['ord_date']);
    $tax_effective_from = (
        $trans['dimension_id']
        && ($dimension = Dimension::find($trans['dimension_id']))
        && $dimension->tax_effective_from
    )
        ? sql2date($dimension->tax_effective_from)
        : null;

    foreach ($trans_details as $line) {
        $taxable_amount = $line['unit_price'] + $line['returnable_amt'] + $line['extra_srv_chg'];
		$discount = $taxable_amount == 0 ? 0 : ($line['discount_amount'] * $line['quantity']);

        $tax_free_price = get_tax_free_price_for_item(
            $line['stk_code'],
            ($taxable_amount * $line['quantity']) - $discount,
            0,
            $trans['tax_included'],
            $tax_group_array,
            null,
            $tran_date,
            $tax_effective_from
        );

		$unit_tax = (
			get_full_price_for_item(
				$line['stk_code'],
				($taxable_amount * $line['quantity']) - $discount,
				0,
				$trans['tax_included'],
				$tax_group_array,
                null,
                $tran_date,
                $tax_effective_from
			)
			- $tax_free_price
		) / $line['quantity'];

        $unit_tax = $line['quantity'] ? ($unit_tax / $line['quantity']) : 0;

        if ($unit_tax != 0) {
            db_query(
                "UPDATE 0_sales_order_details SET _unit_tax = ".db_escape($unit_tax)." WHERE id = ".db_escape($line['id']),
                "Could not update unit tax for sales order line " . $line['id']
            );
        }

        $prices[] = ($taxable_amount * $line['quantity']) - $discount;
		$items[] = $line['stk_code'];
    }

    $taxes = get_tax_for_items(
        $items,
        $prices,
        $trans["freight_cost"],
        0,
        $trans['tax_included'],
        $tax_group_array,
        null,
        null,
        $tran_date,
        $tax_effective_from
    );

    $tax = 0;
    foreach ($taxes as $taxitem) {
        $tax += round2($taxitem['Value'], $dec);
    }

    db_query(
        "UPDATE 0_sales_orders
        SET
            _tax = ".db_escape($tax).",
            _tax_included = ".db_escape($trans['tax_included'])."
        WHERE
            id = ".db_escape($trans['id']),
        "Could not update tax details for sales order " . $trans['order_no']
    );

    commit_transaction();
}