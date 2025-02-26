<?php

// $path_to_root = "ERP";

// require_once $path_to_root . "/admin/db/company_db.inc";
namespace App\Http\Controllers\Labour;

use App\Http\Controllers\Controller;
use App\Models\Labour\Agent;
use App\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;

class AgentController extends Controller
{
    /**
     * Constructor function for this controller
     * 
     * Shares the common variable with the views
     */
    public function __construct()
    {
        View::share([
            'taxGroups' => DB::table('0_tax_groups')
                ->whereInactive('0')
                ->select('id', 'name')
                ->get()
        ]);
    }


    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        abort_unless($request->user()->hasPermission(Permissions::SA_AGENT_LIST), 403);

        return view('labours.agent.index', [
            'agents' => Agent::paginate()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        abort_unless($request->user()->hasPermission(Permissions::SA_CREATE_AGENT), 403);

        return view('labours.agent.create_agent', [
            'url' => route('agent.store'),
            'title' => 'New Agent',
            'method' => 'post',
            'inputs' => $this->getInputableFields()
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
        abort_unless($request->user()->hasPermission(Permissions::SA_CREATE_AGENT), 403);
        
        $validator = Validator::make($request->all(), $this->getValidationRules());

        if ($validator->fails()) {
            return response()->json(['data' =>  $validator->messages()], 422);
        }

        $inputs = $validator->valid();
        $inputs['contact'] = preg_replace(UAE_MOBILE_NO_PATTERN, '+971$2', $inputs['contact']);
        
        if (!empty($inputs['photo'])) {
            $inputs['photo'] = $inputs['photo']->store('docs/agents');
        }

        if (empty($inputs['address'])) {
            $inputs['address'] = 'N/A';
        }

        $agent = new Agent($inputs);
        $agent->payment_terms = DB::table('0_payment_terms')
            ->where('days_before_due', '>', '0')
            ->where('inactive', '0')
            ->orderBy('days_before_due', 'desc')
            ->take(1)
            ->value('terms_indicator');
        $agent->payable_account = pref('gl.purchase.creditors_act');
        $agent->payment_discount_account = pref('gl.purchase.pyt_discount_act');
        $agent->supp_address = '';
        $agent->notes = '';
        $agent->curr_code = 'AED';
        $agent->supplier_type = Agent::TYPE_AGENT;
        $agent->save();

        return response()->json(['data' => $agent->fresh(), 'message' => 'Agent created successfully'], 201);
    }

    /**
     * Get the validation rules for creating and updating agents
     *
     * @param string $ignoreId
     * @return array
     */
    public function getValidationRules($ignoreId = null)
    {
        return [
            'supp_name' => 'required|string',
            'supp_ref'  => ['required', 'string', Rule::unique('0_suppliers')->ignore($ignoreId, 'supplier_id')],
            'tax_group_id' => 'bail|required|integer|exists:0_tax_groups,id',
            'contact_person'  =>'required|string',
            'arabic_name' => 'nullable|string',
            'contact' => 'required',
            'email' => 'required|email',
            'photo' => 'nullable|file|mimes:png,jpg,jpeg|max:2048',
        ];
    }


    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Models\Labour\Agent $agent
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Agent $agent)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Models\Labour\Agent  $agent
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Agent $agent)
    {
        abort_unless($request->user()->hasPermission(Permissions::SA_CREATE_AGENT), 403);
        
        return view('labours.agent.create_agent', [
            'url' => route('agent.update' , $agent->supplier_id),
            'method' => 'put',
            'title' => 'Edit Agent',
            'inputs' => $agent->only(array_keys($this->getInputableFields()))
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Models\Labour\Agent  $agent
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Agent $agent)
    {
        abort_unless($request->user()->hasPermission(Permissions::SA_CREATE_AGENT), 403);
        
        $validator = Validator::make($request->except(['_method', '_token']), $this->getValidationRules($agent->getKey()));

        if ($validator->fails()) {
            return response()->json(['data' =>  $validator->messages()], 422);
        }

        $inputs = $validator->valid();
        $inputs['contact'] = preg_replace(UAE_MOBILE_NO_PATTERN, '+971$2', $inputs['contact']);

        if (!empty($inputs['photo'])) {
            if (Storage::exists($agent->photo)) {
                Storage::delete($agent->photo);
            }
            $inputs['photo'] = $inputs['photo']->store('docs/agents');
        }

        if (empty($inputs['address'])) {
            $inputs['address'] = 'N/A';
        }

        foreach($inputs as $key => $value) {
            $agent->{$key} = $value;
        }

        $agent->save();

        return response()->json(['data' => $agent->fresh(), 'message' => 'Agent updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Models\Labour\Agent  $agent
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Agent $agent)
    {
        //
    }

    /**
     * Return all the inputable fields
     *
     * @return array
     */ 
    public function getInputableFields()
    {
        return [
            'supp_name' => '',
            'supp_ref' => '',
            'tax_group_id' => '',
            'contact_person' => '',
            'arabic_name' => '',
            'contact' => '',
            'email' => '',
            'location' => '',
            'address' => '',
        ];
    }
}
