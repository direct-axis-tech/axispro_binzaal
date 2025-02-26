<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTransactionIdUpdatedByColumnToDebtorTransDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('0_debtor_trans_details', 'transaction_id_updated_by')) return;
        
        Schema::table('0_debtor_trans_details', function (Blueprint $table) {
            $table->bigInteger('transaction_id_updated_by')->nullable()->after('transaction_id_updated_at')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_debtor_trans_details', function (Blueprint $table) {
            $table->dropColumn('transaction_id_updated_by');
        });
    }
}
