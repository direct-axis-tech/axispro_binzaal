<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEmpLeavesTableForLeaveTransactionType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_emp_leaves', function (Blueprint $table) {
            $table->smallInteger('transaction_type')->nullable(false)->after('leave_type_id');
        });

        DB::statement("
            UPDATE 0_emp_leaves, 0_emp_leave_details
            SET 0_emp_leaves.transaction_type = 0_emp_leave_details.type
            WHERE 0_emp_leaves.id = 0_emp_leave_details.leave_id
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_emp_leaves', function (Blueprint $table) {
            $table->dropColumn('transaction_type');
        });
    }
}
