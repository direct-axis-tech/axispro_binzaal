<?php

namespace App\Events\Sales;

use App\Models\Sales\CustomerTransaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CustomerCredited
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The credit note
     *
     * @var CustomerTransaction
     */
    public $creditNote;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(CustomerTransaction $creditNote)
    {
        $this->creditNote = $creditNote;
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
