<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPersonalTimeoutCalculationMethodToSysPrefs extends Migration
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
                "name" => "personal_timeout_calculation_method",
                "category" => "setup.hr",
                "type" => "int",
                "length" => 8,
                "value" => ''
            ]
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
                'personal_timeout_calculation_method'
            ]
        )->delete();
    }
}
