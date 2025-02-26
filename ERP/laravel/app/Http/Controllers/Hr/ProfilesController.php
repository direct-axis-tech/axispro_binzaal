<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Http\Middleware\Hr\EmployeeAssigned;
use App\Models\Document;
use App\Models\Hr\LeaveType;
use App\Models\Hr\Payroll;
use App\Models\Hr\Shift;
use App\Models\System\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\QueryDataTable;

class ProfilesController extends Controller {

    public function __construct()
    {
        $this->middleware(EmployeeAssigned::class);
    }

    public function personal()
    {
        return view('hr.employees.profile.personal');
    }

    public function jobAndPay()
    {
        return view('hr.employees.profile.jobAndPay');
    }

    public function leaves()
    {
        $leaveTypes = LeaveType::active()->get();
        
        return view('hr.employees.profile.leaves', compact('leaveTypes'));
    }

    public function leaveDetails($employee, $leaveType)
    {
        $employee = View::shared('employee');
        $query = app(EmpLeaveController::class)->getLeaveReportBuilder([
            'employee_id' => $employee->id,
            'leave_type' => $leaveType
        ]);

        $dataTable = (new QueryDataTable(DB::query()->fromSub($query, 't')));

        return $dataTable->toJson();
    }

    public function documents()
    {
        $documents = Document::ofEmployee(View::shared('employee')->id)->get();
        return view('hr.employees.profile.documents', compact('documents'));
    }

    public function shifts(Request $request)
    {
        $employee = View::shared('employee'); 
        $now = Carbon::now();
        $format = dateformat();
        $inputs = array_merge(
            // Default values
            [
                'shift_from' => $now->startOfWeek()->format($format),
                'shift_till' => $now->endOfWeek()->format($format)
            ],

            // User inputs
            $request->validate([
                'shift_from'   => "nullable|date_format:{$format}",
                'shift_till'     => "nullable|date_format:{$format}",
            ])
        );

        if ($request->wantsJson()) {
            $query = app(EmployeeController::class)->workRecordsQuery(
                date2sql($inputs['shift_from']),
                date2sql($inputs['shift_till']),
                [
                    'employee_id' => $employee->id
                ]
            );

            $shifts = Shift::all()->keyBy('id');

            $dataTable = (new QueryDataTable(DB::query()->fromSub($query, 't')))
                ->addColumn('custom_shift_code', function($record) use ($shifts) {
                    return $record->custom_shift_id == 'off'
                        ? 'OFF'
                        : data_get($shifts->get($record->custom_shift_id), 'code', '--');
                })
                ->addColumn('custom_shift_timing', function($record) use ($shifts) {
                    $shift = $shifts->get($record->custom_shift_id);
                    if (!$shift || $record->custom_shift_id == 'off') {
                        return '--';
                    }
                    
                    return Shift::formatTiming($shift->from, $shift->till, $shift->from2, $shift->till2);
                })
                ->addColumn('formatted_shift_assignor_name', function($record) {
                    return data_get(
                        User::find($record->emp_shift_updated_by ?: $record->emp_shift_created_by),
                        'formatted_name',
                        'System'
                    );
                });
            return $dataTable->toJson();
        }

        
        return view('hr.employees.profile.shifts', compact('inputs'));
    }

    public function attendances(Request $request)
    {
        $employee = View::shared('employee'); 
        $now = Carbon::now();
        $format = dateformat();
        $inputs = array_merge(
            // Default values
            [
                'attendance_from' => $now->startOfWeek()->format($format),
                'attendance_till' => $now->endOfWeek()->format($format)
            ],

            // User inputs
            $request->validate([
                'attendance_from'   => "nullable|date_format:{$format}",
                'attendance_till'     => "nullable|date_format:{$format}",
            ])
        );

        if ($request->wantsJson()) {
            $query = app(EmployeeController::class)->workRecordsQuery(
                date2sql($inputs['attendance_from']),
                date2sql($inputs['attendance_till']),
                [
                    'employee_id' => $employee->id
                ]
            );

            $shifts = Shift::all()->keyBy('id');

            $dataTable = (new QueryDataTable(DB::query()->fromSub($query, 't')))
                ->addColumn('formatted_attendance_status', function($record) {
                    switch ($record->duty_status) {
                        case DS_PRESENT:
                            return 'Present';
                        case DS_ABSENT:
                            return 'Not Present';
                        case DS_OFF:
                            return 'Off';
                        case DS_HOLIDAY:
                            return $record->holiday_name;
                        case DS_ON_LEAVE:
                            return 'On ' . $record->leave_type;
                        default:
                            return 'Not Present';
                    }
                })
                ->addColumn('based_on_shift_timing', function($record) use ($shifts) {
                    $shift = $shifts->get($record->attendance_based_on_shift_id);
                    if (!$shift) {
                        return '--';
                    }
                    
                    return Shift::formatTiming(
                        $shift->from,
                        $shift->till,
                        $shift->from2,
                        $shift->till2
                    ) ?: '--';
                })
                ->addColumn('attendance_timing', function($record) {
                    return Shift::formatTiming(
                        $record->punchin,
                        $record->punchout,
                        $record->punchin2,
                        $record->punchout2
                    ) ?: '--';
                })
                ->addColumn('formatted_total_duration', function($record) {
                    if ($record->total_work_duration == '00:00:00') {
                        return '--';
                    }

                    [$hours, $minutes, $seconds] = explode(':', $record->total_work_duration);
                    return "{$hours}h {$minutes}m";
                });
            return $dataTable->toJson();
        }

        
        return view('hr.employees.profile.attendances', compact('inputs'));
    }

    public function punchings(Request $request)
    {
        $employee = View::shared('employee'); 
        $now = Carbon::now();
        $format = dateformat();
        $inputs = array_merge(
            // Default values
            [
                'punch_from' => $now->startOfWeek()->format($format),
                'punch_till' => $now->endOfWeek()->format($format)
            ],

            // User inputs
            $request->validate([
                'punch_from'   => "nullable|date_format:{$format}",
                'punch_till'     => "nullable|date_format:{$format}",
            ])
        );

        if ($request->wantsJson()) {
            $query = DB::table('0_empl_punchinouts')
                ->where('empid', $employee->machine_id)
                ->whereBetween('authdate', [
                    date2sql($inputs['punch_from']),
                    date2sql($inputs['punch_till'])
                ]);

            $dataTable = (new QueryDataTable(DB::query()->fromSub($query, 't')));
            return $dataTable->toJson();
        }
        
        return view('hr.employees.profile.punchings', compact('inputs'));
    }

    
    public function payslip(Request $request)
    {
        $employee = View::shared('employee'); 
        $payrolls = Payroll::query()
            ->where('is_processed', 1)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->keyBy('id');
        $inputs = array_merge(
            // Default values
            [
                'payroll_id' => data_get(Arr::first($payrolls), 'id'),
            ],

            // User inputs
            $request->validate([
                'payroll_id'   => "nullable|integer|exists:0_payrolls,id",
            ])
        );
        $selectedPayroll = $inputs['payroll_id'];

        $renderedHtml = $selectedPayroll && ($payroll = $payrolls->get($selectedPayroll))
            ? app(PayslipController::class)->render($payroll->id, $employee->id)
            : null;
        
        return view('hr.employees.profile.payslip', compact('payrolls', 'selectedPayroll', 'renderedHtml'));
    }
}