<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Accounting\JournalTransaction;
use App\Models\Accounting\Ledger;
use App\Models\Entity;
use App\Models\Hr\Employee;
use App\Models\Hr\PayElement;
use App\Models\Hr\EmployeeRewardsDeductions;
use App\Models\Hr\EmployeeRewardDeductionsDetails;
use App\Models\Hr\SubElement;
use App\Models\System\User;
use App\Models\TaskType;
use App\Models\Workflow;
use App\Permissions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateInterval;
use DatePeriod;
use DateTime;
use items_cart;
use Yajra\DataTables\QueryDataTable;

class EmployeeRewardsDeductionsController extends Controller
{
    protected $deductionTypeIds;
    protected $allowanceTypeIds;
    
    public function __construct()
    {
        $this->deductionTypeIds = [
            'violations' => pref('hr.violations_el'),
            'loan_recovery' => pref('hr.loan_recovery_el')
        ];

        $this->allowanceTypeIds = [
            'rewards_bonus' => pref('hr.rewards_bonus_el')
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        abort_unless(authUser()->hasAnyPermission(
            Permissions::HRM_MANAGE_DEDUCTION,
            Permissions::HRM_MANAGE_REWARDS,
            Permissions::HRM_MANAGE_DEDUCTION_ADMIN,
            Permissions::HRM_MANAGE_REWARDS_ADMIN
        ), 403);

        $employees = Employee::active()->orderBy('name')->get();
        $payElementTypes = pay_element_types();

        if(authUser()->hasAnyPermission(Permissions::HRM_MANAGE_DEDUCTION, Permissions::HRM_MANAGE_DEDUCTION_ADMIN)) {
            $elementTypes[PayElement::TYPE_DEDUCTION] = $payElementTypes[PayElement::TYPE_DEDUCTION];
        }

        if(authUser()->hasAnyPermission(Permissions::HRM_MANAGE_REWARDS, Permissions::HRM_MANAGE_REWARDS_ADMIN)) {
            $elementTypes[PayElement::TYPE_ALLOWANCE] = $payElementTypes[PayElement::TYPE_ALLOWANCE];
        }

        $deductionElements = PayElement::whereIn('id', $this->deductionTypeIds)->get();
        $allowanceElements = PayElement::whereIn('id', $this->allowanceTypeIds)->get();
        $deductionSubTypes = SubElement::getDeductionSubElements();
        $allowanceSubTypes = SubElement::getAllowanceSubElements();
        $ledgerAccounts    = $bankAccounts = collect();
        if(pref('hr.auto_journal_deduction_entry')) {
            $ledgerAccounts =   Ledger::select('0_chart_master.account_code', 
                                                '0_chart_master.account_name', 
                                                '0_chart_master.account_type', 
                                                '0_bank_accounts.id AS bank_id', 
                                                '0_bank_accounts.bank_account_name'
                                )
                                ->leftJoin('0_bank_accounts', '0_bank_accounts.account_code', '0_chart_master.account_code')
                                ->where('0_chart_master.inactive', 0)
                                ->get();

            $bankAccounts = $ledgerAccounts->where('bank_id')->values();
        }

        return view('hr.rewardDeductions', 
            compact(
                'employees', 
                'elementTypes', 
                'deductionElements', 
                'allowanceElements', 
                'deductionSubTypes', 
                'allowanceSubTypes',
                'ledgerAccounts',
                'bankAccounts'
            )
        );
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
            Permissions::HRM_MANAGE_DEDUCTION,
            Permissions::HRM_MANAGE_REWARDS,
            Permissions::HRM_MANAGE_DEDUCTION_ADMIN,
            Permissions::HRM_MANAGE_REWARDS_ADMIN
        ), 403);

        $inputs = $this->getValidatedInputs($request);

        if ($inputs['element_type'] == PayElement::TYPE_DEDUCTION) {
            abort_unless(authUser()->hasAnyPermission(
                Permissions::HRM_MANAGE_DEDUCTION,
                Permissions::HRM_MANAGE_DEDUCTION_ADMIN
            ), 403);

            $canOnlyAccessThroughWorkflow = authUser()->doesntHavePermission(Permissions::HRM_MANAGE_DEDUCTION_ADMIN);
        }

        if($inputs['element_type'] == PayElement::TYPE_ALLOWANCE) {
            abort_unless(authUser()->hasAnyPermission(
                Permissions::HRM_MANAGE_REWARDS,
                Permissions::HRM_MANAGE_REWARDS_ADMIN
            ), 403);

            $canOnlyAccessThroughWorkflow = authUser()->doesntHavePermission(Permissions::HRM_MANAGE_REWARDS_ADMIN);
        }

        $rewardDeduction = new EmployeeRewardsDeductions($inputs);
        unset($rewardDeduction->gl_account );
        $rewardDeduction->save();

        $details = $this->generateInstallments($rewardDeduction);

        abort_if(
            isPayslipProcessed(
                $inputs['employee_id'],
                $inputs['effective_date'],
                data_get(last($details), 'installment_date')
            ),
            422,
            'Payroll has already been generated for the selected date range'
        );

        $rewardDeduction->details()->saveMany($details);

        #Workflow
        if ($canOnlyAccessThroughWorkflow) {
            $applicant = User::whereType(Entity::EMPLOYEE)->whereEmployeeId($inputs['employee_id'])->first();
            abort_unless($applicant, 422, 'The selected applicant does not have an assigned user');

            $workflow = Workflow::findByTaskType(TaskType::EMP_DEDUCTION_REWARDS, $applicant);
            abort_unless($workflow, 422, 'Could not find any workflow defined for the applicant');

            $data = [
                'request_id'       => $rewardDeduction->id,
                'Type'             => $inputs['element_type'] == PayElement::TYPE_ALLOWANCE ? 'Allowance' : 'Deduction',
                'Element'          => data_get(PayElement::find($inputs['element']), 'name'),
                'Total Amount'     => $inputs['amount'],
                'No. Installments' => $inputs['number_of_installments'],
                'Effective From'   => (new DateTime($inputs['effective_date']))->format(dateformat()),
                'Remarks'          => $inputs['remarks'],
            ];
            $workflow->initiate($data, $applicant);

            $rewardDeduction->request_status = EmployeeRewardsDeductions::PENDING;
            $rewardDeduction->save();
            
        } else {
            $rewardDeduction->request_status = EmployeeRewardsDeductions::APPROVED;
            $rewardDeduction->save();
        }

        if(pref('hr.auto_journal_deduction_entry') && 
           $inputs['element_type'] == PayElement::TYPE_DEDUCTION &&
           $inputs['amount'] > 0
           ) {
            $transNo = self::processDeductionsGL($inputs);
            $journalTransaction = JournalTransaction::where('trans_no', $transNo)
                ->where('type', ST_JOURNAL)
                ->first(['reference']);
            $rewardDeduction->trans_no  = $transNo;
            $rewardDeduction->reference = $journalTransaction ? $journalTransaction->reference : '';
            $rewardDeduction->save();
        }

        return response()->json([
            'message' => 'Created Successfully.'
        ], Response::HTTP_CREATED);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Hr\EmployeeRewardsDeductions $empRewardDeduction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EmployeeRewardsDeductions $empRewardDeduction)
    {
        abort_unless(false && authUser()->hasAnyPermission(
            Permissions::HRM_MANAGE_DEDUCTION,
            Permissions::HRM_MANAGE_REWARDS,
            Permissions::HRM_MANAGE_DEDUCTION_ADMIN,
            Permissions::HRM_MANAGE_REWARDS_ADMIN
        ), 403);

        abort_if(
            isPayslipProcessed(
                $empRewardDeduction->employee_id,
                $empRewardDeduction->effective_date,
                $empRewardDeduction->details()->orderByDesc('installment_date')->value('installment_date')
            ),
            422,
            'Payroll has already been generated for the selected date range'
        );

        $inputs = $this->getValidatedInputs($request);

        $empRewardDeduction->update($inputs);

        $details = $this->generateInstallments($empRewardDeduction);
        
        abort_if(
            isPayslipProcessed(
                $inputs['employee_id'],
                $inputs['effective_date'],
                data_get(last($details), 'installment_date')
            ),
            422,
            'Payroll has already been generated for the selected date range'
        );

        EmployeeRewardDeductionsDetails::where('reward_deduction_id', $empRewardDeduction->id)->delete();
        $empRewardDeduction->details()->saveMany($details);

        return response()->json([
            'message' => 'Updated Successfully.'
        ], Response::HTTP_OK);

    }

    /**
     * Generate the installments from empRewardDeduction
     *
     * @param EmployeeRewardsDeductions $empRewardDeduction
     * @return EmployeeRewardDeductionsDetails[]
     */
    public function generateInstallments(EmployeeRewardsDeductions $empRewardDeduction)
    {
        $amount = $empRewardDeduction->amount;
        $installmentAmount = $empRewardDeduction->number_of_installments
            ? ($amount / $empRewardDeduction->number_of_installments)
            : $amount;
        $installmentAmount = round2($installmentAmount, user_price_dec());
        $date = CarbonImmutable::parse($empRewardDeduction->effective_date);

        $details = [];
        for ($i = 0; $i < $empRewardDeduction->number_of_installments; $i++) {
            $detail = EmployeeRewardDeductionsDetails::make([
                "reward_deduction_id" => $empRewardDeduction->id,
                "installment_date" => $date->addMonthsWithoutOverflow($i)->format(DB_DATE_FORMAT),
                "installment_amount" => $installmentAmount,
            ]);

            $amount -= $installmentAmount;
            $details[] = $detail;
        }

        // If there are rounding errors, adjust it to the last installment
        if ($amount != 0) {
            $details[count($details) - 1]->installment_amount += $amount;
        }

        return $details;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Hr\EmployeeRewardsDeductions $empRewardDeduction
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmployeeRewardsDeductions $empRewardDeduction)
    {
        abort_unless(authUser()->hasAnyPermission(
            Permissions::HRM_MANAGE_DEDUCTION,
            Permissions::HRM_MANAGE_REWARDS,
            Permissions::HRM_MANAGE_DEDUCTION_ADMIN,
            Permissions::HRM_MANAGE_REWARDS_ADMIN
        ), 403);

        abort_if(
            isPayslipProcessed(
                $empRewardDeduction->employee_id,
                $empRewardDeduction->effective_date,
                $empRewardDeduction->details()->orderByDesc('installment_date')->value('installment_date'),
            ),
            422,
            'Payroll has already been generated for the selected date range'
        );

        $empRewardDeduction->update(['inactive' => true]);

        return response()->json(['message' => 'Deleted Successfully']);
    }

    /**
     * Validate the inputs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function getValidatedInputs(Request $request)
    {
        $validationRules = [
            'employee_id'    => 'required|exists:0_employees,id',
            'element_type'   => 'required|in:' . implode(',', [PayElement::TYPE_ALLOWANCE, PayElement::TYPE_DEDUCTION]),
            'amount'         => 'required|numeric',
            'effective_date' => 'required|date_format:'.dateformat(),
            'document_date'  => 'required|date_format:'.dateformat(),
            'remarks'        => 'required|string|max:255',
        ];

        if ($request->input('element_type') == PayElement::TYPE_ALLOWANCE) {
            $validationRules['allowance_element'] = 'required';
            $validationRules['allowance_sub_element'] = 'required';
        }

        if ($request->input('element_type') == PayElement::TYPE_DEDUCTION) {
            $validationRules['deduction_element'] = 'required';
            $validationRules['deduction_sub_element'] = 'required';
            $validationRules['number_of_installments']  = 'required|integer|min:1|max:5';
        }

        if(pref('hr.auto_journal_deduction_entry')) {
            $validationRules['gl_account'] = 'required_if:element_type,' . PayElement::TYPE_DEDUCTION;
        }
    
        $inputs = $request->validate($validationRules);
    
        if ($request->input('element_type') == PayElement::TYPE_ALLOWANCE) {
            $inputs['number_of_installments'] = 1;
            $inputs['element'] = $inputs['allowance_element'];
            $inputs['sub_element'] = $inputs['allowance_sub_element'];
        }

        if ($request->input('element_type') == PayElement::TYPE_DEDUCTION) {
            $inputs['element'] = $inputs['deduction_element'];
            $inputs['sub_element'] = $inputs['deduction_sub_element'];
        }

        $inputs['effective_date'] = date2sql($inputs['effective_date']);
        $inputs['document_date'] = date2sql($inputs['document_date']);

        return Arr::only($inputs, [
            'employee_id',
            'element_type',
            'element',
            'sub_element',
            'amount',
            'effective_date',
            'document_date',
            'number_of_installments',
            'remarks',
            'gl_account'
        ]);
    }
    
    /**
     * Returns the dataTable api for this resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function dataTable()
    {
        abort_unless(authUser()->hasAnyPermission(
            Permissions::HRM_MANAGE_DEDUCTION,
            Permissions::HRM_MANAGE_REWARDS,
            Permissions::HRM_MANAGE_DEDUCTION_ADMIN,
            Permissions::HRM_MANAGE_REWARDS_ADMIN
        ), 403);

        $builder = EmployeeRewardsDeductions::select(
            '0_emp_reward_deductions.*', 
            '0_employees.name as employee_name', '0_pay_elements.name as pay_element', '0_sub_elements.name as sub_element_name',
            DB::raw("CASE 
                        WHEN element_type = " . PayElement::TYPE_ALLOWANCE . " THEN 'Reward' 
                        WHEN element_type = " . PayElement::TYPE_DEDUCTION . " THEN 'Deduction' 
                        ELSE 'Unknown' 
                    END as type"),
            DB::raw("CASE 
                        WHEN element_type = " . PayElement::TYPE_ALLOWANCE . " THEN element
                        ELSE NULL
                    END as allowance_element"),
            DB::raw("CASE 
                        WHEN element_type = " . PayElement::TYPE_DEDUCTION . " THEN element
                        ELSE NULL
                    END as deduction_element"),
            DB::raw("CASE 
                        WHEN element_type = " . PayElement::TYPE_ALLOWANCE . " THEN sub_element
                        ELSE NULL
                    END as allowance_sub_element"),
            DB::raw("CASE 
                        WHEN element_type = " . PayElement::TYPE_DEDUCTION . " THEN sub_element
                        ELSE NULL
                    END as deduction_sub_element")
        )
        ->join('0_employees', '0_emp_reward_deductions.employee_id', '=', '0_employees.id')
        ->leftJoin('0_pay_elements', '0_pay_elements.id', '=', '0_emp_reward_deductions.element')
        ->leftJoin('0_sub_elements', '0_sub_elements.id', '=', '0_emp_reward_deductions.sub_element')
        ->where('0_emp_reward_deductions.inactive','0')
        ->orderBy('0_emp_reward_deductions.effective_date', 'desc');

        $dataTable = (new QueryDataTable(DB::query()->fromSub($builder, 't')))
            ->addColumn('_is_voided', function($row) {
                if (!empty($row->trans_no) && $this->isNotVoided($row->trans_no)) {
                    return false;
                }
                return true;
            });

        return $dataTable->toJson();

    }

    /**
     * Display a listing of the resource .
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function getIssuedDeductionRewards(Request $request)
    {

        abort_unless($request->user()->hasAnyPermission(
            Permissions::HRM_EMP_DEDUCTION_REWARD,
            Permissions::HRM_EMP_DEDUCTION_REWARD_OWN
        ), 403);

        $canOnlyAccessOwn = $request->user()->doesntHavePermission(Permissions::HRM_EMP_DEDUCTION_REWARD);
        $employees = $canOnlyAccessOwn ? collect([$request->user()->employee]) : Employee::active()->get();
        $elementTypes = pay_element_types();
        $subElements = SubElement::active()->orderBy('seq_no')->get();

        $builder = EmployeeRewardsDeductions::select(
                '0_emp_reward_deductions.*', 
                '0_employees.name as employee_name', '0_pay_elements.name as pay_element', '0_sub_elements.name as sub_element_name',
                '0_emp_reward_deductions_details.installment_amount',
                DB::raw("CASE 
                            WHEN element_type = " . PayElement::TYPE_ALLOWANCE . " THEN 'Reward' 
                            WHEN element_type = " . PayElement::TYPE_DEDUCTION . " THEN 'Deduction' 
                            ELSE 'Unknown' 
                        END as type"
                    ),
                DB::raw("SUM(
                            CASE 
                                WHEN 0_emp_reward_deductions_details.payslip_id IS NOT NULL
                                    THEN 0_emp_reward_deductions_details.processed_amount 
                                ELSE 0
                            END
                        ) AS processed_amount"
                    )
            )
            ->leftjoin('0_emp_reward_deductions_details', '0_emp_reward_deductions_details.reward_deduction_id', '=', '0_emp_reward_deductions.id')
            ->join('0_employees', '0_emp_reward_deductions.employee_id', '=', '0_employees.id')
            ->leftJoin('0_pay_elements', '0_pay_elements.id', '=', '0_emp_reward_deductions.element')
            ->leftJoin('0_sub_elements', '0_sub_elements.id', '=', '0_emp_reward_deductions.sub_element')
            ->where('0_emp_reward_deductions.inactive','0')
            ->where('0_emp_reward_deductions.request_status', EmployeeRewardsDeductions::APPROVED)
            ->groupBy('0_emp_reward_deductions.id')
            ->orderBy('0_emp_reward_deductions.effective_date', 'desc');

        if ($request->input('employee_id')) {
            $builder = $builder->where('0_emp_reward_deductions.employee_id', $request->employee_id);
        }
        
        if ($request->input('element_type')) {
            $builder = $builder->where('element_type', $request->element_type);
        }

        if ($request->input('sub_element')) {
            $builder = $builder->where('sub_element', $request->sub_element);
        }

        if ($request->input('effective_date_from')) {
            $builder = $builder->where('effective_date', '>=', date2sql($request->effective_date_from));
        }

        if ($request->input('effective_date_to')) {
            $builder = $builder->where('effective_date', '<=', date2sql($request->effective_date_to));
        }

        if ($canOnlyAccessOwn) {
            $builder = $builder->where('0_emp_reward_deductions.employee_id', data_get($request->user()->employee, 'id'));
        }

        $resultList = $builder->paginate(15);
        $userInputs = $request->input();
            
        return view('hr.listRewardDeductions', compact('resultList', 'employees', 'elementTypes', 'subElements', 'userInputs'));
    }

    /**
     * Process Journal Entries
     *
     * @param  mixed $rewardDeduction
     * @return void
     */
    public function processDeductionsGL($rewardDeduction)
    {
        global $Refs;
        $transAmount = 0;
    
        begin_transaction();
        $date_ = (new DateTime($rewardDeduction['effective_date']))->format(dateformat()) ?? Today();
        if (!is_date_in_fiscalyear($date_)) {
            $date_ = begin_fiscalyear(); 
        }

        $transAmount = $rewardDeduction['amount'];
        $ledger      = $rewardDeduction['gl_account'];
        $payElement  = PayElement::find($rewardDeduction['element']);
        $elementAccount = data_get($payElement, 'account_code');
        $element    = data_get($payElement, 'name');
        $subElement = data_get(SubElement::find($rewardDeduction['sub_element']), 'name');

        $cart = new items_cart(ST_JOURNAL);
        $cart->tran_date = $cart->doc_date = $cart->event_date = $date_;
        $cart->reference = $Refs->get_next(ST_JOURNAL, null, $cart->tran_date, true);

        $cart->add_gl_item($elementAccount, 0, 0, $transAmount, '', null, $rewardDeduction['employee_id'], $date_);

        $cart->add_gl_item($ledger, 0, 0, -($transAmount), '', null, null, $date_);

        $cart->memo_ = sprintf(
            _("Employee Deductions Applied of %s for %s on %s"),
            number_format2($transAmount, user_price_dec()),
            $element. ' : ' .$subElement,
            $date_
        );

        $transNo = write_journal_entries($cart);
        commit_transaction();

        return $transNo;
    }
    
    /**
     * isNotVoided
     *
     * @param  mixed $transNo
     * @return void
     */
    private function isNotVoided($transNo) {
        $isVoided = DB::table('0_voided')
            ->where('type', JournalTransaction::JOURNAL)
            ->where('id', $transNo)
            ->exists();
        return !$isVoided;
    }

}
