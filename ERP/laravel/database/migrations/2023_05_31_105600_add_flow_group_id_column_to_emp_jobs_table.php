<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFlowGroupIdColumnToEmpJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_emp_jobs', function (Blueprint $table) {
            $table->longText('flow_group_id')->after('supervisor_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_emp_jobs', function (Blueprint $table) {
            $table->dropColumn('flow_group_id');
        });
    }
}
