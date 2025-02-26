<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPendingTxnMaxVisibilityAgeConfigToSysPrefsTable extends Migration
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
                "name" => "imm_pending_txn_max_visibility_age",
                "category" => "autofetch.immigration",
                "type" => "int",
                "length" => 11,
                "value" => 2
            ],
            [
                "name" => "ts_pending_txn_max_visibility_age",
                "category" => "autofetch.tasheel",
                "type" => "int",
                "length" => 11,
                "value" => 2
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
        DB::table('0_sys_prefs')
            ->whereIn('name', [
                "imm_pending_txn_max_visibility_age",
                "ts_pending_txn_max_visibility_age"
            ])
            ->delete();
    }
}
