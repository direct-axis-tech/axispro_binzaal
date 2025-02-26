<?php

namespace App\Events\Labour;

use App\Models\Labour\Installment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class InstallmentCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** 
     * The customer invoice
     * 
     * @var Installment 
     */
    public $installment;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Installment $installment)
    {
        $this->installment = $installment;
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