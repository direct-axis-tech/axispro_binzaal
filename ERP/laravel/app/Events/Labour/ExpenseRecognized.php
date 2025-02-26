<?php

namespace App\Events\Labour;

use App\Models\Accounting\JournalTransaction;
use App\Models\CalendarEvent;
use Carbon\CarbonImmutable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class ExpenseRecognized
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** 
     * The source for this event
     * 
     * @var CalendarEvent
     */
    public $calendarEvent;

    /**
     * The id of contract, for which: the expense is recognized
     *
     * @var int
     */
    public $contractId;

    /**
     * The reference no of the contract
     *
     * @var [type]
     */
    public $contractRef;

    /**
     * The from date
     *
     * @var CarbonImmutable
     */
    public $contractFrom;

    /**
     * The till date
     *
     * @var CarbonImmutable
     */
    public $contractTill;

    /**
     * The total contract amount
     *
     * @var float
     */
    public $contractAmount;

    /**
     * The category of this contract
     *
     * @var float
     */
    public $categoryId;

    /**
     * The date at which previous expense was recognized
     *
     * @var CarbonImmutable
     */
    public $lastRecognizedAt;

    /**
     * The number of days for the expense was recognized
     *
     * @var int
     */
    public $daysRecognized;

    /**
     * The amount(expense) that is applicable for this recognition
     *
     * @var float
     */
    public $amount;

    /**
     * The balance(expense) that is to be recognized
     *
     * @var float
     */
    public $balance;

    /**
     * The journal entry associated with this event if any
     *
     * @var JournalTransaction
     */
    public $journal = null;

    /**
     * The total purchase amount
     *
     * @var float
     */
    public $purchaseAmount;

    /**
     * The supplier invoice reference
     *
     * @var string
     */
    public $invoiceRef;

    /**
     * Create a new event instance.
     *
     * @param CalendarEvent $calendarEvent
     * @return void
     */
    public function __construct($calendarEvent)
    {
        $this->calendarEvent = $calendarEvent;

        $this->contractId = $calendarEvent->context['contract_id'];
        $this->contractRef = $calendarEvent->context['contract_ref'];
        $this->contractFrom = (new CarbonImmutable($calendarEvent->context['contract_from']))->midDay();
        $this->contractTill = (new CarbonImmutable($calendarEvent->context['contract_till']))->midDay();
        $this->contractAmount = $calendarEvent->context['contract_amount'];
        $this->categoryId = $calendarEvent->context['category_id'];
        $this->lastRecognizedAt = (new CarbonImmutable($calendarEvent->context['last_recognized_at']))->midDay();
        $this->daysRecognized = $calendarEvent->context['days_recognized'];
        $this->amount = $calendarEvent->context['amount'];
        $this->balance = $calendarEvent->context['balance'];
        $this->invoiceRef = $calendarEvent->context['invoice_ref'];
        $this->purchaseAmount = $calendarEvent->context['purchase_amount'];
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

    /**
     * Sets the journal entry associated with this event
     *
     * @param JournalTransaction $journal
     * @return void
     */
    public function setJournal(JournalTransaction $journal)
    {
        $this->journal = $journal;
    }
}
