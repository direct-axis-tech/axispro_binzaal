<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfigurationForCommCalcMethodInCustomerDiscountItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_customer_discount_items', function (Blueprint $table) {
            $table->integer('comm_calc_method')->nullable()->default(CCM_AMOUNT)->after('customer_commission');
            $table->integer('comm_emp_sh_calc_method')->nullable()->default(CCM_AMOUNT)->after('cust_comm_emp_share');
            $table->integer('comm_emp_sh_percent_of')->nullable()->default(CBV_CUST_COMMISSION)->after('comm_emp_sh_calc_method');
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
            $table->dropColumn(
                'comm_calc_method',
                'comm_emp_sh_calc_method',
                'comm_emp_sh_percent_of'
            );
        });
    }
}
