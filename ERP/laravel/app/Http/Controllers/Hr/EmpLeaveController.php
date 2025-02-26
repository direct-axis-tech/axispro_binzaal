<?php

namespace App\Http\Controllers\Hr;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Jobs\Hr\GenerateLeaveBalanceJob;
use App\Models\Hr\Company;
use App\Models\Hr\Department;
use App\Models\Hr\Employee;
use App\Models\Hr\EmployeeLeave;
use App\Models\Hr\LeaveType;
use App\Permissions;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Jimmyjs\ReportGenerator\ReportMedia\ExcelReport;
use Jimmyjs\ReportGenerator\ReportMedia\PdfReport;
use Illuminate\Support\Str;

class EmpLeaveController extends Controller
{
    /**
     * Display a listing of the resource for leave report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        abort_unless($request->user()->hasAnyPermission(
            Permissions::HRM_EMPLOYEE_LEAVE_REPORT,
            Permissions::HRM_EMPLOYEE_LEAVE_REPORT_OWN,
            Permissions::HRM_EMPLOYEE_LEAVE_REPORT_DEP
        ), 403);
        
        $canOnlyAccessOwn = $request->user()->doesntHavePermission(Permissions::HRM_EMPLOYEE_LEAVE_REPORT);
        $employees        = $canOnlyAccessOwn ? collect([$request->user()->employee]) : Employee::active()->get();
        $leaveTypes       = LeaveType::active()->get();
        $leaveCategory    = array(EmployeeLeave::CATEGORY_NORMAL => 'Normal', EmployeeLeave::CATEGORY_ADJUSTMENT => 'Adjustment');
        $transactionType  = array(EmployeeLeave::CREDIT => 'Credit', EmployeeLeave::DEBIT => 'Debit');
        $leaveStatus      = array(STS_APPROVED => 'Approved', STS_PENDING => 'Pending');
        $resultList       = $this->getLeaveReportBuilder($request->all())->paginate(25);
        $userInputs       = $request->input();

        return view('hr.leaveReport', compact('employees', 'leaveTypes', 'leaveCategory', 'transactionType', 'leaveStatus', 'resultList', 'userInputs'));
    }

    /**
     * Get leave report data based on request parameters.
     *
     * @param  array  $filters
     * @param  boolean  $authorizedOnly
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getLeaveReportBuilder($filters = [], $authorizedOnly = true)
    {
        $user = authUser();
        $access = [
            'ALL' => $user->hasPermission(Permissions::HRM_EMPLOYEE_LEAVE_REPORT),
            'OWN' => $user->hasPermission(Permissions::HRM_EMPLOYEE_LEAVE_REPORT_OWN),
            'DEP' => $user->hasPermission(Permissions::HRM_EMPLOYEE_LEAVE_REPORT_DEP)
        ];

        $mysqlDateFormat  = getDateFormatForMySQL();

        $builder =  DB::query()
            ->select(
                '0_employees.id',
                '0_employees.emp_ref',
                '0_employees.name',
                '0_emp_leaves.id AS leave_id',
                '0_emp_leaves.leave_type_id',
                '0_leave_types.desc AS leave_type',
                '0_emp_leaves.category_id',
                '0_emp_leaves.days',
                '0_emp_leaves.memo',
                DB::raw("date_format(0_emp_leaves.from, '{$mysqlDateFormat}') as formatted_from_date"),
                DB::raw("date_format(0_emp_leaves.till, '{$mysqlDateFormat}') as formatted_till_date"),
                DB::raw("date_format(0_emp_leaves.requested_on, '{$mysqlDateFormat}') as formatted_requested_on"),
                DB::raw("CASE 
                    WHEN 0_emp_leaves.category_id = " . EmployeeLeave::CATEGORY_NORMAL . " THEN 'Normal' 
                    WHEN 0_emp_leaves.category_id = " . EmployeeLeave::CATEGORY_ADJUSTMENT . " THEN 'Adjustment' 
                    END as category"
                ),
                DB::raw("CASE 
                    WHEN 0_emp_leaves.transaction_type = " . EmployeeLeave::CREDIT . " THEN 'Credit' 
                    WHEN 0_emp_leaves.transaction_type = " . EmployeeLeave::DEBIT . " THEN 'Debit' 
                    END as transaction_type"
                ),
                DB::raw("CASE 
                    WHEN 0_emp_leaves.status = '" . STS_APPROVED . "' THEN 'Approved' 
                    WHEN 0_emp_leaves.status = '" . STS_PENDING . "' THEN 'Pending' 
                    END as leave_status"
                ),
                DB::raw("CASE 
                    WHEN 0_emp_leaves.status = '" . STS_APPROVED . "' THEN approver.real_name
                    ELSE '-'
                    END as approved_by"
                )
            )
            ->from('0_employees')
            ->join('0_emp_leaves', function($join){
                $join->on('0_emp_leaves.employee_id', '=', '0_employees.id')
                ->whereIn('0_emp_leaves.category_id', [EmployeeLeave::CATEGORY_NORMAL, EmployeeLeave::CATEGORY_ADJUSTMENT]);
            })
            ->leftJoin('0_emp_jobs AS empJob', function ($join) {
                $join->on('0_employees.id', 'empJob.employee_id')
                    ->where('empJob.is_current', '1');
            })
            ->leftJoin('0_departments AS dep', 'empJob.department_id', 'dep.id')
            ->leftJoin('0_companies AS wCom', 'empJob.working_company_id', 'wCom.id')
            ->leftJoin('0_leave_types', '0_leave_types.id', '=', '0_emp_leaves.leave_type_id')
            ->leftJoin('0_users as approver', 'approver.id', '=', '0_emp_leaves.reviewed_by')
            ->orderBy('requested_on', 'desc');

        if (!empty($filters['employee_id'])) {
            if (!is_array($filters['employee_id'])) {
                $filters['employee_id'] = explode(",", $filters['employee_id']);
            }
            $builder = $builder->whereIn('0_employees.id', $filters['employee_id']);
        }
        
        if (!empty($filters['leave_type'])) {
            if (!is_array($filters['leave_type'])) {
                $filters['leave_type'] = explode(",", $filters['leave_type']);
            }
            $builder = $builder->whereIn('0_emp_leaves.leave_type_id', $filters['leave_type']);
        }

        if (!empty($filters['leave_category'])) {
            if (!is_array($filters['leave_category'])) {
                $filters['leave_category'] = explode(",", $filters['leave_category']);
            }
            $builder = $builder->whereIn('0_emp_leaves.category_id', $filters['leave_category']);
        }

        if (!empty($filters['trans_type'])) {
            if (!is_array($filters['trans_type'])) {
                $filters['trans_type'] = explode(",", $filters['trans_type']);
            }
            $builder = $builder->whereIn('0_emp_leaves.transaction_type', $filters['trans_type']);
        }

        if (!empty($filters['leave_status'])) {
            if (!is_array($filters['leave_status'])) {
                $filters['leave_status'] = explode(",", $filters['leave_status']);
            }
            $builder = $builder->whereIn('0_emp_leaves.status', $filters['leave_status']);
        }

        if (!empty($filters['leave_from_start'])) {
            $builder = $builder->where('0_emp_leaves.from', '>=', date2sql($filters['leave_from_start']));
        } 
        
        if (!empty($filters['leave_from_end'])) {
            $builder = $builder->where('0_emp_leaves.from', '<=', date2sql($filters['leave_from_end']));
        }

        if (!empty($filters['leave_till_start'])) {
            $builder = $builder->where('0_emp_leaves.till', '<=', date2sql($filters['leave_till_start']));
        }

        if (!empty($filters['leave_till_end'])) {
            $builder = $builder->where('0_emp_leaves.till', '<=', date2sql($filters['leave_till_end']));
        }

        if (!empty($filters['leave_requested_start'])) {
            $builder = $builder->where('0_emp_leaves.requested_on', '>=', date2sql($filters['leave_requested_start']));
        }

        if (!empty($filters['leave_requested_end'])) {
            $builder = $builder->where('0_emp_leaves.requested_on', '<=', date2sql($filters['leave_requested_end']));
        }

        if ($authorizedOnly) {
            if (!$access['ALL']) {
                $employeeId = data_get($user->employee, 'id', -1);
                $builder->where(function ($query) use ($employeeId) {
                    $query->whereRaw("json_contains(empJob.supervisor_id, json_quote(concat('', ?)))", $employeeId)
                        ->orWhereRaw("json_contains(dep.hod_id, json_quote(concat('', ?)))", $employeeId)
                        ->orWhereRaw("json_contains(wCom.in_charge_id, json_quote(concat('', ?)))", $employeeId);
                });

                if (!$access['DEP']) {
                    $builder->where('0_employees.id', $employeeId);
                }
            }
        }
        
        return $builder;
    }

    /**
     * Export the listing of the resource for leave report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        abort_unless($request->user()->hasAnyPermission(
            Permissions::HRM_EMPLOYEE_LEAVE_REPORT,
            Permissions::HRM_EMPLOYEE_LEAVE_REPORT_OWN,
            Permissions::HRM_EMPLOYEE_LEAVE_REPORT_DEP
        ), 403);
        
        $actionType  = $request->input('actionType');
        $exportTitle = 'Employees Leave Report';
        $meta   = [];
        $queryBuilder = $this->getLeaveReportBuilder($request->all());

        $textColumns = [
            'Employee' => 'name',
            'Leave Type' => 'leave_type',
            'Leave Category' => 'category',
            'Type' => 'transaction_type',
            'No Of Days' => 'days',
            'Start Date' => 'formatted_from_date',
            'End Date' => 'formatted_till_date',
            'Requested On' => 'formatted_requested_on',
            'Remarks' => 'memo',
            'Leave Status' => 'leave_status',
            'Approved By' => 'approved_by'
        ];

        $generator = app($actionType == 'xlsx'
            ? ExcelReport::class
            : PdfReport::class
        )->of($exportTitle, $meta, $queryBuilder, $textColumns)
        ->setPaper('a4');

        if ($actionType == 'pdf') {
            foreach ($textColumns as $column => $key) {
                $generator->editColumn($column, [
                    'displayAs' => function ($result) use ($key) {
                        return Str::limit($result->{$key}, '20');
                    }
                ]);
            }
        } else {
            $generator->simple();
        }

        $file = 'download/'.Str::orderedUuid().".$actionType";
        $generator->store($file);

        return [
            "redirect_to" => url(route("file.download", ['type' => 'employee_leave_report', 'file' => basename($file)]))
        ];
    }
    
    /**
     * recomputeLeaveBalanceForEmployee
     *
     * @param  mixed $leaveId
     * @return void
     */
    public static function recomputeLeaveBalanceForEmployee($leaveId)
    {
        $leaveDetails = DB::table('0_emp_leave_details as leaveDetail')
            ->leftJoin('0_emp_leaves as leave', 'leave.id', 'leaveDetail.leave_id')
            ->select(
                'leaveDetail.employee_id',
                'leaveDetail.leave_type_id',
                DB::raw('MIN(leaveDetail.date) as leave_starting_date')
            )
            ->where('leaveDetail.leave_id', $leaveId)
            ->where('leave.status', STS_APPROVED)
            ->first();

        if ($leaveDetails) {
            self::processEmployeeLeaveBalance($leaveDetails->employee_id, $leaveDetails->leave_type_id, $leaveDetails->leave_starting_date);
        }
    }
    
    /**
     * processEmployeeLeaveBalance
     *
     * @param  mixed $employeeId
     * @param  mixed $leaveTypeId
     * @param  mixed $startDate
     * @return void
     */
    public static function processEmployeeLeaveBalance($employeeId = null, $leaveTypeId = null, $startDate = null)
    {
        $leaveIds = DB::table('0_emp_leave_details')
            ->leftJoin('0_emp_leaves', '0_emp_leaves.id', '=', '0_emp_leave_details.leave_id')
            ->whereIn('0_emp_leaves.category_id', [EmployeeLeave::CATEGORY_ACCRUED, EmployeeLeave::CATEGORY_LAPSED]);
        
        if ($employeeId) {
            $leaveIds->where('0_emp_leave_details.employee_id', $employeeId);
        }

        if ($leaveTypeId) {
            $leaveIds->where('0_emp_leave_details.leave_type_id', $leaveTypeId);
        }
        
        if ($startDate) {
            $leaveIds->where('0_emp_leave_details.date', '>', $startDate);
        }
        
        $leaveIds = $leaveIds->pluck('0_emp_leaves.id');
        
        DB::table('0_emp_leave_details')
            ->whereIn('leave_id', $leaveIds)
            ->delete();
       
         DB::table('0_emp_leaves')
            ->whereIn('id', $leaveIds)
            ->delete();

        GenerateLeaveBalanceJob::dispatchNow($employeeId, $leaveTypeId);
    }

    /**
     * Employee Leave Detail Report
     *
     * @param  mixed $request
     * @return void
     */
    public function detailReport(Request $request)
    {
        $canAccess = [
            'ALL' => authUser()->hasPermission(Permissions::HRM_EMP_LEAVE_DETAIL_REPORT),
            'OWN' => authUser()->hasPermission(Permissions::HRM_EMP_LEAVE_DETAIL_REPORT_OWN),
            'DEP' => authUser()->hasPermission(Permissions::HRM_EMP_LEAVE_DETAIL_REPORT_DEP)
        ];

        abort_unless(in_array(true, $canAccess), 403);
        
        $wCompanies = Company::usedWorkingCompanies()->orderBy('name')->get();
        $allLeaveTypes = LeaveType::active()->get();
        $departments = Department::all();
        $leaveAttr = array('Opening', 'Available', 'Taken', 'Adjusted', 'Lapsed', 'Balance');
        
        $employees = app(EmployeeController::class)
            ->builder(['auth' => true, 'status' => Employee::ES_ALL], $canAccess, data_get(authUser()->employee, 'id', -1))
            ->get();

        $leaveTypes = $allLeaveTypes
            ->filter(function ($leaveType) use ($request) {
                $leaveTypeIds = $request->input('leave_type', []);
                return empty($leaveTypeIds) || in_array($leaveType->id, $leaveTypeIds);
            })
            ->map(function ($leaveType) {
                $leaveType->color = self::generateRandomColor($leaveType->id);
                return $leaveType;
            });
        if (empty($request->input('leave_from_start'))) {
            $request->merge([
                'leave_from_start' => date(dateformat(), strtotime('first day of January this year'))
            ]);
        }
        if (empty($request->input('leave_from_end'))) {
            $request->merge([
                'leave_from_end' => date(dateformat())
            ]);
        }

        $resultList = $this->getLeaveDetailReportBuilder($request->all());
        $userInputs = $request->input();
        
        return view('hr.leaveDetailReport', compact('wCompanies', 'departments', 'employees', 'allLeaveTypes', 'leaveTypes', 'leaveAttr', 'resultList', 'userInputs'));
    }

    /**
     * Get leave detail report data based on request parameters.
     *
     * @param  array  $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getLeaveDetailReportBuilder($filters = [])
    {
        $user = authUser();
        $access = [
            'ALL' => $user->hasPermission(Permissions::HRM_EMP_LEAVE_DETAIL_REPORT),
            'OWN' => $user->hasPermission(Permissions::HRM_EMP_LEAVE_DETAIL_REPORT_OWN),
            'DEP' => $user->hasPermission(Permissions::HRM_EMP_LEAVE_DETAIL_REPORT_DEP)
        ];

        $fromDate = date2sql($filters['leave_from_start']);

        $builder = app(\App\Http\Controllers\Hr\EmployeeController::class)
            ->builder(
                [
                    'status' => Employee::ES_ALL,
                    'auth' => true,
                    'working_company_id' => data_get($filters, 'w_company_id'),
                    'department_id' => data_get($filters, 'department_id'),
                    'employee_id' => data_get($filters, 'employee_id'),
                ],
                $access,
                data_get($user->employee, 'id', -1)
            )
            ->crossJoin('0_leave_types as leaveType', function (JoinClause $join) {
                $join->where('leaveType.inactive', '0');
            })
            ->leftJoin('0_emp_leaves as leave', function (JoinClause $join) {
                $join->on('leave.employee_id', '=', 'emp.id')
                    ->whereColumn('leave.leave_type_id', 'leaveType.id')
                    ->where('leave.status', STS_APPROVED);
            })
            ->leftJoin('0_emp_leave_details as detail', function (JoinClause $join) use ($filters) {
                $join->on('detail.leave_id', '=', 'leave.id')
                    ->where('detail.is_cancelled', 0);

                if (!empty($filters['leave_from_end'])) {
                    $join->where('detail.date', '<=', date2sql($filters['leave_from_end']));
                }
            })
            ->groupBy('emp.id', 'leaveType.id')
            ->orderBy('leaveType.id');
        
        if (!empty($filters['leave_type'])) {
            if (!is_array($filters['leave_type'])) {
                $filters['leave_type'] = explode(",", $filters['leave_type']);
            }

            $builder->whereIn('leaveType.id', $filters['leave_type']);
        }

        $result = $builder
            ->select(
                'emp.id',
                DB::raw("CONCAT(emp.emp_ref, ' - ', emp.name) AS employee_name"),
                'leaveType.id AS leave_type_id',
                'leaveType.desc AS leave_type'
            )
            ->selectRaw(
                'SUM(IFNULL(IF(`detail`.`date` < ?, `detail`.`type` * `detail`.`days`, 0), 0)) as Opening',
                [$fromDate]
            )
            ->selectRaw(
                'SUM(IFNULL(IF(`detail`.`date` >= ? AND `detail`.`category_id` = ?, `detail`.`days`, 0), 0)) as Taken',
                [$fromDate, EmployeeLeave::CATEGORY_NORMAL]
            )
            ->selectRaw(
                'SUM(IFNULL(IF(`detail`.`date` >= ? AND `detail`.`category_id` = ?, -1 * `detail`.`type` * `detail`.`days`, 0), 0)) as Adjusted',
                [$fromDate, EmployeeLeave::CATEGORY_ADJUSTMENT]
            )
            ->selectRaw(
                'SUM(IFNULL(IF(`detail`.`date` >= ? AND `detail`.`category_id` = ?, `detail`.`days`, 0), 0)) as Available',
                [$fromDate, EmployeeLeave::CATEGORY_ACCRUED]
            )
            ->selectRaw(
                'SUM(IFNULL(IF(`detail`.`date` >= ? AND `detail`.`category_id` = ?, `detail`.`days`, 0), 0)) as Lapsed',
                [$fromDate, EmployeeLeave::CATEGORY_LAPSED]
            )
            ->selectRaw('SUM(IFNULL(-1 * `detail`.`type` * `detail`.`days`, 0)) as Balance')
            ->get();

        $result = $result->groupBy('id')
            ->map(function ($employeeGroup) {
                $employeeData = [
                    'id' => $employeeGroup->first()->id,
                    'employee_name' => $employeeGroup->first()->employee_name,
                ];

                foreach ($employeeGroup as $leaveRecord) {
                    $employeeData[$leaveRecord->leave_type_id] = Arr::except((array)$leaveRecord, ['id', 'employee_name']);
                }

                return $employeeData;
            })
            ->values()
            ->toArray();

        return $result;
    }

    public function generateRandomColor() {
        // Higher range for lighter colors: 220 - 255
        $red   = rand(220, 255);
        $green = rand(220, 255);
        $blue  = rand(220, 255);

        // Convert RGB to hexadecimal color code
        return sprintf("#%02X%02X%02X", $red, $green, $blue);
    }
}
