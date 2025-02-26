<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexOnCustomerTransactionWithDateToDebtorTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_debtor_trans', function (Blueprint $table) {
            $table->index(['debtor_no', 'tran_date']);
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
            $table->dropIndex(['debtor_no', 'tran_date']);
        });
    }
}
