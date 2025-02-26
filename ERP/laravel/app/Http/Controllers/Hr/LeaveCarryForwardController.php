<?php

namespace App\Http\Controllers\Hr;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Hr\LeaveCarryForward;
use App\Models\Hr\LeaveType;
use App\Models\Hr\EmployeeLeave;
use App\Permissions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\QueryDataTable;

class LeaveCarryForwardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        abort_unless(
            authUser()->hasPermission(Permissions::HRM_MANAGE_LEAVE_CARRY_FORWARD),
            403
        );

        return view('hr.leaveCarryForward');
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
            authUser()->hasPermission(Permissions::HRM_MANAGE_LEAVE_CARRY_FORWARD),
            403
        );
    
        $inputs = $this->getValidatedInputs($request);

        $result = DB::transaction(function () use ($inputs) {
            $this->ensureDateIsUpdatable($inputs['affected_from_date']);

            LeaveCarryForward::insert(array_merge(
                $inputs,
                [
                    'created_by' => authUser()->id
                ]
            ));

            EmpLeaveController::processEmployeeLeaveBalance(null, LeaveType::ANNUAL, $inputs['affected_from_date']);

            return response()->json([
                'message' => 'Limit Added Successfully'
            ], 201);
        });

        return $result;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Hr\LeaveCarryForward $limit
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LeaveCarryForward $leaveCarryForward)
    {
        abort_unless(authUser()->hasPermission(Permissions::HRM_MANAGE_LEAVE_CARRY_FORWARD), 403);

        $inputs = $this->getValidatedInputs($request, $leaveCarryForward->id);

        $result = DB::transaction(function () use ($inputs, $leaveCarryForward) {
            $this->validateTimeSensitiveData($inputs, $leaveCarryForward->id);
            $this->ensureDateIsUpdatable($leaveCarryForward->affected_from_date);

            // Update the Limit table
            $leaveCarryForward->update(array_merge(
                Arr::only($inputs, [
                    'carry_forward_limit',
                    'affected_from_date'
                ]),
                [
                    'updated_by' => authUser()->id
                ]
            ));

            EmpLeaveController::processEmployeeLeaveBalance(null, LeaveType::ANNUAL, $leaveCarryForward->affected_from_date);
                
            return response()->json(['message' => 'Limit Updated Successfully']);
        });

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Hr\LeaveCarryForward $limit
     * @return \Illuminate\Http\Response
     */
    public function destroy(LeaveCarryForward $leaveCarryForward)
    {
        abort_unless(authUser()->hasPermission(Permissions::HRM_MANAGE_LEAVE_CARRY_FORWARD), 403);

        $result = DB::transaction(function () use ($leaveCarryForward) {
            $this->ensureDateIsUpdatable($leaveCarryForward->affected_from_date);
            
            $leaveCarryForward->update(['inactive' => true]);

            EmpLeaveController::processEmployeeLeaveBalance(null, LeaveType::ANNUAL, $leaveCarryForward->affected_from_date);
            
            return response()->json(['message' => 'Limit Deleted Successfully']);
        });

        return $result;
    }

    /**
     * Validates the request and return the inputs
     *
     * @param Request $request
     * @param string $ignoreId
     * @return array
     */
    public function getValidatedInputs(Request $request, $ignoreId = null)
    {
        $inputs = $request->validate(
            [
                'carry_forward_limit' => 'required|integer',
                'affected_from_date' => [
                    'required',
                    'date_format:' . dateformat(),
                ]
            ]
        );

        $inputs['affected_from_date'] = date2sql($inputs['affected_from_date']);
        $inputs['leave_type_id'] = LeaveType::ANNUAL;

        return Arr::only($inputs, [
            'carry_forward_limit',
            'affected_from_date',
            'leave_type_id'
        ]);
    }

    /**
     * Validates that the date is updatable
     *
     * @param string $date
     * @return void
     */
    public function ensureDateIsUpdatable($date)
    {
        // Check For Leave Adjustments
        abort_if(
            $this->hasAdjustmentsAfterDate($date),
            422,
            "The 'Affected From Date' would conflict with 'Leave Adjustments' after the selected date."
        );

        // Check For Payroll Processed For The Date Range
        abort_if(
            DB::table('0_payslips')
                ->where('is_processed', 1)
                ->where('till', '>=', $date)
                ->exists(),
            422,
            'Payroll has already been generated for the selected date range'
        );
    }

    /**
     * Validates the data that changes with time
     *
     * @param string $inputs
     * @param string $ignoreId
     * @return void
     */
    public function validateTimeSensitiveData($inputs, $ignoreId)
    {
        $this->ensureDateIsUpdatable($inputs['affected_from_date']);

        Validator::make(
            $inputs,
            ['affected_from_date' => [
                Rule::unique('0_leave_carry_forward')
                    ->where('inactive', 0)
                    ->ignore($ignoreId)
            ]],
            ['affected_from_date.unique' => 'A carry-forward limit with the same date already exists']
        )->validate();
    }

    /**
     * Returns the dataTable api for this resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function dataTable()
    {
        abort_unless(
            authUser()->hasPermission(Permissions::HRM_MANAGE_LEAVE_CARRY_FORWARD), 
            403
        );

        $mysqlDateFormat = getDateFormatForMySQL();
        $builder = LeaveCarryForward::query()
            ->select(
                'limit.*',
                'leaveType.desc as leave_type_name',
                DB::raw("date_format(`affected_from_date`, '{$mysqlDateFormat}') as formatted_affected_from_date")
            )
            ->from('0_leave_carry_forward as limit')
            ->leftJoin('0_leave_types as leaveType', 'leaveType.id', 'limit.leave_type_id')
            ->where('limit.inactive', false);
        
        $dataTable = (new QueryDataTable(DB::query()->fromSub($builder, 't')))
            ->orderColumn('formatted_affected_from_date', 'affected_from_date $1');

        return $dataTable->toJson();
    }

    /**
     * Check for adjustments in leaves based on the affected date.
     *
     * @param string $value
     * @return bool
     */
    private function hasAdjustmentsAfterDate($value)
    {
        $adjustmentLeaves =  DB::table('0_emp_leave_details as leaveDetail')
            ->selectRaw('SUM(leaveDetail.days) as leave_count')
            ->leftJoin('0_emp_leaves as leave', 'leave.id', '=', 'leaveDetail.leave_id')
            ->where([
                ["leave.requested_on", '>=', $value],
                ["leaveDetail.is_cancelled", '=', 0],
                ["leave.status", '=', STS_APPROVED],
                ["leave.category_id", '=', EmployeeLeave::CATEGORY_ADJUSTMENT],
                ["leave.leave_type_id", '=', LeaveType::ANNUAL],
            ])->first();

        return ($adjustmentLeaves->leave_count ?? 0) > 0;
    }
}