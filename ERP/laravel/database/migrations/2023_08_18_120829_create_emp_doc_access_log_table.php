<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmpDocAccessLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_emp_doc_access_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('employee_id')->index();
            $table->smallInteger('document_type_id');
            $table->smallInteger('user_id')->index();
            $table->string('action');
            $table->dateTime('stamp');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('0_emp_doc_access_log');
    }
}
