<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddsAutoIdToJournal extends Migration
{
        /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_journal', function (Blueprint $table) {
            $table->dropPrimary();
            $table->unique(['type', 'trans_no'], 'uniq_journal_transaction');
            
        });

        Schema::table('0_journal', function (Blueprint $table) {
            $table->bigInteger('id')->nullable(false)->autoIncrement()->first();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_journal', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('0_journal', function (Blueprint $table) {
            $table->dropUnique('uniq_journal_transaction');
            $table->primary(['type', 'trans_no']);
        });
    }
}
