<?php

use Illuminate\Database\Migrations\Migration;

class AddInvoiceElConfigsToSysPrefsTable extends Migration
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
                "name" => "is_contact_person_mandatory",
                "category" => "setup.axispro",
                "type" => "bool",
                "length" => 1,
                "value" => 0
            ],
            [
                "name" => "is_email_mandatory",
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
            'name',
            ['is_contact_person_mandatory', 'is_email_mandatory']
        )->delete();
    }
}
