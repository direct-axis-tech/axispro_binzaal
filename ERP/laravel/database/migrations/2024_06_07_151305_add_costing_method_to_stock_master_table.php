<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCostingMethodToStockMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_stock_master', function (Blueprint $table) {
            $table->integer('costing_method')->nullable(false)->default(COSTING_METHOD_NORMAL)->after('mb_flag');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_stock_master', function (Blueprint $table) {
            $table->dropColumn('costing_method');
        });
    }
}
