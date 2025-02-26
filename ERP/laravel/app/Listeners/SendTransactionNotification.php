<?php

namespace App\Listeners;

use App\Events\Sales\JobOrderCreated;
use App\Models\Sales\SalesOrder;
use App\Models\System\User;
use App\Notifications\TransactionAssignedNotification;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Notification;

class SendTransactionNotification
{
    /**
     * Handle the event.
     *
     * @param  JobOrderCreated  $event
     * @return void
     */
    public function handle(JobOrderCreated $event)
    {
        $salesOrder = $event->salesOrder;
        $notifiables = $this->getNotifiablesForTransaction($salesOrder);
        
        if(!empty($notifiables)) {
            Notification::send(
                $notifiables,
                new TransactionAssignedNotification($event)
            );
        }
    }

    private function getNotifiablesForTransaction(SalesOrder $salesOrder)
    {
        $assignees = (new Builder($salesOrder->getConnection()))
            ->from('0_sales_order_details')
            ->whereOrderNo($salesOrder->order_no)
            ->whereTransType($salesOrder->trans_type)
            ->where('quantity', '!=', 0)
            ->pluck('assignee_id')
            ->filter()
            ->unique()
            ->toArray();
            
        return User::whereIn('id', $assignees)->get();
    }

}
