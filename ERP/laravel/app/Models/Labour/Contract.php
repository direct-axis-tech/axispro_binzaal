<?php

namespace App\Models\Labour;

use App\Models\Accounting\Dimension;
use App\Models\Accounting\JournalTransaction;
use App\Models\Inventory\StockCategory;
use App\Models\Inventory\StockItem;
use App\Models\Inventory\StockMove;
use App\Models\Inventory\StockReplacement;
use App\Models\Sales\Customer;
use App\Models\Sales\CustomerTransaction;
use App\Models\Sales\SalesOrder;
use App\Traits\CachesSyntheticAttributes;
use App\Traits\InactiveModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class Contract extends Model
{
    const TEMPORARY_CONTRACT = 50;
    const CONTRACT = 51;
    
    /**
     * The number of days after the income would be first
     * recognizable for package one
     */
    const PKG_ONE_INCOME_RECOGNITION_DELAY = 180;

    /**
     * The number of days after the expense would be first
     * recognizable for package one
     */
    const PKG_ONE_EXPENSE_RECOGNITION_DELAY = 180;
 
    use InactiveModel, CachesSyntheticAttributes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_labour_contracts';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'contract_from',
        'contract_till'
    ];

    /**
     * Get the customer associated with this contract
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'debtor_no');
    }

    /**
     * Get the maid associated with this contract
     */
    public function maid()
    {
        return $this->belongsTo(Labour::class, 'labour_id');
    }
    
    /**
     * Get the stock associated with this contract
     */
    public function stock()
    {
        return $this->belongsTo(StockItem::class, 'stock_id', 'stock_id');
    }
    
    /**
     * Get the stock associated with this contract
     */
    public function category()
    {
        return $this->belongsTo(StockCategory::class, 'category_id', 'category_id');
    }
    
    /**
     * The sales order against this contract
     */
    public function order()
    {
        return $this->hasOne(SalesOrder::class, 'contract_id');
    }
    
    /**
     * The delivery against this contract
     */
    public function delivery()
    {
        return $this->hasOne(CustomerTransaction::class, 'contract_id')
            ->whereRaw('(`ov_amount` + `ov_gst` + `ov_freight` + `ov_discount` + `ov_freight_tax`) <> 0')
            ->where('type', CustomerTransaction::DELIVERY);
    }

    /**
     * The credit note against this contract
     */
    public function credit()
    {
        return $this->hasOne(CustomerTransaction::class, 'contract_id')
            ->whereRaw('(`ov_amount` + `ov_gst` + `ov_freight` + `ov_discount` + `ov_freight_tax`) <> 0')
            ->where('type', CustomerTransaction::CREDIT);
    }

    /**
     * The invoices made against this contract
     */
    public function invoices()
    {
        return $this->hasMany(CustomerTransaction::class, 'contract_id')
            ->whereRaw('(`ov_amount` + `ov_gst` + `ov_freight` + `ov_discount` + `ov_freight_tax`) <> 0')
            ->where('type', CustomerTransaction::INVOICE);
    }

    /**
     * The payment made against this contract
     */
    public function payments()
    {
        return $this->hasMany(CustomerTransaction::class, 'contract_id')
            ->whereRaw('(`ov_amount` + `ov_gst` + `ov_freight` + `ov_discount` + `ov_freight_tax`) <> 0')
            ->where('type', CustomerTransaction::PAYMENT);
    }

    /**
     * The maid return against this contract
     */
    public function maidReturn()
    {
        return $this->hasOne(StockMove::class, 'contract_id')->where('type', StockMove::STOCK_RETURN);
    }

    /**
     * The maid replacements against this contract
     */
    public function maidReplacements()
    {
        return $this->hasMany(StockReplacement::class, 'contract_id')
            ->where('type', StockReplacement::STOCK_REPLACEMENT);
    }

    /**
     * The maid movements against this contract
     */
    public function maidMovements()
    {
        return $this->hasMany(StockMove::class, 'contract_id');
    }

    /**
     * The latest outgoing maid movement against this contract
     */
    public function latestMaidDispatch()
    {
        return $this->hasOne(StockMove::class, 'contract_id')
            ->where('qty', '<', 0)
            ->orderBy('tran_date', 'desc');
    }

    /**
     * The installment against this contract
     */
    public function installment()
    {
        return $this->hasOne(Installment::class, 'contract_id');
    }
    

    /**
     * Get the next period for invoicing
     *
     * @return string[]
     */
    public function getNextPeriod()
    {
        $contractFrom = (new CarbonImmutable($this->contract_from))->midDay();
        $contractTill = (new CarbonImmutable($this->contract_till))->midDay();
        $lastInvoiceTill = CustomerTransaction::active()->whereContractId($this->id)->max('period_till');
        $periodFrom = $lastInvoiceTill ? (new CarbonImmutable($lastInvoiceTill))->midDay()->addDay() : $contractFrom;
        if ($periodFrom > $contractTill) {
            $periodFrom = $contractTill;
        }

        switch ($this->category_id) {
            case StockCategory::DWD_PACKAGETWO:
                $periodTill = $periodFrom->addMonth()->subDay();
                if ($periodTill > $contractTill) {
                    $periodTill = $contractTill;
                }

                return [$periodFrom->toDateString(), $periodTill->toDateString()];
            case StockCategory::DWD_PACKAGEONE:
            default:
                return [$periodFrom->toDateString(), $contractTill->toDateString()];
        }
    }

    /**
     * Retrieve the total invoiced amount for this contract
     *
     * @param string $excludedReference
     * @param int $excludedType
     * @return string|null
     */
    public function getTotalInvoicedAmount($excludedReference = null, $excludedType = null, $excludeTax = false)
    {
        if (!isset($excludedType)) {
            $excludedType = CustomerTransaction::INVOICE;
        }

        $query = CustomerTransaction::active()
            ->whereIn('type', [CustomerTransaction::INVOICE, CustomerTransaction::CREDIT])
            ->whereContractId($this->id)
            ->selectRaw('SUM('
                . 'if(`type` = '.CustomerTransaction::INVOICE.', 1, -1) * ('
                    . 'ov_amount'
                    . ' + ov_gst'
                    . ' + ov_discount'
                    . ' + ov_freight'
                    . ' + ov_freight_tax'
                    . ' - processing_fee'
                    . ' - if('.intval($excludeTax).', inc_ov_gst, 0)'
                .')'
            . ') as total_amount')
            ->groupBy('contract_id');

        if ($excludedReference) {
            $query->whereRaw("NOT (`type` = ? AND `reference` = ?)", [$excludedType, $excludedReference]);
        }

        return $query->value('total_amount') ?: 0;
    }

    /**
     * Get the dimension_id attribute
     *
     * @return float
     */
    public function getDimensionIdAttribute($dimension_id)
    {
        return $dimension_id ?: Dimension::whereCenterType(CENTER_TYPES['DOMESTIC_WORKER'])->value('id');
    }

    /**
     * Calculates the total amount that can be credited back to the customer
     *
     * @return float
     */
    public function getNoOfInstallmentsAttribute()
    {
        $contractFrom = new CarbonImmutable($this->contract_from);
        $contractTill = new CarbonImmutable($this->contract_till);

        $diffInMonths = round2($contractFrom->floatDiffInRealMonths($contractTill));
        return $diffInMonths;
    }

    /**
     * Calculates the total amount that can be credited back to the customer
     *
     * @return float
     */
    public function getCreditableAmountAttribute()
    {
        return $this->getOrComputeAttribute('creditable_amount', function() {
            switch ($this->category_id) {
                case StockCategory::DWD_PACKAGEONE:
                    $totalRecognized = JournalTransaction::active()
                        ->whereContractId($this->id)
                        ->whereSourceRef($this->reference)
                        ->selectRaw('sum(`amount`) as amount')
                        ->groupBy('contract_id')
                        ->value('amount') ?: 0;
    
                    return $this->getTotalInvoicedAmount(null, null, true) - $totalRecognized;
                default:
                    if ($this->last_made_invoice) {
                        return (
                            $this->last_made_invoice->ov_amount
                            - $this->last_made_invoice->processing_fee
                            - (
                                  $this->last_made_invoice->tax_included
                                ? $this->last_made_invoice->inc_ov_gst
                                : 0
                            )
                        );
                    }
    
                    return 0;
            }
        });
    }

    /**
     * Retrieve the last made invoice against this contract
     *
     * @return \App\Models\Sales\CustomerTransaction|null
     */
    public function getLastMadeInvoiceAttribute()
    {
        return $this->getOrComputeAttribute('last_made_invoice', function() {
            return CustomerTransaction::query()
                ->where('contract_id', $this->id)
                ->where('type', CustomerTransaction::INVOICE)
                ->whereRaw('`ov_amount` + `ov_gst` + `ov_discount` + `ov_freight` + `ov_freight_tax` > 0')
                ->orderBy('tran_date', 'desc')
                ->orderBy('transacted_at', 'desc')
                ->first();
        });
    }

    /**
     * Retrieve the purchase record related to the contract
     *
     * @return stdClass
     */
    public function getPurchaseRecordAttribute()
    {
        return $this->getOrComputeAttribute('purchase_record', function () {
            return DB::table('0_supp_invoice_items as item')
                ->leftJoin('0_supp_trans as trans', function (JoinClause $join) {
                    $join->on('item.supp_trans_type', 'trans.type')
                        ->whereColumn('item.supp_trans_no', 'trans.trans_no');
    
                })
                ->select(
                    'trans.type',
                    'trans.trans_no',
                    'trans.reference',
                    'trans.tran_date',
                    'trans.supplier_id',
                    'item.stock_id',
                    'item.id as line_id',
                    DB::raw('round((`item`.`unit_price` - if(`trans`.`tax_included`, `item`.`unit_tax`, 0)) * `item`.`quantity`, 2) as `amount`'),
                    DB::raw('round(`item`.`unit_tax` * `item`.`quantity`, 2) as `tax`'),
                )
                ->where('item.maid_id', $this->labour_id)
                ->where('item.unit_price', '<>', 0)
                ->first();
        });
    }

    /**
     * Retrieve the last made journal against this contract
     *
     * @return \App\Models\Accounting\JournalTransaction|null
     */
    public function getLastMadeJournalAttribute()
    {
        return $this->getOrComputeAttribute('last_made_journal', function() {
            return JournalTransaction::query()
                ->where('contract_id', $this->id)
                ->where('type', JournalTransaction::JOURNAL)
                ->where('amount', '<>', '0')
                ->orderBy('tran_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();
        });
    }

    /**
     * Calculates the contract amount for the given number of days
     *
     * @param integer $days
     * @param \App\Models\Sales\CustomerTransaction $invoice
     * @return float
     */
    public function getAmountForDays($days, $invoice = null)
    {
        if ($this->category_id == StockCategory::DWD_PACKAGETWO) {
            if (!isset($invoice)) {
                $invoice = $this->last_made_invoice;
            }
            $durationInDays = (CarbonImmutable::parse($invoice->period_from)->diffInDays(CarbonImmutable::parse($invoice->period_till)) + 1);
            $amountPerDay = ($invoice->ov_amount - $invoice->processing_fee) / $durationInDays;
        } else {
            $amountPerDay = $this->amount / ($this->contract_from->diffInDays($this->contract_till) + 1);
        }

        return round2($amountPerDay * $days, user_price_dec());
    }

    /**
     * Guess the number or days for which the income should be recovered
     *
     * @param string $date any string that can be parsed by DateTIme
     * @return int
     */
    public function guessDaysToRecoverIncomeFor($returnDate)
    {
        $returnDate = new CarbonImmutable($returnDate);
        $daysToRecoverIncomeFor = 0;

        switch ($this->category_id) {
            case StockCategory::DWD_PACKAGEONE:
                /**
                 * If there is any journal it means some of the income is already recognized
                 * So, the number of days will start from the last income recognition date.
                 */
                if (!empty($this->last_made_journal)) {
                    $startDate = CarbonImmutable::parse($this->last_made_journal->tran_date)->addDay();
                    $daysToRecoverIncomeFor = $startDate->diffInDays($returnDate) + 1;
                    $max = $startDate->daysInMonth;
                } else {
                    $startDate = $this->contract_from->toImmutable();
                    $daysToRecoverIncomeFor = $startDate->diffInDays($returnDate) + 1;
                    $max = $startDate->diffInDays($startDate->addDays(self::PKG_ONE_INCOME_RECOGNITION_DELAY)->endOfMonth()->startOfDay()) + 1;
                }
                break;

            case StockCategory::DWD_PACKAGETWO:
            default:
                if (empty($this->last_made_invoice->period_from)) {
                    return 0;
                }

                $startDate = CarbonImmutable::parse($this->last_made_invoice->period_from);
                $daysToRecoverIncomeFor = $startDate->diffInDays($returnDate) + 1;
                $max = $startDate->diffInDays(CarbonImmutable::parse($this->last_made_invoice->period_till)) + 1;
                break;
        }

        if ($daysToRecoverIncomeFor > $max) {
            $daysToRecoverIncomeFor = $max;
        }

        return $daysToRecoverIncomeFor;
    }
}
