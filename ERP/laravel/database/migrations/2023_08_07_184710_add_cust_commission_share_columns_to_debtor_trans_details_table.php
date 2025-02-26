<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustCommissionShareColumnsToDebtorTransDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_debtor_trans_details', function (Blueprint $table) {
            $table->decimal('cust_comm_emp_share')->default(0)->nullable(false)->after('customer_commission');
            $table->decimal('cust_comm_center_share')->default(0)->nullable(false)->after('customer_commission');
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
            $table->dropColumn('cust_comm_emp_share', 'cust_comm_center_share');
        });
    }
}
