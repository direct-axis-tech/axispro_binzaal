<?php
 
namespace App\Listeners\Labour;

use App\Events\Labour\ContractDelivered;
use App\Models\CalendarEvent;
use App\Models\CalendarEventType;
use App\Models\Inventory\StockCategory;
use App\Models\Labour\Contract;
use Carbon\CarbonImmutable;
use stdClass;

class CalculateExpenseRecognitionDates
{
    /**
     * Start date of the contract
     *
     * @var CarbonImmutable
     */
    protected $startDate = null;

    /**
     * End date of the contract
     *
     * @var CarbonImmutable
     */
    protected $endDate = null;

    /**
     * Amount per day
     *
     * @var float
     */
    protected $amountPerDay = null;

    /**
     * The purchase record related to the maid
     *
     * @var stdClass
     */
    protected $purchaseRecord = null;

    /**
     * The entire schedule for recognizing the expense on this contract
     *
     * @var array{
     *  last_recognized_at: CarbonImmutable,
     *  date: CarbonImmutable,
     *  days_recognized: int,
     *  amount: float
     * }[]
     */
    protected $expenseRecognitionSchedules = [];
 
    /**
     * Handle the event.
     */
    public function handle(ContractDelivered $event): void
    {
        // Expense recognition is only applicable to package one
        if ($event->contract->category_id != StockCategory::DWD_PACKAGEONE) {
            return;
        }

        // If there is no information related to purchase, we can't calculate the expense
        if (empty($this->purchaseRecord = $event->contract->purchase_record)) {
            return;
        }

        // Check if there is already any expense recognition schedules.
        // Expense needs to be only recognized once
        if (
            CalendarEvent::query()
                ->whereTypeId(CalendarEventType::LBREXPENSE_RECOGNIZED)
                ->where('context->contract_id', $event->contract->id)
                ->exists()
        ) {
            return;
        }

        $this->startDate = (new CarbonImmutable($event->contract->contract_from))->startOfDay();
        $this->endDate = (new CarbonImmutable($event->contract->contract_till))->startOfDay();
        $this->amountPerDay = round2($this->purchaseRecord->amount / ($this->startDate->diffInDays($this->endDate) + 1), user_price_dec());
        $this->generateSchedulesForPackageOne();
        $this->storeEvents($event);
    }

    /**
     * Generates the schedule for package one
     *
     * @return void
     */
    protected function generateSchedulesForPackageOne()
    {
        $lastScheduleDate = null;
        $fromDate = $this->startDate;
        $decimal = user_price_dec();
        $scheduleDate = $this->startDate->addDays(Contract::PKG_ONE_EXPENSE_RECOGNITION_DELAY)->endOfMonth()->startOfDay();

        do {
            $daysRecognized = $fromDate->diffInDays($scheduleDate) + 1;
            $amount = round2($this->amountPerDay * $daysRecognized, $decimal); 
            $totalRecognizedAmount = round2(array_sum(array_column($this->expenseRecognitionSchedules, 'amount')), $decimal);
            $balance = round2($this->purchaseRecord->amount - $totalRecognizedAmount, $decimal);
            
            /*
            | Since we are dealing with rounded figures, The total recognized
            | amount can either be less than or greater than the total expense amount.
            | So, we allocate the remaining amount to the last of the month to avoid
            | rounding errors
            |
            | Examples using a one year contract period:
            | Case 1:
            |      expenseAmount = 5050
            |      amountPerMonth = 5050 / 12 = 420.83
            |      totalRecognizedAmount = 420.83 * 12 = 5049.96
            | Case 2:
            |      expenseAmount = 5150
            |      amountPerMonth = 5150 / 12 = 429.17
            |      totalRecognizedAmount = 429.17 * 12 = 5150.04
            */
            if ($scheduleDate->format('Y-m') == $this->endDate->format('Y-m')) {
                $amount = $balance;
                $daysRecognized = $fromDate->diffInDays($this->endDate) + 1;
            }

            $this->expenseRecognitionSchedules[] = [
                'last_recognized_at' => $lastScheduleDate,
                'date' => $scheduleDate,
                'days_recognized' => $daysRecognized,
                'amount' => $amount,
                'balance' => round2($balance - $amount, $decimal)
            ];

            $lastScheduleDate = $scheduleDate;
            $fromDate = $scheduleDate->addDay();
            $scheduleDate = $fromDate->endOfMonth()->startOfDay();
        } while ($lastScheduleDate->format('Y-m') < $this->endDate->format('Y-m'));
    }

    /**
     * Store the recognized expense schedules as calendar events in the db
     *
     * @param Contract $contract
     * @return void
     */
    public function storeEvents($event)
    {
        $contract = $event->contract;
        $calendarEvents = [];
        $now = date(DB_DATETIME_FORMAT);
        foreach ($this->expenseRecognitionSchedules as $schedule) {
            $calendarEvents[] = [
                'type_id' => CalendarEventType::LBREXPENSE_RECOGNIZED,
                'scheduled_at' => $schedule['date']->startOfDay()->toDateTimeString(),
                'context' => json_encode([
                    'invoice_ref' => $this->purchaseRecord->reference,
                    'purchase_amount' => $this->purchaseRecord->amount,
                    'contract_id' => $contract->id,
                    'contract_ref' => $contract->reference,
                    'contract_from' => $contract->contract_from->toDateString(),
                    'contract_till' => $contract->contract_till->toDateString(),
                    'contract_amount' => $contract->amount,
                    'category_id' => $contract->category_id,
                    'last_recognized_at' => $schedule['last_recognized_at']
                        ? $schedule['last_recognized_at']->startOfDay()->toDateTimeString()
                        : null,
                    'days_recognized' => $schedule['days_recognized'],
                    'amount' => $schedule['amount'],
                    'balance' => $schedule['balance']
                ]),
                'created_at' => $now,
                'updated_at' => $now
            ];
        }

        CalendarEvent::insert($calendarEvents);
    }
}