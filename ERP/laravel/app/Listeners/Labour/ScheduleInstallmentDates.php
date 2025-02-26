<?php
 
namespace App\Listeners\Labour;

use App\Events\Labour\InstallmentCreated;
use App\Models\CalendarEvent;
use App\Models\CalendarEventType;
use App\Models\Labour\Installment;
use Carbon\CarbonImmutable;

class ScheduleInstallmentDates
{

    /**
     * Start date of the contract
     *
     * @var CarbonImmutable
     */
    protected $startDate = null;

    /**
     * installment of the contract
     * 
     * @var Installment
     */
    protected $installment = null;

    /**
     * The entire schedule for installment on this contract
     *
     * @var array{
     *  last_recognized_at: CarbonImmutable,
     *  date: CarbonImmutable,
     *  days_recognized: int,
     *  amount: float
     * }[]
     */
    protected $InstallmentSchedules = [];
 
    /**
     * Handle the event.
     */
    public function handle(InstallmentCreated $event): void
    {
        $this->installment = $event->installment;
        $this->startDate = (new CarbonImmutable($this->installment->start_date))->startOfDay();
    
        $this->generateSchedulesForInstallment();

        $this->storeEvents();
    }

    /**
     * Generates the schedule for Installment
     *
     * @return void
     */
    protected function generateSchedulesForInstallment()
    {
        $lastScheduleDate = null;
        $decimal = user_price_dec();

        foreach ($this->installment->installmentDetails as $schedule) {
            $scheduleDate = (new CarbonImmutable($schedule->due_date))->startOfDay();
            $tillDate = $scheduleDate->add(
                $this->installment->interval,
                $this->installment->interval_unit
            )->subDay();

            $runningTotal = round2(array_sum(array_column($this->InstallmentSchedules, 'amount')), $decimal);
            $balance = round2($this->installment->total_amount - $runningTotal, $decimal);
           
            $this->InstallmentSchedules[] = [
                'installment_detail_id' => $schedule->id,
                'last_recognized_at' => $lastScheduleDate,
                'date' => $scheduleDate,
                'days_recognized' => $scheduleDate->diffInDays($tillDate) + 1,
                'amount' => $schedule->amount,
                'balance' => round2($balance - $schedule->amount, $decimal)
            ];

            $lastScheduleDate = $scheduleDate;
        }
    }

    /**
     * Store the installment schedules as calendar events in the db
     *
     * @return void
     */
    public function storeEvents()
    {
        $contract = $this->installment->contract;
        $calendarEvents = [];
        $now = date(DB_DATETIME_FORMAT);
        foreach ($this->InstallmentSchedules as $schedule) {
            $calendarEvents[] = [
                'type_id' => CalendarEventType::INSTALLMENT_REMINDER,
                'scheduled_at' => $schedule['date']->startOfDay()->toDateTimeString(),
                'context' => json_encode([
                    'contract_id' => $contract->id,
                    'installment_detail_id' => $schedule['installment_detail_id'],
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