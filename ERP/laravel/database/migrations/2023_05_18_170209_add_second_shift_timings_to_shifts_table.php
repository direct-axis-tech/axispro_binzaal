<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSecondShiftTimingsToShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_shifts', function (Blueprint $table) {
            $table->time('duration2')->nullable()->after('duration');
            $table->time('till2')->nullable()->after('duration');
            $table->time('from2')->nullable()->after('duration');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_shifts', function (Blueprint $table) {
            $table->dropColumn('from2');
            $table->dropColumn('till2');
            $table->dropColumn('duration2');
        });
    }
}
