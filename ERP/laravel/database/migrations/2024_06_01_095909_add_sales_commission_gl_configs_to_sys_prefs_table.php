<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSalesCommissionGlConfigsToSysPrefsTable extends Migration
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
                'name' => 'sales_commission_expense_act',
                'category' => 'setup.axispro',
                'type' => 'int',
                'length' => 11,
                'value' => ''
            ],
            [
                'name' => 'sales_commission_payable_act',
                'category' => 'setup.axispro',
                'type' => 'int',
                'length' => 11,
                'value' => ''
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
            'sales_commission_expense_act',
            'sales_commission_payable_act'
        ])->delete();
    }
}
