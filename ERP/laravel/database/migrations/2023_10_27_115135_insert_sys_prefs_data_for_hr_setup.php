<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertSysPrefsDataForHrSetup extends Migration
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
                "name" => "overtime_salary_elements",
                "category" => "setup.hr",
                "type" => "string",
                "length" => 10,
                "value" => ''
            ],
            [
                "name" => "holidays_salary_elements",
                "category" => "setup.hr",
                "type" => "string",
                "length" => 10,
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
        DB::table('0_sys_prefs')->where('name', 'overtime_salary_elements')->delete();
        DB::table('0_sys_prefs')->where('name', 'holidays_salary_elements')->delete();
    }
}
