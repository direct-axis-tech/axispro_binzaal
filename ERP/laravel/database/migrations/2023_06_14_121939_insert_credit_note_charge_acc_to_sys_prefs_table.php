<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class InsertCreditNoteChargeAccToSysPrefsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('0_sys_prefs')->insert([
            'name' => 'credit_note_charge_acc',
            'category' => 'glsetup.sales',
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
        DB::table('0_sys_prefs')->where('name', 'credit_note_charge_acc')->delete();
    }
}
