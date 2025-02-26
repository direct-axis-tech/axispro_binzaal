<?php

use Illuminate\Database\Migrations\Migration;

class AddEnabledPaymentMethodsAtCashierDashboardToSysPrefsTable extends Migration
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
                "name" => "enabled_payment_methods",
                "category" => "setup.axispro",
                "type" => "varchar",
                "length" => 255,
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
        DB::table('0_sys_prefs')->whereIn(
            "name",
            [
                'enabled_payment_methods'
            ]
        )->delete();
    }
}
