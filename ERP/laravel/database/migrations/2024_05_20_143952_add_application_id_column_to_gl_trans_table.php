<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApplicationIdColumnToGlTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasColumn('0_gl_trans','application_id')) {
            Schema::table('0_gl_trans', function (Blueprint $table) {
                $table->string('application_id')->after('transaction_id')->nullable();
            });
        }
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_gl_trans', function (Blueprint $table) {
            $table->dropColumn('application_id');
        });
    }
}
