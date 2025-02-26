<?php

namespace App\Http\Controllers\Hr;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Hr\Department;
use App\Models\Hr\DepartmentShift;
use App\Models\Hr\Employee;
use App\Permissions;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\QueryDataTable;
use Illuminate\Database\Query\JoinClause;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        abort_unless(
            authUser()->hasPermission(Permissions::HRM_MANAGE_DEPARTMENT),
            403
        );

        $employees = Employee::active()->get();
        return view('hr.departments', compact('employees'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        abort_unless(
            authUser()->hasPermission(Permissions::HRM_MANAGE_DEPARTMENT),
            403
        );
        
        $inputs = $request->validate(...$this->validationArgs());

        $department = new Department();
        $department->name = $inputs['name'];
        $department->hod_id = $inputs['hod_id'] ?? [];
        $department->save();

        DepartmentShift::insertUsing(
            ['department_id', 'shift_id'],
            DB::table('0_shifts as shift')
                ->crossJoin('0_departments as dep', function (Builder $query) use ($department) {
                    $query->where('dep.id', $department->id);
                })
                ->addSelect('dep.id as department_id')
                ->addSelect('shift.id as shift_id')
        );
        
        return response()->json([
            'message' => "Department Created Successfully",
            'data' => $department->fresh()
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Hr\Department $department
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Department $department)
    {
        abort_unless(authUser()->hasPermission(Permissions::HRM_MANAGE_DEPARTMENT), 403);

        $inputs = $request->validate(...$this->validationArgs($department->id));

        if (!$department->is_used) {
            $department->name = $inputs['name'];
        }

        $department->hod_id = $inputs['hod_id'] ?? [];
        $department->save();

        return response()->json(['message' => "Department Updated Successfully"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Hr\Department $department
     * @return \Illuminate\Http\Response
     */
    public function destroy(Department $department)
    {
        abort_unless(authUser()->hasPermission(Permissions::HRM_MANAGE_DEPARTMENT), 403);

        abort_if($department->is_used, 422, "This department is already in use");

        $department->delete();
        DepartmentShift::whereDepartmentId($department->id)->delete();

        return response()->json(['message' => 'Department Deleted Successfully']);
    }

    /**
     * Returns the dataTable api for this resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function dataTable()
    {
        abort_unless(authUser()->hasPermission(Permissions::HRM_MANAGE_DEPARTMENT), 403);

        $builder = DB::table('0_departments as department')
            ->leftJoin('0_employees as hod', function(JoinClause $join) {
                $join->whereRaw("json_contains(`department`.`hod_id`,  json_quote(concat('', `hod`.`id`)))");
            })
            ->select("department.*")
            ->selectRaw("group_concat(concat(`hod`.`emp_ref`, ' - ', `hod`.`name`)) as hod_name")
            ->selectRaw("exists(select 1 from `0_emp_jobs` ej where ej.department_id = department.id limit 1) as is_used")
            ->groupBy('department.id');

        $dataTable = (new QueryDataTable(DB::query()->fromSub($builder, 't')))
            ->rawColumns(['hod_id']);
        
        return $dataTable->toJson();
    }

    /**
     * Returns the validation rules for adding and editing
     *
     * @param string $ignoreId The id to ignore when checking for uniqueness
     * @return array
     */
    public function validationArgs($ignoreId = null) {
        return [
            [
                'hod_id' => "nullable|array",
                'hod_id.*' => "bail|integer|exists:0_employees,id",
                'name' => ["bail", "required", "regex:/^[\pL\pM\pN_\- ]+$/u", Rule::unique('0_departments', 'name')->ignore($ignoreId)]
            ],
            [
                'name.regex' => 'The name must only contains alphabets, numbers, dashes, underscore or spaces'
            ]
        ];
    }
}
