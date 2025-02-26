<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;

class CustomerTransactionDetail extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_debtor_trans_details';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];
}
