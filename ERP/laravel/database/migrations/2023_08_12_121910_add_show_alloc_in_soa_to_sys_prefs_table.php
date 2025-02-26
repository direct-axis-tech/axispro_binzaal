<?php

use Illuminate\Database\Migrations\Migration;

class AddShowAllocInSoaToSysPrefsTable extends Migration
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
                'name' => 'show_alloc_in_soa',
                'category' => 'setup.axispro',
                'type' => 'bool',
                'length' => 1,
                'value' => '0'
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
        DB::table('0_sys_prefs')->where("name", 'show_alloc_in_soa')->delete();
    }
}
