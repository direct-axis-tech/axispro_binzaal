<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdditionalColumnsForCreditNoteToDebtorTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_debtor_trans', function (Blueprint $table) {
            $table->decimal('income_recovered_tax', 14, 2)->default('0.00')->after('credit_note_charge');
            $table->decimal('income_recovered', 14, 2)->default('0.00')->after('credit_note_charge');
            $table->decimal('credit_note_charge_tax', 14, 2)->default('0.00')->after('credit_note_charge');
            $table->decimal('inc_ov_gst', 14, 2)->default('0.00')->after('processing_fee');
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
            $table->dropColumn('income_recovered_tax');
            $table->dropColumn('income_recovered');
            $table->dropColumn('credit_note_charge_tax');
            $table->dropColumn('inc_ov_gst');
        });
    }
}
