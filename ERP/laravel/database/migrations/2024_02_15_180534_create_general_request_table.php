<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGeneralRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_general_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('employee_id');
            $table->smallInteger('request_type_id');
            $table->date('request_date');
            $table->string('remarks');
            $table->smallInteger('requested_by');
            $table->string('request_status', 50)->nullable(false);
            $table->smallInteger('reviewed_by')->nullable();
            $table->boolean('inactive')->default(0);
            $table->timestamps();

            $table->index('employee_id');
            $table->index('request_type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('0_general_requests');
    }
}
