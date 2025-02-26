<?php

use App\Models\Hr\Employee;
use App\Models\Hr\EmployeeJob;
use App\Models\System\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Arr;

class MigrateGpssaShareFromHrsetupToMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $configuredPensions = array_merge(
            [
                'default' => [
                    'name' => 'GPSSA Default',
                    'employee_share' => pref('hr.gpssa_employee_share', 0),
                    'employer_share' => pref('hr.gpssa_employer_share', 0),
                ]
            ],
            collect(array_keys(GCC_COUNTRIES))->mapWithKeys(function ($code) {
                return [
                    $code => [
                        'name' => 'GPSSA ' . GCC_COUNTRIES[$code],
                        'employee_share' => pref('hr.gpssa_employee_share_' . strtolower($code), 0),
                        'employer_share' => pref('hr.gpssa_employer_share_' . strtolower($code), 0),
                    ]
                    ];
            })->toArray()
        );

        // Filter the configurations
        $configuredPensions = array_filter($configuredPensions, function ($item) {
            return ($item['employee_share'] != 0 || $item['employer_share'] != 0);
        });

        if (empty($configuredPensions)) {
            $configuredPensions['default'] = [
                'name' => 'GPSSA Default',
                'employee_share' => 11,
                'employer_share' => 15,
            ];
        }

        // Loop for GCC countries
        $currentTime = date(DB_DATETIME_FORMAT);
        foreach ($configuredPensions as $key => $config) {
            $schemeId = DB::table('0_pension_configs')->insertGetId(
                array_merge(
                    $config,
                    [
                        'created_by' => User::SYSTEM_USER,
                        'inactive' => false,
                        'created_at' => $currentTime,
                        'updated_at' => $currentTime
                    ]
                )
            );

            $query = DB::table('0_employees as emp')
                ->leftJoin('0_emp_jobs as job', 'emp.id', 'job.employee_id')
                ->where('job.has_pension', 1);

            if ($key != 'default') {
                $query->where('emp.nationality', $key);
            }

            $query->update([
                'job.pension_scheme' => $schemeId
            ]);
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('0_pension_configs')->truncate();
        DB::table('0_emp_jobs')->update(['pension_scheme' => null]);
    }
}
