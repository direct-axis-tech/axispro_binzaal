<?php

namespace App\Http\Controllers\Hr;

use App\Events\Hr\DocumentUploaded;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Entity;
use App\Models\Hr\Employee;
use App\Permissions;
use DateTime;
use Illuminate\Support\Facades\Event;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Response as FacadesResponse;
use Illuminate\Support\Facades\Storage;

class DocumentsController extends Controller
{
    /**
     * Show the document upload form
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function upload(Request $request) {
        abort_unless(
            $request->user()->hasPermission(Permissions::HRM_UPLOAD_DOC),
            403
        );

        $employees = Employee::active()->get();
        $docTypes = DocumentType::ofEntity(Entity::EMPLOYEE)->get();

        return view('hr.employees.documents.upload', compact('employees', 'docTypes'));
    }

    /**
     * Stores the uploaded document
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request) {
        abort_unless(
            $request->user()->hasPermission(Permissions::HRM_UPLOAD_DOC),
            403
        );

        $inputs = $request->validate([
            'entity_id' => 'bail|required|integer|exists:0_employees,id',
            'document_type' => 'bail|required|integer|exists:0_document_types,id',
            'issued_on' => 'required|date_format:j-M-Y',
            'expires_on' => 'nullable|date_format:j-M-Y',
            'reference' => 'nullable|alpha_dash',
            'file.*' => 'required|file|mimetypes:application/pdf|max:2048'
        ]);

        $inputs['file'] = $inputs['file'][0]->store("docs/employees/{$inputs['document_type']}");
        $inputs['entity_type'] = Entity::EMPLOYEE;
        $inputs['issued_on'] = new DateTime($inputs['issued_on']);
        if ($inputs['expires_on']) {
            $inputs['expires_on'] = new DateTime($inputs['expires_on']);
        }

        $document = Document::create($inputs);

        Event::dispatch(new DocumentUploaded($document));

        return response('OK', 201);
    }

    /**
     * Determines if the file exists or not
     *
     * @param Request $request
     * @return Response
     */
    public function exists(Request $request) {
        abort_unless(
            $request->user()->hasPermission(Permissions::HRM_UPLOAD_DOC),
            403
        );

        $inputs = $request->validate([
            'employee_id' => 'bail|required|integer|exists:0_employees,id',
            'document_type' => 'bail|required|integer|exists:0_document_types,id'
        ]);

        $document = Document::ofEmployee($inputs['employee_id'])->ofType($inputs['document_type'])->first();

        abort_if(!$document, 404);

        return response('', 204);
    }

    public function manage(Request $request) {

        abort_unless($request->user()->hasAnyPermission(
            Permissions::HRM_MANAGE_DOC,
            Permissions::HRM_MANAGE_DOC_OWN
        ), 403);

        $canOnlyAccessOwn = $request->user()->doesntHavePermission(Permissions::HRM_MANAGE_DOC);
        $employees = $canOnlyAccessOwn ? collect([$request->user()->employee]) : Employee::active()->get();

        if ($request->input('entity_id')) {
            $builder = Document::query()
                ->from('0_employees as emp')
                ->crossJoin('0_document_types as docType', function (JoinClause $join) {
                    $join->where('docType.entity_type', Entity::EMPLOYEE);
                })
                ->leftJoin('0_documents as doc', function (JoinClause $join) {
                    $join->on('doc.entity_id', '=', 'emp.id')
                        ->whereColumn('doc.entity_type', 'docType.entity_type')
                        ->whereColumn('doc.document_type', 'docType.id');
                })
                ->where('emp.id', $request->input('entity_id'));
        } else {
            $builder = Document::query()
                ->from('0_documents as doc')
                ->leftJoin('0_document_types as docType', 'doc.document_type', '=', 'docType.id')
                ->leftJoin('0_employees as emp', 'doc.entity_id', '=', 'emp.id')
                ->where('doc.entity_type', Entity::EMPLOYEE);
        }
            
        $builder->select(
            'doc.*',
            'docType.name as document_type_name',
            'emp.name as employee_name'
        );

        if ($request->input('document_type')) {
            $builder = $builder->where('doc.document_type', $request->document_type);
        }

        if ($request->input('reference')) {
            $builder = $builder->where('doc.reference', 'like', '%'.$request->reference.'%');
        }

        if ($request->input('issued_on')) {
            $issuedOn = \Carbon\Carbon::createFromFormat(dateformat(), $request->issued_on)->toDateString();            
            $builder = $builder->where('doc.issued_on', $issuedOn );
        }

        if ($request->input('expires_on')) {
            $expiresOn = \Carbon\Carbon::createFromFormat(dateformat(), $request->expires_on)->toDateString();
            $builder = $builder->whereDate('doc.expires_on', $expiresOn);
        }
        
        if ($canOnlyAccessOwn) {
            $builder = $builder->where('emp.id', data_get($request->user()->employee, 'id'));
        }

        $doc_details = $builder->paginate(10);
        $docTypes = DocumentType::ofEntity(Entity::EMPLOYEE)->get();
        
        return view('hr.employees.documents.viewDocuments', compact('doc_details','employees','docTypes'));  
        
    }

    public function destroy($id){
        abort_unless(
            authUser()->hasPermission(Permissions::HRM_DELETE_DOC),
            403
        );

        abort_unless($document = Document::find($id), 404, "Could not find the record");

        if (Storage::exists($document->file)) {
            Storage::delete($document->file);
        }

        $document->delete();
        return response()->json(["message" => "Document Destroyed Successfully"]);
    }

    public function edit($id) {
        abort_unless(
            authUser()->hasPermission(Permissions::HRM_EDIT_DOC),
            403
        );
        $document=Document::find($id);
        $employees = Employee::active()->get();
        $docTypes = DocumentType::ofEntity(Entity::EMPLOYEE)->get();
    
        if($document){
            return view('hr.employees.documents.updateDocument',compact('document','employees','docTypes'));
        }
    }

    public function update(Request $request, $id){
        abort_unless(
            authUser()->hasPermission(Permissions::HRM_EDIT_DOC),
            403
        );

        $request->validate([
            'entity_id' => 'bail|required|integer|exists:0_employees,id',
            'document_type' => 'bail|required|integer|exists:0_document_types,id',
            'issued_on' => 'required|date_format:d-M-Y',
            'reference' => 'nullable|alpha_dash',
        ]);

        if(isset($request['file']) && $request['file'][0]) {
            $request->validate([
                'file.*' => 'required|file|mimetypes:application/pdf|max:2048'
            ]);
        }

        $documentExist = Document::ofEmployee($request['entity_id'])
            ->ofType($request['document_type'])
            ->where('id', '!=', $id)
            ->first();

        if ($documentExist) {
            if (isset($request['file']) && $request['file'][0]) {
                return response('error', 500);
            }
            return redirect()->back()->with('error', 'A document of the specified type already exists for this employee.');
        }

        $document = Document::find($id);
        $document->update([
            'entity_id'     => $request->entity_id,
            'document_type' => $request->document_type,
            'issued_on'     => Carbon::createFromFormat('d-M-Y', $request->issued_on)->format('Y-m-d'),
            'reference'     => $request->reference
        ]);

        if ($request['expires_on']) {
            $document->update([
                'expires_on' => Carbon::createFromFormat('d-M-Y', $request->expires_on)->format('Y-m-d')
            ]);
        }

        if (isset($request['file']) && $request['file'][0]) {

            $fileLoc = $request['file'][0]->store("docs/employees/{$request['document_type']}");
            $document->update([
                'file' => $fileLoc
            ]);
            return response('Document updated successfully', 201);
        }

        return redirect()->route('employeeDocument.manage')->with('success', 'Document updated successfully');
    }
}
