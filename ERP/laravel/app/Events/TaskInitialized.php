<?php

namespace App\Events;

use App\Models\TaskRecord;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class TaskInitialized
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** 
     * task
     * 
     * @var TaskRecord 
     */
    public $taskRecord;
    /**
     * Create a new event instance.
     *
     * @param TaskRecord $document
     * @return void
     */
    public function __construct(TaskRecord $taskRecord)
    {
        $this->taskRecord = $taskRecord;
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