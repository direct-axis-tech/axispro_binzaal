<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmpTimeoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_emp_timeouts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('employee_id')->index();
            $table->date('time_out_date');
            $table->time('time_out_from');
            $table->time('time_out_to');
            $table->bigInteger('timeout_duration');
            $table->text('remarks')->nullable();
            $table->string('status', 250);
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
        Schema::dropIfExists('0_emp_timeouts');
    }
}
