<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustCommissionShareColumnsToCustomerDiscountItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_customer_discount_items', function (Blueprint $table) {
            $table->decimal('cust_comm_emp_share')->default(0)->nullable(false)->after('customer_commission');
            $table->decimal('cust_comm_center_share')->default(0)->nullable(false)->after('customer_commission');
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
            $table->dropColumn('cust_comm_emp_share');
            $table->dropColumn('cust_comm_center_share');
        });
    }
}
