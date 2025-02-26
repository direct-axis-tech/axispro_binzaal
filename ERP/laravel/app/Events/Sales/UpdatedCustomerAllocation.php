<?php

namespace App\Events\Sales;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class UpdatedCustomerAllocation
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** 
     * The transaction type
     * 
     * @var string 
     */
    public $transType;

    /** 
     * The transaction no
     * 
     * @var string 
     */
    public $transNo;

    /** 
     * The customer id
     * 
     * @var string 
     */
    public $personId;
    
    /** 
     * The transaction date
     * 
     * @var string 
     */
    public $tranDate;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(string $transType, string $transNo, string $personId, string $tranDate)
    {
        $this->transType = $transType;
        $this->transNo = $transNo;
        $this->personId = $personId;
        $this->tranDate = $tranDate;
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
