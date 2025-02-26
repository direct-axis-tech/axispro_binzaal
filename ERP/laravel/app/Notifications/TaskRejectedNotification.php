<?php

namespace App\Notifications;

use App\Events\TaskRejected;
use App\Models\System\User;
use App\Traits\BroadcastableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TaskRejectedNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;
    use BroadcastableNotification;

    /** 
     * The expiring event that triggered this notification
     * 
     * @var TaskRejected 
     */
    public $event;

    /**
     * Create a new notification instance.
     *
     * @param TaskRejected $event
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
        $taskRecord = $this->event->taskRecord;
        $data =  [
            'employee' => $taskRecord->performer_name,
            'title'    => $taskRecord->task_type_name,
            'reference'  => $taskRecord->reference !== '' ? $taskRecord->reference : null,
            'description'   => 'Rejected'
        ];
        $data['actions'][] = [
            "title" => "View Task",
            "class" => "success",
            "url" => "/v3/system/tasks?".http_build_query([
                "InitiatedBy" => $taskRecord->initiated_by,
                "TaskType" => $taskRecord->task_type_id,
                "Ref" => $taskRecord->reference,
                "TaskTransitionId" => $taskRecord->task_transition_id
            ])
        ];
        return $data;
    }
}