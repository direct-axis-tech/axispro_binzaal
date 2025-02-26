<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCreditDaysToDebtorMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_debtors_master', function (Blueprint $table) {
            $table->integer('credit_days')->nullable()->after('credit_limit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_debtors_master', function (Blueprint $table) {
            $table->dropColumn('credit_days');
        });
    }
}
