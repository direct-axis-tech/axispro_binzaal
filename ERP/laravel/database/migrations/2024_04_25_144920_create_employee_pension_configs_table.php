<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeePensionConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_pension_configs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 250);
            $table->double('employee_share', 8, 2);
            $table->double('employer_share', 8, 2);
            $table->smallInteger('created_by');
            $table->boolean('inactive')->nullable(false)->default(0);
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
        Schema::dropIfExists('0_pension_configs');
    }
}
