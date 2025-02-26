<?php

namespace App\Http\Controllers\Sales;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Sales\Customer;
use App\Models\Sales\Token as SalesToken;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;

class TokenController extends Controller
{

    /**
     * Store the token to the database
     */
    public function store(Request $request)
    {
        $inputs = $request->validate([
            'customer_mobile' => ['required', 'regex:'.UAE_MOBILE_NO_PATTERN],
            'token' => [
                'required',
                'string',
                Rule::unique('0_axis_front_desk', 'token')
                    ->where(function (Builder $query) {
                        $query->whereDate('created_at', date(DB_DATE_FORMAT));
                    })
            ],
            'customer_id' => 'bail|required|integer|exists:0_debtors_master,debtor_no',
            'display_customer' => 'required|string',
            'sub_customer_id' => 'required|integer',
            'contact_person' => 'nullable|string',
            'customer_email' => 'nullable|email',
            'customer_trn' => 'nullable|regex:/^100\d{12}$/',
            'customer_iban' => 'nullable|regex:/^AE\d{21}$/',
        ]);

        $inputs['customer_mobile'] = preg_replace(UAE_MOBILE_NO_PATTERN, '+971$2', $inputs['customer_mobile']);
        $inputs['created_by'] = $request->user()->id;

        $customer = Customer::find($inputs['customer_id']);
        if ($customer->debtor_no == Customer::WALK_IN_CUSTOMER && pref('axispro.auto_register_customer', 0)) {
            $customer = Customer::registerAutoCustomer([
                'name' => $inputs['display_customer'],
                'contact_person' => $inputs['contact_person'] ?? '',
                'mobile' => preg_replace(UAE_MOBILE_NO_PATTERN, '+971\2', $inputs['customer_mobile']),
                'email' => $inputs['customer_email'],
                'trn' => $inputs['customer_trn'] ?: '',
                'iban_no' => $inputs['customer_iban'] ?: ''
            ]);
            $inputs['customer_id'] = $customer->debtor_no;
        }

        // Update the details if specified
        if (empty($customer->iban_no) && !empty($inputs['customer_iban'])) {
            $customer->iban_no = $inputs['customer_iban'];
        }

        $customer->save();

        $token = SalesToken::create($inputs);

        return response()->json(["data" => $token], 201);
    }
}
