<?php

namespace App\Models\Purchase;

use App\Traits\InactiveModel;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use InactiveModel;

    const TYPE_SUPPLIER = -1;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_suppliers';

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
    protected $primaryKey = 'supplier_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function getFormattedNameAttribute()
    {
        return $this->supp_ref . ' - ' . $this->supp_name;
    }
}
