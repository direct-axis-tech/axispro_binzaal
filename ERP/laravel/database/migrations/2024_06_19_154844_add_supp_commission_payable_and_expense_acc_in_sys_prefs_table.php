<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSuppCommissionPayableAndExpenseAccInSysPrefsTable extends Migration
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
                "name" => "supp_comm_receivable_acc",
                "category" => "setup.axispro",
                "type" => "int",
                "length" => 11,
                "value" => ''
            ],
            [
                "name" => "supp_comm_income_acc",
                "category" => "setup.axispro",
                "type" => "int",
                "length" => 11,
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
            'supp_comm_receivable_acc',
            'supp_comm_income_acc'
        ])->delete();
    }
}
