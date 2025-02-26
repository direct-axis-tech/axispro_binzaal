<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddContractIdColumnToVoidedStockMovesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_voided_stock_moves', function (Blueprint $table) {
            $table->bigInteger('contract_id')->nullable()->after('reference');
            $table->integer('maid_id')->nullable()->after('contract_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_voided_stock_moves', function (Blueprint $table) {
            $table->dropColumn('contract_id');
            $table->dropColumn('maid_id');
        });
    }
}
