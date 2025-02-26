<?php

use App\Models\TaskState;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaskStatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_task_states',function(Blueprint $table){
            $table->integer('id');
            $table->string('name');
        });
        
        TaskState::insert([
            ["id" => TaskState::STATE_1, "name" => "State 1"],
            ["id" => TaskState::STATE_2, "name" => "State 2"],
            ["id" => TaskState::STATE_3, "name" => "State 3"],
            ["id" => TaskState::STATE_4, "name" => "State 4"],
            ["id" => TaskState::STATE_5, "name" => "State 5"],
            ["id" => TaskState::STATE_6, "name" => "State 6"],
            ["id" => TaskState::STATE_7, "name" => "State 7"],
            ["id" => TaskState::STATE_8, "name" => "State 8"],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('0_task_states');
    }
}
