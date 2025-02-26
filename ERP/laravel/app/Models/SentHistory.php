<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SentHistory extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_sent_history';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];
}
