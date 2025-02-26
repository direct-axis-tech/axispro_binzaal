<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IndexAutofetchedTransaction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_autofetched_trans', function (Blueprint $table) {
            $table->index('application_id');
            $table->index('system_id');
            $table->index('created_at');
            $table->index('web_user');
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
            $table->dropIndex('0_autofetched_trans_application_id_index');
            $table->dropIndex('0_autofetched_trans_system_id_index');
            $table->dropIndex('0_autofetched_trans_created_at_index');
            $table->dropIndex('0_autofetched_trans_web_user_index');
        });
    }
}
