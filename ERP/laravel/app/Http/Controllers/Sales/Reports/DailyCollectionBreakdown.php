<?php

namespace App\Http\Controllers\Sales\Reports;

use App\Http\Controllers\Controller;
use App\Models\Accounting\BankTransaction;
use App\Models\Accounting\JournalTransaction;
use App\Models\Sales\CustomerTransaction;
use App\Permissions;
use App\Traits\ValidatesDatedDashboardReport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DailyCollectionBreakdown extends Controller {
    use ValidatesDatedDashboardReport;

    public function get(Request $request)
    {
        abort_unless($request->user()->hasPermission(Permissions::SA_DSH_COLL_BD), 403);
        $dateTime = $this->validateRequestWithDate($request);
        return $this->getReport($dateTime);
    }

    /**
     * Get the breakdown on all the money collected for the specified date
     *
     * @param string|DateTimeInterface $date
     * @return array
     */
    public function getReport($date = null)
    {
        $date = (new Carbon($date ?: new Carbon()))->toDateString();

        $salesInvoice = CustomerTransaction::INVOICE;
        $customerPayment = CustomerTransaction::PAYMENT;
        $journalEntry = JournalTransaction::JOURNAL;
        $receiptVoucher = BankTransaction::DEBIT;
        $creditNote = CustomerTransaction::CREDIT;

        $lineTotal = '`trans`.`ov_amount` + `trans`.`ov_gst` + `trans`.`ov_freight` + `trans`.`ov_freight_tax` + `trans`.`ov_discount`';
        $paymentTotal = "{$lineTotal} + `trans`.`round_of_amount`";
        $builder = DB::table('0_debtor_trans AS trans')
            ->selectRaw("ROUND(SUM(IF(`trans`.`type` = {$salesInvoice}, {$lineTotal}, 0)), 2) AS `invoice`")
            ->selectRaw("ROUND(SUM(IF(`trans`.`type` = {$salesInvoice} AND `trans`.`payment_method` = 'CreditCustomer', {$lineTotal}, 0)), 2) AS `credit_invoice`")
            ->selectRaw("ROUND(SUM(IF(`trans`.`type` = {$customerPayment} AND `trans`.`payment_method` = 'Cash', {$paymentTotal}, 0)), 2) AS `cash`")
            ->selectRaw("ROUND(SUM(IF(`trans`.`type` = {$customerPayment} AND `trans`.`payment_method` = 'CreditCard', {$paymentTotal}, 0)), 2) AS `credit_card`")
            ->selectRaw("ROUND(SUM(IF(`trans`.`type` = {$customerPayment} AND `trans`.`payment_method` = 'BankTransfer', {$paymentTotal}, 0)), 2) AS `bank_transfer`")
            ->selectRaw("ROUND(SUM(IF(`trans`.`type` = {$customerPayment} AND `trans`.`payment_method` = 'CustomerCard', {$paymentTotal}, 0)), 2) AS `customer_card`")
            ->selectRaw("ROUND(SUM(IF(`trans`.`type` = {$customerPayment} AND `trans`.`payment_method` = 'OnlinePayment', {$paymentTotal}, 0)), 2) AS `online_payment`")
            ->selectRaw(
                "ROUND("
                    . " SUM("
                        . " CASE"
                            . " WHEN `trans`.`type` = {$journalEntry} THEN ABS({$paymentTotal})"
                            . " WHEN `trans`.`type` IN ({$receiptVoucher}, {$creditNote}) THEN {$paymentTotal}"
                            . " ELSE 0"
                        . " END"
                    . " ),"
                    . " 2"
                . ") AS `other`"
            )
            ->whereIn('trans.type', [$salesInvoice, $customerPayment, $journalEntry, $receiptVoucher, $creditNote])
            ->where('trans.tran_date', $date)
            ->whereRaw("IF(`trans`.`type` = {$journalEntry}, {$lineTotal} < 0, {$lineTotal} > 0)");

        $result = $builder->first();
        $columns = [
            'invoice' => 'Total Invoices إجمالي الفواتير',
            'credit_invoice' => 'Credit Invoices فواتير الائتمان',
            'cash' => 'Cash  نقدا',
            'credit_card' => 'Credit Card  بطاقة ائتمان',
            'bank_transfer' => 'Bank Transfer  تحويل الحساب البنكي',
            'other' => 'Customer Payments  مدفوعات العملاء',
            'customer_card' => 'Customer Card  بطاقة العميل',
            'online_payment' => 'Online Payment  الدفع الالكتروني'
        ];

        // Build the report
        $report = collect();
        foreach ($columns as $key => $desc) {
            $report->push((object)[
                'description' => $desc,
                'amount' => $result->{$key}
            ]);
        }

        $total = [
            'amount' => (
                  $result->credit_invoice
                + $result->cash
                + $result->credit_card
                + $result->bank_transfer
                + $result->other
                + $result->customer_card
                + $result->online_payment
            )
        ];

        return [
            "data" => $report,
            "total" => $total
        ];
    }
}