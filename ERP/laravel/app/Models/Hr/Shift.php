<?php

namespace App\Models\Hr;

use App\Traits\CachesSyntheticAttributes;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use CachesSyntheticAttributes;

    const OFF_COLOR_CODE = '#a55353';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_shifts';

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
     * Indicates if the shift is already been used
     *
     * @return bool
     */
    public function getIsUsedAttribute()
    {
        return $this->getOrComputeAttribute('is_used', function() {
            return (
                $this->id == pref('hr.default_shift_id')
                || EmployeeShift::whereShiftId($this->id)->exists()
                || Attendance::whereBasedOnShiftId($this->id)->exists()
            );
        });
    }

    /**
     * Fix the dates in the correct order
     * 
     * @param \Carbon\CarbonImmutable ...$dates
     * @return void
     */
    public static function fixDatesInOrder(&...$dates) {
        $previous = reset($dates);

        for ($next = 1; $next < count($dates); $next++) {
            while ($previous > $dates[$next]) {
                $dates[$next] = $dates[$next]->addDay();
            }

            $previous = new CarbonImmutable($dates[$next]);
        }
    }

    /**
     * Format the timing of shift to a human readable string
     *
     * @param string|null $from
     * @param string|null $till
     * @param string|null $from2
     * @param string|null $till2
     * @return string|null
     */
    public static function formatTiming($from = null, $till = null, $from2 = null, $till2 = null)
    {
        $displayTimeFormat = 'h:i A';

        $timings = [];
        foreach (['', '2'] as $postfix) {
            $_from = 'from'.$postfix;
            $_till = 'till'.$postfix;

            $timing = implode(' - ', [
                $$_from ? Carbon::parse($$_from)->format($displayTimeFormat) : '--',
                $$_till ? Carbon::parse($$_till)->format($displayTimeFormat) : '--',
            ]);

            if ($timing == '-- - --') {
                $timing = null;
            }

            $timings[] = $timing;
        }

        // Format: 08:30 AM - 01:00 PM Then 03:00 PM - 07:00 PM
        return implode(' Then ', array_filter($timings)) ?: null;
    }
}
