<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTransactionBankAndApplicationBankToReconciledResultTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_reconcile_result', function (Blueprint $table) {

            $table->string('transaction_bnk')->after('transaction_')->nullable();
            $table->string('application_bnk')->after('application_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_reconcile_result', function (Blueprint $table) {

            $table->dropColumn('transaction_bnk');
            $table->dropColumn('application_bnk');
        });
    }
}
