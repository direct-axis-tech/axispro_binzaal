<?php

namespace App\Http\Controllers\Hr;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Hr\Designation;
use App\Permissions;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\QueryDataTable;

class DesignationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        abort_unless(authUser()->hasPermission(Permissions::HRM_MANAGE_DESIGNATION), 403);
        
        return view('hr.designations');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        abort_unless(authUser()->hasPermission(Permissions::HRM_MANAGE_DESIGNATION), 403);

        $inputs = $request->validate(...$this->validationArgs());

        $designation = new Designation();
        $designation->name = $inputs['name'];
        $designation->save();

        return response()->json([
            'message' => "Designation Created Successfully",
            'data' => $designation->fresh()
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Hr\Designation $designation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Designation $designation)
    {
        abort_unless(authUser()->hasPermission(Permissions::HRM_MANAGE_DESIGNATION), 403);

        abort_if($designation->is_used, 422, "This designation is already in use");

        $inputs = $request->validate(...$this->validationArgs($request->id));

        $designation->name = $inputs['name'];
        $designation->save();
        
        return response()->json(['message' => "Designation Updated Successfully"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Hr\Designation $designation
     * @return \Illuminate\Http\Response
     */
    public function destroy(Designation $designation)
    {
        abort_unless(authUser()->hasPermission(Permissions::HRM_MANAGE_DESIGNATION), 403);

        abort_if($designation->is_used, 422, "This designation is already in use");

        $designation->delete();

        return response()->json(['message' => 'Designation Deleted Successfully']);
    }

    /**
     * Returns the dataTable api for this resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function dataTable()
    {
        abort_unless(authUser()->hasPermission(Permissions::HRM_MANAGE_DESIGNATION), 403);

        $builder = DB::table('0_designations as designation')
            ->select("designation.*")
            ->selectRaw("exists(select 1 from `0_emp_jobs` ej where ej.designation_id = designation.id limit 1) as is_used");

        $dataTable = new QueryDataTable(DB::query()->fromSub($builder, 't'));
        
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
                'name' => ["bail", "required", "regex:/^[\pL\pM\pN_\- ]+$/u", Rule::unique('0_designations', 'name')->ignore($ignoreId)]
            ],
            [
                'name.regex' => 'The name must only contains alphabets, numbers, dashes, underscore or spaces'
            ]
        ];
    }
}
