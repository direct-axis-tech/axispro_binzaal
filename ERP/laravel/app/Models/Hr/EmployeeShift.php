<?php

namespace App\Models\Hr;

use App\Traits\CachesSyntheticAttributes;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

class EmployeeShift extends Model
{
    use CachesSyntheticAttributes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_emp_shifts';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Returns true if its defined as off else false
     *
     * @return boolean
     */
    public function getIsOffAttribute()
    {
        return $this->id != null and $this->shift_id == null; 
    }

    /**
     * Returns true if its a split shift else false
     *
     * @return boolean
     */
    public function getIsSplitShiftAttribute()
    {
        return $this->starts_at2 != null;
    }

    /**
     * Returns true if this shift spans midnight
     *
     * @return boolean
     */
    public function getSpansMidnightAttribute()
    {
        return (
            $this->ends_at->startOfDay() > $this->starts_at->startOfDay()
            || (
                $this->starts_at2
                && (
                    $this->starts_at2->startOfDay() > $this->ends_at->startOfDay()
                    || $this->ends_at2->startOfDay() > $this->starts_at2->startOfDay()
                )
            )
        );
    }

    /**
     * The Shift associated with this day for this employee
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Returns when the first shift starts from
     *
     * @return null|\Carbon\CarbonImmutable
     */
    public function getStartsAtAttribute()
    {
        return $this->getOrComputeAttribute('starts_at', function () {
            return $this->shift == null
                ? null
                : new CarbonImmutable($this->date . ' ' . $this->shift->from);
        });
    }

    /**
     * Returns when the first shift will end
     *
     * @return null|\Carbon\CarbonImmutable
     */
    public function getEndsAtAttribute()
    {
        return $this->getOrComputeAttribute('ends_at', function () {
            if ($this->shift == null) {
                return null;
            }
            
            $endsAt = new CarbonImmutable($this->date . ' ' . $this->shift->till);
            Shift::fixDatesInOrder($this->starts_at, $endsAt);
            return $endsAt;
        });
    }
    
    /**
     * Returns when the second shift will starts from
     *
     * @return null|\Carbon\CarbonImmutable
     */
    public function getStartsAt2Attribute()
    {
        return $this->getOrComputeAttribute('starts_at2', function () {
            if ($this->shift == null || $this->shift->from2 == null) {
                return null;
            }
            
            $startsAt2 = new CarbonImmutable($this->date . ' ' . $this->shift->from2);
            Shift::fixDatesInOrder($this->ends_at, $startsAt2);
            return $startsAt2;
        });
    }

    /**
     * Returns when the second shift will end
     *
     * @return null|\Carbon\CarbonImmutable
     */
    public function getEndsAt2Attribute()
    {
        return $this->getOrComputeAttribute('ends_at2', function () {
            if ($this->shift == null || $this->shift->till2 == null) {
                return null;
            }
            
            $endsAt2 = new CarbonImmutable($this->date . ' ' . $this->shift->till2);
            Shift::fixDatesInOrder($this->starts_at2, $endsAt2);
            return $endsAt2;
        });
    }
}
