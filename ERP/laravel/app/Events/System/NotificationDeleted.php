<?php

namespace App\Events\System;

use App\Models\DatabaseNotification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NotificationDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The notificationId that is in context
     *
     * @var string $notificationId
     */
    public $notificationId;

    /**
     * The notifiable that is in context
     *
     * @var mixed $notifiable
     */
    public $notifiable;

    /**
     * Create a new event instance.
     *
     * @param DatabaseNotification $notification
     * @return void
     */
    public function __construct($notification)
    {
        $this->notificationId = $notification->id;

        // The original notification is deleted so we will not be able to fetch this later
        $this->notifiable = $notification->notifiable;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel($this->notifiable->receivesBroadcastNotificationsOn());
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return ['notification' => ["id" => $this->notificationId]];
    }
}