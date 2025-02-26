<?php

use App\Models\TaskType;
use App\Permissions;
use Illuminate\Database\Migrations\Migration;

class InsertNewRowEditTimesheetToTaskTypesTbl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        TaskType::insert([
            "id"  => TaskType::EDIT_TIMESHEET,
            "name" => "Edit Timesheet",
            "class" => "App\Http\Controllers\Hr\AttendanceController",
            "type_prefix" => "TU",
            "module_permission" => Permissions::HEAD_MENU_HR,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        TaskType::whereId(TaskType::EDIT_TIMESHEET)->delete();
    }
}
