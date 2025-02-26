<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\TaskType;

class AddEmployeesDeductionRewardsTaskType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('0_task_types')->insert([
            'id' => TaskType::EMP_DEDUCTION_REWARDS,         
            'name' => 'Deductions / Rewards Request', 
            'class' => 'App\Models\Hr\EmployeeRewardsDeductions',
            'type_prefix' => 'EDRR',
            'module_permission' => 'HEAD_MENU_HR'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('0_task_types')->where('id', TaskType::EMP_DEDUCTION_REWARDS)->delete();
    }
}
