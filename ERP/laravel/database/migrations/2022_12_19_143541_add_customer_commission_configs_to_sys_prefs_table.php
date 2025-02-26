<?php


use Illuminate\Database\Migrations\Migration;

class AddCustomerCommissionConfigsToSysPrefsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (['customer_commission_expense_act', 'customer_commission_payable_act'] as $k) {
            $value = DB::table('0_sys_prefs')->whereName($k)->value('value') ?: '';
            DB::table('0_sys_prefs')->updateOrInsert(
                ['name' => $k],
                [
                    'name' => $k,
                    'category' => 'setup.axispro',
                    'type' => 'int',
                    'length' => 11,
                    'value' => $value
                ]
            );
        }
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
                'customer_commission_expense_act',
                'customer_commission_payable_act'
            ]
        )->delete();
    }
}
