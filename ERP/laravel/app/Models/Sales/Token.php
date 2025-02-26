<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_axis_front_desk';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Scope this query using today's date
     *
     * @param Builder $query
     */
    public function scopeOfToday($query)
    {
        $query->whereRaw('date(created_at) = ?', [now()->toDateString()]);
    }
}
