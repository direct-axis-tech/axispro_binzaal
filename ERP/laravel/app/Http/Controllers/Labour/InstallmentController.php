<?php

namespace App\Http\Controllers\Labour;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Labour\Contract;
use App\Permissions;
use Carbon\Carbon;
use App\Models\Labour\Installment;
use App\Models\Bank;
use App\Events\Labour\InstallmentCreated;
use App\Models\CalendarEvent;
use App\Models\CalendarEventType;
use App\Models\Labour\InstallmentDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class InstallmentController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Contract $contract)
    {
        $user = authUser();
        abort_unless($user->hasPermission(Permissions::SA_LBR_CONTRACT_INSTALLMENT), 403);

        $banks = Bank::all();
        return view('labours.contract.installment',[
            'title' => 'Convert Contract to Installment',
            'banks' => $banks,
            'contract' => $contract
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request     $request
     * @param  \App\Models\Labour\Contract  $contract
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Contract $contract)
    {
        $user = authUser();
        abort_unless($user->hasPermission(Permissions::SA_LBR_CONTRACT_INSTALLMENT), 403);
        
        $inputs = $this->validateInputs($request, __FUNCTION__);
       
        DB::transaction(function () use ($contract, $inputs, $user) {
            $contract->lockForUpdate()->first();

            $this->validateTimeSensitiveData($contract, $inputs);

            $data = [
                'contract_id' => $contract->id,
                'trans_date' => date2sql($inputs['transaction_date']),
                'person_type_id' => PT_CUSTOMER,
                'person_id' => $contract->debtor_no, 
                'total_amount' => $inputs['total_amount'],
                'no_installment' => $inputs['no_installment'],
                'interval' => $inputs['interval'],
                'interval_unit' => $inputs['interval_unit'],
                'installment_amount' => $inputs['installment_amount'],
                'bank_id' => $inputs['bank_id'],
                'payee_name' => $inputs['payee_name'],
                'start_date' => date2sql($inputs['start_date']),
                'initial_cheque_no' => $inputs['initial_cheque_no'],
                'created_by' => $user->id
            ];
            $instModel = new Installment($data);
            $instModel->save();
            $newInstId = $instModel->id;
    
            foreach ($inputs['details'] as $installment) {
                InstallmentDetail::create([
                    'installment_id' => $newInstId,
                    'installment_number' => $installment['installment_number'],
                    'due_date' => date2sql($installment['due_date']),
                    'payee_name' => $installment['payee_name'],
                    'bank_id' => $installment['bank_id'],
                    'cheque_no' => $installment['cheque_no'],
                    'amount' => $installment['amount'],
                ]);
            }

            Event::dispatch(new InstallmentCreated($instModel));
        });

        return response()->json(['message' => 'Installment Created Successfully'], 201);
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
        // Abort if already converted to installment
        abort_if(Installment::whereContractId($contract->id)->exists(), 422, "Installment against this contract already exists" );

        // Abort if the contract is already voided/returned
        abort_if($contract->inactive, 422, 'This contract is already discontinued');
        
        // Abort if any payment against maid
        abort_if($contract->payments()->exists(), 422, "Already Paid against this contract.");

        // Abort if the contract is already invoiced
        abort_if($contract->invoices()->exists(), 422, "The contract is already invoiced.");
    }

    /**
     * Validate the user inputs against installment
     *
     * @param \Illuminate\Http\Request $request
     * @param "details"|"store" $type
     * @return void
     */
    public function validateInputs($request, $type)
    {
        $rules = [
            'no_installment' => 'required|numeric|gt:0',
            'installment_amount' => 'required|numeric|gt:0',
            'bank_id' => 'bail|required|integer|exists:0_banks,id',
            'start_date' => 'required|date_format:'.dateformat(),
            'transaction_date' => 'required|date_format:'.dateformat(),
            'initial_cheque_no' => 'required|alpha_num',
            'interval' => 'required|integer',
            'interval_unit' => 'required|in:'.implode(',', array_keys(installment_interval_units())),
            'payee_name' => 'required|string|regex:/^[\pL\pM\pN_\- ]+$/u',
            'total_amount' => 'required|numeric'
        ];

        if ($type == 'store') {
            $rules += [
                "details" => [
                    'bail',
                    'required',
                    'array',
                    'min:1',
                    function ($attribute, $value, $fail) {
                        $noOfInstallments = request()->input('no_installment');
                        if (count($value) != $noOfInstallments) {
                            $fail("{$attribute} must have exactly the same size as No. of Installments: {$noOfInstallments}");
                        }
                    },
                ],
                "details.*.installment_number" => "required|integer",
                "details.*.payee_name"  => "required|string|regex:/^[\pL\pM\pN_\- ]+$/u",
                "details.*.due_date"  => "required|date_format:".dateformat(),
                "details.*.bank_id"  => "bail|required|integer|exists:0_banks,id",
                "details.*.cheque_no"  => "required|alpha_num",
                "details.*.amount"  => "required|numeric|gt:0",
            ];
        }

        $inputs = $request->validate(
            $rules,
            [
                'payee_name.regex' => "The Payee Name must only contains alphabets, numbers, dashes, underscore or spaces",
                'details.*.payee_name.regex' => "The Payee Name must only contains alphabets, numbers, dashes, underscore or spaces"
            ]
        );

        if ($type == 'store') {
            abort_if(
                (
                    round2(array_sum(array_column($inputs['details'], 'amount')), user_price_dec())
                    != round2($inputs['total_amount'], user_price_dec())
                ),
                422,
                "The sum of all installment amount exceeds the total contract value"
            );

            abort_if(
                (
                    array_column($inputs['details'], 'installment_number')
                    != range(1, count($inputs['details']))
                ),
                422,
                "The installments are not in order. Cannot proceed with the request"
            );
        }
        

        return $inputs;
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Models\Labour\Contract $contract
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Contract $contract)
    {
        $contract->load('customer', 'maid', 'stock', 'category');

        return response()->json(compact('contract'));
    }

    /**
     * Generate and respond with the details
     *
     * @param Request $request
     * @return void
     */
    public function details(Request $request) {
        abort_unless(authUser()->hasPermission(Permissions::SA_LBR_CONTRACT_INSTALLMENT), 403);

        $inputs = $this->validateInputs($request, __FUNCTION__);

        $installments = $this->generateInstallments($inputs);

        return response()->json(compact('installments'));
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Labour\Installment $installment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Installment $installment)
    {
        abort_unless(authUser()->hasPermission(Permissions::SA_INSTALLMENT_DELETE), 403);

        abort_if($installment->is_used, 422, "Installment already invoiced.");

        $installment->delete();
        InstallmentDetail::whereInstallmentId($installment->id)->delete();
        CalendarEvent::whereTypeId(CalendarEventType::INSTALLMENT_REMINDER)
            ->whereJsonContains('context->contract_id', $installment->contract_id)
            ->delete();

        return response()->json(['message' => 'Installment Deleted Successfully']);
    }

    /**
     * Generates the installments
     *
     * @param array $inputs
     * @return array
     */
    public function generateInstallments($inputs = [])
    {
        $startDate = date2sql($inputs['start_date']);
        $entry_date = date2sql($inputs['transaction_date']);
        $numberOfInstallments = $inputs['no_installment'];
        $initialChequeNumber = $inputs['initial_cheque_no'];
        $intervalUnit = $inputs['interval_unit'];
        $interval = $inputs['interval'];
        $totalAmount = $inputs['total_amount'];
        $startDate = Carbon::parse($startDate); 
        $installmentAmount = $inputs['installment_amount'];
        $installments = [];
        $installmentSum = 0;

        for ($i = 0; $i < $numberOfInstallments; $i++) {
            $installmentDate = $startDate->copy()->add($i * $interval, $intervalUnit);
            
            $chequeNo = preg_replace_callback('/(\d+)/', function ($matches) use ($i) {
                return str_pad(intval($matches[1]) + $i, strlen($matches[1]), '0', STR_PAD_LEFT);
            }, $initialChequeNumber);

            $remainingAmount = $totalAmount - ($installmentAmount * $i);
            if ($remainingAmount < $installmentAmount) {
                $installmentAmount = $remainingAmount;
            }

            if ($installmentAmount == 0)
                abort(422, 'The sum of installment amount exceeds the total contract value. Please adjust the installment amount.');
            
            $installment = [
                'installment_number' => $i+1,
                'payee_name' => $inputs['payee_name'],
                'bank_id' => $inputs['bank_id'],
                'due_date' => sql2date($installmentDate->toDateString()),
                'entry_date' => sql2date($entry_date),
                'cheque_no' => $chequeNo,
                'amount' => $installmentAmount,
            ];
            $installmentSum += $installmentAmount;

            $installments[] = $installment;  
        }

        // abort_if($contract->contract_till < Carbon::parse(sql2date($installmentDate->toDateString())), 422, 'The installment date exceeds the contract period. Please adjust the inputs.');

        if ($installmentSum != $totalAmount) 
            abort(422, 'Sum of provided installment amounts does not match the total amount. Please adjust the inputs.');
        
        return $installments;

    }
}