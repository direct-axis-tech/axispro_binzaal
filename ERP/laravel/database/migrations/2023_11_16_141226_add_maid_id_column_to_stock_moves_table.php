<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMaidIdColumnToStockMovesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_stock_moves', function (Blueprint $table) {
            $table->integer('maid_id')->nullable()->after('stock_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_stock_moves', function (Blueprint $table) {
            $table->dropColumn('maid_id');
        });
    }
}

