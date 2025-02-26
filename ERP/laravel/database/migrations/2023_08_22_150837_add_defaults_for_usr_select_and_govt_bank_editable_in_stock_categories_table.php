<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultsForUsrSelectAndGovtBankEditableInStockCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_stock_category', function (Blueprint $table) {
            $table->integer('govt_bnk_editable')->nullable(false)->default('1')->change();
            $table->integer('usr_sel_ac')->nullable(false)->default('0')->change();
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
            $table->integer('govt_bnk_editable')->nullable(false)->change();
            $table->integer('usr_sel_ac')->nullable(false)->change();
        });
    }
}
