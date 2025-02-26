<?php

use Illuminate\Database\Migrations\Migration;

class AddWorkingHoursGraceTimeConfigToSysPrefsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        DB::table('0_sys_prefs')->insert([
            [
                "name" => "working_hours_grace_time",
                "category" => "setup.hr",
                "type" => "int",
                "length" => 4,
                "value" => '5'
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
        DB::table('0_sys_prefs')->whereIn(
            "name",
            [
                'working_hours_grace_time',
            ]
        )->delete();
    }
}
