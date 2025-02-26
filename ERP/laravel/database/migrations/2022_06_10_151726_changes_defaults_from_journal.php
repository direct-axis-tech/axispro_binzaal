<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangesDefaultsFromJournal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_journal', function (Blueprint $table) {
            $table->date('tran_date')->default('1971-01-01')->change();
            $table->date('event_date')->default('1971-01-01')->change();
            $table->date('doc_date')->nullable(false)->default('1971-01-01')->change();
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
            $table->date('tran_date')->default('0000-00-00')->change();
            $table->date('event_date')->default('0000-00-00')->change();
            $table->date('doc_date')->nullable(false)->default('0000-00-00')->change();
        });
    }
}
