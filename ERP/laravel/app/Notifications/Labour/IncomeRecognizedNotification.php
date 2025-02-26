<?php

namespace App\Notifications\Labour;

use App\Events\Labour\IncomeRecognized;
use App\Models\Inventory\StockCategory;
use App\Models\Labour\Contract;
use App\Traits\BroadcastableNotification;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class IncomeRecognizedNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;
    use BroadcastableNotification;

    /** 
     * The expired event that triggered this notification
     * 
     * @var IncomeRecognized
     */
    public $event;

    /**
     * Create a new notification instance.
     *
     * @param IncomeRecognized $event
     * @return void
     */
    public function __construct(IncomeRecognized $event)
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
            'date' => $this->event->calendarEvent->scheduled_at->toDateString(),
            'lastRecognizedAt' => $this->event->lastRecognizedAt,
            'daysRecognized' => $this->event->daysRecognized,
            'amount' => $this->event->amount,
            'balance' => $this->event->balance,
        ];
        $contract = Contract::find($this->event->contractId);

        switch ($data['categoryId']) {
            case StockCategory::DWD_PACKAGEONE:
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
                break;
            case StockCategory::DWD_PACKAGETWO:
                $periodFrom = (new CarbonImmutable($data['date']));
                $periodTill = $periodFrom->addDays($data['daysRecognized'] - 1);
                $data['actions'][] = [
                    "title" => "Make Invoice",
                    "class" => "info",
                    "url" => "/ERP/sales/sales_order_entry.php?".http_build_query([
                        "NewInvoice" => 0,
                        "ContractID" => $this->event->contractId,
                        "CalendarEventId" => $this->event->calendarEvent->id,
                        "TransDate" => $data['date'],
                        "PeriodFrom" => $periodFrom->toDateString(),
                        "PeriodTill" => $periodTill->toDateString(),
                        "TransAmount" => $data['amount'],
                        "dim_id" => $contract->dimension_id,
                    ])
                ];
                break;
        }

        return $data;
    }
}
