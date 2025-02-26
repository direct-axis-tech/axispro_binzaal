<?php

namespace App\Events\Accounting;

use App\Models\Accounting\BankTransaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class BankDebited
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** 
     * The bank receipt
     * 
     * @var BankTransaction 
     */
    public $receipt;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(BankTransaction $receipt)
    {
        $this->receipt = $receipt;
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
