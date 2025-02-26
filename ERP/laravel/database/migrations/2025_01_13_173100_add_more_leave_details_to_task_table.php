<?php

use App\Models\Hr\EmployeeLeave;
use App\Models\TaskType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreLeaveDetailsToTaskTable extends Migration
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
            $leaveDetails = EmployeeLeave::find($data['leave_id']);
            $data['leave_type_id']      = data_get($leaveDetails, 'leave_type_id');
            $data['Requested On'] = data_get($leaveDetails, 'requested_on');
            $data['Memo']               = data_get($leaveDetails, 'memo');
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
        foreach (DB::table('0_tasks')->where('task_type', TaskType::LEAVE_REQUEST)->get() as $req) {
            $data = json_decode($req->data, true);
            unset($data['leave_type_id']); 
            unset($data['Requested On']);
            unset($data['Memo']);
            DB::table('0_tasks')->where('id', $req->id)
                ->update(['data' => json_encode($data)]);
        }
    }
}
