<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSmsConfigurationToSysPrefsTable extends Migration
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
                "name" => "send_sms_automatically",
                "category" => "setup.axispro",
                "type" => "bool",
                "length" => 1,
                "value" => 0
            ],
            [
                "name" => "sms_template",
                "category" => "setup.axispro",
                "type" => "string",
                "length" => 255,
                "value" => "Dear Customer, \nThank you for using our services. Please use the link to download your {transType} {transRef} {link}"
            ],
            [
                "name" => "sent_link_lifetime",
                "category" => "setup.axispro",
                "type" => "int",
                "length" => 6,
                "value" => "0"
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
                'send_sms_automatically',
                'sms_template',
                'sent_link_lifetime'
            ]
        )->delete();
    }
}
