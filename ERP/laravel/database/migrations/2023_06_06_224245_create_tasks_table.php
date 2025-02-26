<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('flow_id');
            $table->integer('task_type');
            $table->bigInteger('initiated_group_id');
            $table->mediumInteger('initiated_by');
            $table->json('data')->nullable();
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
        Schema::dropIfExists('0_tasks');
    }
}
