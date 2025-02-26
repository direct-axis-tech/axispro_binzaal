<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAllowInvoiceBelowThresholdsFlagsToStockCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_stock_category', function (Blueprint $table) {
            $table->boolean('is_allowed_below_service_chg')->nullable(false)->default(0);
            $table->boolean('is_allowed_below_govt_fee')->nullable(false)->default(0);
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
            $table->dropColumn('is_allowed_below_service_chg', 'is_allowed_below_govt_fee');
        });
    }
}
