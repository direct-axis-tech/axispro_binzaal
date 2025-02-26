<?php

use Illuminate\Database\Migrations\Migration;

class CorrectPrepaidPaymentTermsName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('0_payment_terms')
            ->whereDaysBeforeDue(-1)
            ->update(['terms' => 'Prepaid']);
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
