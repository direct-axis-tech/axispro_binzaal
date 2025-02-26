<?php

namespace App\Http\Controllers\Sales;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Accounting\Dimension;
use App\Models\Sales\AutofetchedTransaction;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\JoinClause;

class AutofetchController extends Controller
{
    /**
     * Returns all the pending applications
     *
     * @param Request $request
     * @return array
     */
    public function pending(Request $request, $systemId)
    {
        $request->validate([
            'from' => 'nullable|in:invoice,srq',
            'dimension' => 'bail|required|integer|exists:0_dimensions,id'
        ]);

        $dimension = Dimension::find($request->input('dimension'));

        $query = DB::table('0_autofetched_trans AS autofetch')
            ->select('autofetch.*')
            ->leftJoin('0_debtor_trans_details AS trans', function (JoinClause $join) {
                $join->on('autofetch.application_id', 'trans.application_id')
                ->where('trans.quantity', '!=' , 0);
            })
            ->leftJoin('0_service_request_items AS item', function (JoinClause $join) {
                $join->on('autofetch.application_id', 'item.application_id')
                ->where('item.qty', '!=' , 0);
            })
            ->whereIn('webuser_id', [authUser()->webuser_id, authUser()->imm_webuser_id])
            ->whereNull('trans.application_id')
            ->whereNull('item.application_id');
        
        $visibility_age = 2;
        switch ($dimension->center_type) {
            case CENTER_TYPES['TASHEEL']:
            case CENTER_TYPES['TAWJEEH']:
            case CENTER_TYPES['TADBEER']:
                $query->where('type', AIT_TASHEEL);
                $visibility_age = pref('autofetch.tasheel.ts_pending_txn_max_visibility_age');
                break;
            case CENTER_TYPES['AMER']:
            case CENTER_TYPES['OTHER']:
            case CENTER_TYPES['TYPING']:
                $query->whereIn('type', [AIT_IMM_PRE_DXB, AIT_IMM_POST_DXB]);
                $visibility_age = pref('autofetch.immigration.imm_pending_txn_max_visibility_age');
                break;
        }

        if ($visibility_age) {
            $query->where('autofetch.created_at', '>', Carbon::today()->subDays($visibility_age));
        }

        return ['data' => $query->get()];
    }

    /**
     * Store the auto fetched content to database
     *
     * @param Request $request
     * @return 
     */
    public function store(Request $request)
    {
        $inputs = $request->validate([
            'system_id' => 'bail|required|alpha_dash',
            'type'=> 'required|in:TASHEEL,IMM_PRE_DXB',
            'service_en' => 'required|string',
            'service_ar' => 'nullable|string',
            'service_chg' => 'required|numeric',
            'processing_chg' => 'nullable|numeric',
            'total' => 'required|numeric',
            'transaction_id' => 'required_if:type,TASHEEL|nullable|string',
            'application_id' => 'bail|required|alpha_num|unique:0_autofetched_trans',
            'company' => 'nullable|string',
            'company_mol_id' => 'nullable|string',
            'contact_name' => 'nullable|string',
            'contact_no' => ['nullable', "regex:".UAE_MOBILE_NO_PATTERN],
            'web_user' => 'required|string',
            'webuser_id' => 'required_if:type,IMM_PRE_DXB|nullable|string',
        ]);

        if (empty($inputs['webuser_id'])) {
            $inputs['webuser_id'] = explode(" - ", $request->web_user)[1] ?? null;
        }

        $transaction = AutofetchedTransaction::create($inputs);

        return response()->json(['data' => $transaction], 201);
    }

    /**
     * Route to handle completed auto fetched applicationId
     *
     * @param Request $request
     * @return 
     */
    public function completed(Request $request, $applicationId)
    {
        $now = date(DB_DATETIME_FORMAT);
        $inputs = $request->validate([
            'system_id' => 'bail|required|alpha_dash',
            'type'=> 'required|in:IMM_POST_DXB',
            'service_en' => 'required|string',
            'service_chg' => 'required|numeric',
            'application_id' => 'required|alpha_num',
            'amount_paid' => 'required|numeric',
            'contact_name' => 'nullable|string',
            'web_user' => 'required|string',
            'webuser_id' => 'required|string',
        ]);

        if ($transaction = AutofetchedTransaction::whereApplicationId($applicationId)->first()) {
            $transaction->service_chg = $transaction->total - $inputs['amount_paid'];
            $transaction->amount_paid = $inputs['amount_paid'];
            $transaction->contact_name = $inputs['contact_name'];
            $transaction->paid_at = $now;
            $transaction->save();
        }

        else {
            $transaction = AutofetchedTransaction::create(array_merge(
                $inputs,
                [
                    'amount_paid' => $inputs['amount_paid'],
                    'total' => $inputs['amount_paid'] + $inputs['service_chg'],
                    'paid_at' => $now
                ]
            ));
        }
        
        return response()->json(['data' => $transaction->refresh()], 200);
    }
}
