<?php


use Illuminate\Database\Migrations\Migration;

class AddLeaveAccrualAccountsToSysPrefsTbl extends Migration
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
                'name' => 'leave_accrual_payable_account',
                'category' => 'setup.hr',
                'type' => 'int',
                'length' => 11,
                'value' => ''
            ],
            [
                'name' => 'leave_accrual_expense_account',
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
                'leave_accrual_payable_account',
                'leave_accrual_expense_account',
            ]
        )->delete();
    }
}

