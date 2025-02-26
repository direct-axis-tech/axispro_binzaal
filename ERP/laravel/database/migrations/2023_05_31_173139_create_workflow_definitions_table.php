<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkflowDefinitionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_workflow_definitions',function(Blueprint $table){
            $table->bigIncrements('id');
            $table->integer('flow_id');
            $table->integer('previous_state_id')->nullable();
            $table->integer('state_id');
            $table->integer('assigned_group_id');
            $table->integer('next_state_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('0_workflow_definitions');
    }
}
