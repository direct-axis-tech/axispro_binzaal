<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdditionalColumnsToPayslipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_payslips', function (Blueprint $table) {
            $table->date('till')->after('employee_id');
            $table->date('from')->after('employee_id');
            $table->tinyInteger('work_days')->after('per_hour_salary');
        });

        DB::table('0_payslips as payslip')
            ->leftJoin('0_payrolls as payroll', 'payslip.payroll_id', 'payroll.id')
            ->update([
                'payslip.from' => DB::raw('`payroll`.`from`'),
                'payslip.till' => DB::raw('`payroll`.`till`'),
                'payslip.work_days' => DB::raw('`payroll`.`work_days`')
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_payslips', function (Blueprint $table) {
            $table->dropColumn('from', 'till', 'work_days');
        });
    }
}
