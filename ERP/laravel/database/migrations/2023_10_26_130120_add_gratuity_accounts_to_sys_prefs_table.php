<?php


use Illuminate\Database\Migrations\Migration;

class AddGratuityAccountsToSysPrefsTable extends Migration
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
                'name' => 'gratuity_payable_account',
                'category' => 'setup.hr',
                'type' => 'int',
                'length' => 11,
                'value' => ''
            ],
            [
                'name' => 'gratuity_expense_account',
                'category' => 'setup.hr',
                'type' => 'int',
                'length' => 11,
                'value' => ''
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
                'gratuity_payable_account',
                'gratuity_expense_account'
            ]
        )->delete();
    }
}


