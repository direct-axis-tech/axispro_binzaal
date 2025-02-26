<?php

namespace App\Events\System;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TransactionVoided
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The type of this transaction
     *
     * @var int $type
     */
    public $type;

    /**
     * The transNo of this transaction
     *
     * @var int $transNo;
     */
    public $transNo;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($type, $transNo)
    {
        $this->type = $type;
        $this->transNo = $transNo;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
