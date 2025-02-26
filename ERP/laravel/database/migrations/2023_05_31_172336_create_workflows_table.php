<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkflowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_workflows',function(Blueprint $table){
            $table->bigIncrements('id');
            $table->integer('task_type');
            $table->bigInteger('applicable_group_id');
            $table->timestamps();
            $table->unique(['task_type', 'applicable_group_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('0_workflows');
    }
}
