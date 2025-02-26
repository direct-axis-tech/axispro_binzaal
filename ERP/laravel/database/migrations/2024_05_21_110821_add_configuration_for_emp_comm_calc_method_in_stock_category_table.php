<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfigurationForEmpCommCalcMethodInStockCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_stock_category', function (Blueprint $table) {
            $table->integer('emp_comm_calc_method')->nullable(false)->default(CCM_AMOUNT);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_stock_category', function (Blueprint $table) {
            $table->dropColumn('emp_comm_calc_method');
        });
    }
}
