<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_service_requests';

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
     * Scope this query using today's date
     *
     * @param Builder $query
     */
    public function scopeOfToday($query)
    {
        $query->whereRaw('date(created_at) = ?', [now()->toDateString()]);
    }
}
