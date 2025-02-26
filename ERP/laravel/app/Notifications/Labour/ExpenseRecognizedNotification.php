<?php

namespace App\Notifications\Labour;

use App\Events\Labour\ExpenseRecognized;
use App\Models\Accounting\Dimension;
use App\Models\Inventory\StockCategory;
use App\Traits\BroadcastableNotification;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ExpenseRecognizedNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;
    use BroadcastableNotification;

    /** 
     * The expired event that triggered this notification
     * 
     * @var ExpenseRecognized
     */
    public $event;

    /**
     * Create a new notification instance.
     *
     * @param ExpenseRecognized $event
     * @return void
     */
    public function __construct(ExpenseRecognized $event)
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
        $data = [
            'contractId' => $this->event->contractId,
            'categoryId' => $this->event->categoryId,
            'contractRef' => $this->event->contractRef,
            'invoiceRef' => $this->event->invoiceRef,
            'date' => $this->event->calendarEvent->scheduled_at->toDateString(),
            'lastRecognizedAt' => $this->event->lastRecognizedAt,
            'daysRecognized' => $this->event->daysRecognized,
            'amount' => $this->event->amount,
            'balance' => $this->event->balance,
        ];

        if ($this->event->journal) {
            $data['actions'][] = [
                "title" => "View Journal",
                "class" => "success",
                "url" => "/ERP/gl/view/gl_trans_view.php?".http_build_query([
                    "type_id" => $this->event->journal->type,
                    "trans_no" => $this->event->journal->trans_no
                ])
            ];
        }

        return $data;
    }
}
