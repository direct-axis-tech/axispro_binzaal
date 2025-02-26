<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGovtFeeAndServiceChargeEditableTo0StockCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_stock_category', function (Blueprint $table) {
            $table->boolean('is_govt_fee_editable')->nullable(false)->default(1);
            $table->boolean('is_srv_chrg_editable')->nullable(false)->default(1);
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
            $table->dropColumn(['is_govt_fee_editable', 'is_srv_chrg_editable']);
        });
    }
}
