<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;

class SalesOrderDetail extends Model
{
    const ORDER_LINE_ITEM = 33;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_sales_order_details';

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
}
