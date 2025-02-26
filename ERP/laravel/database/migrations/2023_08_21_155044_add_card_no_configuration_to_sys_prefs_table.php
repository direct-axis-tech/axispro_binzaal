<?php

use Illuminate\Database\Migrations\Migration;

class AddCardNoConfigurationToSysPrefsTable extends Migration
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
                "name" => "req_card_no_4_cr_cd_pmt",
                "category" => "setup.axispro",
                "type" => "bool",
                "length" => 1,
                "value" => 0
            ],
            [
                "name" => "req_card_no_4_cn_cd_pmt",
                "category" => "setup.axispro",
                "type" => "bool",
                "length" => 1,
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
            "name",
            [
                'req_card_no_4_cr_cd_pmt',
                'req_card_no_4_cn_cd_pmt'
            ]
        )->delete();
    }
}
