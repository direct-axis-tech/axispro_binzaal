<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomerCommission2ConfigToCustomerDiscountItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_customer_discount_items', function (Blueprint $table) {
            $table->double('customer_commission2')->nullable(false)->default(0);
            $table->integer('comm2_calc_method')->nullable()->default(CCM_AMOUNT)->after('customer_commission2');
        });
    }

    /**
     * Reverse the migrations.
    *
     * @return void
     */
    public function down()
    {
        Schema::table('0_customer_discount_items', function (Blueprint $table) {
            $table->dropColumn('customer_commission2');
            $table->dropColumn('comm2_calc_method');
        });
    }
}