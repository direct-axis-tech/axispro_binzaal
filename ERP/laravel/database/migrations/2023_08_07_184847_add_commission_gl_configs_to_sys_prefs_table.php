<?php

use Illuminate\Database\Migrations\Migration;

class AddCommissionGlConfigsToSysPrefsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('0_sys_prefs')->whereIn(
            "name",
            [
                'customer_commission_expense_act',
                'customer_commission_payable_act'
            ]
        )->delete();
        DB::table('0_sys_prefs')->insert([
            [
                'name' => 'customer_commission_expense_act',
                'category' => 'setup.axispro',
                'type' => 'int',
                'length' => 11,
                'value' => ''
            ],
            [
                'name' => 'customer_commission_payable_act',
                'category' => 'setup.axispro',
                'type' => 'int',
                'length' => 11,
                'value' => ''
            ],
            [
                "name" => "emp_commission_payable_act",
                "category" => "setup.axispro",
                "type" => "smallint",
                "length" => 6,
                "value" => ''
            ],
            [
                "name" => "emp_commission_expense_act",
                "category" => "setup.axispro",
                "type" => "smallint",
                "length" => 6,
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
        DB::table('0_sys_prefs')->whereIn('name', [
            'emp_commission_payable_act',
            'emp_commission_expense_act'
        ])->delete();
    }
}
