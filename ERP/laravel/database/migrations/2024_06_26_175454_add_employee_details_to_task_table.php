<?php

use App\Models\Hr\EmployeeLeave;
use App\Models\TaskType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmployeeDetailsToTaskTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (DB::table('0_tasks')->where('task_type', TaskType::LEAVE_REQUEST)->get() as $req) {
            $data = json_decode($req->data, true);
            $data['employee_id'] = data_get(EmployeeLeave::find($data['leave_id']), 'employee_id');
            DB::table('0_tasks')->where('id', $req->id)
                ->update(['data' => json_encode($data)]);
        }   
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
