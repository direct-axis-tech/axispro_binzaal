<?php

namespace App\Jobs\Sales;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class CalculateRunningCustomerBalancesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Name of the temporary table to store intermediate processed data
     *
     * @var string TEMP_TABLE_NAME
     */
    const TEMP_TABLE_NAME = 'temp';

    /**
     * Determines if the job is already initialized
     *
     * @var boolean
     */
    private $isInitialized = false;

    /**
     * @var array
     */
    private $columns;
    
    /**
     * @var array
     */
    private $uniqueKey;

    /**
     * @var array
     */
    private $filters;

    /**
     * Create a new CalculateCustomerBalancesJob instance.
     *
     * @param string customerId
     * @return void
     */
    public function __construct($filters = [])
    {
        $this->filters = $filters;
        $this->onQueue('high-priority');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $sub = DB::table('0_customer_balances as bal')
            ->select('bal.id')
            ->selectRaw('lag(`bal`.`id`) over (partition by `bal`.`debtor_no` order by `bal`.`from_date` rows between unbounded preceding and current row) as previous_id')
            ->selectRaw('sum(`bal`.`debit`) over (partition by `bal`.`debtor_no` order by `bal`.`from_date` rows between unbounded preceding and current row) as running_debit')
            ->selectRaw('sum(`bal`.`credit`) over (partition by `bal`.`debtor_no` order by `bal`.`from_date` rows between unbounded preceding and current row) as running_credit')
            ->selectRaw('sum(`bal`.`balance`) over (partition by `bal`.`debtor_no` order by `bal`.`from_date` rows between unbounded preceding and current row) as running_balance')
            ->selectRaw('sum(`bal`.`alloc_alloc`) over (partition by `bal`.`debtor_no` order by `bal`.`from_date` rows between unbounded preceding and current row) as alloc_running_alloc')
            ->selectRaw('sum(`bal`.`alloc_due`) over (partition by `bal`.`debtor_no` order by `bal`.`from_date` rows between unbounded preceding and current row) as alloc_running_due')
            ->selectRaw('sum(`bal`.`alloc_outstanding`) over (partition by `bal`.`debtor_no` order by `bal`.`from_date` rows between unbounded preceding and current row) as alloc_running_outstanding')
            ->selectRaw('sum(`bal`.`alloc_balance`) over (partition by `bal`.`debtor_no` order by `bal`.`from_date` rows between unbounded preceding and current row) as alloc_running_balance')
            ->selectRaw('sum(`bal`.`alloc_date_alloc`) over (partition by `bal`.`debtor_no` order by `bal`.`from_date` rows between unbounded preceding and current row) as alloc_date_running_alloc')
            ->selectRaw('sum(`bal`.`alloc_date_due`) over (partition by `bal`.`debtor_no` order by `bal`.`from_date` rows between unbounded preceding and current row) as alloc_date_running_due')
            ->selectRaw('sum(`bal`.`alloc_date_outstanding`) over (partition by `bal`.`debtor_no` order by `bal`.`from_date` rows between unbounded preceding and current row) as alloc_date_running_outstanding')
            ->selectRaw('sum(`bal`.`alloc_date_balance`) over (partition by `bal`.`debtor_no` order by `bal`.`from_date` rows between unbounded preceding and current row) as alloc_date_running_balance')
            ->selectRaw('max(`bal`.`last_invoiced_date`) over (partition by `bal`.`debtor_no` order by `bal`.`from_date` rows between unbounded preceding and current row) as running_last_invoiced_date')
            ->selectRaw('max(`bal`.`last_payment_date`) over (partition by `bal`.`debtor_no` order by `bal`.`from_date` rows between unbounded preceding and current row) as running_last_payment_date')
            ->selectRaw('min(`bal`.`first_unpaid_invoice_date`) over (partition by `bal`.`debtor_no` order by `bal`.`from_date` rows between unbounded preceding and current row) as running_first_unpaid_invoice_date')
            ->orderBy('bal.debtor_no')
            ->orderBy('bal.from_date');
        
        if (!empty($this->filters['debtor_no'])) {
            $sub->where('bal.debtor_no', $this->filters['debtor_no']);
        }

        DB::table('0_customer_balances as rep')
            ->joinSub($sub, 'sub', 'sub.id', 'rep.id')
            ->update([
                'rep.previous_id' => DB::raw('sub.previous_id'),
                'rep.running_debit' => DB::raw('sub.running_debit'),
                'rep.running_credit' => DB::raw('sub.running_credit'),
                'rep.running_balance' => DB::raw('sub.running_balance'),
                'rep.alloc_running_alloc' => DB::raw('sub.alloc_running_alloc'),
                'rep.alloc_running_due' => DB::raw('sub.alloc_running_due'),
                'rep.alloc_running_outstanding' => DB::raw('sub.alloc_running_outstanding'),
                'rep.alloc_running_balance' => DB::raw('sub.alloc_running_balance'),
                'rep.alloc_date_running_alloc' => DB::raw('sub.alloc_date_running_alloc'),
                'rep.alloc_date_running_due' => DB::raw('sub.alloc_date_running_due'),
                'rep.alloc_date_running_outstanding' => DB::raw('sub.alloc_date_running_outstanding'),
                'rep.alloc_date_running_balance' => DB::raw('sub.alloc_date_running_balance'),
                'rep.running_last_payment_date' => DB::raw('sub.running_last_payment_date'),
                'rep.running_last_invoiced_date' => DB::raw('sub.running_last_invoiced_date'),
                'rep.running_first_unpaid_invoice_date' => DB::raw('sub.running_first_unpaid_invoice_date'),
            ]);
    }
}
