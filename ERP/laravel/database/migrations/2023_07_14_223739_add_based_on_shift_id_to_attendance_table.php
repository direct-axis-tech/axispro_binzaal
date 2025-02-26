<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBasedOnShiftIdToAttendanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_attendance', function (Blueprint $table) {
            $table->smallInteger('based_on_shift_id')->nullable()->after('date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_attendance', function (Blueprint $table) {
            $table->dropColumn('based_on_shift_id');
        });
    }
}
