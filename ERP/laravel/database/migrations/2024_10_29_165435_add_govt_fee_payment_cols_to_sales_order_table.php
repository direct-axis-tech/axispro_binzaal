<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGovtFeePaymentColsToSalesOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_sales_orders', function (Blueprint $table) {
            $table->string('govt_fee_pay_method')->nullable()->after('payment_terms');
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
        Schema::table('0_sales_orders', function (Blueprint $table) {
            $table->dropColumn('govt_fee_pay_account', 'govt_fee_pay_method');
        });
    }
}
