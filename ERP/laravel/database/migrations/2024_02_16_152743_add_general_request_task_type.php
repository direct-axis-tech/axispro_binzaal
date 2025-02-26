<?php

use App\Models\TaskType;
use App\Permissions;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGeneralRequestTaskType extends Migration
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
                "id" => TaskType::GENERAL_REQUEST, 
                "name" => "General Request", 
                "class" => \App\Models\Hr\GeneralRequest::class,
                "type_prefix" => "GR",
                "module_permission" => Permissions::HEAD_MENU_HR
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
        TaskType::whereId(TaskType::GENERAL_REQUEST)->delete();
    }
}
