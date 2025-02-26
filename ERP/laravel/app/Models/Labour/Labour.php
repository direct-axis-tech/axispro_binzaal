<?php

namespace App\Models\Labour;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use LogicException;

class Labour extends Model
{
    const DOC_TYPE_PASSPORT    = 7;
    const DOC_TYPE_VISA        = 8;
    const DOC_TYPE_LABOUR_CARD = 9;
    const DOC_TYPE_PASSPORT_SIZE_PHOTO = 10;
    const DOC_TYPE_FULL_BODY_PHOTO = 11;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_labours';

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
        'dob',
        'application_date',
        'date_of_joining'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'skills' => 'array',
        'languages' => 'array',
        'locations' => 'array',
        'is_available' => 'boolean',
        'inactive' => 'boolean'
    ];

    public function documents()
    {
        return $this->morphMany(\App\Models\Document::class, __FUNCTION__, 'entity_type', 'entity_id');
    }

    /**
     * This labour's nationality
     *
     * @return void
     */
    public function country()
    {
        return $this->belongsTo(\App\Models\Country::class, 'nationality', 'code');
    }
    
    /**
     * This agent associated with this labour
     *
     * @return void
     */
    public function agent()
    {
        return $this->belongsTo(\App\Models\Labour\Agent::class, 'agent_id', 'supplier_id');
    }

    /**
     * Shortcut for formattedName
     */
    public function getFormattedNameAttribute()
    {
        return $this->maid_ref . ' - ' . $this->name;
    }

    public function document_path($path = '')
    {
        return "docs/labours".($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Checks if the maid is available the the provided date
     *
     * @param string $maid_id
     * @param string $date
     * @param integer $delta_qty
     * @return boolean
     */
    public static function isValidInventoryUpdate($maid_id, $date, $delta_qty=-1)
    {
        if (abs($delta_qty) > 1) {
            throw new LogicException("There cannot be more than one quantity of a person");
        }

        // check stock status on date
        $qohQuery = DB::table('0_stock_moves')
            ->select(
                DB::raw('IFNULL(SUM(qty), 0) as qty'),
                'tran_date'
            )
            ->where('maid_id', $maid_id);

        $qty = (clone $qohQuery)->where('tran_date', '<=', $date)->value('qty') ?: 0;

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
                    ->orderBy('tran_Date'),
                'daily'
            )
            ->crossJoin(DB::raw('(select @total := 0) as total_var'));

        $qtyQuery = DB::query()
            ->selectRaw('? + total as qty', [$qty])
            ->addSelect('tran_date')
            ->fromSub($runningTotalQuery, 'stock_status');

        if ($delta_qty > 0) {
            $maxQtyResult = (clone $qtyQuery)
                ->orderByDesc('total')
                ->orderBy('tran_date')
                ->first();

            if ($maxQtyResult && ($maxQtyResult->qty > $qty))
                $qty = $maxQtyResult->qty;
        
            return !($qty >= 1);
        }

        else {
            $minQtyResult = (clone $qtyQuery)
                ->orderBy('total')
                ->orderBy('tran_date')
                ->first();

            if ($minQtyResult && ($minQtyResult->qty < $qty))
                $qty = $minQtyResult->qty;

            return !(-$delta_qty > $qty);
        }
    }
}
