<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDfltCostingMethodToStockCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_stock_category', function (Blueprint $table) {
            $table->integer('dflt_costing_method')->nullable(false)->default(COSTING_METHOD_NORMAL)->after('dflt_mb_flag');
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
            $table->dropColumn('dflt_costing_method');
        });
    }
}
