<?php

namespace App\Models\Inventory;

use App\Traits\InactiveModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StockItem extends Model
{
    use InactiveModel;

    /** Al Adheed Others - Legal Service */
    const LEGAL_SERVICE = '---------';

    /** Al Adheed Others - Legal Agreement */
    const LEGAL_AGREEMENT = '---------';

    /** Al Adheed Others - Legal Agreement (Lawyer Commission 70%) */
    const LAW_FIRM_AGREEMENT_LC70 = '---------';

    /** Al Adheed Others - Legal Agreement (Lawyer Commission 40%) */
    const LAW_FIRM_AGREEMENT_LC40 = '---------';
    
    /** Insurance Office - Comprehensive Insurance */
    const COMPREHENSIVE_INSURANCE = '---------';

    const TAS_AUTO = 'TAS_AUTO';
    const TAS_AUTO_19 = 'TAS_AUTO19';
    const TAS_AUTO_240 = 'TAS_AUTO240';
    const TAS_AUTO_40 = 'TAS_AUTO40';
    const TWJ_AUTO_151_2 = 'TWJ_AUTO151_2';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_stock_master';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'stock_id';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    public function getFormattedNameAttribute()
    {
        return $this->stock_id . ' - ' . $this->description;
    }

    /**
     * Check whether change in stock on date would not cause negative qoh in stock history
     * 
     * Returns null on success
     * or maximum available quantity with respective date on failure.  
     * 
     * Running balance is checked on daily basis only, as we do not control time of transaction.
     *
     * @param string $stockId Stock ID of the item against which: the history needs to be checked
     * @param double $deltaQty Change in stock qty at `$date`
     * @param string|null $location Location at which the inventory is being changed
     * @param string|null $date date of the transaction; When set to null checks history against today.
     * @param string|null $soLineReference sales order line reference against the history is being checked
     *
     * @return null|array[]
     */
    public static function causesNegativeStock(
        $stockId,
        $deltaQty,
        $location=null,
        $date=null,
        $soLineReference=null,
        $maid_id = null
    )
    {
        // an increase in qty is always safe
        if ($deltaQty >= 0) {
            return null;
        }

        if (!isset($date)) {
            $date = Today();
        }

        $date = date2sql($date);

        // check stock status on date
        $qohQuery = DB::table('0_stock_moves')
            ->select(
                DB::raw('IFNULL(SUM(qty), 0) as qty'),
                'tran_date'
            );

        if ($location) {
            $qohQuery->where('loc_code', $location);
        }

        if ($maid_id) {
            $qohQuery->where('maid_id', $maid_id);
        } else {
            $qohQuery->where('stock_id', $stockId);
        }

        if ($soLineReference) {
            $qohQuery->where('so_line_reference', $soLineReference);
        }

        $qoh = (clone $qohQuery)->where('tran_date', '<=', $date)->first();
        $qoh = $qoh ? (array)$qoh : ['qty' => 0, 'tran_date' => $date];

        // check stock status after the date for any negatives
        $runningTotalQuery = DB::query()
            ->select(
                'daily.tran_date',
                'daily.qty',
                DB::raw('(@total := @total + `daily`.`qty`) as `total`')
            )
            ->fromSub(
                (clone $qohQuery)
                    ->where('tran_date', '>', $date)
                    ->groupBy('tran_date')
                    ->orderBy('tran_date'),
                'daily'
            )
            ->crossJoin(DB::raw('(select @total := 0) as total_var'));

        $qtyQuery = DB::query()
            ->selectRaw('? + total as qty', [$qoh['qty']])
            ->addSelect('tran_date')
            ->fromSub($runningTotalQuery, 'stock_status');

        $minQtyResult = (clone $qtyQuery)
            ->orderBy('total')
            ->orderBy('tran_date')
            ->first();

        if ($minQtyResult && ($minQtyResult->qty < $qoh['qty'])) {
            $qoh = (array)$minQtyResult;
        }

        return -$deltaQty > $qoh['qty'] ? $qoh : null;
    }
}