<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddAutoAllocationSchedulerConfigToSysPrefsTable extends Migration
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
                "name" => "is_auto_alloc_scheduler_enabled",
                "category" => "setup.axispro",
                "type" => "bool",
                "length" => 1,
                "value" => 0
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
                'is_auto_alloc_scheduler_enabled',
            ]
        )->delete();
    }
}
