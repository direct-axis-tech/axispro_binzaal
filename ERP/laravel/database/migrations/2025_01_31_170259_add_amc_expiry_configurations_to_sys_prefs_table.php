<?php

use App\Models\System\AccessRole;
use Illuminate\Database\Migrations\Migration;

class AddAmcExpiryConfigurationsToSysPrefsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $targetRoles = AccessRole::query()
            ->where('role', 'like', '%admin%')
            ->orWhere('role', 'like', '%account%')
            ->orWhere('role', 'like', '%finance%')
            ->orWhere('role', 'like', '%it%')
            ->pluck('id')
            ->all();

        DB::table('0_sys_prefs')->insert([
            [
                "name" => "amc_last_renewed_till",
                "category" => "setup.axispro",
                "type" => "string",
                "length" => 19,
                "value" => ''
            ],
            [
                "name" => "amc_duration_in_months",
                "category" => "setup.axispro",
                "type" => "string",
                "length" => 3,
                "value" => '12'
            ],
            [
                "name" => "amc_notify_to_roles",
                "category" => "setup.axispro",
                "type" => "string",
                "length" => 225,
                "value" => implode(',', $targetRoles)
            ],
            [
                "name" => "amc_early_notice_days",
                "category" => "setup.axispro",
                "type" => "string",
                "length" => 3,
                "value" => '30'
            ],
            [
                "name" => "amc_late_notice_days",
                "category" => "setup.axispro",
                "type" => "string",
                "length" => 3,
                "value" => '7'
            ],
            [
                "name" => "amc_grace_days_after_expiry",
                "category" => "setup.axispro",
                "type" => "string",
                "length" => 3,
                "value" => '0'
            ],
            [
                "name" => "amc_system_updated_at",
                "category" => "setup.axispro",
                "type" => "string",
                "length" => 19,
                "value" => ''
            ],
            [
                "name" => "amc_server_updated_at",
                "category" => "setup.axispro",
                "type" => "string",
                "length" => 19,
                "value" => ''
            ],
            [
                "name" => "amc_last_fetched_at",
                "category" => "setup.axispro",
                "type" => "string",
                "length" => 19,
                "value" => ''
            ],
            [
                "name" => "amc_last_fetch_result",
                "category" => "setup.axispro",
                "type" => "string",
                "length" => 19,
                "value" => ''
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
        DB::table('0_sys_prefs')->whereIn('name', [
            'amc_last_renewed_till',
            'amc_duration_in_months',
            'amc_notify_to_roles',
            'amc_early_notice_days',
            'amc_late_notice_days',
            'amc_grace_days_after_expiry',
            'amc_last_fetched_at',
            'amc_last_fetch_result',
            'amc_system_updated_at',
            'amc_server_updated_at',
        ])->delete();
    }
}
