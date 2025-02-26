<?php

use App\Models\Hr\EmployeeJob;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Alter0EmpJobsSupervisorIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $empJobs = DB::table('0_emp_jobs')->get();

        Schema::table('0_emp_jobs', function (Blueprint $table) {
            $table->json('supervisor_id')->nullable()->default('[]')->change();
        });

        $empJobs->each(function ($jobs) {
            DB::table('0_emp_jobs')
                ->where('id', $jobs->id)
                ->update(['supervisor_id' => json_encode(array_filter(["{$jobs->supervisor_id}"]))]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $empJobs = DB::table('0_emp_jobs')->get();

        EmployeeJob::query()->update(['supervisor_id' => null]);

        Schema::table('0_emp_jobs', function (Blueprint $table) {
            $table->bigInteger('supervisor_id')->nullable()->default(null)->charset(null)->collation(null)->change();
        });

        $empJobs->each(function ($jobs) {
            DB::table('0_emp_jobs')
                ->where('id', $jobs->id)
                ->update(['supervisor_id' => Arr::first(json_decode($jobs->supervisor_id))]);
        });
    }
}
