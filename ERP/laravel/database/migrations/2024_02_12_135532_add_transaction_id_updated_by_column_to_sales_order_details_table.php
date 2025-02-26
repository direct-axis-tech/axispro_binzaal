<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTransactionIdUpdatedByColumnToSalesOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_sales_order_details', function (Blueprint $table) {
            $table->Integer('transaction_id_updated_by')->nullable()->after('transaction_id_updated_at');
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
            $table->dropColumn('transaction_id_updated_by');
        });
    }
}
