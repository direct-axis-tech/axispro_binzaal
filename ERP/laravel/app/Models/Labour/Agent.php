<?php

namespace App\Models\Labour;

use App\Models\Purchase\Supplier as Model;

class Agent extends Model
{
    const TYPE_AGENT = 1;

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
 
        static::addGlobalScope('ofTypeAgent', function ($builder) {
            $builder->where('supplier_type', self::TYPE_AGENT);
        });
    }

    public function getIdAttribute()
    {
        return $this->supplier_id;
    }
    
    public function getNameAttribute()
    {
        return $this->supp_name;
    }
    
    public function getRefAttribute()
    {
        return $this->supp_ref;
    }
}
