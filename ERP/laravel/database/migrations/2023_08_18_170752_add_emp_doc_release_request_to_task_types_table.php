<?php

use App\Models\TaskType;
use Illuminate\Database\Migrations\Migration;

class AddEmpDocReleaseRequestToTaskTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        TaskType::insert([
            [
                "id" => TaskType::EMP_DOC_RELEASE_REQ,
                "name" => "Passport Release Request",
                "class" => \App\Models\Hr\EmpDocReleaseRequest::class
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        TaskType::whereId(TaskType::EMP_DOC_RELEASE_REQ)->delete();
    }
}
