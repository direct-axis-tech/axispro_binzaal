<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPensionSchemeToEmployeeJobs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_emp_jobs', function (Blueprint $table) {
            $table->smallInteger('pension_scheme')->nullable()->after('supervisor_id');
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
            $table->dropColumn('pension_scheme');
        });
    }
}
