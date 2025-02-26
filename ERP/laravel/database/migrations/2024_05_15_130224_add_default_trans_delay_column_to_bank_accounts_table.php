<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultTransDelayColumnToBankAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_bank_accounts', function (Blueprint $table) {
            $table->integer('dflt_trans_delay')->default(10);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_bank_accounts', function (Blueprint $table) {
            $table->dropColumn('dflt_trans_delay');
        });
    }
}
