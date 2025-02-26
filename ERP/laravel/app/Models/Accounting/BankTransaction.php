<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class BankTransaction extends Model
{
    /** @var string CREDIT Transaction Type - Bank Payment */
    const CREDIT = 1;
    
    /** @var string DEBIT Transaction Type - Bank Receipt */
    const DEBIT = 2;

    /** @var string TRANSFER Transaction Type - Inter Bank Transfer */
    const TRANSFER = 4;

   /**
    * The table associated with the model.
    *
    * @var string
    */
   protected $table = '0_bank_trans';

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
     * Scopes this query with the type of transaction
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type) {
        return $query->where('type', $type);
    }

    /**
     * Scopes this query with the activeness of transaction
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query) {
        return $query->where('amount', '<>', 0);
    }

}
