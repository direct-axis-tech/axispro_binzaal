<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddContractIdColumnToSalesOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_sales_orders', function (Blueprint $table) {
            $table->bigInteger('contract_id')->nullable()->after('branch_code');
            $table->date('period_till')->nullable()->after('ord_date');
            $table->date('period_from')->nullable()->after('ord_date');
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
            $table->dropColumn('contract_id');
            $table->dropColumn('period_from');
            $table->dropColumn('period_till');
        });
    }
}
