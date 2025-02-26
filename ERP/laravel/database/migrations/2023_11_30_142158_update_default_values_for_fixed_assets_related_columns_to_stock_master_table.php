<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDefaultValuesForFixedAssetsRelatedColumnsToStockMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_stock_master', function (Blueprint $table) {
            $table->date('up_to_date')->nullable(true)->default(null)->change();
            $table->dateTime('created_date')->nullable(true)->default(null)->change();
            $table->dateTime('updated_date')->nullable(true)->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
