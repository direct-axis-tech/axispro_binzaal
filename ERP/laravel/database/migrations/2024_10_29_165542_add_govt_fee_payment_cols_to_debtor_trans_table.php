<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGovtFeePaymentColsToDebtorTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_debtor_trans', function (Blueprint $table) {
            $table->string('govt_fee_pay_method')->nullable()->after('payment_ref');
            $table->string('govt_fee_pay_account')->nullable()->after('govt_fee_pay_method');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_debtor_trans', function (Blueprint $table) {
            $table->dropColumn('govt_fee_pay_account', 'govt_fee_pay_method');
        });
    }
}
