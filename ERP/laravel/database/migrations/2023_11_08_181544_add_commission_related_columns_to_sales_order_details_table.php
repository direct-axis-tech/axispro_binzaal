<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCommissionRelatedColumnsToSalesOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_sales_order_details', function (Blueprint $table) {
            $table->decimal('user_commission')->nullable(false)->default(0);
            $table->decimal('customer_commission')->nullable(false)->default(0);
            $table->decimal('cust_comm_center_share')->nullable(false)->default(0);
            $table->decimal('cust_comm_emp_share')->nullable(false)->default(0);
            $table->bigInteger('created_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_sales_order_details', function (Blueprint $table) {
            $table->dropColumn(
                'user_commission',
                'customer_commission',
                'cust_comm_center_share',
                'cust_comm_emp_share',
                'created_by'
            );
        });
    }
}
