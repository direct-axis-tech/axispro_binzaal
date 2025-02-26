<?php

namespace App\Http\Controllers\Sales;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Accounting\LedgerTransaction;
use App\Models\Entity;
use App\Models\Sales\Customer;

class CustomersController extends Controller
{
    public function select2(Request $request) {
        $inputs = $request->validate([
            'term' => 'nullable',
            'page' => 'nullable|integer|min:1',
            'except' => 'nullable|array',
            'except.*' => 'integer',
            'account' => 'nullable|integer',
            'showInactive' => 'nullable|integer'
        ]);

        $pageLength = 25;
        $page = $inputs['page'] ?? 1;
        $builder = Customer::selectRaw("debtor_no as id, concat_ws(' - ', nullif(debtor_ref, ''), nullif(mobile, ''), nullif(name, '')) as text");

        if (!empty($inputs['term'])) {
            $q = "%{$inputs['term']}%";
            $q2 = "\\b{$inputs['term']}";
            $builder->where(function ($query) use ($q, $q2) {
                $query->where('debtor_ref', 'like', $q)
                    ->orWhere('mobile', 'like', $q)
                    ->orWhereRaw('`name` REGEXP ?', [$q2]);
            });
        }

        if (!empty($inputs['except'])) {
            $builder->whereNotIn('debtor_no', $inputs['except']);
        }

        if (!empty($inputs['account']) && $inputs['account'] != pref('axispro.customer_commission_payable_act')) {
            $builder->whereHas('branches', function ($query) use ($inputs) {
                $query->where('receivables_account', $inputs['account']);
            });
        }

        if (empty($inputs['showInactive'])) {
            $builder->active();
        }

        $totalFiltered = $builder->count();
        $results = $builder->orderBy('name')
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

    public function getCustomer(Request $request, Customer $customer) {
        $customer->append('formatted_name');
        return compact('customer');
    }

    public function commissionPayable(Customer $customer)
    {
        $balance = !$customer ? 0 : (
            LedgerTransaction::wherePersonId($customer->debtor_no)
                ->wherePersonTypeId(PT_CUSTOMER)
                ->whereAccount(pref('axispro.customer_commission_payable_act', -1))
                ->where('amount', '<>', 0)
                ->sum('amount') ?: 0
        );

        return compact('balance');
    }
}
