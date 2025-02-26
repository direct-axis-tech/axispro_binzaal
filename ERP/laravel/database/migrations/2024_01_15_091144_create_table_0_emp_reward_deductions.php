<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTable0EmpRewardDeductions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_emp_reward_deductions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('employee_id');
            $table->smallInteger('element_type');
            $table->smallInteger('element');
            $table->smallInteger('sub_element');
            $table->decimal('amount', 8, 2)->default(0);
            $table->date('effective_date');
            $table->smallInteger('number_of_installments')->nullable(false);
            $table->date('document_date');
            $table->string('remarks', 255)->nullable()->default(null);
            $table->string('request_status', 50)->nullable();
            $table->boolean('inactive')->default(0);
            $table->timestamps();

            $table->index('employee_id');
            $table->index('element_type');
        });

        Schema::create('0_emp_reward_deductions_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('reward_deduction_id');
            $table->date('installment_date');
            $table->decimal('installment_amount', 8, 2)->default(0);
            $table->integer('payslip_id')->nullable();
            $table->decimal('processed_amount', 8, 2)->default(0);
            $table->timestamps();

            $table->index('reward_deduction_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('0_emp_reward_deductions');
        Schema::dropIfExists('0_emp_reward_deductions_details');
    }
}
