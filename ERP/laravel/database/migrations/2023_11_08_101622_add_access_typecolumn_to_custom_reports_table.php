<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAccessTypecolumnToCustomReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_custom_reports', function (Blueprint $table) {
            $table->string('access_type')->nullable()->default('All')->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_custom_reports', function (Blueprint $table) {
            $table->dropColumn('access_type');
        });
    }
}
