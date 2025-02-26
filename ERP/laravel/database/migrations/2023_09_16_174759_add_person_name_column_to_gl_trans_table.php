<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPersonNameColumnToGlTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_gl_trans', function (Blueprint $table) {
            $table->longText('person_name')->nullable()->after('person_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_gl_trans', function (Blueprint $table) {
            $table->dropColumn('person_name');
        });
    }
}
