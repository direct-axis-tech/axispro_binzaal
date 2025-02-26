<?php

use Illuminate\Database\Migrations\Migration;

class AddAdditionalColumnsInStatementToSysPrefs extends Migration
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
                "name" => "extra_cols_in_statement",
                "category" => "setup.axispro",
                "type" => "string",
                "length" => 225,
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
        DB::table('0_sys_prefs')->where('name', 'extra_cols_in_statement')->delete();
    }
}
