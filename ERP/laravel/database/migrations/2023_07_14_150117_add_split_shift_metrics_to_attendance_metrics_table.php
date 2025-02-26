<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSplitShiftMetricsToAttendanceMetricsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_attendance_metrics', function (Blueprint $table) {
            $table->decimal('amount2', 8, 4)->default(0.00)->after('amount');
            $table->smallInteger('minutes2')->default(0)->after('amount');
            $table->decimal('amount1', 8, 4)->default(0.00)->after('amount');
            $table->smallInteger('minutes1')->default(0)->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_attendance_metrics', function (Blueprint $table) {
            $table->dropColumn('amount2', 'minutes2', 'amount1', 'minutes1');
        });
    }
}
