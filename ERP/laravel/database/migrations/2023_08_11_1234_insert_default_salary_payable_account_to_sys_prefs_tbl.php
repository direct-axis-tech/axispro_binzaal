<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class InsertDefaultSalaryPayableAccountToSysPrefsTbl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('0_sys_prefs')->insert([
            'name' => 'default_salary_payable_account',
            'category' => 'setup.hr',
            'type' => 'varchar',
            'length' => 15,
            'value' => ''
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('0_sys_prefs')->where('name', 'default_salary_payable_account')->delete();
    }
}
