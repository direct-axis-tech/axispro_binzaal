<?php

use App\Models\Sales\CustomerTransaction;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\JoinClause;

class AddPaymentAccountColumnToDebtorTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_debtor_trans', function (Blueprint $table) {
            $table->string('payment_account')->nullable()->after('payment_method');
        });

        try {
            $detailsQuery = DB::table('0_debtor_trans_details as dtd')
                ->select(
                    'dtd.debtor_trans_no',
                    'dtd.debtor_trans_type'
                )
                ->selectRaw('max(`dtd`.`govt_bank_account`) as govt_bank_account')
                ->where('dtd.debtor_trans_type', CustomerTransaction::INVOICE)
                ->where('dtd.govt_bank_account', '<>', '')
                ->groupBy('dtd.debtor_trans_type', 'dtd.debtor_trans_no');

            DB::table('0_debtor_trans as dt')
                ->leftJoinSub($detailsQuery, 'dtd', function (JoinClause $join) {
                    $join->whereColumn('dtd.debtor_trans_type', 'dt.type')
                        ->whereColumn('dtd.debtor_trans_no', 'dt.trans_no');
                })
                ->leftJoin('0_bank_accounts as ba', 'ba.account_code', 'dtd.govt_bank_account')
                ->whereRaw('(`dt`.`ov_amount` + `dt`.`ov_gst` + `dt`.`ov_freight` + `dt`.`ov_freight_tax` + `dt`.`ov_discount`) <> 0')
                ->where('dt.type', CustomerTransaction::INVOICE)
                ->whereIn('dt.payment_method', ['CustomerCard', 'CenterCard'])
                ->update([
                    'payment_account' => DB::raw('ba.id')
                ]);
        }

        catch (Throwable $e) {
            $this->down();

            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_debtor_trans', function (Blueprint $table) {
            $table->dropColumn('payment_account');
        });
    }
}
