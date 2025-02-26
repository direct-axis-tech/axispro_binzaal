<?php

namespace App\Console;

use App\Jobs\CleanUpTemporaryFilesJob;
use App\Jobs\Hr\GenerateAttendanceJob;
use App\Jobs\Hr\GenerateLeaveBalanceJob;
use App\Jobs\TriggerCalendarEventsJob;
use App\Jobs\Sales\AggregateCustomerBalancesJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $php = PHP_BINARY;
        $appDir = realpath(base_path() . '/../../');

        // Generates the attendances
        $schedule->job(new GenerateAttendanceJob())->hourlyAt(10);

        // Trigger calendar events
        $schedule->job(new TriggerCalendarEventsJob())->daily();

        // Trigger customer balances
        $schedule->job(new AggregateCustomerBalancesJob())->dailyAt('04:30');

        if (pref('axispro.is_leave_accrual_scheduler_enabled')) {
            $schedule->exec("cd {$appDir}/scripts/ && {$php} ./leave_accrual.php")
                ->monthlyOn(date('t'), '05:00')
                ->runInBackground();
        }

        // Allocates the customer payments and receipt vouchers to invoices
        if (pref('axispro.is_auto_alloc_scheduler_enabled')) {
            $schedule->exec("cd {$appDir}/scripts/ && {$php} ./allocate_automatically.php")
                ->dailyAt('01:00')
                ->runInBackground();
        }

        if (pref('axispro.is_gratuity_accrual_sched_enabled')) {
            $schedule->exec("cd {$appDir}/scripts/ && {$php} ./gratuity_accrual.php")
                ->monthlyOn(date('t'), '05:00')
                ->runInBackground();
        }

        $schedule->job(new GenerateLeaveBalanceJob)->dailyAt('04:30');

        // Remove old files
        $schedule->job(new CleanUpTemporaryFilesJob)->everyFifteenMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
