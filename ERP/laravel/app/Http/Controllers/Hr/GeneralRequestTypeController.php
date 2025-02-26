<?php

namespace App\Http\Controllers\Hr;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Hr\GeneralRequest;
use App\Models\Hr\GeneralRequestType;
use App\Permissions;
use Arr;
use DB;
use Illuminate\Http\Response;
use Yajra\DataTables\QueryDataTable;

class GeneralRequestTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        abort_unless(
            authUser()->hasPermission(Permissions::HRM_MANAGE_GENERAL_REQUEST_TYPE),
            403
        );

        return view('hr.generalRequestTypes');
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
            authUser()->hasPermission(Permissions::HRM_MANAGE_GENERAL_REQUEST_TYPE),
            403
        );

        $inputs = $this->getValidatedInputs($request);

        $result = DB::transaction(function() use ($inputs) {
           
            $inputs['created_by'] = authUser()->id;
            $requestType = new GeneralRequestType($inputs);
            $requestType->save();
                    
            return response()->json([
                'message' => 'Request Type Created Successfully.'
            ], Response::HTTP_CREATED);

        });

        return $result;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Hr\GeneralRequestType $general_request_type
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GeneralRequestType $general_request_type)
    {
        abort_unless(
            authUser()->hasPermission(Permissions::HRM_MANAGE_GENERAL_REQUEST_TYPE),
            403
        );

        $inputs = $this->getValidatedInputs($request);

        $result = DB::transaction(function() use ($inputs, $general_request_type) {

            $general_request_type->update($inputs);

            return response()->json([
                'message' => 'Request Type Updated Successfully.'
            ], Response::HTTP_OK);

        });

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Hr\GeneralRequestType $general_request_type
     * @return \Illuminate\Http\Response
     */
    public function destroy(GeneralRequestType $general_request_type)
    {
        $result = DB::transaction(function () use ($general_request_type) {

            $this->getValidateRequestTypeIsInUse($general_request_type);
            $general_request_type->update(['inactive' => true]);

            return response()->json(['message' => 'Deleted Successfully']);
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
            'request_type' => 'required|unique:0_request_types',
            'remarks' => 'required|string|max:255',
        ]);

        return Arr::only($inputs, [
            'request_type',
            'remarks'
        ]);
    }

    /**
     * Validates request type already is in use
     *
     * @param array $requestType
     * @return void
     */
    public function getValidateRequestTypeIsInUse($requestType)
    {
        $conflictingRequest = GeneralRequest::active()
            ->where('request_type_id', $requestType['id'])
            ->whereIn('request_status', [GeneralRequest:: APPROVED, GeneralRequest::PENDING]);
        
        abort_if(
            $conflictingRequest->exists(),
            422,
            'Request already in use !'
        );
    }

    /**
     * Returns the dataTable api for this resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function dataTable()
    {
        abort_unless(
            authUser()->hasPermission(Permissions::HRM_MANAGE_GENERAL_REQUEST_TYPE),
            403
        );

        $builder = GeneralRequestType::active();
        $dataTable = (new QueryDataTable(DB::query()->fromSub($builder, 't')));

        return $dataTable->toJson();
    }

}
