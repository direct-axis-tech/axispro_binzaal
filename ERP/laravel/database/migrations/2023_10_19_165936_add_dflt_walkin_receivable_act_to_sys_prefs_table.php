<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddDfltWalkinReceivableActToSysPrefsTable extends Migration
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
                "name" => "walkin_receivable_act",
                "category" => "glsetup.sales",
                "type" => "string",
                "length" => 25,
                "value" => pref('gl.sales.debtors_act')
            ]
        ]);

        DB::table('0_gl_trans')
            ->where('account', '1201')
            ->where('person_type_id', PT_CUSTOMER)
            ->update([
                'account' => pref('gl.sales.debtors_act')
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
                'walkin_receivable_act',
            ]
        )->delete();
    }
}
