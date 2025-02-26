<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class CategoryGroup extends Model
{
    /** Fillet King */
    const FILLETKING = '-1';

    /** Tap Cafeteria */
    const TAPCAFETERIA = '-1';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_category_groups';

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