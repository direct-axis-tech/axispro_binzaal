<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddContractIdColumnToDebtorTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_debtor_trans', function (Blueprint $table) {
            $table->bigInteger('contract_id')->nullable()->after('branch_code')->index();
            $table->date('period_till')->nullable()->after('due_date');
            $table->date('period_from')->nullable()->after('due_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_debtor_trans', function (Blueprint $table) {
            $table->dropColumn('contract_id');
            $table->dropColumn('period_from');
            $table->dropColumn('period_till');
        });
    }
}
