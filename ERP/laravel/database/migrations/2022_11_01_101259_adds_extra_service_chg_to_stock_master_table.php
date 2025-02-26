<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddsExtraServiceChgToStockMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_stock_master', function (Blueprint $table) {
            $table->decimal('extra_srv_chg')->nullable(false)->default('0.00');
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
            $table->dropColumn('extra_srv_chg');
        });
    }
}
