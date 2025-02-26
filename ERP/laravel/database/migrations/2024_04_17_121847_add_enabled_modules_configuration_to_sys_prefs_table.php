<?php

use App\Permissions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddEnabledModulesConfigurationToSysPrefsTable extends Migration
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
                "name" => "enabled_modules",
                "category" => "setup.axispro",
                "type" => "string",
                "length" => 225,
                "value" => implode(',', [
                    Permissions::HEAD_MENU_SALES,
                    Permissions::HEAD_MENU_PURCHASE,
                    Permissions::HEAD_MENU_FINANCE,
                    Permissions::HEAD_MENU_ASSET,
                    Permissions::HEAD_MENU_HR,
                    Permissions::HEAD_MENU_LABOUR,
                    'HEAD_MENU_INVENTORY'
                ])
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
        DB::table('0_sys_prefs')->where('name', 'enabled_modules')->delete();
    }
}
