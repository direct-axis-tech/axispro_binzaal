<?php

use Illuminate\Database\Migrations\Migration;

class AddEmployerPensionPayableHrAccountToSysPrefsTable extends Migration
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
                "name" => "pension_expense_account",
                "category" => "setup.hr",
                "type" => "double",
                "length" => 15,
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
        DB::table('0_sys_prefs')
            ->whereIn('name', [
                "pension_expense_account"
            ])
            ->delete();
    }
}
