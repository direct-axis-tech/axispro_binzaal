<?php

namespace App\Notifications;

use App\Models\System\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TransactionAssignedNotification extends Notification
{
    use Queueable;

    /** 
     * The transaction event that triggered this notification
     * 
     * @var \App\Events\Sales\JobOrderCreated 
     */
    public $event;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($event)
    {
        $this->event = $event;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
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
        $salesOrder = $this->event->salesOrder;
        $user       = User::find($salesOrder->created_by);
        $createdBy  = $user ? $user->name : '-';

        $data = [
            'title'       => 'Transaction',
            'description' => 'Alert',
            'reference'   => $salesOrder->reference,
            'order_date' => $salesOrder->ord_date,
            'assigned_by_id' =>  $user->id,   
            'assigned_by' => $createdBy,
            'assigned_at' => date('D-M-Y h:i:s', strtotime($salesOrder->transacted_at)),
        ];
        $data['actions'][] = [
            "title" => "View Transaction",
            "class" => "success",
            "url" => "/v3/sales/orders/details?".http_build_query([
                "order_reference" => $salesOrder->reference,
            ])
        ];
        return $data;
    }
}

