<?php
 
namespace App\Listeners\Labour;

use App\Events\Accounting\JournalRecorded;
use App\Events\Labour\IncomeRecognized;
use App\Models\Accounting\AuditTrail;
use App\Models\Accounting\BankAccount;
use App\Models\Accounting\BankTransaction;
use App\Models\Accounting\FiscalYear;
use App\Models\Accounting\JournalTransaction;
use App\Models\Accounting\LedgerTransaction;
use App\Models\EntityGroup;
use App\Models\Inventory\StockCategory;
use App\Models\Inventory\StockItem;
use App\Models\Labour\Contract;
use App\Models\MetaReference;
use App\Models\MetaTransaction;
use App\Models\System\User;
use App\Notifications\Labour\IncomeRecognizedNotification;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

class HandleIncomeRecognition
{
    /**
     * The event which triggered this handler
     *
     * @var IncomeRecognized
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
    public function handle(IncomeRecognized $event): void
    {
        $this->event = $event;
        $this->contract = Contract::active()->whereReference($event->contractRef)->first();
        
        if (empty($this->contract) || $this->isContractStale()) {
            return;
        }

        Auth::login(new User(["id" => User::SYSTEM_USER]));
        
        switch ($event->categoryId) {
            case StockCategory::DWD_PACKAGEONE:
                $this->handlePackageOne();
                break;
            case StockCategory::DWD_PACKAGETWO:
                $this->handlePackageTwo();
                break;
        };
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

    /**
     * Handles the package one contracts
     *
     * @return void
     */
    protected function handlePackageOne()
    {
        $deferredSalesAccount = StockCategory::whereCategoryId($this->contract->category_id)->value('dflt_pending_sales_act');
        
        // If deferred account is empty, It means the income will be directly
        // recognized when invoicing, so there is no need to pass accrual entry
        if (empty($deferredSalesAccount)) {
            return;
        }

        $journal = DB::transaction(function () use ($deferredSalesAccount) {
            $type = JournalTransaction::JOURNAL;
            $transDate = $this->event->calendarEvent->scheduled_at->toDateString();
            $reference = MetaReference::getNext($type, null, sql2date($transDate), true);
            $transNo = MetaTransaction::getNextTransNo($type);
            $decimalPoints = user_price_dec();
            $recognizedAmount = round2($this->event->amount, $decimalPoints);
            $created_by = User::SYSTEM_USER;
            $memo = "Income against contract '{$this->contract->reference}' for {$this->event->daysRecognized} days";
            $salesAccount = StockItem::whereStockId($this->contract->stock_id)->value('sales_account');
            $bankTransactions = [];

            $journal = JournalTransaction::create([
                'type' => $type,
                'trans_no' => $transNo,
                'tran_date' => $transDate,
                'contract_id' => $this->contract->id,
                'reference' => $reference,
                'source_ref' => $this->event->contractRef,
                'event_date' => $transDate,
                'doc_date' => $transDate,
                'currency' => pref('company.curr_default'),
                'amount' => $recognizedAmount,
                'rate' => 1,
            ]);
    
            foreach([[$deferredSalesAccount, $recognizedAmount], [$salesAccount, -$recognizedAmount]] as [$account, $amount]) {
                LedgerTransaction::insert([
                    'type' => $type,
                    'type_no' => $transNo,
                    'tran_date' => $transDate,
                    'account' => $account,
                    'dimension_id' => $this->contract->dimension_id,
                    'dimension2_id' => 0,
                    'memo_' => $memo,
                    'amount' => $amount,
                    'transaction_id' => '',
                    'created_by' => $created_by
                ]);

                if (!empty($bankAccountId = BankAccount::whereAccountCode($account)->value('id'))) {
                    if (!isset($bankTransactions[$bankAccountId])) {
                        $bankTransactions[$bankAccountId] = 0;
                    }

                    $bankTransactions[$bankAccountId] += $amount;
                }
            }

            foreach ($bankTransactions as $bankAccountId => $amount) {
                BankTransaction::insert([
                    'type' => $type,
                    'trans_no' => $transNo,
                    'trans_date' => $transDate,
                    'bank_act' => $bankAccountId,
                    'ref' =>  $reference,
                    'amount' => $amount,
                    'person_type_id' => PT_MISC,
                    'person_id' => $memo,
                    'payment_type' => 0,
                    'cheq_no' => 0,
                    'created_by' => $created_by
                ]);
            }

            MetaReference::saveReference($type, $transNo, $reference);
            DB::table('0_comments')->insert(['type' => $type, 'id' => $transNo, 'date_' => $transDate, 'memo_' => $memo]);
            AuditTrail::insert([
                'type' => $type,
                'trans_no' => $transNo,
                'user' => $created_by,
                'description' => '',
                'gl_date' => $transDate,
                'gl_seq' => 0,
                'fiscal_year' => FiscalYear::whereRaw('? between `begin` and `end`', [$transDate])->value('id') ?: 0,
                'created_at' => date(DB_DATETIME_FORMAT)
            ]);

            return $journal->fresh();
        });

        Event::dispatch(new JournalRecorded($journal));
        $this->event->setJournal($journal);
        Notification::send($this->getNotifiables(), new IncomeRecognizedNotification($this->event));
    }

    /**
     * Handles the package two contracts
     * 
     *
     * @return void
     */
    protected function handlePackageTwo()
    {
        Notification::send($this->getNotifiables(), new IncomeRecognizedNotification($this->event));
    }
    
    /**
     * Get the group to which the notification is to be sent
     * 
     * @return Collection|User[]
     */
    protected function getNotifiables() {
        $group = EntityGroup::find(EntityGroup::LBR_INCOME_RECOGNITION_NOTIFICATION);
        return $group->distinctMemberUsers() ?: collect();
    }
}