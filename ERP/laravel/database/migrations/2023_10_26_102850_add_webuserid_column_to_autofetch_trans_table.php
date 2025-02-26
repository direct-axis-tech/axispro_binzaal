<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWebuseridColumnToAutofetchTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_autofetched_trans', function (Blueprint $table) {
            $table->string('webuser_id', 30)->nullable()->after('web_user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_autofetched_trans', function (Blueprint $table) {
            $table->dropColumn('webuser_id');
        });
    }
}
