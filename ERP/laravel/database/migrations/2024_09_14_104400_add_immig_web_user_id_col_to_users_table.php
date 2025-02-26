<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddImmigWebUserIdColToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_users', function (Blueprint $table) {
            $table->string('imm_webuser_id')->nullable()->after('webuser_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_users', function (Blueprint $table) {
            $table->dropColumn('imm_webuser_id');
        });
    }
}
