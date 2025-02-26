<?php

namespace App\Notifications;

use App\Events\TaskTransitioned;
use App\Models\System\User;
use App\Traits\BroadcastableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TaskTransitionedNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;
    use BroadcastableNotification;

    /** 
     * The expiring event that triggered this notification
     * 
     * @var TaskTransitioned 
     */
    public $event;

    /**
     * Create a new notification instance.
     *
     * @param TaskTransitioned $event
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
     * @param  User  $notifiable
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
     * @param  User  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $currentTaskRecord = $this->event->currentTaskRecord;
        $nextTaskRecord = $this->event->nextTaskRecord;
        $data =  [
            'employee' => $currentTaskRecord->performer_name,
            'title'    => $currentTaskRecord->task_type_name,
            'reference'  => $currentTaskRecord->reference !== '' ? $currentTaskRecord->reference : null,
            'description'   => 'Approved'
        ];
        $data['actions'][] = [
            "title" => "View Task",
            "class" => "success",
            "url" => "/v3/system/tasks?".http_build_query([
                "InitiatedBy" => $currentTaskRecord->initiated_by,
                "TaskType" => $currentTaskRecord->task_type_id,
                "Ref" => $currentTaskRecord->reference,
                "TaskTransitionId" => $nextTaskRecord->task_transition_id
            ])
        ];
        return $data;
    }
}