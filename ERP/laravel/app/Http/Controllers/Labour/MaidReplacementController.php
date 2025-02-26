<?php

namespace App\Http\Controllers\Labour;

use App\Contracts\Flowable;
use App\Http\Controllers\Controller;
use App\Models\Accounting\AuditTrail;
use App\Models\Accounting\FiscalYear;
use App\Models\Inventory\Location;
use App\Models\Inventory\StockCategory;
use App\Models\Inventory\StockReplacement;
use App\Models\Inventory\StockMove;
use App\Models\Labour\Contract;
use App\Models\Labour\Labour;
use App\Models\MetaReference;
use App\Models\MetaTransaction;
use App\Models\Sales\CustomerTransaction;
use App\Models\System\Attachment;
use App\Models\System\User;
use App\Models\TaskRecord;
use App\Models\TaskType;
use App\Models\Workflow;
use App\Permissions;
use App\Traits\Flowable as TraitsFlowable;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use UnexpectedValueException;

class MaidReplacementController extends Controller implements Flowable {

    use TraitsFlowable;

    /**
     * Show the stock return create form
     *
     * @param Request $request
     * @return void
     */
    public function create(Request $request)
    {
        abort_unless(authUser()->hasAnyPermission([
            Permissions::SA_STOCK_REPLACEMENT,
            Permissions::SA_MAID_REPLACEMENT
        ]), 403);

        $selectedContract = null;
        
        abort_if(
            $request->has('contract_id')
            && !($selectedContract = Contract::find($request->input('contract_id'))),
            404,
            "The requested contract could not be found"
        );

        if ($selectedContract) {
            $this->validateContractIsEligibleForReplacement($selectedContract);
        }

        return view('labours.contract.maidReplacement', compact('selectedContract'));
    }

    /**
     * Store the stock return
     *
     * @param Request $request
     * @return void
     */
    public function store(Request $request)
    {
        abort_unless(authUser()->hasAnyPermission([
            Permissions::SA_STOCK_REPLACEMENT,
            Permissions::SA_MAID_REPLACEMENT
        ]), 403);
        
        $inputs = $this->validateInputs($request->all());

        // If the user does have admin level permission
        // We don't need to initiate a request. Directly save the request
        if (authUser()->hasPermission(Permissions::SA_STOCK_REPLACEMENT)) {
            $this->writeTransaction($inputs);
            return response()->json(['message' => 'Maid Replacement Posted Successfully'], 201);
        }

        // User doesn't have admin level permission So, Send this request
        // through the workflow 
        abort_if(
            empty($workflow = Workflow::findByTaskType(TaskType::MAID_REPLACEMENT)),
            422,
            "Cannot find the workflow definition for you!"
        );

        abort_if(
            $this->isAlreadyRequested($inputs),
            422,
            "There is already a pending replacement request against this contract. Please retry after the completion of it."
        );

        $contract = Contract::find($inputs['contract_id']);

        $this->validateTimeSensitiveData($contract, $inputs);

        if (!empty($inputs['attachment'])) {
            $inputs['attachment_name'] = $inputs['attachment']->getClientOriginalName();
            $inputs['attachment'] = $inputs['attachment']->store('docs/labours/tmp');
        }

        $workflow->initiate(array_merge(
            $inputs,
            [
                'Contract' => $contract->reference,
                'Sponsor' => $contract->customer->name,
                'Replacement Date' => $inputs['replace_date'],
                'Returning Maid' => $contract->maid->formatted_name,
                'End Old Date' => $inputs['end_old_maid'],
                'Replacing Maid' => Labour::find($inputs['new_labour_id'])->formatted_name,
                'Start New Maid' => $inputs['start_new_maid']
            ],
            empty($inputs['attachment_name'])
                ? []
                : [
                    'Attachment' => $inputs['attachment_name']
                ] 
        ));

        return response()->json(['message' => 'Maid Replacement Requested Successfully'], 201);
    }

    /**
     * API to handle contracts select2 for maid return
     *
     * @param Request $request
     * @return void
     */
    public function contractsSelect2(Request $request)
    {
        $inputs = $request->validate([
            'term' => 'nullable',
            'page' => 'nullable|integer|min:1'
        ]);
 
        $pageLength = 25;
        $page = $inputs['page'] ?? 1;
        $builder = Contract::from('0_labour_contracts as contract')
            ->leftJoin('0_labours as maid', 'maid.id', 'contract.labour_id')
            ->leftJoin('0_stock_moves as return', function (JoinClause $join) {
                $join->on('return.contract_id', 'contract.id')
                    ->where('return.type', StockMove::STOCK_RETURN);
            })
            ->leftJoin('0_debtor_trans as delivery', function (JoinClause $join) {
                $join->on('delivery.contract_id', 'contract.id')
                    ->where('delivery.type', CustomerTransaction::DELIVERY)
                    ->whereRaw('`delivery`.`ov_amount`  + `delivery`.`ov_gst` + `delivery`.`ov_freight` + `delivery`.`ov_discount` + `delivery`.`ov_freight_tax` <> 0');
            })
            ->selectRaw("contract.id, concat_ws(' - ', contract.reference, nullif(maid.maid_ref, ''), maid.name) as text")
            ->whereNull("return.trans_id")
            ->whereNotNull("delivery.id")
            ->where("contract.inactive", 0)
            ->where("contract.category_id", '<>', StockCategory::DWD_PACKAGEONE)
            ->groupBy('contract.id');
                
        if (!empty($inputs['term'])) {
            $q = "%{$inputs['term']}%";
            $builder->whereRaw("concat_ws(' - ', contract.reference, nullif(maid.maid_ref, ''), maid.name) like ?", $q);
        }

        $totalFiltered = $builder->count();
        $results = $builder->orderBy('contract_no')
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
     * Persist the transaction to database
     *
     * @param array $inputs
     * @param User|null $user
     * @throws Exception
     */
    public function writeTransaction($inputs, $user = null)
    {
        $user = $user ?: authUser();
        
        DB::transaction(function () use ($inputs, $user) {
            $contract = Contract::whereId($inputs['contract_id'])->lockForUpdate()->first();
            
            $this->validateTimeSensitiveData($contract, $inputs);

            $type = StockReplacement::STOCK_REPLACEMENT;
            $transDate = date2sql($inputs['replace_date']);
            $transNo = MetaTransaction::getNextTransNo($type);
            $reference = MetaReference::getNext($type, null, $inputs['replace_date'], true);
            $oldMaidEndDate = date2sql($inputs['end_old_maid']);
            $newMaidStartDate = date2sql($inputs['start_new_maid']);
         
            StockReplacement::make([
                'type' => $type,
                'trans_no' => $transNo,
                'contract_id' => $contract->id,
                'tran_date' => $transDate,
                'reference' => $reference,
                'created_by' => $user->id
            ])->save();
            
            // maid that is being replaced
            StockMove::insert([
                'type' => $type,
                'trans_no' => $transNo,
                'stock_id' => $contract->stock_id,
                'contract_id' => $contract->id,
                'maid_id' => $contract->labour_id,
                'loc_code' => Location::DEFAULT,
                'tran_date' => $oldMaidEndDate,
                'price' => 0,
                'reference' => $reference,
                'qty' => 1,
                'standard_cost' => 0
            ]);
        
            // maid that will replace the old one
            StockMove::insert([
                'type' => $type,
                'trans_no' => $transNo,
                'stock_id' => $contract->stock_id,
                'contract_id' => $contract->id,
                'maid_id' => $inputs['new_labour_id'],
                'loc_code' => Location::DEFAULT,
                'tran_date' => $newMaidStartDate,
                'price' => 0,
                'reference' => $reference,
                'qty' => -1,
                'standard_cost' => 0
            ]);

            MetaReference::saveReference($type, $transNo, $reference);

            if ($inputs['memo']) {
                DB::table('0_comments')->insert([
                    'type' => $type,
                    'id' => $transNo,
                    'date_' => $transDate,
                    'memo_' => $inputs['memo']
                ]);
            }

            AuditTrail::insert([
                'type' => $type,
                'trans_no' => $transNo,
                'user' => $user->id,
                'description' => '',
                'gl_date' => $transDate,
                'gl_seq' => 0,
                'fiscal_year' => FiscalYear::whereRaw('? between `begin` and `end`', [$transDate])->value('id') ?: 0,
                'created_at' => date(DB_DATETIME_FORMAT)
            ]);

            // update contract with new maid
            Contract::where('id', $contract->id)->update([
                'labour_id' => $inputs['new_labour_id']
            ]);

            if (!empty($inputs['attachment'])) {
                $file = $inputs['attachment'];
                $uniqueName = Str::random(40);
                Attachment::insert([
                    'description' => $inputs['attachment_type'],
                    'type_no' => $type,
                    'trans_no' => $transNo,
                    'tran_date' => $transDate,
                    'unique_name' => $uniqueName,
                    'filename' => $file->getClientOriginalName(),
                    'filesize' => $file->getSize(),
                    'filetype' => $file->getMimeType()
                ]);
                $file->move(join_paths(dirname(base_path()), '/company/0/attachments'), $uniqueName);
            }
        });
    }

    /**
     * Check if the maid return is already requested or not
     *
     * @param array $inputs
     * @return boolean
     */
    public function isAlreadyRequested($inputs)
    {
        $filters = [
            'status' => 'Pending',
            'task_type' => TaskType::MAID_REPLACEMENT,
            'skip_authorisation' => true
        ];

        return TaskRecord::getBuilder($filters)
            ->where('task.data->contract_id', $inputs['contract_id'])
            ->exists();
    }

    /**
     * Validate the user inputs against maid return
     *
     * @param array $inputs
     * @return void
     */
    public function validateInputs($inputs = [])
    {
        $inputs = Validator::make($inputs, [
            'contract_id' => 'required|exists:0_labour_contracts,id',
            'new_labour_id' => 'required|exists:0_labours,id',
            'replace_date' => 'required|date_format:'.dateformat(),
            'end_old_maid' => 'required|date_format:'.dateformat(),
            'start_new_maid' => 'required|after:end_old_maid|date_format:'.dateformat(),
            'attachment' => 'nullable|file|max:2048',
            'attachment_type' => 'nullable|required_with:attachment|string',
            'memo' => 'nullable|string',
        ])->validate();

        return $inputs;
    }

    /**
     * Validate data that are sensitive to time
     *
     * @param Contract $contract
     * @param array $inputs
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function validateTimeSensitiveData(Contract $contract, $inputs)
    {
        $transDate = date2sql($inputs['replace_date']);
        $oldMaidEndDate = date2sql($inputs['end_old_maid']);
        $newMaidStartDate = date2sql($inputs['start_new_maid']);

        $this->validateContractIsEligibleForReplacement($contract);

        // Abort if the current maid against this contract was
        // dispatched after the inputted end_date of old maid
        abort_if(
            $contract->latestMaidDispatch->tran_date > $oldMaidEndDate,
            422,
            'The end date of old maid is invalid because the last maid dispatched was after the selected date'
        );

        // In case of hours or daily the dates can be pretty close
        abort_if(
            $contract->contract_from > Carbon::parse($transDate),
            422,
            'The replacement date is invalid. The contract has not begun yet.'
        );

        // In case of hours or daily the dates can be pretty close
        abort_if(
            $contract->contract_till < Carbon::parse($transDate),
            422,
            'The replacement date is invalid. The contract is expired.'
        );

        // Abort if the start date of new maid is after the contract expiry
        abort_if(
            $contract->contract_till < Carbon::parse($newMaidStartDate),
            422,
            'The start date of new maid is after the contract expiry.'
        );

        // in case of maid is not available
        abort_if(
            !(Labour::isValidInventoryUpdate($contract->labour_id, $oldMaidEndDate, 1)),
            422,
            'The return of old maid on the selected date will result in a scheduling conflict.'
        );

        // in case of maid is not available
        abort_if(
            !(Labour::isValidInventoryUpdate($inputs['new_labour_id'], $newMaidStartDate)),
            422,
            'The departure of new maid on the selected date is not valid or will result in a scheduling conflict.'
        );
    }

    /**
     * Validate that the Contract is eligible for replacement
     *
     * @param Contract $contract
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function validateContractIsEligibleForReplacement(Contract $contract)
    {
        // Abort if the contract is already voided/returned
        abort_if($contract->inactive, 422, 'This contract is already discontinued');

        // Abort if there is already a maid return against this contract
        abort_if($contract->maidReturn, 422, 'The maid against this contract is already returned');

        // Abort if the maid is not yet delivered to the client
        abort_if(empty($contract->delivery), 422, "The maid is not yet delivered against this contract.");

        // Abort if the maid is in StockCategory::DWD_PACKAGEONE
        abort_if(
            ($contract->maid->category == StockCategory::DWD_PACKAGEONE),
            422,
            "This maid cannot be replaced."
        );

        if ($contract->latestMaidDispatch->maid_id != $contract->labour_id) {
            // Somewhere along the way the system is broken. This shouldn't ever happen
            throw new UnexpectedValueException("The latest maid dispatch doesn't confirms to the maid in the contract");
        }
    }

    /**
     * The callback function to be called after being completed during the flow
     *
     * @param  \App\Models\TaskRecord  $taskRecord
     * @return void
     */
    public static function resolve(TaskRecord $taskRecord)
    {
        $instance = app(static::class);

        $data = $taskRecord->data;
        
        abort_if(
            !empty($data['attachment']) && !Storage::exists($data['attachment']),
            422,
            "Could not locate the attachment associated with this request"
        );

        if (!empty($data['attachment'])) {
            // Restore the uploaded file instance
            $data['attachment'] = new UploadedFile(
                Storage::path($data['attachment']),
                $data['attachment_name'],
                null,
                null,
                true
            );
        }

        $inputs = $instance->validateInputs($data);
        $instance->writeTransaction($inputs, User::find($taskRecord->initiated_by));
    }

    /**
     * The callback function to be called after being rejected during the flow
     *
     * @param  \App\Models\TaskRecord  $taskRecord
     * @return void
     */
    public static function reject(TaskRecord $taskRecord)
    {
        static::cancel($taskRecord);
    }

    /**
     * The callback function to be called after the flow was cancelled
     *
     * @param  \App\Models\TaskRecord  $taskRecord
     * @return void
     */
    public static function cancel(TaskRecord $taskRecord)
    {
        if (
            !empty($taskRecord->data['attachment'])
            && Storage::exists($taskRecord->data['attachment'])
        ) {
            Storage::delete($taskRecord->data['attachment']);
        }
    }

    /**
     * Returns the relevant data to be shown to public
     *
     * @param  \App\Models\TaskRecord  $taskRecord
     * @return array
     */
    public static function getDataForDisplay(TaskRecord $taskRecord): array
    {
        return Arr::only($taskRecord->data, [
            'Contract',
            'Sponsor',
            'Replacement Date',
            'Returning Maid',
            'End Old Date',
            'Replacing Maid',
            'Start New Maid'
        ]);
    }
}