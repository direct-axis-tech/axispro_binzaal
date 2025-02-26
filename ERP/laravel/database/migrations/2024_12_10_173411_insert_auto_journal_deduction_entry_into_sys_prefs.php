<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertAutoJournalDeductionEntryIntoSysPrefs extends Migration
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
                "name" => "auto_journal_deduction_entry",
                "category" => "setup.hr",
                "type" => "int",
                "length" => 2,
                "value" => 0
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
        DB::table('0_sys_prefs')
        ->whereIn('name', [
            'auto_journal_deduction_entry',
        ])
        ->delete();
    }
}
