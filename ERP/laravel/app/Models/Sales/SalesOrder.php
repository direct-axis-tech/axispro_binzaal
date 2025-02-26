<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    /** Transaction Type - Sales Order */
    const ORDER = 30;

    /** Transaction Type - Sales Quote */
    const QUOTE = 32;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_sales_orders';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Scopes this query with the type of transaction
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type) {
        return $query->where('trans_type', $type);
    }
}
