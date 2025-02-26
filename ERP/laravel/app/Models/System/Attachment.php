<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_attachments';

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
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    
}