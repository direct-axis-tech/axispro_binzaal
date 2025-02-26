<?php

use App\Http\Controllers\Hr\EmpLeaveController;
use App\Jobs\Hr\GenerateLeaveBalanceJob;
use App\Models\Hr\EmployeeLeave;
use App\Models\Hr\LeaveType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RecomputeEmployeesLeaveBalance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        EmpLeaveController::processEmployeeLeaveBalance(null, LeaveType::ANNUAL);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
