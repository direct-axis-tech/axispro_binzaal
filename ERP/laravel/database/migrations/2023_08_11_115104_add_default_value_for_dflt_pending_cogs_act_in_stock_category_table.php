<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultValueForDfltPendingCogsActInStockCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_stock_category', function (Blueprint $table) {
            $table->string('dflt_pending_cogs_act', 50)->nullable(false)->default('')->after('dflt_pending_sales_act')->change();
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
            $table->string('dflt_pending_cogs_act', 50)->after('dflt_pending_sales_act')->change();
        });
    }
}
