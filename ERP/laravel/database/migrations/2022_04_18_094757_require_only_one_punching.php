<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RequireOnlyOnePunching extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_emp_jobs', function (Blueprint $table) {
            $table
                ->boolean('require_presence_only')
                ->nullable(false)
                ->default(false)
                ->after('require_attendance');
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
            $table->dropColumn('require_presence_only');
        });
    }
}
