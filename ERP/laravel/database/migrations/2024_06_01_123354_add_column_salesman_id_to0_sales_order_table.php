<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnSalesmanIdTo0SalesOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_sales_orders', function (Blueprint $table) {   
            $table->integer('salesman_id')->nullable()->after('debtor_no');
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
            $table->dropColumn('salesman_id');
        });
    }
}
