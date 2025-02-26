<?php

use Illuminate\Database\Migrations\Migration;

class AddEmailRelatedConfigurationToSysPrefsTable extends Migration
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
                "name" => "send_email_automatically",
                "category" => "setup.axispro",
                "type" => "bool",
                "length" => 1,
                "value" => 0
            ],
            [
                "name" => "email_subject",
                "category" => "setup.axispro",
                "type" => "string",
                "length" => 255,
                "value" => "{transType} - {companyName}"
            ],
            [
                "name" => "email_template",
                "category" => "setup.axispro",
                "type" => "string",
                "length" => 255,
                "value" => "Dear Customer,\n\nThank you for using our services.\nPlease use the link to download your {transType} {link}.\n\n{companyName}"
            ],
            [
                "name" => "email_link_lifetime",
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
                'email_link_lifetime',
                'email_subject',
                'email_template',
                'send_email_automatically',
            ]
        )->delete();
    }
}
