<?php

namespace App\Traits;

use App\Models\Entity;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Support\Carbon;

trait BroadcastableNotification {

    /**
     * Get the broadcastable representation of the notification.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'notifiable_type' => Entity::where('model', get_class($notifiable))->value('id'),
            'notifiable_id' => $notifiable->id,
            'data' => $this->toArray($notifiable),
            'read_at' => null,
            'created_at' => (new Carbon())->toIso8601ZuluString('millisecond')
        ]);
    }
}