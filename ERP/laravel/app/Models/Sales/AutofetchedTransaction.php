<?php

namespace App\Models\Sales;

use App\Traits\CachesSyntheticAttributes;
use Illuminate\Database\Eloquent\Model;

class AutofetchedTransaction extends Model
{
    use CachesSyntheticAttributes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_autofetched_trans';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Gets the invoiced item line
     */
    public function invoicedTransactions() {
        return $this
            ->hasMany(CustomerTransactionDetail::class, 'application_id', 'application_id')
            ->where('quantity', '>', 0);
    }

    
    /**
     * Indicates if the autofetch is already been used
     *
     * @return bool
     */
    public function getIsUsedAttribute()
    {
        return $this->getOrComputeAttribute('is_used', function() {
            return (
                CustomerTransactionDetail::where('quantity', '<>', 0)->where('application_id',$this->application_id)->exists()
            );
        });
    }

}