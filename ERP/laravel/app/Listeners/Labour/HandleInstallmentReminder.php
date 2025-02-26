<?php
 
namespace App\Listeners\Labour;

use App\Events\Labour\InstallmentReminder;
use App\Models\EntityGroup;
use App\Models\Labour\Contract;
use App\Models\System\User;
use App\Notifications\Labour\InstallmentReminderNotification;
use Auth;
use Illuminate\Support\Facades\Notification;

class HandleInstallmentReminder{
    /**
     * The event which triggered this handler
     *
     * @var InstallmentReminder
     */
    protected $event;

    /**
     * Current version of the contract
     *
     * @var Contract
     */
    protected $contract;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        // ...
    }
 
    /**
     * Handle the event.
     */
    public function handle(InstallmentReminder $event): void
    {
        $this->event = $event;
        $this->contract = Contract::active()->whereReference($event->contractRef)->first();
        
        if (empty($this->contract) || $this->isContractStale()) {
            return;
        }

        Auth::login(new User(["id" => User::SYSTEM_USER]));
        
        if (
            ($group = EntityGroup::find(EntityGroup::INSTALLMENT_REMINDER_NOTIFICATION))
            && ($members = $group->distinctMemberUsers())
            && !blank($members)
        ) {
            Notification::send($members, new InstallmentReminderNotification($event));
        }
    }

    /**
     * Decide if the contract is stale
     * 
     * @return boolean
     */
    protected function isContractStale(): bool
    {
        return (
            $this->contract->inactive
            || $this->contract->contract_from->toDateString() != $this->event->contractFrom->toDateString()
            || $this->contract->contract_till->toDateString() != $this->event->contractTill->toDateString()
            || $this->contract->amount != $this->event->contractAmount
            || $this->contract->category_id != $this->event->categoryId
        );
    }
}