<?php

namespace App\Http\Controllers\Hr;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Accounting\Ledger;
use App\Models\Hr\PayElement;
use App\Permissions;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\QueryDataTable;

class PayElementsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        abort_unless(authUser()->hasPermission(Permissions::HRM_MANAGE_PAY_ELEMENTS), 403);
        
        $accounts = Ledger::all();
        return view('hr.payElements', compact('accounts'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        abort_unless(authUser()->hasPermission(Permissions::HRM_MANAGE_PAY_ELEMENTS), 403);

        $inputs = $request->validate(...$this->validationArgs());
        $inputs['is_fixed'] = !empty($inputs['is_fixed']);

        $payElement = PayElement::create($inputs);

        return response()->json([
            'message' => "Pay Element Created Successfully",
            'data' => $payElement
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Hr\PayElement $payElement
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PayElement $payElement)
    {
        abort_unless(authUser()->hasPermission(Permissions::HRM_MANAGE_PAY_ELEMENTS), 403);

        $inputs = $request->validate(...$this->validationArgs($payElement->id));

        if (!$payElement->is_used) {
            $payElement->name = $inputs['name'];
            $payElement->type = $inputs['type'];
            $payElement->is_fixed = !empty($inputs['is_fixed']);
        }
        $payElement->account_code = $inputs['account_code'] ?? null;

        if ($payElement->id == pref('hr.commission_el')) {
            $payElement->account_code = null;
        }

        $payElement->save();
        
        return response()->json(['message' => "Pay Element Updated Successfully"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Hr\PayElement $payElement
     * @return \Illuminate\Http\Response
     */
    public function destroy(PayElement $payElement)
    {
        abort_unless(authUser()->hasPermission(Permissions::HRM_MANAGE_PAY_ELEMENTS), 403);

        abort_if($payElement->is_used, 422, "This pay element is already in use");

        $payElement->delete();

        return response()->json(['message' => 'Pay Element Deleted Successfully']);
    }

    /**
     * Returns the dataTable api for this resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function dataTable()
    {
        abort_unless(authUser()->hasPermission(Permissions::HRM_MANAGE_PAY_ELEMENTS), 403);

        $builder = DB::table('0_pay_elements as elem')
            ->leftJoin('0_chart_master as account', function (JoinClause $join) {
                $join->whereRaw('if(elem.id = ?, ?, elem.account_code) = account.account_code', [
                    pref('hr.commission_el', -1),
                    pref('axispro.emp_commission_payable_act', -1)
                ]);
            })
            ->select("elem.*")
            ->selectRaw("concat(account.account_code, ' - ', account.account_name) as account_name")
            ->selectRaw(PayElement::isUsedQuery('elem.id')." as is_used")
            ->selectRaw(
                "case"
                    ." when elem.type = 1 then 'Allowance'"
                    ." when elem.type = -1 then 'Deduction'"
                . "end as type_name"
            )
            ->selectRaw("if(elem.is_fixed, 'Yes', 'No') as is_fixed_label");

        $dataTable = new QueryDataTable(DB::query()->fromSub($builder, 't'));
        
        return $dataTable->toJson();
    }

    /**
     * Returns the validation rules for adding and editing
     *
     * @param string $ignoreId The id to ignore when checking for uniqueness
     * @return array
     */
    public function validationArgs($ignoreId = null) {
        $accountCodeRules = ["bail", "nullable", "alpha_num", "exists:0_chart_master,account_code"];
        $subledgerElements = Arr::only(pref('hr'), Arr::except(subledger_elements(), ['commission_el']));

        if (in_array($ignoreId, $subledgerElements)) {
            $accountCodeRules[] = Rule::unique('0_pay_elements', 'account_code')->ignore($ignoreId);

            // there must not be any gl transactions in this account
            if (
                !empty($accountCode = request()->input('account_code'))
                && PayElement::find($ignoreId)->account_code != $accountCode
            ) {
                $accountCodeRules[] = Rule::unique('0_gl_trans', 'account')->whereNot('amount', 0);
            }
        }
        
        else {
            $accountCodeRules[] = Rule::unique('0_pay_elements', 'account_code')->whereIn('id', array_filter($subledgerElements));
        } 
        
        return [
            [
                'name' => ["bail", "required", "regex:/^[\pL\pM\pN_\- ]+$/u", Rule::unique('0_pay_elements', 'name')->ignore($ignoreId)],
                'type' => "required|in:1,-1",
                'is_fixed' => 'nullable|boolean',
                'account_code' => $accountCodeRules
            ],
            [
                'name.regex' => 'The name must only contains alphabets, numbers, dashes, underscore or spaces'
            ]
        ];
    }
}
