<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFineColumnToDebtorTransDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_debtor_trans_details', function (Blueprint $table) {
            $table->float('fine')->nullable(false)->default('0.00')->after('govt_fee');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_debtor_trans_details', function (Blueprint $table) {
            $table->dropColumn('fine');
        });
    }
}
