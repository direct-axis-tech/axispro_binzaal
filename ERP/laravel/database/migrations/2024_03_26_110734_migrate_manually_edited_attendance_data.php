<?php

use App\Models\Hr\Shift;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MigrateManuallyEditedAttendanceData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $attendances = DB::table('0_attendance')
            ->whereNotNull('reviewed_at')
            ->where('status', ATS_PRESENT)
            ->get();
            
        foreach ($attendances as $att) {
            $punchin = CarbonImmutable::createFromFormat(DB_DATETIME_FORMAT, "{$att->date} {$att->punchin}");
            $punchout = CarbonImmutable::createFromFormat(DB_DATETIME_FORMAT, "{$att->date} {$att->punchout}");
            Shift::fixDatesInOrder($punchin, $punchout);

            $punchin2 = $punchout2 = null;
            if ($att->punchin2) {
                $punchin2 = CarbonImmutable::createFromFormat(DB_DATETIME_FORMAT, "{$att->date} {$att->punchin2}");
                $punchout2 = CarbonImmutable::createFromFormat(DB_DATETIME_FORMAT, "{$att->date} {$att->punchout2}");
                Shift::fixDatesInOrder($punchout, $punchin2, $punchout2);
            }

            DB::table('0_attendance')
                ->where('id', $att->id)
                ->update([
                    'punchin_stamp' => $punchin->format(DB_DATETIME_FORMAT),
                    'punchout_stamp' => $punchout->format(DB_DATETIME_FORMAT),
                    'punchin2_stamp' => $punchin2 ? $punchin2->format(DB_DATETIME_FORMAT) : null,
                    'punchout2_stamp' => $punchout2 ? $punchout2->format(DB_DATETIME_FORMAT) : null,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
