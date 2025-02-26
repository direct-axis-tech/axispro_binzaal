<?php

namespace App\Models;

use App\Contracts\Flowable;
use App\Traits\Flowable as FlowableTraits;
use Illuminate\Database\Eloquent\Model;

class FlowableModel extends Model implements Flowable
{
    use FlowableTraits;
}
