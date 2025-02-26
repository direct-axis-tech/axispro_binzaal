<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmpDocReleaseRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_emp_doc_release_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('reference', 50)->nullable();
            $table->bigInteger('employee_id')->index();
            $table->smallInteger('document_type_id')->index();
            $table->date('requested_from');
            $table->date('return_date');
            $table->longText('reason');
            $table->string('status');
            $table->smallInteger('created_by')->index();
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
        Schema::dropIfExists('0_emp_doc_release_requests');
    }
}
