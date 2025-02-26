<?php

namespace App\Http\Controllers\Hr;

use App\Models\Hr\EmployeePensionConfig;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Hr\EmployeeJob;
use App\Permissions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\QueryDataTable;

class EmployeePensionConfigController extends Controller
{
        
    /**
     * index
     *
     * @return void
     */
    public function index()
    {
        abort_unless(
            authUser()->hasPermission(Permissions::HRM_MANAGE_PENSION_CONFIG),
            403
        );
        
        return view('hr.pensionConfig');
    }
    
    /**
     * store
     *
     * @param  mixed $request
     * @return void
     */
    public function store(Request $request)
    {
        abort_unless(
            authUser()->hasPermission(Permissions::HRM_MANAGE_PENSION_CONFIG),
            403
        );

        $inputs = $this->getValidatedInputs($request);

        $pensionConfig = new EmployeePensionConfig([
            'name' => $inputs['name'],
            'employee_share' => $inputs['employee_share'],
            'employer_share' => $inputs['employer_share'],
            'created_by' => authUser()->id
        ]);
        $pensionConfig->save();

        return response()->json(['message' => 'Pension Configuration Added Successfully'], 201);
    }
    
    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $employeePensionConfig
     * @return void
     */
    public function update(Request $request, EmployeePensionConfig $employeePensionConfig)
    {
        abort_unless(
            authUser()->hasPermission(Permissions::HRM_MANAGE_PENSION_CONFIG),
            403
        );

        $inputs = $this->getValidatedInputs($request);

        $employeePensionConfig->update($inputs);
            
        return response()->json(['message' => 'Pension Configuration Updated Successfully']);
    }
    
    /**
     * destroy
     *
     * @param  mixed $request
     * @param  mixed $employeePensionConfig
     * @return void
     */
    public function destroy(EmployeePensionConfig $employeePensionConfig)
    {
        abort_unless(
            authUser()->hasPermission(Permissions::HRM_MANAGE_PENSION_CONFIG),
            403
        );

        abort_if(
            EmployeeJob::query()
                ->where('pension_scheme', $employeePensionConfig->id)
                ->where('is_current', 1)
                ->exists(),
            422,
            'Config already in use !'
        );

        $employeePensionConfig->update(['inactive' => true]);

        return response()->json(['message' => 'Pension Configuration Deleted Successfully']);
    }
    
    /**
     * getValidatedInputs
     *
     * @param  mixed $request
     * @return void
     */
    public function getValidatedInputs(Request $request) 
    {
        $inputs = $request->validate([
            'name' => 'required',
            'employee_share' => 'required|numeric|min:0|max:100',
            'employer_share' => 'required|numeric|min:0|max:100'
        ]);
    

        return Arr::only($inputs, [
            'name',
            'employee_share',
            'employer_share'
        ]);
    }
    
    /**
     * dataTable
     *
     * @return void
     */
    public function dataTable()
    {
        abort_unless(
            authUser()->hasPermission(Permissions::HRM_MANAGE_PENSION_CONFIG), 
            403
        );

        $builder = EmployeePensionConfig::active();

        $dataTable = (new QueryDataTable(DB::query()->fromSub($builder, 't')));

        return $dataTable->toJson();
    }
}
