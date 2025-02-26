<?php

use App\Models\TaskType;
use App\Permissions;
use Illuminate\Database\Migrations\Migration;

class AddNewTaskTypeForMaidReturn extends Migration
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
                "id" => TaskType::MAID_RETURN,
                "name" => "Maid Return Request",
                "class" => \App\Http\Controllers\Labour\MaidReturnController::class,
                "type_prefix" => "MRR",
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
        TaskType::whereId(TaskType::MAID_RETURN)->delete();
    }
}
