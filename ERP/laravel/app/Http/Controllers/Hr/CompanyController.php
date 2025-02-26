<?php

namespace App\Http\Controllers\Hr;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Hr\Company;
use App\Models\Hr\Employee;
use App\Models\Hr\EmployeeJob;
use App\Permissions;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\QueryDataTable;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        abort_unless(
            authUser()->hasPermission(Permissions::HRM_MANAGE_COMPANY),
            403
        );

        $employees = Employee::active()->get();
        return view('hr.companies', compact('employees'));
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
            authUser()->hasPermission(Permissions::HRM_MANAGE_COMPANY),
            403
        );
        
        $inputs = $request->validate(...$this->validationArgs());

        $company = new Company();
        $company->name = $inputs['name'];
        $company->prefix = $inputs['prefix'];
        $company->mol_id = $inputs['mol_id'];
        $company->in_charge_id = $inputs['in_charge_id'] ?? [];
        $company->save();

        return response()->json([
            'message' => "Company Created Successfully",
            'data' => $company->fresh()
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Hr\Company $company
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Company $company)
    {
        abort_unless(authUser()->hasPermission(Permissions::HRM_MANAGE_COMPANY), 403);

        $inputs = $request->validate(...$this->validationArgs($company->id));

        if (!$company->is_used) {
            $company->name = $inputs['name'];
            $company->prefix = $inputs['prefix'];
            
        }

        if (!$company->is_used || ($company->is_used && empty($company->mol_id))) {
            $company->mol_id = $inputs['mol_id'];
        }

        $company->in_charge_id = $inputs['in_charge_id'] ?? [];
        $company->save();

        return response()->json(['message' => "Company Updated Successfully"]);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Hr\Company $company
     * @return \Illuminate\Http\Response
     */
    public function destroy(Company $company)
    {
        abort_unless(authUser()->hasPermission(Permissions::HRM_MANAGE_COMPANY), 403);

        abort_if($company->is_used, 422, "This Company is already in use");

        $company->delete();

        return response()->json(['message' => 'Company Deleted Successfully']);
    }

    /**
     * Returns the dataTable api for this resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function dataTable()
    {
        abort_unless(authUser()->hasPermission(Permissions::HRM_MANAGE_COMPANY), 403);

        $builder = DB::table('0_companies as company')
            ->leftJoin('0_employees as in_charge', function(JoinClause $join) {
                $join->whereRaw("json_contains(`company`.`in_charge_id`,  json_quote(concat('', `in_charge`.`id`)))");
            })
            ->select("company.*")
            ->selectRaw("group_concat(concat(`in_charge`.`emp_ref`, ' - ', `in_charge`.`name`)) as in_charge_name")
            ->selectRaw(Company::isUsedQuery('company.id')." as is_used")
            ->groupBy('company.id');

        $dataTable = (new QueryDataTable(DB::query()->fromSub($builder, 't')))
            ->rawColumns(['in_charge_id']);
        
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
                'prefix' =>  ["bail", "required", "regex:/^[0-9a-zA-Z]{2,3}$/",Rule::unique('0_companies', 'prefix')->ignore($ignoreId)],
                'name' => ["bail", "required", "regex:/^[\pL\pM\pN_\- ]+$/u", Rule::unique('0_companies', 'name')->ignore($ignoreId)],
                'in_charge_id' => "nullable|array",
                'in_charge_id.*' => "bail|integer|exists:0_employees,id",
                'mol_id' => ["bail", "nullable", "regex:/^\d{13}$/u", Rule::unique('0_companies', 'mol_id')->ignore($ignoreId)]
            ],
            [
                'prefix.regex' => 'The prefix must be alpha numeric with 2-3 letters',
                'name.regex' => 'The name must only contains alphabets, numbers, dashes, underscore or spaces',
                'mol_id.regex' => 'The MOL id seems to be invalid'
            ]
        ];
    }
}