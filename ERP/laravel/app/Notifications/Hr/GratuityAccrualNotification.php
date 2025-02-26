<?php

namespace App\Notifications\Hr;
use App\Models\Hr\Employee;
use App\Traits\BroadcastableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class GratuityAccrualNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;
    use BroadcastableNotification;

    public $data;

    /**
     * Create a new notification instance.
     *
     * @param Employee $data
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  Employee $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return $notifiable->notificationsVia($this);
    }


    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'type' => $this->data['type'],
            'trans_no' => $this->data['trans_no'],
            'reference' => $this->data['reference'],
            'as_of_date' => $this->data['as_of_date'],
            'trans_date' => $this->data['trans_date'],
            'actions' => [
                [
                    "title" => "View Journal",
                    "class" => "success",
                    "url" => "/ERP/gl/view/gl_trans_view.php?".http_build_query([
                        "type_id" => $this->data['type'],
                        "trans_no" => $this->data['trans_no']
                    ])
                ]
            ]
        ];
    }
}