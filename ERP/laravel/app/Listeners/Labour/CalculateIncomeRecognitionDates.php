<?php
 
namespace App\Listeners\Labour;

use App\Events\Labour\ContractDelivered;
use App\Models\CalendarEvent;
use App\Models\CalendarEventType;
use App\Models\Inventory\StockCategory;
use App\Models\Labour\Contract;
use Carbon\CarbonImmutable;
class CalculateIncomeRecognitionDates
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
     * Total contract amount
     *
     * @var float
     */
    protected $contractAmount = null;

    /**
     * The entire schedule for recognizing the income on this contract
     *
     * @var array{
     *  last_recognized_at: CarbonImmutable,
     *  date: CarbonImmutable,
     *  days_recognized: int,
     *  amount: float
     * }[]
     */
    protected $incomeRecognitionSchedules = [];
 
    /**
     * Handle the event.
     */
    public function handle(ContractDelivered $event): void
    {
        $contract = $event->contract;
        $this->startDate = (new CarbonImmutable($contract->contract_from))->startOfDay();
        $this->endDate = (new CarbonImmutable($contract->contract_till))->startOfDay();
        $this->contractAmount = $contract->amount;
        $this->amountPerDay = round2($this->contractAmount / ($this->startDate->diffInDays($this->endDate) + 1), user_price_dec());

        switch ($event->contract->category_id) {
            case StockCategory::DWD_PACKAGEONE:
                $this->generateSchedulesForPackageOne();
                break;
            case StockCategory::DWD_PACKAGETWO:
                $this->generateSchedulesForPackageTwo();
                break;
        }

        $this->storeEvents($contract);
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
        $scheduleDate = $this->startDate->addDays(Contract::PKG_ONE_INCOME_RECOGNITION_DELAY)->endOfMonth()->startOfDay();

        do {
            $daysRecognized = $fromDate->diffInDays($scheduleDate) + 1;
            $amount = round2($this->amountPerDay * $daysRecognized, $decimal); 
            $totalRecognizedAmount = round2(array_sum(array_column($this->incomeRecognitionSchedules, 'amount')), $decimal);
            $balance = round2($this->contractAmount - $totalRecognizedAmount, $decimal);
            
            // If this is the last recognition, reset it to the balance amount
            // for the same reasons mentioned in the case of package 2
            if ($scheduleDate->format('Y-m') == $this->endDate->format('Y-m')) {
                $amount = $balance;
                $daysRecognized = $fromDate->diffInDays($this->endDate) + 1;
            }

            $this->incomeRecognitionSchedules[] = [
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
     * Generates the schedule for package two
     *
     * @return void
     */
    protected function generateSchedulesForPackageTwo()
    {
        $lastScheduleDate = null;
        $scheduleDate = $this->startDate;
        $decimal = user_price_dec();
        $contractDurationInMonths = $this->startDate->diffInMonths($this->endDate) + 1;
        $amountPerMonth = round2($this->contractAmount / $contractDurationInMonths, $decimal);

        for ($noOfMonths = 1; $noOfMonths <= $contractDurationInMonths; $noOfMonths++) {
            $tillDate = $this->startDate->addMonthsNoOverflow($noOfMonths)->subDay();
            $amount = $amountPerMonth;
            $totalRecognizedAmount = round2(array_sum(array_column($this->incomeRecognitionSchedules, 'amount')), $decimal);
            $balance = round2($this->contractAmount - $totalRecognizedAmount, $decimal);

            /*
             | Since we are dealing with rounded figures, The total recognized
             | amount can either be less than or greater than the total contract amount.
             | So, we allocate the remaining amount to the last of the month to avoid
             | rounding errors
             |
             | Examples using a one year contract period:
             | Case 1:
             |      contractAmount = 5050
             |      amountPerMonth = 5050 / 12 = 420.83
             |      totalRecognizedAmount = 420.83 * 12 = 5049.96
             | Case 2:
             |      contractAmount = 5150
             |      amountPerMonth = 5150 / 12 = 429.17
             |      totalRecognizedAmount = 429.17 * 12 = 5150.04
             */

            if ($noOfMonths == $contractDurationInMonths) {
                $amount = $balance;
            }

            $this->incomeRecognitionSchedules[] = [
                'last_recognized_at' => $lastScheduleDate,
                'date' => $scheduleDate,
                'days_recognized' => $scheduleDate->diffInDays($tillDate) + 1,
                'amount' => $amount,
                'balance' => round2($balance - $amount, $decimal)
            ];

            $lastScheduleDate = $scheduleDate;
            $scheduleDate = $tillDate->addDay();
        }
    }

    /**
     * Store the recognized income schedules as calendar events in the db
     *
     * @param Contract $contract
     * @return void
     */
    public function storeEvents(Contract $contract)
    {
        $calendarEvents = [];
        $now = date(DB_DATETIME_FORMAT);
        foreach ($this->incomeRecognitionSchedules as $schedule) {
            $calendarEvents[] = [
                'type_id' => CalendarEventType::LBRINCOME_RECOGNIZED,
                'scheduled_at' => $schedule['date']->startOfDay()->toDateTimeString(),
                'context' => json_encode([
                    'contract_id' => $contract->id,
                    'contract_ref' => $contract->reference,
                    'category_id' => $contract->category_id,
                    'contract_from' => $contract->contract_from->toDateString(),
                    'contract_till' => $contract->contract_till->toDateString(),
                    'contract_amount' => $contract->amount,
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