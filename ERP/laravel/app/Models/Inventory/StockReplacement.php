<?php

namespace App\Models\Inventory;

use App\Models\Labour\Contract;
use App\Traits\InactiveModel;
use Illuminate\Database\Eloquent\Model;

class StockReplacement extends Model
{
    use InactiveModel;

    const STOCK_REPLACEMENT = 19;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_stock_replacement';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The contract associated with this replacement
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    /**
     * The incoming stock move associated with this replacement
     */
    public function incomingStockMove()
    {
        return $this->hasOne(StockMove::class, 'trans_no', 'trans_no')
            ->where('type', $this->type)
            ->where('qty', 1);
    }

    /**
     * The outgoing stock move associated with this replacement
     */
    public function outgoingStockMove()
    {
        return $this->hasOne(StockMove::class, 'trans_no', 'trans_no')
            ->where('type', $this->type)
            ->where('qty', -1);
    }

    /**
     * The stock movements associated with this replacement
     */
    public function stockMoves()
    {
        return $this->hasMany(StockMove::class, 'trans_no', 'trans_no')
            ->where('type', $this->type);
    }
}