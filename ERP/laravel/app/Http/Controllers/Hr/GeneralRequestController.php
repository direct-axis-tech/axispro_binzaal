<?php

namespace App\Http\Controllers\Hr;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Hr\Employee;
use App\Models\Hr\GeneralRequest;
use App\Models\Hr\GeneralRequestType;
use App\Models\TaskType;
use App\Models\Workflow;
use App\Permissions;
use Arr;
use DB;
use Illuminate\Http\Response;
use Yajra\DataTables\QueryDataTable;

class GeneralRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        abort_unless(authUser()->hasAnyPermission(
            Permissions::HRM_MANAGE_GENERAL_REQUEST,
            Permissions::HRM_MANAGE_GENERAL_REQUEST_ALL
        ), 403);
        
        $canOnlyAccessOwn = authUser()->doesntHavePermission(Permissions::HRM_MANAGE_GENERAL_REQUEST_ALL);
        $authorizedEmployees = $canOnlyAccessOwn ? collect(authUser()->employee ? [authUser()->employee] : []) : Employee::active()->get();
        $currentEmployeeId = authUser()->employee_id ?: -1;
        $requestTypes = GeneralRequestType::active()->get();

        return view('hr.generalRequests', compact('authorizedEmployees', 'requestTypes', 'canOnlyAccessOwn', 'currentEmployeeId'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        abort_unless(authUser()->hasAnyPermission(
            Permissions::HRM_MANAGE_GENERAL_REQUEST,
            Permissions::HRM_MANAGE_GENERAL_REQUEST_ALL
        ), 403);

        $inputs = $this->getValidatedInputs($request);
        $canOnlyAccessOwn = authUser()->doesntHavePermission(Permissions::HRM_MANAGE_GENERAL_REQUEST_ALL);

        $result = DB::transaction(function() use($inputs, $canOnlyAccessOwn) {

            $this->getValidateRequest($inputs);

            $request = new GeneralRequest();
            $request->employee_id  = $inputs['employee_id'];
            $request->request_type_id = $inputs['request_type'];
            $request->request_date = $inputs['request_date'];
            $request->remarks      = $inputs['remarks'];
            $request->requested_by = authUser()->id;
            $request->request_status = GeneralRequest::PENDING;
            $request->save();

            if($canOnlyAccessOwn) {

                abort_unless(($workflow = Workflow::findByTaskType(TaskType::GENERAL_REQUEST)), 403);

                $data = [
                    'request_id' => $request->id,
                    'Employee' => Employee::find($request->employee_id)->formatted_name,
                    'Request Type' => GeneralRequestType::find($request->request_type_id)->request_type,
                    'Requested Date' => sql2date($request->request_date),
                    'Remarks' => $request->remarks
                ];
                $workflow->initiate($data);

            } else {

                $request->request_status = GeneralRequest::APPROVED;
                $request->reviewed_by    = authUser()->id;
                $request->save();
            }

            return response()->json([
                'message' => 'Request Created Successfully.'
            ], Response::HTTP_CREATED);

        });

        return $result;
    }

    /**
     * Validate the inputs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function getValidatedInputs(Request $request)
    {
        $inputs = $request->validate([
            'employee_id' => 'required|integer|exists:0_employees,id',
            'request_type' => 'required|integer|exists:0_request_types,id',
            'request_date' => 'required',
            'remarks' => 'required|string'
        ]);
        
        $inputs['request_date'] = date2sql($inputs['request_date']);

        return Arr::only($inputs, [
            'employee_id',
            'request_type',
            'request_date',
            'remarks'
        ]);
    }

    /**
     * Validates time sensitive data
     *
     * @param array $inputs
     * @param string $ignoreId
     * @return void
     */
    public function getValidateRequest($inputs, $ignoreId = null)
    {
        $conflictingRequest = GeneralRequest::active()
            ->where('employee_id', $inputs['employee_id'])
            ->where('request_type_id', $inputs['request_type'])
            ->where('request_date', $inputs['request_date'])
            ->whereIn('request_status', [GeneralRequest:: APPROVED, GeneralRequest::PENDING]);        

        if ($ignoreId) {
            $conflictingRequest->where('id', '!=', $ignoreId);
        }

        abort_if(
            $conflictingRequest->exists(),
            422,
            'Request already exists'
        );
    }

    /**
     * Returns the dataTable api for this resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function dataTable()
    {
        abort_unless(authUser()->hasAnyPermission(
            Permissions::HRM_MANAGE_GENERAL_REQUEST,
            Permissions::HRM_MANAGE_GENERAL_REQUEST_ALL
        ), 403);

        $canOnlyAccessOwn = authUser()->doesntHavePermission(Permissions::HRM_MANAGE_GENERAL_REQUEST_ALL);
        $mysqlDateFormat = getDateFormatForMySQL();

        $builder = GeneralRequest::select('0_general_requests.*', '0_request_types.request_type', '0_users.real_name as initiated_by',
                            'review.real_name as completed_by', '0_employees.preferred_name',
                            DB::raw("date_format(`request_date`, '{$mysqlDateFormat}') as formatted_request_date"))
                ->join('0_employees', '0_employees.id', '0_general_requests.employee_id')
                ->join('0_request_types', '0_request_types.id', '0_general_requests.request_type_id')
                ->join('0_users', '0_users.id', '0_general_requests.requested_by')
                ->leftJoin('0_users as review', 'review.id', '0_general_requests.reviewed_by')
                ->where('0_general_requests.inactive', 0)
                ->where('0_request_types.inactive', 0)
                ->orderBy('0_general_requests.request_date');
        
        if($canOnlyAccessOwn){
            $builder->where('0_general_requests.employee_id', authUser()->employee_id);
        }

        $dataTable = (new QueryDataTable(DB::query()->fromSub($builder, 't')));

        return $dataTable->toJson();
    }

}
