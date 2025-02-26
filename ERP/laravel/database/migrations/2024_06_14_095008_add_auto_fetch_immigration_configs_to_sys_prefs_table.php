<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAutoFetchImmigrationConfigsToSysPrefsTable extends Migration
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
                "name" => "imm_auto_stock_category",
                "category" => "autofetch.immigration",
                "type" => "int",
                "length" => 11,
                "value" => ''
            ],
            [
                "name" => "imm_80_cat",
                "category" => "setup.axispro",
                "type" => "int",
                "length" => 11,
                "value" => ''
            ],
            [
                "name" => "imm_auto_govt_bank_acc",
                "category" => "autofetch.immigration",
                "type" => "varchar",
                "length" => 15,
                "value" => ''
            ],
            [
                "name" => "imm_next_auto_stock_no",
                "category" => "autofetch.immigration",
                "type" => "smallint",
                "length" => 6,
                "value" => '1'
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
                "imm_auto_stock_category",
                "imm_auto_govt_bank_acc",
                "imm_next_auto_stock_no",
                "imm_80_cat"
            ])
            ->delete();
    }
}
