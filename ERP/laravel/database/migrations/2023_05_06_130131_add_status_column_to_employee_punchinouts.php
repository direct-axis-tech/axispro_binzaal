<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusColumnToEmployeePunchinouts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_empl_punchinouts', function (Blueprint $table) {
            $table->string('status')->nullable()->after('authtime');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_empl_punchinouts', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
