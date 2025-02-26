<?php

namespace App\Events\Sales;

use App\Models\Sales\SalesOrder;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class JobOrderCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    /**
     * salesOrder
     *
     * @var \App\Models\Sales\SalesOrder
     */
    public $salesOrder;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(SalesOrder $salesOrder)
    {
        $this->salesOrder = $salesOrder;
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
