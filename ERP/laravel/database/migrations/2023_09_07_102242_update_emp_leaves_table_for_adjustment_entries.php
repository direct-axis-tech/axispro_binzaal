<?php

use App\Models\Hr\EmployeeLeave;
use App\Models\Hr\EmployeeLeaveDetail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateEmpLeavesTableForAdjustmentEntries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_emp_leaves', function (Blueprint $table) {
            $table->integer('category_id')->after('id')->nullable(false)->default(EmployeeLeave::CATEGORY_NORMAL);
            $table->date('from')->nullable()->change();
            $table->date('till')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::transaction(function () {
            DB::statement('LOCK TABLES 0_emp_leaves WRITE, 0_emp_leave_details WRITE');
            $leaveIds = EmployeeLeave::whereNull('from')
                ->orWhereNull('till')
                ->orWhere('category_id', '<>', EmployeeLeave::CATEGORY_NORMAL)
                ->pluck('id')
                ->toArray();

            if ($leaveIds) {
                EmployeeLeaveDetail::whereIn('leave_id', $leaveIds)->delete();
                EmployeeLeave::whereIn('id', $leaveIds)->delete();
            }
            DB::statement('UNLOCK TABLES');
        });

        Schema::table('0_emp_leaves', function (Blueprint $table) {
            $table->dropColumn('category_id');
            $table->date('from')->nullable(false)->change();
            $table->date('till')->nullable(false)->change();
        });
    }
}
