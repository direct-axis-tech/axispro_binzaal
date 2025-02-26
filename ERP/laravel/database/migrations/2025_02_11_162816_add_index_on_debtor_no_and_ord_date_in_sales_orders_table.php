<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexOnDebtorNoAndOrdDateInSalesOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_sales_orders', function (Blueprint $table) {
            $table->index(['debtor_no', 'ord_date']);
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
            $table->dropIndex(['debtor_no', 'ord_date']);
        });
    }
}
