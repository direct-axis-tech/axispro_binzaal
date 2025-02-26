<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaymentConfigAccountsToDimensionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_dimensions', function (Blueprint $table) {
            $table->string('center_card_accounts',50)->nullable()->after('type_');
            $table->string('cash_accounts',50)->nullable()->after('center_card_accounts');
            $table->string('credit_card_accounts',50)->nullable()->after('cash_accounts');
            $table->string('customer_card_accounts',50)->nullable()->after('credit_card_accounts');
            $table->string('bank_transfer_accounts',50)->nullable()->after('customer_card_accounts');
            $table->string('online_payment_accounts',50)->nullable()->after('bank_transfer_accounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_dimensions', function (Blueprint $table) {
            $table->dropColumn('center_card_accounts');
            $table->dropColumn('cash_accounts');
            $table->dropColumn('credit_card_accounts');
            $table->dropColumn('customer_card_accounts');
            $table->dropColumn('bank_transfer_accounts');
            $table->dropColumn('online_payment_accounts');
        });
    }
}
