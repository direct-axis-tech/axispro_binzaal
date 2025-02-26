<?php

namespace App\Providers;

use App\Listeners\DocumentExpiryEventSubscriber;
use App\Listeners\TransactionEventSubscriber;
use App\Listeners\ManageTaskEventSubscriber;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        \App\Events\Labour\ContractDelivered::class => [
            \App\Listeners\Labour\CalculateIncomeRecognitionDates::class,
            \App\Listeners\Labour\CalculateExpenseRecognitionDates::class
        ],
        \App\Events\Labour\IncomeRecognized::class => [
            \App\Listeners\Labour\HandleIncomeRecognition::class
        ],
        \App\Events\Labour\ExpenseRecognized::class => [
            \App\Listeners\Labour\HandleExpenseRecognition::class
        ],
        \App\Events\Labour\InstallmentCreated::class => [
            \App\Listeners\Labour\ScheduleInstallmentDates::class
        ],
        \App\Events\Labour\InstallmentReminder::class => [
            \App\Listeners\Labour\HandleInstallmentReminder::class
        ],
        \App\Events\Sales\JobOrderCreated::class => [
            \App\Listeners\SendTransactionNotification::class,
        ],
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        TransactionEventSubscriber::class,
        DocumentExpiryEventSubscriber::class,
        ManageTaskEventSubscriber::class
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
