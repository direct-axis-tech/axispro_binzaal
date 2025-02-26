<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmployerPensionRelatedHrConfigurationsToSysPrefsTable extends Migration
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
                "name" => "gpssa_employer_share",
                "category" => "setup.hr",
                "type" => "double",
                "length" => 2,
                "value" => ''
            ],
            [
                "name" => "gpssa_employer_share_bh",
                "category" => "setup.hr",
                "type" => "double",
                "length" => 2,
                "value" => ''
            ],
            [
                "name" => "gpssa_employer_share_kw",
                "category" => "setup.hr",
                "type" => "double",
                "length" => 2,
                "value" => ''
            ],
            [
                "name" => "gpssa_employer_share_om",
                "category" => "setup.hr",
                "type" => "double",
                "length" => 2,
                "value" => ''
            ],
            [
                "name" => "gpssa_employer_share_qa",
                "category" => "setup.hr",
                "type" => "double",
                "length" => 2,
                "value" => ''
            ],
            [
                "name" => "gpssa_employer_share_sa",
                "category" => "setup.hr",
                "type" => "double",
                "length" => 2,
                "value" => ''
            ],
            [
                "name" => "gpssa_employer_share_ae",
                "category" => "setup.hr",
                "type" => "double",
                "length" => 2,
                "value" => ''
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
                "gpssa_employer_share",
                "gpssa_employer_share_bh",
                "gpssa_employer_share_kw",
                "gpssa_employer_share_om",
                "gpssa_employer_share_qa",
                "gpssa_employer_share_sa",
                "gpssa_employer_share_ae"
            ])
            ->delete();
    }
}
