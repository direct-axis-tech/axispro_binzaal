<?php

use Illuminate\Database\Migrations\Migration;

class AddShowCommissionTotalInReportsToSysPrefsTable extends Migration
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
                'name' => 'show_comm_total_in_reports',
                'category' => 'setup.axispro',
                'type' => 'bool',
                'length' => 1,
                'value' => 1
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
        DB::table('0_sys_prefs')->where("name", 'show_comm_total_in_reports')->delete();
    }
}
