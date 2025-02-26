<?php

use App\Models\TaskType;
use App\Permissions;
use Illuminate\Database\Migrations\Migration;

class AddTimeoutRequestToTaskTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (DB::table('0_task_types')->where('name', 'Timeout Request')->exists()) {
            return;
        }

        DB::table('0_task_types')->insert([
            'id' => TaskType::TIMEOUT_REQUEST,
            'name' => 'Timeout Request',
            'class' => 'App\Models\Hr\EmpTimeoutRequest',
            'type_prefix' => 'TOR',
            'module_permission' => Permissions::HEAD_MENU_HR,
            'uses_fa_code' => 0
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('0_task_types')->where('name', 'Timeout Request')->delete();
    }
}
