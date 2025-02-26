<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfigurationForAutofetchCategoriesToSysPrefsTable extends Migration
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
                "name" => "tas_17_1_cat",
                "category" => "setup.axispro",
                "type" => "smallint",
                "length" => 6,
                "value" => 0
            ],
            [
                "name" => "tas_216_cat",
                "category" => "setup.axispro",
                "type" => "smallint",
                "length" => 6,
                "value" => 0
            ],
            [
                "name" => "tas_36_cat",
                "category" => "setup.axispro",
                "type" => "smallint",
                "length" => 6,
                "value" => 0
            ],
            [
                "name" => "tas_72_cat",
                "category" => "setup.axispro",
                "type" => "smallint",
                "length" => 6,
                "value" => 0
            ],
            [
                "name" => "twj_144_cat",
                "category" => "setup.axispro",
                "type" => "smallint",
                "length" => 6,
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
        DB::table('0_sys_prefs')->whereIn(
            "name",
            [
                'tas_17_1_cat',
                'tas_216_cat',
                'tas_36_cat',
                'tas_72_cat',
                'twj_144_cat'
            ]
        )->delete();
    }
}
