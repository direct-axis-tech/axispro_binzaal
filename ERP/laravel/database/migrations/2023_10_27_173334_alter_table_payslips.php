<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTablePayslips extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_payslips', function (Blueprint $table) {
            $table->decimal('per_month_holiday_salary', 8, 2);
            $table->decimal('per_day_holiday_salary', 8, 2);
            $table->decimal('per_month_overtime_salary', 8, 2);
            $table->decimal('per_day_overtime_salary', 8, 2);
            $table->decimal('per_hour_overtime_salary', 8, 2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_payslips', function (Blueprint $table) {
            $table->dropColumn([
                'per_month_holiday_salary',
                'per_day_holiday_salary',
                'per_month_overtime_salary',
                'per_day_overtime_salary',
                'per_hour_overtime_salary',
            ]);
        });
    }
}
