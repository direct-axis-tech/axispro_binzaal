<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
class AddDeveloperPasswordToSystemPrefTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('0_sys_prefs')->insert([
            'name' => 'developer_password',
            'category' => 'setup.company',
            'type' => 'varchar',
            'length' => '255',
            'value' => app('hash')->make(config('auth.developer_credential')),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       DB::table('0_sys_prefs')->where('name', 'developer_password')->delete();
    }
}
