<?php

use App\Models\TaskType;
use App\Permissions;
use Illuminate\Database\Migrations\Migration;

class AddNewTaskTypeForMaidReplacement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        TaskType::insert([
            [
                "id" => TaskType::MAID_REPLACEMENT,
                "name" => "Maid Replacement Request",
                "class" => \App\Http\Controllers\Labour\MaidReplacementController::class,
                "type_prefix" => "MPR",
                "module_permission" => Permissions::HEAD_MENU_LABOUR
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
        TaskType::whereId(TaskType::MAID_REPLACEMENT)->delete();
    }
}
