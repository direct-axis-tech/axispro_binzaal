<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFixedAssetsRelatedColumnsToStockMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_stock_master', function (Blueprint $table) {
            $table->double('current_value', 14, 2)->default(0.00);
            $table->date('up_to_date');
            $table->dateTime('created_date');
            $table->dateTime('updated_date');
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
            $table->dropColumn(['current_value', 'up_to_date', 'created_date', 'updated_date']);
        });
    }
}
