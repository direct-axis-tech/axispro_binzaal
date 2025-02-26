<?php

namespace App\Models\Hr;

use App\Models\Hr\PayElement;
use Illuminate\Database\Eloquent\Model;
use App\Traits\InactiveModel;

class SubElement extends Model
{
    use InactiveModel;
    
    protected $table = '0_sub_elements';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];


    public static function getDeductionSubElements()
    {
        return self::where('type', PayElement::TYPE_DEDUCTION)
            ->where('inactive', 0)
            ->orderBy('seq_no')
            ->get();
    }

    public static function getAllowanceSubElements()
    {
        return self::where('type', PayElement::TYPE_ALLOWANCE)
            ->where('inactive', 0)
            ->orderBy('seq_no')
            ->get();
    }

}