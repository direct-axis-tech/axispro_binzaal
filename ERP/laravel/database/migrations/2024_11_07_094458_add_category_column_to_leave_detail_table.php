<?php

use App\Models\Hr\EmployeeLeave;
use App\Traits\MigratesData;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCategoryColumnToLeaveDetailTable extends Migration
{
    use MigratesData;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_emp_leave_details', function (Blueprint $table) {
            $table->integer('category_id')->after('leave_type_id')->nullable(false)->default(EmployeeLeave::CATEGORY_NORMAL);
            $table->dropUnique('uniq_leave');
            $table->unique(['employee_id', 'leave_type_id', 'date', 'type', 'category_id'], 'uniq_leave');
        });

        $this->migrateData(function () {
            DB::table('0_emp_leave_details')
                ->join('0_emp_leaves', '0_emp_leaves.id', '0_emp_leave_details.leave_id')
                ->update([
                    '0_emp_leave_details.category_id' => DB::raw('0_emp_leaves.category_id')
                ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_emp_leave_details', function (Blueprint $table) {
            $table->dropUnique('uniq_leave');
            $table->dropColumn('category_id');
            $table->unique(['employee_id', 'leave_type_id', 'date', 'type'], 'uniq_leave');
        });
    }
}
