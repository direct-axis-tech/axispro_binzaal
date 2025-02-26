<?php

namespace App\Http\Controllers\Hr;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use App\Models\Hr\EmpDocAccessLog;
use App\Models\Hr\EmpDocReleaseRequest;
use App\Models\Hr\Employee;
use App\Models\TaskType;
use App\Models\Workflow;
use App\Permissions;
use DateTime;
use Illuminate\Support\Facades\DB;

class EmpDocReleaseRequestsController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = authUser();
        abort_unless($user->hasAnyPermission(
            Permissions::HRM_DOC_RELEASE_REQ,
            Permissions::HRM_DOC_RELEASE_REQ_ALL
        ), 403);

        $currentEmployeeId = $user->employee_id ?: -1;
        $canOnlyAccessOwn = $user->doesntHavePermission(Permissions::HRM_DOC_RELEASE_REQ_ALL);

        $authorizedEmployees = $canOnlyAccessOwn ? collect($user->employee ? [$user->employee] : []) : Employee::active()->get();
        
        return view('hr.employees.documents.releaseRequest', compact(
            'currentEmployeeId',
            'canOnlyAccessOwn',
            'authorizedEmployees'
        ));
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
            Permissions::HRM_DOC_RELEASE_REQ,
            Permissions::HRM_DOC_RELEASE_REQ_ALL
        ), 403);

        $canOnlyAccessOwn = authUser()->doesntHavePermission(Permissions::HRM_DOC_RELEASE_REQ_ALL);
        $inputs = $request->validate([
            'document_type_id' => 'required|in:'.DocumentType::EMP_PASSPORT,
            'employee_id' => 'bail|required|integer|exists:0_employees,id',
            'requested_from' => 'required|date_format:'.dateformat(),
            'return_date' => 'required|date_format:'.dateformat(),
            'reason' => $canOnlyAccessOwn ? 'required' : 'nullable'
        ]);

       $req= EmpDocReleaseRequest::where('employee_id', $inputs['employee_id'])
            ->where('status', 'Approved')
            ->orwhere('status', 'Pending')
            ->whereRaw(
                '('
                        .     '(? between `requested_from` and `return_date`)'
                        . ' or (? between `requested_from` and `return_date`)'
                        . ' or (`requested_from` >= ? and `return_date` <= ?)'
                    .')',
                    [
                        date2Sql($inputs['requested_from']),
                        date2Sql($inputs['return_date']),
                        date2Sql($inputs['requested_from']),
                        date2Sql($inputs['return_date']),
                    ]
            )->exists();
            
        if($req){
            return response()->json(['message' => 'Employee already has a release request for this period.'], 400, ['error']);
        }


        DB::transaction(function () use ($inputs, $canOnlyAccessOwn) {
            $inputs['status'] = EmpDocReleaseRequest::PENDING;
            $inputs['created_by'] = authUser()->id;
            foreach (['requested_from', 'return_date'] as $key) {
                $inputs[$key] = DateTime::createFromFormat(dateformat(), $inputs[$key])->format(DB_DATE_FORMAT);
            }
            $request = EmpDocReleaseRequest::create($inputs);

            if ($canOnlyAccessOwn) {
                abort_unless(($workflow = Workflow::findByTaskType(TaskType::EMP_DOC_RELEASE_REQ)), 403);

                $data = [
                    'request_id' => $request->id,
                    'Document Type' => 'Passport',
                    'Requested From' => (new DateTime($inputs['requested_from']))->format(dateformat()),
                    'Return Date' => (new DateTime($inputs['return_date']))->format(dateformat()),
                    'Reason' => $request->reason
                ];
                $workflow->initiate($data);
            } else {
                $request->status = EmpDocReleaseRequest::APPROVED;
                $request->save();
            }

            EmpDocAccessLog::insert([
                "employee_id" => $request->employee_id,
                "document_type_id" => $request->document_type_id,
                "user_id" => $request->created_by,
                "action" => $canOnlyAccessOwn ? EmpDocAccessLog::REQUESTED : EmpDocAccessLog::RELEASED,
                "stamp" => date(DB_DATETIME_FORMAT)
            ]);
        });

        return response()->json(['message' => 'Request Placed Successfully'], 201);
    }
}
