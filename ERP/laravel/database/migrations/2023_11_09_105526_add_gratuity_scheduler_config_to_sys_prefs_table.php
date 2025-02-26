<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGratuitySchedulerConfigToSysPrefsTable extends Migration
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
                "name" => "is_gratuity_accrual_sched_enabled",
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
                'is_gratuity_accrual_sched_enabled',
            ]
        )->delete();
    }
}
