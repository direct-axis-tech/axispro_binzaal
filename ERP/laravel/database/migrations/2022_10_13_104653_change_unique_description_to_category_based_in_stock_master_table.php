<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeUniqueDescriptionToCategoryBasedInStockMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_stock_master', function (Blueprint $table) {
            $table->dropUnique('description');
            $table->unique(['description', 'category_id']);
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
            $table->unique('description', 'description');
            $table->dropUnique(['description', 'category_id']);
        });
    }
}
