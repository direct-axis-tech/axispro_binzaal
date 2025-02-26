<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFineColumnToDimensionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_dimensions', function (Blueprint $table) {
            $table->boolean('is_fine_col_enabled')->default(0)->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_dimensions', function (Blueprint $table) {
            $table->dropColumn(
                'is_fine_col_enabled'
            );
        });
    }
}
