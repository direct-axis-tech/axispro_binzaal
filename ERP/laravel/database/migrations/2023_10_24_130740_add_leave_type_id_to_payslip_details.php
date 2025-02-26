<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLeaveTypeIdToPayslipDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_payslip_details', function (Blueprint $table) {
            $table->string('leave_type_id')->nullable()->after('payslip_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_payslip_details', function (Blueprint $table) {
            $table->dropColumn('leave_type_id');
        });
    }
}
