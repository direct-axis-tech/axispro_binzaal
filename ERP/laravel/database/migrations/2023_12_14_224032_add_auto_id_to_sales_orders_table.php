<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAutoIdToSalesOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_sales_orders', function (Blueprint $table) {
            $table->dropPrimary();
            $table->unique(['trans_type', 'order_no']);
            
        });

        Schema::table('0_sales_orders', function (Blueprint $table) {
            $table->bigInteger('id')->nullable(false)->autoIncrement()->first();
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
            $table->dropColumn('id');
        });

        Schema::table('0_sales_orders', function (Blueprint $table) {
            $table->dropUnique(['trans_type', 'order_no']);
            $table->primary(['trans_type', 'order_no']);
        });
    }
}
