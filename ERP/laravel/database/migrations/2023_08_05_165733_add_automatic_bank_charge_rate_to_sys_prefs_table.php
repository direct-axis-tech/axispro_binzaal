<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAutomaticBankChargeRateToSysPrefsTable extends Migration
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
                "name" => "enable_auto_bank_charge",
                "category" => "setup.axispro",
                "type" => "bool",
                "length" => 1,
                "value" => 0
            ],
            [
                "name" => "auto_bank_charge_rate",
                "category" => "setup.axispro",
                "type" => "double",
                "length" => 6,
                "value" => 0
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
            'name',
            [
                'enable_auto_bank_charge',
                'auto_bank_charge_rate'
            ]
        )->delete();
    }
}
