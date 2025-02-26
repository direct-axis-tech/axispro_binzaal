<?php

use App\Models\Entity;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\JoinClause;

class RemoveFlowGroupIdColumnFromEmpJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_emp_jobs', function (Blueprint $table) {
            $table->dropColumn('flow_group_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_emp_jobs', function (Blueprint $table) {
            $table->bigInteger('flow_group_id')->after('supervisor_id')->nullable();
        });

        DB::table('0_users')
            ->join('0_emp_jobs', function (JoinClause $join) {
                $join->on('0_users.employee_id', '0_emp_jobs.employee_id')
                    ->where('0_users.type', Entity::EMPLOYEE);
            })
            ->where('0_emp_jobs.is_current', 1)
            ->update([
                '0_emp_jobs.flow_group_id' => DB::raw('0_users.flow_group_id')
            ]);
    }
}
