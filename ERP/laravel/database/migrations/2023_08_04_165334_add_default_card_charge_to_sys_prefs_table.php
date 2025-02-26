<?php

use Illuminate\Database\Migrations\Migration;
use PhpParser\Node\Name;

class AddDefaultCardChargeToSysPrefsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $value = DB::table('0_sys_prefs')->whereName('default_card_charge')->value('value') ?: 0;
        DB::table('0_sys_prefs')->updateOrInsert(
            ["name" => "default_card_charge"],
            [
                "name" => "default_card_charge",
                "category" => "setup.axispro",
                "type" => "bool",
                "length" => 1,
                "value" => $value
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('0_sys_prefs')->where('name', 'default_card_charge')->delete();
    }
}
