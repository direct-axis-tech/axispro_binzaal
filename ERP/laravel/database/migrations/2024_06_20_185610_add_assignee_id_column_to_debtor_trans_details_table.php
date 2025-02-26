<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAssigneeIdColumnToDebtorTransDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_debtor_trans_details', function (Blueprint $table) {
            $table->integer('assignee_id')->nullable()->index()->after('cust_comm_emp_share');
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
            $table->dropColumn('assignee_id');
        });
    }
}
