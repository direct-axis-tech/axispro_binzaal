<?php

namespace App\Models\Labour;

use App\Traits\CachesSyntheticAttributes;
use Illuminate\Database\Eloquent\Model;


class Installment extends Model
{
    use CachesSyntheticAttributes;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_contract_installments';

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
     * Get the details associated with this installment
     */
    public function installmentDetails()
    {
        return $this->hasMany(InstallmentDetail::class, 'installment_id');
    }

    /**
     * Get the contract with this installment
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    /**
     * Indicates if the installment is already been invoiced
     *
     * @return bool
     */
    public function getIsUsedAttribute()
    {
        return $this->getOrComputeAttribute('is_used', function() {
            return InstallmentDetail::whereInstallmentId($this->id)->whereNotNull('invoice_ref')->exists();
        });
    }

}