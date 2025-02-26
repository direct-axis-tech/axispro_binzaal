<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class JournalTransaction extends Model
{
    /** @var string JOURNAL Transaction Type - Journal Entry */
    const JOURNAL = 0;

    /** @var string COST_UPDATE Transaction Type - Cost Updation */
    const COST_UPDATE = 35;

    /** @var string PAYROLL Transaction Type - Payroll */
    const PAYROLL = 60;

   /**
    * The table associated with the model.
    *
    * @var string
    */
   protected $table = '0_journal';

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
    * The gls associated with journal
    *
    * @return Illuminate\Database\Eloquent\Relations\HasMany
    */
   public function gl() {
        return $this->hasMany(LedgerTransaction::class, 'type_no', 'trans_no')->where('type', self::JOURNAL);
   }

   /**
     * Scopes this query with the activeness of transaction
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->whereRaw('amount <> 0');
    }
}
