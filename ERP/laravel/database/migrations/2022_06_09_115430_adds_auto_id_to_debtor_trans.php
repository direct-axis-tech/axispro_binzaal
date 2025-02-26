<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddsAutoIdToDebtorTrans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_debtor_trans', function (Blueprint $table) {
            $table->dropPrimary();
            $table->unique(['type', 'trans_no', 'debtor_no'], 'uniq_debtor_transaction');
            
        });

        Schema::table('0_debtor_trans', function (Blueprint $table) {
            $table->bigInteger('id')->nullable(false)->autoIncrement()->first();
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
            $table->dropColumn('id');
        });

        Schema::table('0_debtor_trans', function (Blueprint $table) {
            $table->dropUnique('uniq_debtor_transaction');
            $table->primary(['type', 'trans_no', 'debtor_no']);
        });
    }
}
