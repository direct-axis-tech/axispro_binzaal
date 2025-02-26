<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification as LaravelNotification;
use Illuminate\Support\Carbon;

class DatabaseNotification extends LaravelNotification
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_notifications';

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return (new Carbon($date))->toIso8601ZuluString('millisecond');
    }
}
