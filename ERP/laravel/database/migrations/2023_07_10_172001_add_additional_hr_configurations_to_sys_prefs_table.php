<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdditionalHrConfigurationsToSysPrefsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('0_sys_prefs')->where('name', 'grace_time')->update(['name' => 'late_in_grace_time']);
        DB::table('0_sys_prefs')->insert([
            [
                "name" => "early_out_grace_time",
                "category" => "setup.hr",
                "type" => "int",
                "length" => 4,
                "value" => 0
            ],
            [
                "name" => "absent_when_late_in_exceeds_min",
                "category" => "setup.hr",
                "type" => "int",
                "length" => 4,
                "value" => 300
            ],
            [
                "name" => "absent_when_early_out_exceeds_min",
                "category" => "setup.hr",
                "type" => "int",
                "length" => 4,
                "value" => 300
            ],
            [
                "name" => "count_missing_punch_as",
                "category" => "setup.hr",
                "type" => "int",
                "length" => 4,
                "value" => MPO_EARLY_OUT
            ],
            [
                "name" => "value_count_missing_punch_as",
                "category" => "setup.hr",
                "type" => "double",
                "length" => 2,
                "value" => 0.5
            ],
            [
                "name" => "duplicate_punch_interval",
                "category" => "setup.hr",
                "type" => "int",
                "length" => 4,
                "value" => 3
            ],
            [
                "name" => "shift_spans_midnight",
                "category" => "setup.hr",
                "type" => "bool",
                "length" => 1,
                "value" => 1
            ],
            [
                "name" => "overtime_algorithm",
                "category" => "setup.hr",
                "type" => "int",
                "length" => 4,
                "value" => 1
            ],
            [
                "name" => "default_overtime_status",
                "category" => "setup.hr",
                "type" => "char",
                "length" => 1,
                "value" => STS_PENDING
            ],
            [
                "name" => "overtime_grace_time",
                "category" => "setup.hr",
                "type" => "int",
                "length" => 4,
                "value" => 30
            ],
            [
                "name" => "overtime_round_to",
                "category" => "setup.hr",
                "type" => "double",
                "length" => 2,
                "value" => 1
            ],
            [
                "name" => "overtime_rounding_algorithm",
                "category" => "setup.hr",
                "type" => "int",
                "length" => 4,
                "value" => 1
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('0_sys_prefs')->where('name', 'late_in_grace_time')->update(['name' => 'grace_time']);
        DB::table('0_sys_prefs')
            ->whereIn('name', [
                'early_out_grace_time',
                'absent_when_late_in_exceeds_min',
                'absent_when_early_out_exceeds_min',
                'count_missing_punch_as',
                'value_count_missing_punch_as',
                'duplicate_punch_interval',
                'shift_spans_midnight',
                'overtime_algorithm',
                'default_overtime_status',
                'overtime_grace_time',
                'overtime_round_to',
                'overtime_rounding_algorithm',
            ])
            ->delete();
    }
}
