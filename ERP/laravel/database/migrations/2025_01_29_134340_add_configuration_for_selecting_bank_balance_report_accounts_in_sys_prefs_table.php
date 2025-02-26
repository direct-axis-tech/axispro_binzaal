<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfigurationForSelectingBankBalanceReportAccountsInSysPrefsTable extends Migration
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
                "name" => "bank_bal_rep_accounts",
                "category" => "setup.axispro",
                "type" => "string",
                "length" => 255,
                "value" => ''
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
        DB::table('0_sys_prefs')->where('name', 'bank_bal_rep_accounts')->delete();
    }
}
