<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTaxEffectFromToDimensionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_dimensions', function (Blueprint $table) {
            $table->date('tax_effective_from')->nullable()->default(null)->after('gst_no');
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
            $table->dropColumn('tax_effective_from');
        });
    }
}
