<?php

namespace App\Jobs\Sales;

use App\Models\Accounting\BankTransaction;
use App\Models\Sales\Customer;
use App\Models\Sales\CustomerTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class CalculateCustomerBalanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The customer's ID for which the balance is being calculated
     *
     * @var string
     */
    private $customerId;

    /**
     * Create a new CalculateCustomerBalanceJob instance.
     *
     * @param string customerId
     * @return void
     */
    public function __construct($customerId)
    {
        $this->customerId = $customerId;
        $this->onQueue('high-priority');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!$customer = Customer::find($this->customerId)) {
            return;
        }

        $today = date(DB_DATE_FORMAT);
        $cache = Customer::cachedOpeningBalanceQuery($this->customerId, $today)->first();

        $total = "`ov_amount` + `ov_gst` + `ov_freight` + `ov_freight_tax` + `ov_discount`";
        $query = DB::table('0_debtor_trans')
            ->selectRaw("SUM(IFNULL(IF(`type` IN(?, ?, ?), -1, 1) * ({$total}), 0)) AS balance",
                [CustomerTransaction::CREDIT, CustomerTransaction::PAYMENT, BankTransaction::DEBIT]
            )
            ->selectRaw(
                "MIN(IF(`type` = ? AND round(abs({$total}) - abs(alloc), 5) > 0, tran_date, NULL)) as first_unpaid_invoice_date",
                [CustomerTransaction::INVOICE]
            )
            ->whereRaw("{$total} <> 0")
            ->where('type', '<>', CustomerTransaction::DELIVERY)
            ->where(function (Builder $query) {
                $query->where('type', '<>', CustomerTransaction::INVOICE)
                    ->orWhere('payment_flag', '<>', PF_TASHEEL_CC);
            })
            ->where('debtor_no', $this->customerId)
            ->groupBy('debtor_no');

        if ($tillDate = data_get($cache, 'till_date')) {
            $query->where('tran_date', '>', $tillDate);
        }

        $result = $query->first();

        $oldest_invoice_dues = array_filter([
            data_get($cache, 'first_unpaid_invoice_date'),
            data_get($result, 'first_unpaid_invoice_date')
        ]);
        
        $customer->balance = data_get($cache, 'running_balance', 0) + data_get($result, 'balance', 0);
        $customer->first_unpaid_invoice_date = $oldest_invoice_dues ? min($oldest_invoice_dues) : null;

        $customer->save();
    }
}
