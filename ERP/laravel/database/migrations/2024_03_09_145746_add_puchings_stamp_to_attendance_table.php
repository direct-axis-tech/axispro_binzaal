<?php

use App\Jobs\Hr\GenerateAttendanceJob;
use App\Models\Hr\Attendance;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPuchingsStampToAttendanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_attendance', function (Blueprint $table) {
            $table->dateTime('punchin_stamp')->nullable()->after('punchin');
            $table->dateTime('punchout_stamp')->nullable()->after('punchout');
            $table->dateTime('punchin2_stamp')->nullable()->after('punchin2');
            $table->dateTime('punchout2_stamp')->nullable()->after('punchout2');
        });

        try {
            $dates = Attendance::query()
                ->selectRaw('max(`date`) as max_date')
                ->selectRaw('min(`date`) as min_date')
                ->first();

            if ($dates) {
                GenerateAttendanceJob::dispatchNow($dates->min_date, $dates->max_date);
            }
        }

        catch (Throwable $e) {

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_attendance', function (Blueprint $table) {
            $table->dropColumn(
                'punchin_stamp',
                'punchout_stamp',
                'punchin2_stamp',
                'punchout2_stamp',
            );
        });
    }
}
