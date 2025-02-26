<?php

namespace App\Traits;

use App\Models\DatabaseNotification;
use Illuminate\Notifications\Notifiable as LaravelNotifiable;

trait Notifiable {
    
    use LaravelNotifiable;

    /**
     * Get the entity's notifications.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function notifications()
    {
        return $this->morphMany(DatabaseNotification::class, 'notifiable')->orderBy('created_at', 'desc');
    }
}