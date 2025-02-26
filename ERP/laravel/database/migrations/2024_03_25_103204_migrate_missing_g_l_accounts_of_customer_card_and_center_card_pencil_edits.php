<?php

use App\Models\Accounting\BankAccount;
use App\Models\Sales\CustomerTransaction;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Str;

class MigrateMissingGLAccountsOfCustomerCardAndCenterCardPencilEdits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $transactions = DB::table('0_gl_trans as gt')
            ->join('0_debtor_trans as dt', function (JoinClause $join) {
                $join->whereColumn('dt.type', 'gt.type')
                    ->whereColumn('dt.trans_no', 'gt.type_no');
            })
            ->select('dt.*')
            ->where('gt.type', CustomerTransaction::INVOICE)
            ->where('gt.amount', '<>', 0)
            ->where('gt.account', '')
            ->whereIn('gt.memo_', [
                'Govt.Fee',
                'Bank service charge',
                'VAT for Bank service charge',
                'Service charge'
            ])
            ->whereIn('dt.payment_method', ['CenterCard', 'CustomerCard'])
            ->groupBy('dt.id')
            ->get();

        $centerCardAccounts = array_filter(explode(',', pref('axispro.center_card_accounts')));
        $customerCardAccounts = array_filter(explode(',', pref('axispro.customer_card_accounts')));
        $bankAccounts = BankAccount::query()->pluck('account_code', 'id')->toArray();

        foreach ($transactions as $invoice) {
            $editedQuery = DB::table('0_debtor_trans as dt')
                ->join('0_voided_debtor_trans_details as dtd', function ($join) {
                    $join->whereColumn('dtd.debtor_trans_type', 'dt.type')
                        ->whereColumn('dtd.debtor_trans_no', 'dt.trans_no');
                })
                ->select(
                    'dtd.id',
                    'dtd.govt_bank_account'
                )
                ->where('dt.reference', $invoice->reference)
                ->where('dt.type', CustomerTransaction::INVOICE)
                ->where('dtd.govt_bank_account', '<>', '');

            $voidedQuery = DB::table('0_voided_debtor_trans_details as dtd')
                ->select(
                    'dtd.id',
                    'dtd.govt_bank_account'
                )
                ->where('dtd.debtor_trans_type', $invoice->type)
                ->where('dtd.debtor_trans_no', $invoice->trans_no)
                ->where('dtd.govt_bank_account', '<>', '');

            // Check if the data is there in the edited or voided table
            $govtBankAccount = DB::query()
                ->fromSub($editedQuery->unionAll($voidedQuery), 't')
                ->orderByDesc('t.id')
                ->value('govt_bank_account');

            // If not, Check if can be detected from the previous gl trans
            if (!$govtBankAccount) {
                $govtBankAccount = DB::table('0_gl_trans as gt')
                    ->leftJoin('0_gl_trans as gt_next', function (JoinClause $join) {
                        $join->whereRaw('`gt_next`.`counter` = (`gt`.`counter` + 1)');
                    })
                    ->select('gt.account')
                    ->where('gt.type', $invoice->type)
                    ->where('gt.type_no', $invoice->trans_no)
                    ->where('gt.account', '!=', '')
                    ->whereIn('gt.memo_', [
                        'Govt.Fee',
                        'Bank service charge',
                        'VAT for Bank service charge',
                        'Service charge'
                    ])
                    ->whereRaw("(gt_next.counter is null or (gt_next.memo_ != 'Govt.Fee'))") // Split Govt Fee Account
                    ->orderBy('gt.counter')
                    ->value('gt.account');
            }

            // If not, Check if can be detected from payment or if there is only one configuration
            if (!$govtBankAccount) {
                $bankAccount = null;
                switch ($invoice->payment_method) {
                    case 'CustomerCard':
                        $bankAccount = DB::table('0_comments as cm')
                            ->join('0_bank_trans as bt', function (JoinClause $join) {
                                $join->whereColumn('bt.type', 'cm.type')
                                    ->whereColumn('bt.trans_no', 'cm.id');
                            })
                            ->where('cm.memo_', "Payment for Invoice No. #{$invoice->reference}")
                            ->value('bt.bank_act');
        
                        if (!$bankAccount && count($customerCardAccounts) == 1) {
                            $bankAccount = reset($customerCardAccounts);
                        }

                        break;
                    case 'CenterCard':
                        if (count($centerCardAccounts) == 1) {
                            $bankAccount = reset($customerCardAccounts);
                        }

                        break;
                }

                if ($bankAccount) {
                    $govtBankAccount = $bankAccounts[$bankAccount];
                }
            }

            if ($govtBankAccount) {
                DB::table('0_debtor_trans as dt')
                    ->join('0_debtor_trans_details as dtd', function ($join) {
                        $join->whereColumn('dtd.debtor_trans_type', 'dt.type')
                            ->whereColumn('dtd.debtor_trans_no', 'dt.trans_no');
                    })
                    ->leftJoin('0_gl_trans as gt', function (JoinClause $join) {
                        $join->whereColumn('dt.type', 'gt.type')
                            ->whereColumn('dt.trans_no', 'gt.type_no')
                            ->where('gt.amount', '<>', 0)
                            ->where('gt.account', '')
                            ->whereIn('gt.memo_', [
                                'Govt.Fee',
                                'Bank service charge',
                                'VAT for Bank service charge',
                                'Service charge'
                            ]);
                    })
                    ->where('dt.id', $invoice->id)
                    ->update([
                        'dtd.govt_bank_account' => $govtBankAccount,
                        'gt.account' => $govtBankAccount
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
