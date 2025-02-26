<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDecimalColumnToDoubleInSalesOrderAndDebtorTransDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_sales_order_details', function (Blueprint $table) {
            $table->float('split_govt_fee_amt')->nullable(false)->default(0.00)->change();
            $table->float('returnable_amt')->nullable(false)->default(0.00)->change();
            $table->float('receivable_commission_amount')->nullable(false)->default(0.00)->change();
            $table->float('extra_srv_chg')->nullable(false)->default(0.00)->change();
            $table->float('user_commission')->nullable(false)->default(0.00)->change();
            $table->float('customer_commission')->nullable(false)->default(0.00)->change();
            $table->float('cust_comm_center_share')->nullable(false)->default(0.00)->change();
            $table->float('cust_comm_emp_share')->nullable(false)->default(0.00)->change();
            $table->float('customer_commission2')->nullable(false)->default(0.00)->change();
        });

        Schema::table('0_debtor_trans_details', function (Blueprint $table) {
            $table->float('split_govt_fee_amt')->nullable(false)->default(0.00)->change();
            $table->float('returnable_amt')->nullable(false)->default(0.00)->change();
            $table->float('receivable_commission_amount')->nullable(false)->default(0.00)->change();
            $table->float('extra_srv_chg')->nullable(false)->default(0.00)->change();
            $table->float('user_commission')->nullable(false)->default(0.00)->change();
            $table->float('customer_commission')->nullable(false)->default(0.00)->change();
            $table->float('cust_comm_center_share')->nullable(false)->default(0.00)->change();
            $table->float('cust_comm_emp_share')->nullable(false)->default(0.00)->change();
            $table->float('customer_commission2')->nullable(false)->default(0.00)->change();
        });
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
