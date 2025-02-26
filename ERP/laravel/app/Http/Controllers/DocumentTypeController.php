<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use App\Models\Entity;
use App\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\QueryDataTable;

class DocumentTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('system.documentType', [
            'entities' => Entity::whereIn('id', [
                Entity::EMPLOYEE,
                Entity::LABOUR,
                Entity::USER,
                Entity::CUSTOMER,
            ])->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        abort_unless(authUser()->hasPermission(Permissions::SA_MANAGE_DOCUMENT_TYPE), 403);

        $inputs = Arr::except(
            $request->validate($this->getValidationRules()),
            ['_method', '_token']
        );
        
        $type = DocumentType::create($inputs);
        
        return response()->json([
            'message' => 'Type Saved Successfully',
            'type' => $type
        ], 201);
    }

     /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DocumentType $documentType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DocumentType $documentType)
    {
        abort_unless(authUser()->hasPermission(Permissions::SA_MANAGE_DOCUMENT_TYPE), 403);

        $inputs = Arr::except(
            $request->validate($this->getValidationRules($request->id)),
            ['_method', '_token']
        );
        
        if ($documentType->is_used) {
            $inputs = Arr::except($inputs, ['id', 'name', 'entity_type']);
        }

        $inputs['is_required'] = !empty($request->input('is_required'));

        $documentType->update($inputs);
        
        return response()->json(['message' => 'Type Updated Successfully']);
    }

    protected function getValidationRules($ignoreId = null)
    {
        return [
            'entity_type' => 'required',
            'name' => [
                'required',
                'string',
                Rule::unique('0_document_types')
                    ->where('entity_type', request()->input('entity_type'))
                    ->ignore($ignoreId)
            ],
            'is_required' => 'nullable|boolean',
            'notify_before' => 'nullable|numeric',
            'notify_before_unit' => 'required_with:notify_before|nullable|in:'.implode(',', array_keys(notify_before_units()))
        ];
    }

    public function dataTable(Request $request)
    {
        abort_unless($request->user()->hasPermission(Permissions::SA_MANAGE_DOCUMENT_TYPE), 403);

        $builder = DocumentType::from('0_document_types as docType')
            ->select('docType.*')
            ->selectRaw(DocumentType::isUsedQuery('docType.id').' as is_used')
            ->selectRaw("if(is_required, 'Yes', 'No') as formatted_is_required")
            ->where('docType.id', '!=', DocumentType::SPECIAL_RESERVED)
            ->orderBy('docType.entity_type')
            ->orderBy('docType.name', 'asc');

        $dataTable = (new QueryDataTable(DB::query()->fromSub($builder, 't')))
            ->addColumn('entity_type_name', function($row) {
                return Entity::find($row->entity_type)->name;
            });
        
        return $dataTable->toJson();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\DocumentType $documentType
     * @return \Illuminate\Http\Response
     */
    public function destroy(DocumentType $documentType)
    {
        abort_unless(authUser()->hasPermission(Permissions::SA_MANAGE_DOCUMENT_TYPE), 403);

        abort_if(
            $documentType->is_used,
            422,
            'This Document Type is already used by some of the documents'
        );

        $documentType->delete();
        
        return response()->json(['message' => 'DocumentType Deleted Successfully']);
    }
}
