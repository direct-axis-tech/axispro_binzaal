<?php

namespace App\Http\Controllers\Hr\Reports;

use App\Http\Controllers\Controller;
use App\Permissions;
use App\Models\Hr\Shift;
use App\Models\Hr\Company;
use Carbon\Carbon as Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\QueryDataTable;
use Illuminate\Support\Str;
use Jimmyjs\ReportGenerator\ReportMedia\ExcelReport;
use Jimmyjs\ReportGenerator\ReportMedia\PdfReport;

class Shifts extends Controller
{
    /**
     * Display the shift report form.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $authUser = authUser();
        abort_unless($authUser->hasAnyPermission(
            Permissions::HRM_EMPLOYEE_SHIFT_VIEW_OWN,
            Permissions::HRM_EMPLOYEE_SHIFT_VIEW_DEP,
            Permissions::HRM_EMPLOYEE_SHIFT_VIEW_ALL
        ), 403);

        $employees = DB::table('0_employees as emp') 
            ->leftJoin('0_emp_jobs AS empJob', function (JoinClause $join) {
                $join->on('emp.id', 'empJob.employee_id')
                    ->where('empJob.is_current', '1');
            })
            ->select(
                "emp.id",
                "empJob.working_company_id"
            )
            ->selectRaw("CONCAT_WS(' - ', emp.emp_ref, emp.name) as name")
            ->get()
            ->keyBy('id'); 
        $shifts = Shift::orderBy('id', 'DESC')->get();
        $companies = Company::all()->sortBy('name');
        
        $defaultFilters = [
            'working_company_id' => $authUser->hasPermission(Permissions::HRM_EMPLOYEE_SHIFT_VIEW_ALL)
                ? null
                : data_get($employees[$authUser->employee_id], 'working_company_id'),
            'from' => Carbon::now()->subWeek()->format(dateformat()),
            'till' => date(dateformat())
        ];
       
        return view('hr.shiftReport',
            compact('defaultFilters', 'employees', 'shifts', 'companies')
        );
    }

    /**
     * Returns the query builder instance
     *
     * @param array $filters
     * @param boolean $authorizedOnly
     * @return Builder
     */
    public function getBuilder($filters = [], $authorizedOnly = true)
    {
        $builder = DB::table('0_employees AS emp')
            ->leftJoin('0_emp_jobs AS empJob', function (JoinClause $join) {
                $join->on('emp.id', 'empJob.employee_id')
                    ->where('empJob.is_current', '1');
            })
            ->leftJoin('0_departments AS dep', 'empJob.department_id', 'dep.id')
            ->leftJoin('0_companies AS wCom', 'empJob.working_company_id', 'wCom.id')
            ->crossJoin('0_calendar as cal', function (JoinClause $join) use ($filters) {
                $join->where('cal.date', '>=', Carbon::parse(date2sql($filters['from'])))
                    ->where('cal.date', '<=', Carbon::parse(date2sql($filters['till'])));
            })
            ->leftJoin('0_emp_shifts AS empShift', function (JoinClause $join) {
                $join->on('empShift.employee_id', 'emp.id')
                    ->whereColumn('empShift.date', 'cal.date');
            })
            ->leftJoin('0_shifts as shift', 'shift.id', 'empShift.shift_id')
            ->select(
                "emp.emp_ref",
                "emp.id as employee_id",
                "empJob.department_id",
                "empJob.designation_id",
                "empJob.working_company_id",
                "emp.name as employee_name",
                "dep.name as department_name",
                "wCom.name as working_company_name",
            )
            ->groupBy('emp.id');
        
        foreach (CarbonPeriod::create(date2sql($filters['from']), date2sql($filters['till'])) as $date) {   
            $builder->selectRaw("GROUP_CONCAT(if(cal.date = ".quote($date->format(DB_DATE_FORMAT)).", if(empShift.id IS NOT NULL AND empShift.shift_id IS NULL, 'Off', shift.code), NULL)) as ".quote('shift_code_'.$date->format(dateformat())));
        }

        if (!empty($filters['working_company_id'])) {
            $builder->where('empJob.working_company_id', $filters['working_company_id']);
        }

        if (!empty($filters['employee_ids'])) {
            $builder->whereIn('emp.id', $filters['employee_ids']);
        }
        
        if (!empty($filters['department_ids'])) {
            $builder->whereIn('empJob.department_id', $filters['department_ids']);
        }
        
        if (!empty($filters['shift_ids'])) {
            $builder->where(function (Builder $query) use ($filters) {
                $query->whereIn('empShift.shift_id', $filters['shift_ids']);
            
                if (in_array('off' , $filters['shift_ids'])){
                    $query->orWhereRaw('(empShift.id IS NOT NULL AND empShift.shift_id IS NULL)');
                }
            });
        }

        if (
            $authorizedOnly
            && ($user = authUser())->doesntHavePermission(Permissions::HRM_EMPLOYEE_SHIFT_VIEW_ALL)
        ) {
            $employeeId = data_get($user->employee, 'id', -1);
            $builder->where(function (Builder $query) use ($employeeId) {
                $query->whereRaw("json_contains(empJob.supervisor_id, json_quote(concat('', ?)))", $employeeId)
                    ->orWhereRaw("json_contains(dep.hod_id, json_quote(concat('', ?)))", $employeeId)
                    ->orWhereRaw("json_contains(wCom.in_charge_id, json_quote(concat('', ?)))", $employeeId);
            });

            if ($user->doesntHavePermission(Permissions::HRM_EMPLOYEE_SHIFT_VIEW_DEP)) {
                $builder->where('emp.id', $employeeId);
            }
        }

        return $builder;
    }

    public function dataTable(Request $request)
    {
        abort_unless(authUser()->hasAnyPermission(
            Permissions::HRM_EMPLOYEE_SHIFT_VIEW_OWN,
            Permissions::HRM_EMPLOYEE_SHIFT_VIEW_DEP,
            Permissions::HRM_EMPLOYEE_SHIFT_VIEW_ALL
        ), 403);

        $request->validate([
            'from' => 'required|date_format:'.dateformat(),
            'till' => 'required|date_format:'.dateformat(),
            'working_company_id' => 'nullable|integer',
            'shift_ids' => 'nullable|array',
            'shift_ids.*' => 'integer',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'integer'
        ]);

        $builder = DB::query()->fromSub($this->getBuilder($request->all()), 't');
        $dataTable = new QueryDataTable($builder);
        
        return $dataTable->toJson();
    }

     /**
     * Exports the report
     *
     * @param Request $request
     */
    public function export(Request $request)
    {
        $inputs = $request->validate([
            'to' => 'required|in:pdf,xlsx',
            'from' => 'required|date_format:'.dateformat(),
            'till' => 'required|date_format:'.dateformat(),
            'working_company_id' => 'nullable|integer',
            'shift_ids' => 'nullable|array',
            'shift_ids.*' => 'integer',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'integer'
        ]);

        $ext = $inputs['to'];
        $title = 'Shift Report';
        $meta = [
            "Period" => (
                  ($inputs['from'] ?? 'Beginning')
                . ' to '
                . ($inputs['till'] ?? date(dateformat()))
            )
        ];

        // Set Column to be displayed
        $columns = [
            'Emp. #' => 'emp_ref',
            'Emp. Name' => 'employee_name',
            'Department' => 'department_name',
            'Company' => 'working_company_name',
        ];
        foreach (CarbonPeriod::create(date2sql($inputs['from']), date2sql($inputs['till'])) as $date) {
            $columns[$date->format('M-j D')] =  'shift_code_'.$date->format(dateformat());
        }

        $builder = $this->getBuilder($request->all());

        $generator = app($ext == 'xlsx' 
            ? ExcelReport::class
            : PdfReport::class
        )->of($title, $meta, $builder, $columns)
        ->setPaper('a4');

        $generator->simple();
    
        $file = 'download/'.Str::orderedUuid().".$ext";
        $generator->store($file);

        return [
            "redirect_to" => url(route("file.download", ['type' => 'shift-report', 'file' => basename($file)]))
        ];
    }


}
