<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsRelatedToCreditNoteToDebtorTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_debtor_trans', function (Blueprint $table) {
            $table->integer('credit_inv_no')->nullable()->after('reference');
            $table->decimal('credit_note_charge', 18, 2)->default('0.00')->after('round_of_amount');
            $table->decimal('days_income_recovered_for', 6, 2)->default(0)->after('credit_note_charge');
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
            $table->dropColumn('days_income_recovered_for');
            $table->dropColumn('credit_note_charge');
            $table->dropColumn('credit_inv_no');
        });
    }
}
