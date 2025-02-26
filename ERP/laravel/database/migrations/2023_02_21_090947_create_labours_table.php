<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLaboursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_labours', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->string('arabic_name')->nullable();
            $table->string('mothers_name')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('address')->nullable();
            $table->string('religion')->nullable();
            $table->string('nationality')->nullable();
            $table->string('gender')->nullable();
            $table->integer('age')->nullable();
            $table->date('dob')->nullable();
            $table->integer('height')->nullable();
            $table->integer('weight')->nullable();
            $table->string('marital_status')->nullable();
            $table->integer('no_of_children')->nullable();
            $table->integer('mother_tongue')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('education')->nullable();
            $table->json('languages')->nullable();
            $table->json('skills')->nullable();
            $table->text('work_experience')->nullable();
            $table->text('video')->nullable();
            $table->integer('agent_id')->nullable();
            $table->integer('job_type')->nullable();
            $table->integer('type')->nullable();
            $table->integer('category')->nullable();
            $table->json('locations')->nullable();
            $table->text('contract_period')->nullable();
            $table->date('application_date')->nullable();
            $table->decimal('salary', 14, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('is_available')->default(1);
            $table->boolean('inactive')->default(1);
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
        Schema::dropIfExists('0_labours');
    }
}
