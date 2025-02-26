<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\Employee;
use App\Models\Hr\EmployeeLeave;
use App\Permissions;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $user = authUser();
        $canAccess = [
            'OWN' => $user->hasPermission(Permissions::HRM_VIEWEMPLOYEES),
            'DEP' => $user->hasPermission(Permissions::HRM_VIEWEMPLOYEES_DEP),
            'ALL' => $user->hasPermission(Permissions::HRM_VIEWEMPLOYEES_ALL),
        ];

        abort_unless(in_array(true, $canAccess, true), 403);
        
        if ($request->ajax()) {
            return response()->json([
                'data' => $this->builder(['auth' => true, 'status' => Employee::ES_ALL], $canAccess, data_get($user->employee, 'id', -1))->get()->toArray()
            ], 200);
        }

        return view('hr.employees.index', [
            'maritalStatuses' => marital_statuses(),
            'genders' => genders(),
            'modeOfPays' => [
                'C' => 'Cash',
                'B' => 'Bank'
            ],
            'employmentStatuses'=> employment_statuses()
        ]);
    }

    /**
     * Get the builder for querying employee data
     * 
     * @param array $filters
     * @return \Illuminate\Database\Query\Builder
     */
    public function builder(
        $filters = [],
        $canAccess = [],
        $currentEmployeeId = -1,
        $inverseSelf = false
    )
    {
        $query = DB::table('0_employees as emp')
            ->leftJoin('0_emp_jobs as job', function ($join) {
                $join->on('job.employee_id', 'emp.id')
                    ->where('job.is_current', 1);
            })
            ->leftJoin('0_emp_salaries as sal', function ($join) {
                $join->on('sal.employee_id', 'emp.id')
                    ->where('sal.is_current', 1);
            })
            ->leftJoin('0_emp_salary_details as basicPay', function ($join) {
                $join->on('basicPay.salary_id', 'sal.id')
                    ->where('basicPay.pay_element_id', pref('hr.basic_pay_el'));
            })
            ->leftJoin('0_banks as bank', 'emp.bank_id', 'bank.id')
            ->leftJoin('0_pension_configs as pension', 'pension.id', 'job.pension_scheme')
            ->leftJoin('0_departments as dep', 'dep.id', 'job.department_id')
            ->leftJoin('0_designations as desig', 'desig.id', 'job.designation_id')
            ->leftJoin('0_users as user', 'user.employee_id', 'emp.id')
            ->leftJoin('0_countries as cntry', 'cntry.code', 'emp.nationality')
            ->leftJoin('0_companies as wCom', 'wCom.id', 'job.working_company_id')
            ->leftJoin('0_companies as vCom', 'vCom.id', 'job.visa_company_id')
            ->leftJoin('0_entity_groups as flowGroup', 'flowGroup.id', 'user.flow_group_id')
            ->select([
                'emp.*',
                DB::raw("CONCAT(emp.emp_ref, ' - ', emp.name) as formatted_name"),
                DB::raw("emp.status = '1' as is_active"),
                'cntry.name as country',
                'pension.employee_share as gpssa_employee_share',
                'pension.employer_share as gpssa_employer_share',
                'job.id as job_id',
                'job.working_company_id',
                'job.visa_company_id',
                'job.department_id',
                'job.designation_id',
                'job.default_shift_id',
                'job.week_offs',
                'job.work_hours',
                'job.has_commission',
                'job.has_pension',
                'job.has_overtime',
                'job.commence_from',
                'job.end_date as last_working_date',
                'job.require_attendance',
                'job.require_presence_only',
                'job.supervisor_id',
                'user.flow_group_id',
                'sal.id as salary_id',
                'sal.from as salary_from',
                'sal.gross_salary as monthly_salary',
                'basicPay.amount as basic_salary',
                'bank.name as bank_name',
                'bank.routing_no',
                'dep.name as department_name',
                'dep.hod_id',
                'desig.name as designation_name',
                'user.id as user_id',
                'user.user_id as username',
                'wCom.name as working_company_name',
                'vCom.name as visa_company_name',
                'flowGroup.name as flow_group_name',
                'wCom.in_charge_id as working_company_in_charge_id',
                'job.attendance_type',
                'job.pension_scheme'
            ])
            ->orderBy(DB::raw('CAST(emp.emp_ref as UNSIGNED)'));

        // Apply filters with separate if conditions
        if (!isset($filters['status'])) {
            $query->where('emp.status', Employee::ES_ACTIVE);
        } else if ($filters['status'] != Employee::ES_ALL) {
            $query->where('emp.status', $filters['status']);
        }

        if (!empty($filters['not_status'])) {
            $query->where('emp.status', '<>', $filters['not_status']);
        }

        if (!empty($filters['employee_id'])) {
            if (is_array($filters['employee_id'])) {
                $filters['employee_id'] = implode(",", $filters['employee_id']);
            }

            $query->whereIn('emp.id', explode(',', $filters['employee_id']));
        }
        
        if (!empty($filters['department_id'])) {
            if (is_array($filters['department_id'])) {
                $filters['department_id'] = implode(",", $filters['department_id']);
            }
            
            $query->whereIn('job.department_id', explode(',', $filters['department_id']));
        }
        
        if (!empty($filters['designation_id'])) {
            if (is_array($filters['designation_id'])) {
                $filters['designation_id'] = implode(",", $filters['designation_id']);
            }

            $query->whereIn('job.designation_id', explode(',', $filters['designation_id']));
        }
        
        if (!empty($filters['joined_on_or_before'])) {
            $query->where('emp.date_of_join', '<=', $filters['joined_on_or_before']);
        }
        
        if (isset($filters['has_user'])) {
            $filters['has_user']
                ? $query->whereNotNull('user.id')
                : $query->whereNull('user.id');
        }
        
        if (!empty($filters['user_id'])) {
            $query->where('user.id', $filters['user_id']);
        }
        
        if (!empty($filters['auth'])) {
            $filters['auth'] === true
                ? $query->where(function (Builder $query) use ($canAccess, $currentEmployeeId, $inverseSelf) {
                    if (!$canAccess['ALL']) {
                        if (!$canAccess['DEP']) {
                            if (!$inverseSelf) {
                                $query->where('emp.id', $currentEmployeeId);
                            }
                        } else {
                            $query->whereRaw(
                                "("
                                    . "emp.id = ?"
                                    . " OR JSON_CONTAINS(dep.hod_id, JSON_QUOTE(?))"
                                    . " OR JSON_CONTAINS(job.supervisor_id, JSON_QUOTE(?))"
                                    . " OR JSON_CONTAINS(wCom.in_charge_id, JSON_QUOTE(?))"
                                .")",
                                [
                                    $currentEmployeeId,
                                    $currentEmployeeId,
                                    $currentEmployeeId,
                                    $currentEmployeeId
                                ]
                            );
                        }
                    }
                
                    if ($inverseSelf && !$canAccess['OWN']) {
                        $query->where('emp.id', '!=', $currentEmployeeId);
                    }
                })
                : $query->whereRaw('(' . $filters['auth'] . ')');
        }
        
        if (!empty($filters['working_company_id'])) {
            if (is_array($filters['working_company_id'])) {
                $filters['working_company_id'] = implode(",", $filters['working_company_id']);
            }

            $query->whereIn('job.working_company_id', explode(',', $filters['working_company_id']));
        }

        return $query;
    }

    /**
     * Get the query builder for employees' work record for the specified period
     * 
     * TLDR;  
     * It is not recomended to filter employee using department, designation & salary
     * using this function. for that use the normal builder function
     * 
     * Note:  
     * We are explicitly storing the job commencement and ending date so the
     * department and designation can be correctly queried for historic data.
     * 
     * However, the salary is almost always dependant on the company policy so
     * we cannot get the exact salary for the employee at a given point in time without
     * joining payroll table. If in the future we need to have salary also, we may
     * do so and may need complex calculation
     * 
     * We are not saving the history for department head and supervisor - at the moment: it is of less
     * or no importance. If needed we may need to redesign how we handle the HOD and supervisors.
     * As of now, we are not storing any time-related data and the fields are freely modifyable.
     * 
     * **Important!** Temporal data could be different when querying the record.  
     * 
     * e.g. 
     * 
     * If an employee joined on 01 Jan 2019 as `Jr` and got promoted on 01 Jul 2019 to `Sr`,
     * and we queried data of period 26 Jun to 25 Jul, The designation for
     * the period 26 till 30 of Jun would be `Jr` and from Jul 01 onwards it would be `Sr`
     * 
     * So if we filter using designation `Sr` we would only get the records from 01 to 25 Jul,
     * But we would expect it to contain from 26 till 25.
     * 
     * The same also applies to the department & salary.
     * 
     * **Conclusion** Temporal fields are based on the actual value - at the period of query rather than
     * the current value.
     * 
     * @param string $from date in MySQL date format
     * @param string $till date in MySQL date format
     * @param array $filters
     * 
     * @return mysqli_result
     */
    public function workRecordsQuery($from, $till, $filters = [])
    {
        $present = DS_PRESENT;
        $notPresent = DS_ABSENT;
        $onLeave = DS_ON_LEAVE;
        $holiday = DS_HOLIDAY;
        $off = DS_OFF;
        $customShiftId = (
            "("
                . "CASE "
                    . "WHEN NOT ISNULL(empShift.shift_id) THEN empShift.shift_id "
                    . "WHEN NOT ISNULL(empShift.id) AND ISNULL(empShift.shift_id) THEN 'off' "
                    . "WHEN cal.is_holiday THEN 'off' "
                    . "WHEN JSON_CONTAINS(job.week_offs, JSON_QUOTE(cal.day_name)) = 1 THEN 'off' "
                    . "ELSE IFNULL(job.default_shift_id, ".pref('hr.default_shift_id', "'off'").")"
                . "END "
            . ")"
        );


        $query = DB::table('0_employees AS emp')
            ->select(
                DB::raw("concat(emp.id, '_', cal.date) as custom_id"),
                'emp.id as employee_id',
                'emp.emp_ref as employee_ref',
                'emp.machine_id as employee_machine_id',
                'emp.name as employee_name',
                DB::raw("cal.date >= emp.date_of_join as is_employee_joined"),
                'job.working_company_id',
                'job.visa_company_id',
                'job.department_id',
                'job.designation_id',
                DB::raw("JSON_CONTAINS(job.week_offs, JSON_QUOTE(cal.day_name)) = 1 as is_week_off"),
                'cal.date',
                'cal.day_name',
                'cal.is_holiday',
                'cal.holiday_name',
                'cal.day_of_week',
                'attd.status as attendance_status',
                'attd.based_on_shift_id as attendance_based_on_shift_id',
                'attd.punchin',
                'attd.punchout',
                DB::raw(
                    "("
                        ."(!isnull(`attd`.`punchin`) or !isnull(`attd`.`punchout`))"
                        ." and `attd`.`duration` = '00:00:00'"
                    .")"
                    ." or ("
                        ."(!isnull(`attd`.`punchin2`) or !isnull(`attd`.`punchout2`))"
                        ." and isnull(`attd`.`punchin`)"
                        ." and isnull(`attd`.`punchout`)"
                    .")"
                . " as is_missing_punch"),
                'attd.duration as work_duration',
                'attd.punchin2',
                'attd.punchout2',
                DB::raw(
                    "("
                        ."(!isnull(`attd`.`punchin2`) or !isnull(`attd`.`punchout2`))"
                        ." and `attd`.`duration2` = '00:00:00'"
                    .")"
                    ." or ("
                        ."(!isnull(`attd`.`punchin`) or !isnull(`attd`.`punchout`))"
                        ." and isnull(`attd`.`punchin2`)"
                        ." and isnull(`attd`.`punchout2`)"
                        ." and !isnull(`shift`.`from2`)"
                    .")"
                . " as is_missing_punch2"),
                'attd.duration2 as work_duration2',
                DB::raw(
                    "addtime("
                        ."ifnull(`attd`.`duration`, '00:00:00'), "
                        ."ifnull(`attd`.`duration2`, '00:00:00')"
                    .")"
                ." as total_work_duration"),
                'attd.punchin_stamp',
                'attd.punchout_stamp',
                'attd.punchin2_stamp',
                'attd.punchout2_stamp',
                'attd.remarks as attendance_remarks',
                'attd.reviewed_at as attendance_reviewed_at',
                DB::raw("not isnull(`empShift`.`id`) as is_shift_defined"),
                DB::raw(
                    "("
                        ."not isnull(`empShift`.`id`) "
                        ."and isnull(`empShift`.`shift_id`)"
                    .")"
                ." as is_shift_off"),
                DB::raw(
                    "IF("
                        . "isnull(`empShift`.`id`), "
                        . "json_contains(`job`.`week_offs`, json_quote(`cal`.`day_name`)) = 1, "
                        . "isnull(`empShift`.`shift_id`)"
                    .")"
                ." as is_off"),
                'empShift.id as emp_shift_id',
                'empShift.shift_id',
                'empShift.created_by as emp_shift_created_by',
                'empShift.updated_by as emp_shift_updated_by',
                'shift.description as shift_desc',
                'shift.from as shift_from',
                'shift.till as shift_till',
                'shift.duration as shift_duration',
                'shift.from2 as shift_from2',
                'shift.till2 as shift_till2',
                'shift.duration2 as shift_duration2',
                DB::raw("not isnull(`shift`.`from2`) as is_on_split_shift"),
                'shift.total_duration as total_shift_duration',
                DB::raw(
                    "case "
                        . "when `empShift`.`id` is not null and `empShift`.`shift_id` is null then 'Off' "
                        . "when `shift`.`id` is not null then `shift`.`code` "
                        . "else 'Not Assigned' "
                    . "end"
                ." as shift_state"),
                DB::raw("{$customShiftId} as custom_shift_id"),
                DB::raw("not isnull(`leaveDetail`.`id`) as is_on_leave"),
                DB::raw("(`leaveDetail`.`id` is not null and `leaveDetail`.`days` < 1) as is_on_partial_leave"),
                'leaveDetail.id as leave_id',
                'leaveDetail.leave_id as leave_request_id',
                'leaveDetail.leave_type_id',
                'leaveType.desc as leave_type',
                'leaveDetail.days as leave_total',
                DB::raw(
                    "case "
                        . "when not isnull(`absence`.`id`) then '{$notPresent}' "
                        . "when `attd`.`status` = 'P' then '{$present}' "
                        . "when `leaveDetail`.`id` is not null then '{$onLeave}' "
                        . "when `empShift`.`id` is not null then if("
                            . "`empShift`.`shift_id` is not null, "
                            . "'{$notPresent}', "
                            . "if(`cal`.`is_holiday`, '{$holiday}', '{$off}')"
                        . ") "
                        . "when `cal`.`is_holiday` then '{$holiday}' "
                        . "when json_contains(`job`.`week_offs`, json_quote(`cal`.`day_name`)) then '{$off}' "
                        . "else '{$notPresent}' "
                    . "end"
                ." as duty_status"), 
                DB::raw(
                    "case "
                        ."when nullif(`attd`.`remarks`, '') is not null then `attd`.`remarks` "
                        ."when `leaveDetail`.`id` is not null then `leaveType`.`desc` "
                        ."when `cal`.`is_holiday` then `cal`.`holiday_name` "
                    ."end"
                ." as custom_remarks"), 
                DB::raw("not isnull(`absence`.`id`) as is_marked_absent"),
                DB::raw("not isnull(`overtime`.`id`) as has_overtime"),
                DB::raw("not isnull(`latemin`.`id`) as has_lateminutes"),
                DB::raw("not isnull(`shortmin`.`id`) as has_shortminutes"),
                'overtime.minutes as minutes_overtime',
                'latemin.minutes as late_by_minutes',
                'shortmin.minutes as short_by_minutes',
                'overtime.amount as overtime_amount',
                'latemin.amount as lateminutes_amount',
                'shortmin.amount as shortminutes_amount'
            )
            ->crossJoin('0_calendar as cal', function ($join) use ($from, $till) {
                $join->where('cal.date', '>=', $from)
                    ->where('cal.date', '<=', $till);
            })
            ->leftJoin('0_emp_jobs as job', function ($join) {
                $join->on('job.employee_id', '=', 'emp.id')
                    ->whereRaw('(`cal`.`date` >= `job`.`commence_from` and (isnull(`job`.`end_date`) or `cal`.`date` <= `job`.`end_date`))');
            })
            ->leftJoin('0_attendance as attd', function ($join) {
                $join->on('attd.machine_id', '=', 'emp.machine_id')
                    ->on('attd.date', '=', 'cal.date');
            })
            ->leftJoin('0_emp_shifts as empShift', function ($join) {
                $join->on('empShift.employee_id', '=', 'emp.id')
                    ->on('empShift.date', '=', 'cal.date');
            })
            ->leftJoin('0_shifts as shift', 'shift.id', '=', 'empShift.shift_id')
            ->leftJoin('0_attendance_metrics as overtime', function ($join) {
                $join->on('overtime.employee_id', '=', 'emp.id')
                    ->on('overtime.date', '=', 'cal.date')
                    ->where('overtime.type', '=', AT_OVERTIME)
                    ->where('overtime.status', '=', STS_VERIFIED);
            })
            ->leftJoin('0_attendance_metrics as latemin', function ($join) {
                $join->on('latemin.employee_id', '=', 'emp.id')
                    ->on('latemin.date', '=', 'cal.date')
                    ->where('latemin.type', '=', AT_LATEHOURS)
                    ->where('latemin.status', '=', STS_VERIFIED);
            })
            ->leftJoin('0_attendance_metrics as shortmin', function ($join) {
                $join->on('shortmin.employee_id', '=', 'emp.id')
                    ->on('shortmin.date', '=', 'cal.date')
                    ->where('shortmin.type', '=', AT_SHORTHOURS)
                    ->where('shortmin.status', '=', STS_VERIFIED);
            })
            ->leftJoin('0_attendance_metrics as absence', function ($join) {
                $join->on('absence.employee_id', '=', 'emp.id')
                    ->on('absence.date', '=', 'cal.date')
                    ->where('absence.type', '=', AT_ABSENT)
                    ->where('absence.status', '=', STS_VERIFIED);
            })
            ->leftJoin('0_emp_leaves as leave', function ($join) {
                $join->on('leave.employee_id', '=', 'emp.id')
                    ->whereRaw("(`cal`.`date` BETWEEN `leave`.`from` AND `leave`.`till`)")
                    ->where('leave.category_id', '=', EmployeeLeave::CATEGORY_NORMAL)
                    ->where('leave.status', '=', STS_APPROVED);
            })
            ->leftJoin('0_emp_leave_details as leaveDetail', function ($join) {
                $join->on('leaveDetail.leave_id', '=', 'leave.id')
                    ->on('leaveDetail.date', '=', 'cal.date')
                    ->where('leaveDetail.type', '=', EmployeeLeave::DEBIT)
                    ->where('leaveDetail.is_cancelled', '=', 0);
            })
            ->leftJoin('0_leave_types as leaveType', 'leaveType.id', '=', 'leaveDetail.leave_type_id')
            ->orderBy('emp.id')
            ->orderBy('cal.date');

        if (!empty($filters['employee_id'])) {
            if (is_array($filters['employee_id'])) {
                $filters['employee_id'] =  implode(",", $filters['employee_id']);
            }
            $query->whereIn('emp.id', explode(',', $filters['employee_id']));
        }

        return $query;
    }
}
