<?php
 
namespace App\Listeners;

use App\Events\Hr\DocumentExpired;
use App\Events\Hr\DocumentExpiring;
use App\Events\Hr\DocumentUploaded;
use App\Models\CalendarEvent;
use App\Models\CalendarEventType;
use App\Models\Entity;
use App\Models\EntityGroup;
use App\Models\System\User;
use App\Notifications\Hr\DocumentExpiredNotification;
use App\Notifications\Hr\DocumentExpiringNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class DocumentExpiryEventSubscriber
{ 
    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(DocumentUploaded::class, [$this, 'handleDocumentUploaded']);
        $events->listen(DocumentExpiring::class, [$this, 'handleDocumentExpiring']);
        $events->listen(DocumentExpired::class, [$this, 'handleDocumentExpired']);
    }

    /**
     * Handles the event when a document is uploaded
     * 
     * @param DocumentUploaded $event
     * @return void
     */
    public function handleDocumentUploaded($event) {
        $doc = $event->document;

        if (empty($doc->expires_on)) return;

        $context = [
            'id' => $doc->id,
            'expires_on' => $doc->expires_on->format(DB_DATE_FORMAT),
            'file' => $doc->file
        ];

        CalendarEvent::create([
            'type_id' => CalendarEventType::EMPDOC_EXPIRED,
            'scheduled_at' => $doc->expires_on,
            'context' => $context
        ]);

        $type = $doc->type;
        if (empty($type->notify_before)) return;

        $expiresOn = new Carbon($doc->expires_on);
        $notifyBefore = "{$type->notify_before} {$type->notify_before_unit}";
        $notifyOn = $expiresOn->sub($notifyBefore)->format(DB_DATETIME_FORMAT);
        $context['notify_before'] = $notifyBefore;

        CalendarEvent::create([
            'type_id' => CalendarEventType::EMPDOC_EXPIRING,
            'scheduled_at' => $notifyOn,
            'context' => $context
        ]);
    }

    /**
     * Handles the event when a document is about to expire
     *
     * @param DocumentExpiring $event
     * @return void
     */
    public function handleDocumentExpiring($event) {
        // If the document is alreay expired we will only sent the expired notificaion
        if (($currentDateTime = new Carbon()) >= $event->expiresOn) return;

        // Make sure that the document is still relevant
        if ($this->isDocumentStale($event)) return;

        // Send notification
        $notifiables = $this->getNotifiables($event);
        
        Notification::send($notifiables, new DocumentExpiringNotification($event));
    }

    /**
     * Handles the event when a document is expired
     *
     * @param DocumentExpired $event
     * @return void
     */
    public function handleDocumentExpired($event) {
        // Make sure that the document is still relevant
        if ($this->isDocumentStale($event)) return;

        // Send notification
        $notifiables = $this->getNotifiables($event);
        
        Notification::send($notifiables, new DocumentExpiredNotification($event));
    }

    /**
     * Get the group to which the notification is to be sent
     * 
     * @param DocumentExpired|DocumentExpiring $event
     * @return Collection|User[]
     */
    protected function getNotifiables($event) {
        $group = EntityGroup::find(EntityGroup::EMP_DOC_EXPIRY_REMINDER);
        $user = User::whereType(Entity::EMPLOYEE)
            ->whereEmployeeId($event->document->entity_id)
            ->first();

        return $group->distinctMemberUsers($user);
    }

    /**
     * Check if the document is stale or not
     * 
     * @param DocumentExpired|DocumentExpiring $event
     * @return boolean
     */
    protected function isDocumentStale($event) {
        $document = $event->document;

        // Check if the document exists or is now removed
        if (!$document) return true;

        // Check the employee is active or not
        if (!$document->owner->is_active) return true;

        // Check the old file still exists
        if (!Storage::exists($event->file) || $event->file != $document->file) return true;

        // Check the expiry date is not updated
        if ($event->expiresOn != $document->expires_on) return true;

        return false;
    }
}