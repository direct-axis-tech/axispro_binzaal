<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTable0Attendance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_attendance', function (Blueprint $table) {
            $table->time('duration2')->nullable()->after('duration');
            $table->time('punchout2')->nullable()->after('duration');
            $table->time('punchin2')->nullable()->after('duration');
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

            $table->dropColumn('duration2');
            $table->dropColumn('punchout2');
            $table->dropColumn('punchin2');

        });
    }
}
