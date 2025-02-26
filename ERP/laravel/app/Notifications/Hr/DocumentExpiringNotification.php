<?php

namespace App\Notifications\Hr;

use App\Events\Hr\DocumentExpiring;
use App\Models\System\User;
use App\Traits\BroadcastableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class DocumentExpiringNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;
    use BroadcastableNotification;

    /** 
     * The expiring event that triggered this notification
     * 
     * @var DocumentExpiring 
     */
    public $event;

    /**
     * Create a new notification instance.
     *
     * @param DocumentExpiring $event
     * @return void
     */
    public function __construct($event)
    {
        $this->event = $event;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  User $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return $notifiable->notificationsVia($this);
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $document = $this->event->document;
        
        return [
            'docType' => $document->type->name,
            'employee' => $document->owner->formatted_name,
            'expiresOn' => $document->expires_on,
            'docId' => $document->id,
            'employeeId' => $document->owner->id,
            'eventId' => $this->event->calendarEvent->id
        ];
    }
}
