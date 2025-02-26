<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddLoanAndSalaryAdvancesPayElementsToSysPrefsTable extends Migration
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
                "name" => "advance_recovery_el",
                "category" => "setup.hr",
                "type" => "int",
                "length" => 8,
                "value" => ''
            ],
            [
                "name" => "loan_recovery_el",
                "category" => "setup.hr",
                "type" => "int",
                "length" => 8,
                "value" => ''
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
                'advance_recovery_el',
                'loan_recovery_el',
            ]
        )->delete();
    }
}
