<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRequireAuthCodeForCreditCardToSysPrefsTable extends Migration
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
                "name" => "req_auth_code_4_cc_pmt",
                "category" => "setup.axispro",
                "type" => "bool",
                "length" => 1,
                "value" => 0
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
        DB::table('0_sys_prefs')->where('name', 'req_auth_code_4_cc_pmt')->delete();
    }
}
