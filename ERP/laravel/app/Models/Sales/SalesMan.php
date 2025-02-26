<?php

namespace App\Models\Sales;

use App\Traits\InactiveModel;
use Illuminate\Database\Eloquent\Model;

class SalesMan extends Model
{
    use InactiveModel;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_salesman';

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
    protected $primaryKey = 'salesman_code';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
