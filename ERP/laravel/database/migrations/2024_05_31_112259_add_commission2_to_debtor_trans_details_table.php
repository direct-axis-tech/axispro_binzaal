<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCommission2ToDebtorTransDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_debtor_trans_details', function (Blueprint $table) {
            $table->decimal('customer_commission2', 14)->nullable(false)->default('0')->after('cust_comm_emp_share');
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
            $table->dropColumn('customer_commission2');
        });
    }
}