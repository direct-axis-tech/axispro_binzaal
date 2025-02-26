<?php

namespace App\Http\Controllers\Labour;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Bank;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Emirate;
use App\Models\Entity;
use App\Models\Inventory\StockMove;
use App\Models\Labour\Agent;
use App\Models\Labour\Labour;
use App\Models\Language;
use App\Models\Religion;
use App\Permissions;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\QueryDataTable;

class LabourController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        abort_unless($request->user()->hasPermission(Permissions::SA_LBR_VIEW), 403);

        return view('labours.labour.index', [
            'labour_types' => labour_types(),
            'labour_job_types' => labour_job_types(),
        ]);
    }

    /**
     * Returns the dataTable api for this resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function dataTable(Request $request)
    {
        abort_unless($request->user()->hasPermission(Permissions::SA_LBR_VIEW), 403);

        $builder = Labour::from('0_labours as labour')
            ->select(
                DB::raw('`labour`.*'),
                'religion.name as religion_name',
                'country.name as country_name',
                'photo.file as profile_photo',
                'passport.reference as passport_ref',
                'category.description as category_name',
            )
            ->selectRaw("date_format(labour.created_at, '".dateformat('mySQL')." %h:%I %p') as formatted_created_at")
            ->selectRaw("concat_ws(' - ', creator.user_id, nullif(creator.real_name, '')) as creator_name")
            ->selectRaw("concat_ws(' - ', agent.supp_ref, agent.supp_name) as agent_name")
            ->selectRaw('json_unquote(json_extract(?, concat(\'$.\', labour.type))) AS type_name', [json_encode(labour_types(), JSON_FORCE_OBJECT)])
            ->selectRaw('json_unquote(json_extract(?, concat(\'$.\', job_type))) AS job_type_name', [json_encode(labour_job_types(), JSON_FORCE_OBJECT)])
            ->leftJoin('0_religions as religion', 'religion.id', 'labour.religion')
            ->leftJoin('0_countries as country', 'country.code', 'labour.nationality')
            ->leftJoin('0_suppliers as agent', function (JoinClause $join) {
                $join->on('agent.supplier_id', 'labour.agent_id')
                    ->where('agent.supplier_type', Agent::TYPE_AGENT);
            })
            ->leftJoin('0_stock_category as category', 'category.category_id', 'labour.category')
            ->leftJoin('0_documents as photo', function(JoinClause $join) {
                $join->on('photo.entity_id', 'labour.id')
                    ->where('photo.entity_type', Entity::LABOUR)
                    ->where('photo.document_type', Labour::DOC_TYPE_PASSPORT_SIZE_PHOTO);
            })
            ->leftJoin('0_documents as passport', function(JoinClause $join) {
                $join->on('passport.entity_id', 'labour.id')
                    ->where('passport.entity_type', Entity::LABOUR)
                    ->where('passport.document_type', Labour::DOC_TYPE_PASSPORT);
            })
            ->leftJoin('0_users as creator', 'creator.id', 'labour.created_by')
            ->leftJoin('0_users as modifier', 'modifier.id', 'labour.updated_by')
            ->groupBy('labour.id')
            ->orderBy('labour.created_at', 'desc');

        $dataTable = (new QueryDataTable(DB::query()->fromSub($builder, 't')));

        return $dataTable->toJson();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        abort_unless($request->user()->hasPermission(Permissions::SA_LBR_CREATE), 403);

        return view('labours.labour.create', array_merge(
            $this->getMasterData(),
            [
                'url' => route('labour.store'),
                'title' => 'New Maid',
                'inputs' => $this->getInputableFields(),
                'is_editing' => false
            ]
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
        abort_unless($request->user()->hasPermission(Permissions::SA_LBR_CREATE), 403);

        $pp_photo = Labour::DOC_TYPE_PASSPORT_SIZE_PHOTO;
        $fs_photo = Labour::DOC_TYPE_FULL_BODY_PHOTO;
        $this->prepareInputs($request);

        $validator = Validator::make(
            $request->except(['_method', '_token']),
            $this->getValidationRules(),
            [
                'mobile.regex' => 'The mobile number is not valid'
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => $validator->errors()->getMessages()
            ], 422);
        }

        $inputs = $validator->valid();
        foreach(['is_available', 'inactive'] as $boolCol) {
            $inputs[$boolCol] = !empty($inputs[$boolCol]);
        };

        $labour = DB::transaction(function () use ($pp_photo, $fs_photo, $inputs) {
            foreach (['dob', 'application_date', 'date_of_joining'] as $dateCol) {
                if (!empty($inputs[$dateCol])) {
                    $inputs[$dateCol] = new DateTime($inputs[$dateCol]);
                }
            }
            $labour = new Labour(Arr::except($inputs, ['video', 'docs']));
            $labour->created_by = authUser()->id;
            $labour->save();

            // Video tends to have large size, so its better to save them once the employee is successfully stored
            if (!empty($inputs['video'])) {
                $labour->video = $inputs['video']->store($labour->document_path());
                $labour->save();
            }

            // Add issue date for photos in case of needing to update them every 6 months in the future
            foreach ([$pp_photo, $fs_photo] as $k) {
                if (!empty($inputs['docs'][$k])) {
                    $inputs['docs'][$k]['issued_on'] = date(getNativeDateFormat());
                }
            }

            $documents = [];
            $documentTypes = $this->documentTypes()->pluck('id')->toArray();
            foreach ($documentTypes as $docType) {
                if (empty($inputs['docs'][$docType]['file'])) {
                    continue;
                }

                $doc = $inputs['docs'][$docType];
                $doc['document_type'] = $docType;
                $doc['file'] = $doc['file']->store($labour->document_path());
                
                foreach (['issued_on', 'expires_on'] as $dateCol) {
                    if (!empty($doc[$dateCol])) {
                        $doc[$dateCol] = new DateTime($doc[$dateCol]);
                    }
                }

                $documents[] = new Document($doc);
            }

            // Save documents
            if (!empty($documents)) {
                $labour->documents()->saveMany($documents);
            }

            return $labour;
        });

        return response()->json(['status' => 201, "message" => "Labour Created Successfully"], 201);
    }

    public function select2(Request $request) {
        $inputs = $request->validate([
            'term' => 'nullable',
            'page' => 'nullable|integer|min:1',
            'except' => 'nullable|array',
            'except.*' => 'integer',
            'showInactive' => 'nullable|integer',
            'agentId' => 'nullable|array',
            'agentId.*' => 'integer',
            'nationality' => 'nullable|array',
            'nationality.*' => 'alpha|size:2',
            'categoryId' => 'nullable|array',
            'categoryId.*' => 'integer',
        ]);

        $pageLength = 25;
        $page = $inputs['page'] ?? 1;
        $builder = Labour::selectRaw("id, CONCAT_WS(' - ', nullif(maid_ref, ''), name) as text");

        if (!empty($inputs['term'])) {
            $q = "%{$inputs['term']}%";
            $builder->whereRaw("CONCAT_WS(' - ', nullif(maid_ref, ''), name) like ?", $q);
        }

        if (!empty($inputs['except'])) {
            $builder->whereNotIn('id', $inputs['except']);
        }

        if (empty($inputs['showInactive'])) {
            $builder->whereInactive('0');
        }

        if (!empty($inputs['agentId'])) {
            $builder->whereIn('agent_id', $inputs['agentId']);
        }

        if (!empty($inputs['nationality'])) {
            $builder->whereIn('nationality', $inputs['nationality']);
        }

        if (!empty($inputs['categoryId'])) {
            $builder->whereIn('category', $inputs['categoryId']);
        }

        $totalFiltered = $builder->count();
        $results = $builder->orderByRaw('name')
            ->offset(($page - 1) * $pageLength)
            ->limit($pageLength)
            ->get();

        return response()->json([
            'results' => $results->toArray(),
            'totalRecords' => $totalFiltered,
            'pagination' => [
                'more' => $totalFiltered > $page * $pageLength
            ]
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Labour\Labour $labour
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Labour $labour)
    {
        abort_unless($request->user()->hasPermission(Permissions::SA_LBR_CREATE), 403);

        $inputableFields = $this->getInputableFields();
        $inputs = $labour->only(array_keys($inputableFields));

        if (empty($inputs['languages'])) {
            $inputs['languages'] = $inputableFields['languages'];
        }

        $dateFormat = getNativeDateFormat();
        foreach (['dob', 'application_date', 'date_of_joining'] as $key) {
            if (!empty($inputs[$key])) {
                $inputs[$key] = $inputs[$key]->format($dateFormat);
            }
        }

        return view('labours.labour.create', array_merge(
            $this->getMasterData($labour->id),
            [
                'url' => route('labour.update', $labour->id),
                'title' => "Modify Maid {$labour->name}",
                'inputs' => $inputs,
                'is_editing' => true,
                'labour_id' => $labour->id
            ]
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Labour\Labour $labour
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Labour $labour)
    {
        abort_unless($request->user()->hasPermission(Permissions::SA_LBR_CREATE), 403);

        $pp_photo = Labour::DOC_TYPE_PASSPORT_SIZE_PHOTO;
        $fs_photo = Labour::DOC_TYPE_FULL_BODY_PHOTO;

        $this->prepareInputs($request);

        $validator = Validator::make(
            $request->except(['_method', '_token']),
            $this->getValidationRules($labour->id),
            [
                'mobile.regex' => 'The mobile number is not valid'
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => $validator->errors()->getMessages()
            ], 422);
        }

        $inputs = $validator->valid();

        foreach(['is_available', 'inactive'] as $boolCol) {
            $inputs[$boolCol] = !empty($inputs[$boolCol]);
        };

        foreach (['dob', 'application_date', 'date_of_joining'] as $dateCol) {
            if (!empty($inputs[$dateCol])) {
                $inputs[$dateCol] = new DateTime($inputs[$dateCol]);
            }
        }

        if (!empty($inputs['video'])) {
            if (Storage::exists($labour->video)) {
                Storage::delete($labour->video);
            }
            $inputs['video'] = $inputs['video']->store($labour->document_path());
        }

        foreach(Arr::except($inputs, ['docs']) as $key => $value) {
            $labour->{$key} = $value;
        }

        $labour->updated_by = authUser()->id;
        $labour->save();

        // Update issue date for photos
        foreach ([$pp_photo, $fs_photo] as $key) {
            if (!empty($inputs['docs'][$key]['file'])) {
                $inputs['docs'][$key]['issued_on'] = date(getNativeDateFormat());
            }
        }

        $docs = $labour->documents->keyBy('document_type');
        $documentTypes = $this->documentTypes()->pluck('id')->toArray();
        foreach ($documentTypes as $docType) {
            $input_doc = $inputs['docs'][$docType] ?? [];
            $original_doc = $docs[$docType] ?? $labour->documents()->make([
                'document_type' => $docType
            ]);
            
            if (empty($input_doc['file'])) {
                continue;
            }
            
            if (Storage::exists($original_doc->file)) {
                Storage::delete($original_doc->file);
            }
            
            $input_doc['file'] = $input_doc['file']->store($labour->document_path());
            
            foreach (['issued_on', 'expires_on'] as $dateCol) {
                if (!empty($input_doc[$dateCol])) {
                    $input_doc[$dateCol] = new DateTime($input_doc[$dateCol]);
                }
            }
            
            foreach ($input_doc as $key => $val) {
                $original_doc->{$key} = $val;
            }

            $original_doc->save();
        }

        return response()->json(['status' => 200, 'data' => $labour->fresh(), 'message' => 'Labour updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    
    /**
     * Validates if the reference is unique
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function isReferenceUnique(Request $request)
    {
        $builder = Labour::whereMaidRef($request->input('reference'));
        
        if ($request->input('id')) {
            $builder->where('id', '<>', $request->input('id'));
        }

        return response()->json([
            'result' => !$builder->exists()
        ]);
    }

    /**
     * Check for availability of the maid for the period
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkAvailability(Request $request)
    {
        $inputs = $request->validate([
            'maid_id' => 'bail|required|integer|exists:0_labours,id',
            'delivery_date' => 'required|date_format:'.dateformat(),
        ]);
        $inputs['delivery_date'] = date2sql($inputs['delivery_date']);

        if (Labour::isValidInventoryUpdate($inputs['maid_id'], $inputs['delivery_date'])) {
            return response()->json([
                'data' => [
                    'is_available' => true,
                    'status' => 'Available'
                ]
            ]);
        }

        $result = StockMove::query()
            ->whereMaidId($inputs['maid_id'])
            ->selectRaw('SUM(tran_date < ?) > 0 as is_purchased', [$inputs['delivery_date']])
            ->selectRaw('SUM(IF(tran_date < ?, qty, 0)) <= 0 as is_out_on_date', [$inputs['delivery_date']])
            ->selectRaw("NULLIF(MAX(IF(tran_date < ? AND qty = -1, tran_date, '0001-01-01')), '0001-01-01') last_preceding_out_date", [$inputs['delivery_date']])
            ->selectRaw("NULLIF(MIN(IF(tran_date > ? AND qty = -1, tran_date, '9999-99-99')), '9999-99-99') first_following_out_date", [$inputs['delivery_date']])
            ->first();

        if ($result) {
            $result = $result->toArray();
        }

        $status = 'Not Available';

        if (!$result['is_purchased']) {
            $status = 'Not purchased yet';
        }

        else if ($result['is_out_on_date']) {
            $status = 'Already working';

            if ($result['last_preceding_out_date']) {
                $status .= ' from '.sql2date($result['last_preceding_out_date']);
            }
        }

        else if ($result['first_following_out_date']) {
            $status = 'Conflicts with the work from '.sql2date($result['first_following_out_date']).' onwards';
        }

        return response()->json([
            'data' => array_merge(
                [
                    'is_available' => false,
                    'status' => $status
                ],
                $result
            )
        ]);
    }

    public function getInputableFields() {
        return [
            'name' => '',
            'arabic_name' => '',
            'maid_ref' => '',
            'mothers_name' => '',
            'mobile_number' => '',
            'address' => '',
            'religion' => '',
            'nationality' => '',
            'gender' => '',
            'age' => '',
            'dob' => '',
            'height' => '',
            'weight' => '',
            'marital_status' => '',
            'no_of_children' => '',
            'mother_tongue' => '',
            'place_of_birth' => '',
            'education' => '',
            'languages' => [['id' => '', 'proficiency' => '']],
            'skills' => [],
            'work_experience' => '',
            'agent_id' => '',
            'job_type' => '',
            'type' => '',
            'category' => '',
            'maid_status' => '',
            'locations' => [],
            'application_date' => '',
            'date_of_joining' => '',
            'salary' => '',
            'basic_salary' => '',
            'other_allowance' => '',
            'accommodation_allowance' => '',
            'transportation_allowance' => '',
            'mol_id' => '',
            'bank_id' => '',
            'branch_name' => '',
            'account_number' => '',
            'iban' => '',
            'remarks' => '',
            'is_available' => '1',
            'inactive' => '0',
            'video' => '',
        ];
    }

    public function getMasterData($ignoreId = null) {
        $docTypesQuery = $this->documentTypes($ignoreId);

        return [
            'labour_skills' => labour_skills(),
            'emirates' => Emirate::all(),
            'labour_types' => labour_types(),
            'religions' => Religion::all(),
            'countries' => Country::all(),
            'job_types' => labour_job_types(),
            'languages' => Language::all(),
            'language_proficiencies' => language_proficiencies(),
            'agents' => Agent::active()->get(),
            'banks' => bank::all(),
            'genders' => genders(),
            'marital_statuses' => marital_statuses(),
            'labour_categories' => labour_invoice_categories(),
            'maid_status' => maid_status(),
            'education_levels' => education_levels(),
            'pp_photo' => (clone $docTypesQuery)->where('docType.id', Labour::DOC_TYPE_PASSPORT_SIZE_PHOTO)->first(),
            'fs_photo' => (clone $docTypesQuery)->where('docType.id', Labour::DOC_TYPE_FULL_BODY_PHOTO)->first(),
            'docTypes' => (clone $docTypesQuery)->whereNotIn('docType.id', [
                    Labour::DOC_TYPE_PASSPORT_SIZE_PHOTO,
                    Labour::DOC_TYPE_FULL_BODY_PHOTO
                ])
                ->get()
                ->each(function ($item) {
                    $item->context = $item->context ? json_decode($item->context) : null;
                    $item->issued_on = $item->issued_on ? sql2date($item->issued_on) : null;
                    $item->expires_on = $item->expires_on ? sql2date($item->expires_on) : null;
                }),
        ];
    }

    public function getValidationRules($ignoreId = null)
    {
        $pp_photo = Labour::DOC_TYPE_PASSPORT_SIZE_PHOTO;
        $fs_photo = Labour::DOC_TYPE_FULL_BODY_PHOTO;
        $visa = Labour::DOC_TYPE_VISA;
        $passport = Labour::DOC_TYPE_PASSPORT;
        $labour_card = Labour::DOC_TYPE_LABOUR_CARD;

        $rules = [
            'name'                                      => 'required|string',
            'maid_ref'                                  => 'required|string',
            'arabic_name'                               => 'nullable|string',
            'mothers_name'                              => 'nullable|string',
            'mobile_number'                             => ['required', 'regex:/^[0-9]{5,14}$/'],
            'address'                                   => 'nullable|string',
            'religion'                                  => 'nullable|integer',
            'nationality'                               => 'nullable|bail|alpha|exists:0_countries,code',
            'gender'                                    => 'nullable|in:' . implode(',', array_keys(genders())),
            'age'                                       => 'nullable|integer|min:0',
            'dob'                                       => 'nullable|date_format:' . getNativeDateFormat(),
            'height'                                    => 'nullable|numeric',
            'weight'                                    => 'nullable|numeric',
            'marital_status'                            => 'nullable|in:' . implode(',', array_keys(marital_statuses())),
            'no_of_children'                            => 'nullable|integer|min:0',
            'mother_tongue'                             => 'bail|integer|exists:0_languages,id',
            'place_of_birth'                            => 'nullable|string',
            'education'                                 => 'nullable|string',
            'languages'                                 => 'nullable|array',
            'languages.*.id'                            => 'required|bail|integer|exists:0_languages,id',
            'languages.*.proficiency'                   => 'required|in:'.implode(',', array_keys(language_proficiencies())),
            'skills'                                    => 'nullable|array',
            'skills.*'                                  => 'in:' . implode(',', array_keys(labour_skills())),
            'work_experience'                           => 'nullable|string',
            'video'                                     => 'nullable|file|mimetypes:video/mp4',
            'agent_id'                                  => 'bail|required|integer|exists:0_suppliers,supplier_id',
            'job_type'                                  => 'nullable|in:' . implode(',', array_keys(labour_job_types())),
            'type'                                      => 'nullable|in:' . implode(',', array_keys(labour_types())),
            'category'                                  => 'required|in:' . implode(',', array_keys(labour_invoice_categories())),
            'maid_status'                               => 'nullable|in:' . implode(',', array_keys(maid_status())),
            'locations'                                 => 'nullable|array',
            'locations.*'                               => 'bail|integer|exists:0_emirates,id',
            'application_date'                          => 'nullable|date_format:' . getNativeDateFormat(),
            'date_of_joining'                           => 'nullable|date_format:' . getNativeDateFormat(),
            'salary'                                    => 'nullable|numeric',
            'basic_salary'                              => 'nullable|numeric',
            'other_allowance'                           => 'nullable|numeric',
            'accommodation_allowance'                   => 'nullable|numeric',
            'transportation_allowance'                  => 'nullable|numeric',
            'mol_id'                                    => 'nullable|numeric',
            'bank_id'                                   => 'nullable|integer',
            'branch_name'                               => 'nullable|string',
            'account_number'                            => 'nullable|string',
            'iban'                                      => 'nullable|string',
            'remarks'                                   => 'nullable|string',
            'is_available'                              => 'nullable|boolean',
            'inactive'                                  => 'nullable|boolean',
            'docs'                                      => 'nullable|array',
        ];

        $documentTypes = $this->documentTypes($ignoreId)->get()->keyBy('id');
        return array_merge(
            $rules,
            ...array_map(
                function ($d) {
                    return [
                        "docs.{$d->id}.file" => [
                            $d->is_required && empty($d->file)
                                ? "required"
                                : "nullable",
                            "mimes:png,jpg,jpeg",
                            "max:2048"
                        ]
                    ];
                },
                $documentTypes->only([$pp_photo, $fs_photo])->all()
            ),
            ...array_map(
                function ($d) use ($passport, $labour_card, $visa) {
                    return [
                        "docs.{$d->id}.reference" => [
                            "nullable",
                            "required_with:docs.{$d->id}.file",
                            "string"
                        ],
                        "docs.{$d->id}.context.issue_place" => array_filter([
                            "nullable",
                            in_array($d->id, [$passport, $labour_card, $visa])
                                ? "required_with:docs.{$d->id}.file"
                                : null,
                            "string"
                        ]),
                        "docs.{$d->id}.issued_on" => [
                            "nullable",
                            "required_with:docs.{$d->id}.file",
                            "date_format:" . getNativeDateFormat()
                        ],
                        "docs.{$d->id}.expires_on" => array_filter([
                            "nullable",
                            $d->notify_before
                                ? "required_with:docs.{$d->id}.file"
                                : null,
                            "date_format:" . getNativeDateFormat()
                        ]),
                        "docs.{$d->id}.file" => [
                            $d->required && empty($d->file) 
                                ? "required"
                                : "nullable",
                            "file",
                            "mimetypes:image/jpeg,application/pdf"
                        ]
                    ];
                },
                $documentTypes->except([$pp_photo, $fs_photo])->all()
            )
        );
    }

    /**
     * Returns the query builder for document types
     *
     * @param string $ignoreId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function documentTypes($ignoreId = null) {
        return DocumentType::query()
            ->select(
                'docType.*',
                'doc.id as doc_id',
                'doc.reference',
                'doc.issued_on',
                'doc.expires_on',
                'doc.file',
                'doc.context'
            )
            ->from('0_document_types as docType')
            ->leftJoin('0_documents as doc', function (JoinClause $join) use ($ignoreId) {
                $join->on('doc.document_type', 'docType.id')
                    ->whereColumn('doc.entity_type', 'docType.entity_type')
                    ->whereIn('doc.id', function ($query) use ($ignoreId) {
                        $query->selectRaw("max(id) as id")
                            ->from('0_documents')
                            ->where('entity_type', Entity::LABOUR)
                            ->where('entity_id', $ignoreId ?: -1)
                            ->groupBy('entity_type', 'entity_id', 'document_type');
                    });
            })
            ->where('docType.entity_type', Entity::LABOUR);
    }

    public function prepareInputs(Request $request)
    {
        $inputLanguages = $request->input('languages');
        if (is_array($inputLanguages)) {
            $request->merge([
                'languages' => array_values(array_filter($inputLanguages, function($knownLanguage) {
                    return $knownLanguage['id'] && $knownLanguage['proficiency'];
                }))
            ]);
        }
    }

    public function generateCv(Request $request, Labour $labour)
    {
        abort_unless($request->user()->hasPermission(Permissions::SA_LBR_VIEW), 403);

        $ofType = function ($type) {
            return function ($item) use ($type) {
                return $item->document_type == $type;
            };
        };

        $documents = Document::ofLabour($labour->id)->get();
        $passportPhoto = data_get($documents->first($ofType(Labour::DOC_TYPE_PASSPORT_SIZE_PHOTO)), 'file');
        $fullSizePhoto = data_get($documents->first($ofType(Labour::DOC_TYPE_FULL_BODY_PHOTO)), 'file');
        $passport = $documents->first($ofType(Labour::DOC_TYPE_PASSPORT));
        $labour = array_merge(
            $labour->toArray(),
            [
                'religion_name' => data_get(Religion::find($labour->religion), 'name', ''),
                'country_name' => data_get(Country::find($labour->nationality), 'name', ''),
                'computed_age' => $labour->dob ? Carbon::now()->diffInYears(new Carbon($labour->dob)) : '',
                'formatted_locations' => $labour->locations
                    ? implode(', ', Emirate::whereIn('id', $labour->locations)->pluck('name')->toArray())
                    : ''
            ]
            );

        $contentHtml = view('labours.labour.cv.cv', [
            'labour' => $labour,
            'documents' => $documents,
            'passportPhoto' => $passportPhoto && Storage::exists($passportPhoto)
                ? Storage::path($passportPhoto)
                : media_path('avatars/blank.png'),
            'fullSizePhoto' => $fullSizePhoto && Storage::exists($fullSizePhoto)
                ? Storage::path($fullSizePhoto)
                : media_path('avatars/full-body-placeholder.jpg'),
            'passport' => $passport ?: new Document(),
            'labour_types' => labour_types(),
            'marital_statuses' => marital_statuses(),
            'education_types' => education_levels(),
            'proficiencies_en' => language_proficiencies(),
            'proficiencies_ar' => language_proficiencies('ar'),
            'languages' => Language::whereIn('id', array_column($labour['languages'] ?: [], 'id'))->pluck('name', 'id')->toArray(),
            'skills_en' => labour_skills(),
            'skills_ar' => labour_skills('ar'),
        ])->render();
        
        $mPdf = app(\Mpdf\Mpdf::class);
        $mPdf->WriteHTML($contentHtml, \Mpdf\HTMLParserMode::HTML_BODY);
        $mPdf->Output('MaidCV.pdf', 'I');
    }
}
