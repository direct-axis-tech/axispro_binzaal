<?php

namespace App\Events;

use App\Models\TaskRecord;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class TaskTransitioned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** 
     * @var TaskRecord 
     */
    public $currentTaskRecord;

    /** 
     * @var TaskRecord 
     */
    public $nextTaskRecord;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($currentTaskRecord, $nextTaskRecord)
    {
        $this->currentTaskRecord = $currentTaskRecord;
        $this->nextTaskRecord = $nextTaskRecord;
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