<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_audit_trail';

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
