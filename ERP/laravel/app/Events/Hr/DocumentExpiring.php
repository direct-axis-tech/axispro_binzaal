<?php

namespace App\Events\Hr;

use App\Models\CalendarEvent;
use App\Models\Document;
use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DocumentExpiring
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** 
     * The source for this event
     * 
     * @var CalendarEvent
     */
    public $calendarEvent;

    /** 
     * The id of the document
     * 
     * @var int 
     */
    public $documentId;

    /**
     * The current version of the document
     *
     * @var Document
     */
    public $document;

    /** 
     * The expiry date
     * 
     * @var Carbon 
     */
    public $expiresOn;

    /** 
     * The file path relative to storage directory
     * 
     * @var string 
     */
    public $file;

    /** 
     * When the event was supposed to be raised before the actual expiry
     * 
     * @var string 
     */
    public $notifyBefore;
    
    /**
     * Create a new event instance.
     *
     * @param CalendarEvent $calendarEvent
     * @return void
     */
    public function __construct($calendarEvent)
    {
        $this->calendarEvent = $calendarEvent;

        // The context at the time of saving the event
        $this->documentId = $calendarEvent->context['id'];
        $this->file = $calendarEvent->context['file'];
        $this->expiresOn = new Carbon($calendarEvent->context['expires_on']);
        $this->notifyBefore = $calendarEvent->context['notify_before'];

        // The current version of document in context
        $this->document = Document::find($this->documentId);
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
