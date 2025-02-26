<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReturnDateColumnToLabourContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_labour_contracts', function (Blueprint $table) {
            $table->date('maid_returned_at')->nullable()->after('maid_delivered_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_labour_contracts', function (Blueprint $table) {
            $table->dropColumn('maid_returned_at');
        });
    }
}
