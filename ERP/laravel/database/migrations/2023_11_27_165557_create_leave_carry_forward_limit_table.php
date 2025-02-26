<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaveCarryForwardLimitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_leave_carry_forward', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('carry_forward_limit');
            $table->date('affected_from_date');
            $table->integer('leave_type_id');
            $table->boolean('inactive')->nullable(false)->default(0);
            $table->smallInteger('created_by')->nullable(false);
            $table->smallInteger('updated_by')->nullable();
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
        Schema::dropIfExists('0_leave_carry_forward');
    }
}
