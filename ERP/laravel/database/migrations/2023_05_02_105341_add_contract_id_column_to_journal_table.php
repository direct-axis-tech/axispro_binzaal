<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddContractIdColumnToJournalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_journal', function (Blueprint $table) {
            $table->bigInteger('contract_id')->nullable()->index()->after('tran_date');
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
            $table->dropColumn('contract_id');
        });
    }
}
