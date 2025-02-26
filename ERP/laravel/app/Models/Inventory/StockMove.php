<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use App\Models\Labour\Labour;

class StockMove extends Model
{
    const STOCK_RETURN = 15;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_stock_moves';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'trans_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the maid associated with this movement
     */
    public function maid()
    {
        return $this->belongsTo(Labour::class, 'maid_id');
    }
    

}
