<?php

namespace App\Jobs\Sales;

use App\Models\Accounting\BankTransaction;
use App\Models\Accounting\JournalTransaction;
use App\Models\Sales\CustomerTransaction;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AggregateCustomerBalancesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Name of the temporary table to store intermediate processed data
     *
     * @var string TEMP_TABLE_NAME
     */
    const TEMP_TABLE_NAME = 'temp_aggregate_balances';

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
    private $nullableColumns;
    
    /**
     * @var array
     */
    private $uniqueKey;

    /**
     * @var array
     */
    private $filters;
    
    /**
     * @var array
     */
    private $shouldCalculateRunningBal;

    /**
     * Create a new CalculateCustomerBalancesJob instance.
     *
     * @param string customerId
     * @return void
     */
    public function __construct($filters = [], $shouldCalculateRunningBal = true)
    {
        $this->filters = $filters;
        $this->shouldCalculateRunningBal = $shouldCalculateRunningBal;
        $this->onQueue('high-priority');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $this->initialize();

            // Clear the temporary table
            DB::table(self::TEMP_TABLE_NAME)->truncate();

            // Insert data into temporary processing table
            DB::table(self::TEMP_TABLE_NAME)->insertUsing($this->columns, $this->getBuilder());

            // Update if exists
            $this->updateIfExists();

            // Delete excess
            $this->deleteExcess();

            // Insert if not already exists
            $this->insertIfNotExists();

            if ($this->shouldCalculateRunningBal) {
                // Calculate running balances also
                CalculateRunningCustomerBalancesJob::dispatchNow($this->filters);
            }

            $this->tearDown();
        }

        catch (Throwable $e) {
            if ($this->isInitialized) {
                $this->tearDown();
            }
            
            throw $e;
        }
    }

    /**
     * Initialize this job
     *
     * @return void
     */
    private function initialize()
    {
        if ($this->isInitialized) return;

        // Create a temporary table to hold the intermediate attendance data.
        $this->createTemporaryTable();

        $this->columns = [
            'debtor_no',
            'key',
            'from_date',
            'till_date',
            'debit',
            'credit',
            'balance',
            'alloc_alloc',
            'alloc_due',
            'alloc_outstanding',
            'alloc_balance',
            'alloc_date_alloc',
            'alloc_date_due',
            'alloc_date_outstanding',
            'alloc_date_balance',
            'last_payment_date',
            'last_invoiced_date',
            'first_unpaid_invoice_date'
        ];

        $this->nullableColumns = [
            'last_payment_date',
            'last_invoiced_date',
            'first_unpaid_invoice_date'
        ];

        $this->uniqueKey = [
            'debtor_no',
            'key',
        ];

        $this->isInitialized = true;
    }


    /**
     * Teardown this job
     *
     * @return void
     */
    private function tearDown()
    {
        Schema::drop(self::TEMP_TABLE_NAME);
        $this->isInitialized = false;
    }

    /**
     * Update the balances if it already exists
     *
     * @return void
     */
    private function updateIfExists()
    {
        $currDateTime = date(DB_DATETIME_FORMAT);

        $columns = Arr::except($this->columns, $this->uniqueKey);
        $builder = DB::table('0_customer_balances as bal')
            ->join(self::TEMP_TABLE_NAME . ' as tmp', function(JoinClause $join) {
                $join->on('bal.debtor_no', 'tmp.debtor_no')
                    ->on('bal.key', 'tmp.key');
            })
            ->where(function(Builder $query) use ($columns) {
                foreach ($columns as $column) {
                    $query->orWhereColumn("bal.$column", "!=", "tmp.$column");

                    if (in_array($column, $this->nullableColumns)) {
                        $query->orWhereRaw("isnull(`bal`.`$column`) != isnull(`tmp`.`$column`)");
                    }
                }
            });

        $updates = [];
        foreach ($columns as $column) {
            $updates["bal.$column"] = DB::raw("`tmp`.`$column`");
        }
        $updates['bal.updated_at'] = $currDateTime;

        $builder->update($updates);
    }

    /**
     * Delete the excess balances if it already exists
     *
     * @return void
     */
    private function deleteExcess()
    {
        $query = DB::table('0_customer_balances as bal')
            ->leftJoin(self::TEMP_TABLE_NAME . ' as tmp', function (JoinClause $join) {
                $join->on('tmp.debtor_no', 'bal.debtor_no')
                    ->whereColumn('tmp.key', 'bal.key');
            })
            ->select('bal.id')
            ->whereNull('tmp.debtor_no');

        if (!empty($this->filters['debtor_no'])) {
            $query->where("bal.debtor_no", $this->filters['debtor_no']);
        }

        if (!empty($this->filters['key'])) {
            $query->where("bal.key", $this->filters['key']);
        }

        if (!empty($idsToDelete = $query->pluck('id')->toArray())) {
            DB::table('0_customer_balances')
                ->whereIn('id', $idsToDelete)
                ->delete();
        }
    }

    /**
     * Insert the missing balances
     *
     * @return void
     */
    private function insertIfNotExists()
    {
        $currDateTime = date(DB_DATETIME_FORMAT);

        $selects = array_merge(array_map(function ($col) {return "tmp.{$col}";}, $this->columns));
        $selects[] = DB::raw("'{$currDateTime}' AS created_at");

        DB::table('0_customer_balances')->insertUsing(
            array_merge($this->columns, ['created_at']),
            DB::table(self::TEMP_TABLE_NAME . ' as tmp')
                ->leftJoin('0_customer_balances as bal', function(JoinClause $join) {
                    $join->on('bal.debtor_no', 'tmp.debtor_no')
                        ->on('bal.key', 'tmp.key');
                })
                ->select($selects)
                ->whereNull('bal.id')
        );
    }

    /**
     * Generate balances for the given employee for the given period
     *
     * @return void
     */
    public function getBuilder()
    {
        $yesterday = Carbon::now()->subDay()->toDateString();
        $total = function($key) {
            return "(`{$key}`.`ov_amount` + `{$key}`.`ov_gst` + `{$key}`.`ov_freight` + `{$key}`.`ov_freight_tax` + `{$key}`.`ov_discount`)";
        };

        $allocatedFrom = DB::table('0_cust_allocations as al')
            ->join('0_debtor_trans as t', function (JoinClause $join) {
                $join->on('al.person_id', 't.debtor_no')
                    ->whereColumn('al.trans_type_to', 't.type')
                    ->whereColumn('al.trans_no_to', 't.trans_no');
            })
            ->select(
                'al.person_id as debtor_no',
                'al.trans_type_from as trans_type',
                'al.trans_no_from as trans_no',
            )
            ->selectRaw("sum(ifnull(if(`al`.`date_alloc_to` between date_format(`al`.`date_alloc`, '%Y-%m-01') and last_day(`al`.`date_alloc`), `al`.`amt`, 0), 0)) as amount")
            ->groupBy('al.person_id', 'al.trans_type_from', 'al.trans_no_from');
        
        $allocatedTo = DB::table('0_cust_allocations as al')
            ->join('0_debtor_trans as t', function (JoinClause $join) {
                $join->on('al.person_id', 't.debtor_no')
                    ->whereColumn('al.trans_type_from', 't.type')
                    ->whereColumn('al.trans_no_from', 't.trans_no');
            })
            ->select(
                'al.person_id as debtor_no',
                'al.trans_type_to as trans_type',
                'al.trans_no_to as trans_no',
            )
            ->selectRaw("sum(ifnull(if(`al`.`date_alloc` between date_format(`al`.`date_alloc_to`, '%Y-%m-01') and last_day(`al`.`date_alloc_to`), `al`.`amt`, 0), 0)) as amount")
            ->groupBy('al.person_id', 'al.trans_type_to', 'al.trans_no_to');


        $debit = "(`trans`.`type` IN (".CustomerTransaction::INVOICE." , ".JournalTransaction::JOURNAL." , ".BankTransaction::CREDIT." , ".CustomerTransaction::REFUND.") AND `trans`.`ov_amount` > 0)";
        $totalBal = $total('trans');
        $totalAllocBal = "(abs({$totalBal}) - abs(`trans`.`alloc`))";
        $totalAllocDateBal = "(abs({$totalBal}) - ifnull(abs(`allocFrom`.`amount`), 0) - ifnull(abs(`allocTo`.`amount`), 0))";
        $query = DB::table('0_debtor_trans as trans')
            ->select('trans.debtor_no')
            ->selectRaw("DATE_FORMAT(`trans`.`tran_date`, '%Y-%m') as `key`")
            ->selectRaw("DATE_FORMAT(`trans`.`tran_date`, '%Y-%m-01') as `from_date`")
            ->selectRaw("LEAST(LAST_DAY(`trans`.`tran_date`), '{$yesterday}') AS `till_date`") 
            ->selectRaw("SUM(IF({$debit}, abs({$totalBal}), 0)) AS debit")
            ->selectRaw("SUM(IF({$debit}, 0, abs({$totalBal}) )) AS credit")
            ->selectRaw("SUM(IF({$debit}, 1, -1) * abs({$totalBal})) AS balance")
            ->selectRaw("SUM(IF({$debit}, 1, -1) * abs(`trans`.`alloc`)) AS alloc_alloc")
            ->selectRaw("SUM(IF({$debit}, {$totalAllocBal}, 0)) AS alloc_due")
            ->selectRaw("SUM(IF({$debit}, 0, {$totalAllocBal})) AS alloc_outstanding")
            ->selectRaw("SUM(IF({$debit}, 1, -1) * {$totalAllocBal}) AS alloc_balance")
            ->selectRaw("SUM(IF({$debit}, 1, -1) * abs(coalesce(`allocFrom`.`amount`, `allocTo`.`amount`, 0))) AS alloc_date_alloc")
            ->selectRaw("SUM(IF({$debit}, abs({$totalAllocDateBal}), 0)) AS alloc_date_due")
            ->selectRaw("SUM(IF({$debit}, 0, abs({$totalAllocDateBal}))) AS alloc_date_outstanding")
            ->selectRaw("SUM(IF({$debit}, 1, -1) * abs({$totalAllocDateBal})) AS alloc_date_balance")
            ->selectRaw("MAX(IF(`trans`.`type` in (?, ?), `trans`.`tran_date`, NULL)) as last_payment_date", [CustomerTransaction::PAYMENT, BankTransaction::DEBIT]) 
            ->selectRaw("MAX(IF(`trans`.`type` = ?, `trans`.`tran_date`, NULL)) as last_invoiced_date", [CustomerTransaction::INVOICE])
            ->selectRaw("MIN(IF(`trans`.`type` = ? AND round({$totalAllocBal}, 5) > 0, `trans`.`tran_date`, NULL)) as first_unpaid_invoice_date", [CustomerTransaction::INVOICE])
            ->leftJoinSub($allocatedFrom, 'allocFrom', function(JoinClause $join) {
                $join->on('allocFrom.debtor_no', 'trans.debtor_no')
                    ->whereColumn('allocFrom.trans_type', 'trans.type')
                    ->whereColumn('allocFrom.trans_no', 'trans.trans_no');

            })
            ->leftJoinSub($allocatedTo, 'allocTo', function(JoinClause $join) {
                $join->on('allocTo.debtor_no', 'trans.debtor_no')
                    ->whereColumn('allocTo.trans_type', 'trans.type')
                    ->whereColumn('allocTo.trans_no', 'trans.trans_no');
            })
            ->where('trans.type', '<>', CustomerTransaction::DELIVERY)
            ->whereRaw("{$totalBal} <> 0")
            ->where('trans.tran_date', '<=', $yesterday)
            ->where(function (Builder $query) {
                $query->where('trans.type', '<>', CustomerTransaction::INVOICE)
                    ->orWhere('trans.payment_flag', '<>', PF_TASHEEL_CC);
            })
            ->groupBy(
                'trans.debtor_no',
                DB::raw("DATE_FORMAT(`trans`.`tran_date`, '%Y-%m')")
            );

        if (!empty($this->filters['debtor_no'])) {
            $query->where("trans.debtor_no", $this->filters['debtor_no']);
        }

        if (!empty($this->filters['key'])) {
            $query->whereRaw("DATE_FORMAT(`trans`.`tran_date`, '%Y-%m') = ?", [$this->filters['key']]);
        }

        return $query;
    }

    /**
     * Get key for the given date
     *
     * @param DateTimeInterface $date
     * @return string
     */
    public static function getKeyForDate(DateTimeInterface $date)
    {
        return $date->format('Y-m');
    }

    /**
     * Creates a temporary table to store the processed information
     *
     * @return void
     */
    private function createTemporaryTable()
    {
        if (Schema::hasTable(self::TEMP_TABLE_NAME)) return;

        Schema::create(self::TEMP_TABLE_NAME, function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            $table->temporary();
            $table->integer('debtor_no')->nullable();
            $table->string('key');
            $table->date('from_date');
            $table->date('till_date');
            $table->double('debit', 14, 4)->default(0.00);
            $table->double('credit', 14, 4)->default(0.00);
            $table->double('balance', 14, 4)->default(0.00);
            $table->double('alloc_alloc', 14, 4)->default(0.00);
            $table->double('alloc_due', 14, 4)->default(0.00);
            $table->double('alloc_outstanding', 14, 4)->default(0.00);
            $table->double('alloc_balance', 14, 4)->default(0.00);
            $table->double('alloc_date_alloc', 14, 4)->default(0.00);
            $table->double('alloc_date_due', 14, 4)->default(0.00);
            $table->double('alloc_date_outstanding', 14, 4)->default(0.00);
            $table->double('alloc_date_balance', 14, 4)->default(0.00);
            $table->date('last_payment_date')->nullable();
            $table->date('last_invoiced_date')->nullable();
            $table->date('first_unpaid_invoice_date')->nullable();
            $table->primary(['debtor_no', 'key']);
        });
    }
}
