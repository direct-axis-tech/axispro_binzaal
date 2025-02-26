<?php

namespace App\Models\Labour;

use App\Models\Bank;
use Illuminate\Database\Eloquent\Model;


class InstallmentDetail extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_contract_installment_details';

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
     * Get the installment associated with this detail
     */
    public function installment()
    {
        return $this->belongsTo(Installment::class, 'installment_id');
    }

    /**
     * Get the bank associated with the cheque
     */
    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }
}