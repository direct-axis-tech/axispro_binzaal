<?php

namespace App\Events\Sales;

use App\Models\Sales\CustomerTransaction;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class CustomerRefunded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** 
     * The customer refund
     * 
     * @var CustomerTransaction 
     */
    public $refund;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(CustomerTransaction $refund)
    {
        $this->refund = $refund;
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
