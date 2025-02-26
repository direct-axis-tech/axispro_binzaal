<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEmployeeJobAddAttendanceType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_emp_jobs', function (Blueprint $table) {
            $table->smallInteger('attendance_type')->nullable(false)->default(ACT_SHIFT_BASED)->after('work_hours');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_emp_jobs', function (Blueprint $table) {
            $table->dropColumn('attendance_type');
        });
    }
}
