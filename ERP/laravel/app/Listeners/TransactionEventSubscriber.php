<?php
 
namespace App\Listeners;

use App\Events\Accounting\BankCredited;
use App\Events\Accounting\BankDebited;
use App\Events\Accounting\JournalRecorded;
use App\Events\Sales\CustomerCredited;
use App\Events\Sales\CustomerInvoiced;
use App\Events\Sales\CustomerPaid;
use App\Events\Sales\CustomerRefunded;
use App\Events\Sales\UpdatedCustomerAllocation;
use App\Events\Sales\VoidedCustomerAllocation;
use App\Events\System\TransactionVoided;
use App\Models\Accounting\LedgerTransaction;
use App\Models\Sales\CustomerTransaction;

class TransactionEventSubscriber
{ 
    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(CustomerInvoiced::class, [$this, 'handleCustomerInvoiced']);
        $events->listen(CustomerPaid::class, [$this, 'handleCustomerPaid']);
        $events->listen(CustomerCredited::class, [$this, 'handleCustomerCredited']);
        $events->listen(BankCredited::class, [$this, 'handleBankCredited']);
        $events->listen(BankDebited::class, [$this, 'handleBankDebited']);
        $events->listen(JournalRecorded::class, [$this, 'handleJournalRecorded']);
        $events->listen(TransactionVoided::class,  [$this, 'handleTransactionVoided']);
        $events->listen(UpdatedCustomerAllocation::class,  [$this, 'handleUpdatedCustomerAllocation']);
        $events->listen(VoidedCustomerAllocation::class,  [$this, 'handleVoidedCustomerAllocation']);
        $events->listen(CustomerRefunded::class, [$this, 'handleCustomerRefund']);
    }

    /**
     * Handles the event when a customer is invoiced
     * 
     * @param CustomerInvoiced $event
     * @return void
     */
    public function handleCustomerInvoiced($event) {
        $this->shouldCalculateCustomerBalance($event->invoice->debtor_no, $event->invoice->tran_date);
    }

    /**
     * Handles the event when a customer makes a payment
     * 
     * @param CustomerPaid $event
     * @return void
     */
    public function handleCustomerPaid($event) {
        $this->shouldCalculateCustomerBalance($event->payment->debtor_no, $event->payment->tran_date);
    }


    /**
     * Handles the event when a customer makes a refund
     * 
     * @param CustomerRefund $event
     * @return void
     */
    public function handleCustomerRefund($event) {
        $this->shouldCalculateCustomerBalance($event->refund->debtor_no, $event->refund->tran_date);
    }

    /**
     * Handles the event when a there is a sales return
     * 
     * @param CustomerCredited $event
     * @return void
     */
    public function handleCustomerCredited($event) {
        $this->shouldCalculateCustomerBalance($event->creditNote->debtor_no, $event->creditNote->tran_date);
    }
    
    /**
     * Handles the event when a bank account is credited
     * 
     * @param BankCredited $event
     * @return void
     */
    public function handleBankCredited($event) {
        if ($event->payment->person_type_id == PT_CUSTOMER) {
            $this->shouldCalculateCustomerBalance($event->payment->person_id, $event->payment->trans_date);
        }
    }

    /**
     * Handles the event when a bank account is debited
     * 
     * @param BankDebited $event
     * @return void
     */
    public function handleBankDebited($event) {
        if ($event->receipt->person_type_id == PT_CUSTOMER) {
            $this->shouldCalculateCustomerBalance($event->receipt->person_id, $event->receipt->trans_date);
        }
    }

    /**
     * Handles the event when a journal entry is recorded
     * 
     * @param JournalRecorded $event
     * @return void
     */
    public function handleJournalRecorded($event) {
        $event->journal->load('gl');

        $event->journal->gl->each(function(LedgerTransaction $gl) {
            if ($gl->person_type_id == PT_CUSTOMER) {
                $this->shouldCalculateCustomerBalance($gl->person_id, $gl->tran_date);
            }
        });
    }

    /**
     * Handles the event when a transaction is voided
     *
     * @param TransactionVoided $event
     * @return void
     */
    public function handleTransactionVoided($event) {
        $gls = LedgerTransaction::where('type', $event->type)
            ->where('type_no', $event->transNo)
            ->get();

        $gls->each(function(LedgerTransaction $gl) {
            if ($gl->person_type_id == PT_CUSTOMER) {
                $this->shouldCalculateCustomerBalance($gl->person_id, $gl->tran_date);
            }
        });
    }

    /**
     * Handles the event when an allocation is updated
     *
     * @param UpdatedCustomerAllocation $event
     * @return void
     */
    public function handleUpdatedCustomerAllocation($event) {
        $this->shouldCalculateCustomerBalance($event->personId, $event->tranDate);
    }

    /**
     * Handles the event when an allocation is voided
     *
     * @param VoidedCustomerAllocation $event
     * @return void
     */
    public function handleVoidedCustomerAllocation($event) {
        if ($event->personId) {
            return $this->shouldCalculateCustomerBalance($event->personId, $event->tranDate);
        }

        CustomerTransaction::whereTransNo($event->transNo)
            ->whereType($event->transType)
            ->get()
            ->each(function ($item) {
                $this->shouldCalculateCustomerBalance($item->debtor_no, $item->tran_date);
            });
    }

    /**
     * Configure the customers and dates where the balance should be calculated
     *
     * @param string $debtorNo
     * @param string $tranDate
     */
    public function shouldCalculateCustomerBalance($debtorNo, $tranDate)
    {
        $key = "shouldCalculateCustomerBalance.{$debtorNo}";
        
        $array = config($key, []);
        
        $array[] = $tranDate;

        config()->set($key, $array);
    }
}