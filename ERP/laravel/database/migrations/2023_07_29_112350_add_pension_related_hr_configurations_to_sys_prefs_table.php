<?php

use Illuminate\Database\Migrations\Migration;

class AddPensionRelatedHrConfigurationsToSysPrefsTable extends Migration
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
                "name" => "gpssa_employee_share_bh",
                "category" => "setup.hr",
                "type" => "double",
                "length" => 2,
                "value" => ''
            ],
            [
                "name" => "gpssa_employee_share_kw",
                "category" => "setup.hr",
                "type" => "double",
                "length" => 2,
                "value" => ''
            ],
            [
                "name" => "gpssa_employee_share_om",
                "category" => "setup.hr",
                "type" => "double",
                "length" => 2,
                "value" => ''
            ],
            [
                "name" => "gpssa_employee_share_qa",
                "category" => "setup.hr",
                "type" => "double",
                "length" => 2,
                "value" => ''
            ],
            [
                "name" => "gpssa_employee_share_sa",
                "category" => "setup.hr",
                "type" => "double",
                "length" => 2,
                "value" => ''
            ],
            [
                "name" => "gpssa_employee_share_ae",
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
                "gpssa_employee_share_bh",
                "gpssa_employee_share_kw",
                "gpssa_employee_share_om",
                "gpssa_employee_share_qa",
                "gpssa_employee_share_sa",
                "gpssa_employee_share_ae"
            ])
            ->delete();
    }
}
