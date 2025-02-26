<?php

use Illuminate\Database\Migrations\Migration;

class ClearCurrentPaymentMethodCofigurationsFromDimensionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('0_dimensions')->update([
            'online_payment_accounts' => '',
            'customer_card_accounts' => '',
            'center_card_accounts' => '',
            'cash_accounts' => '',
            'bank_transfer_accounts' => '',
            'credit_card_accounts' => '',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
