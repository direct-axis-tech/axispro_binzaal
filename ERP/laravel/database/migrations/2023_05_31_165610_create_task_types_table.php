<?php

use App\Models\TaskType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaskTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_task_types',function(Blueprint $table){
            $table->integer('id');
            $table->string('name');
            $table->string('class');
        });
        
        TaskType::insert([
            ["id" => TaskType::LEAVE_REQUEST, "name" => "Leave Request", "class" => \App\Models\Hr\EmployeeLeave::class],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('0_task_types');
    }
}
