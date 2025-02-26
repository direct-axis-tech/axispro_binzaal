<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTotalDurationColumnToShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_shifts', function (Blueprint $table) {
            $table->time('total_duration')->after('duration2');
        });

        DB::table('0_shifts')->update([
            'total_duration' => DB::raw("addtime(ifnull(duration, '00:00:00'), ifnull(duration2, '00:00:00'))")
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_shifts', function (Blueprint $table) {
            $table->dropColumn('total_duration');
        });
    }
}
