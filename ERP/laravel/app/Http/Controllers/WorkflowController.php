<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\EntityGroup;
use App\Models\EntityGroupCategory;
use App\Models\TaskState;
use App\Models\TaskType;
use App\Models\Workflow;
use App\Models\WorkflowDefinition;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Models\Entity;
use App\Models\SpecialEntities;
use App\Models\System\User;
use App\Models\Hr\Employee;
use App\Models\System\AccessRole;

class WorkflowController extends Controller
{
    public function index()
    {
        $user = authUser();
        return view('system.workflow', [
            'entityTypes' => Entity::whereIn('id', [Entity::USER, Entity::GROUP, Entity::SPECIAL_GROUP, Entity::ACCESS_ROLE])->get(),
            'flowGroups' => EntityGroup::whereCategory(EntityGroupCategory::WORK_FLOW_RELATED)->get(),
            'specialGroups' => SpecialEntities::all(),
            'taskTypes' => TaskType::all()->filter(function ($type) use ($user) {
                return $user->hasPermission($type->module_permission);
            }),
            'taskStates' => TaskState::all(),
            'users' => DB::table('0_users')
                ->select(
                    'id',
                    DB::raw("concat(user_id, if(real_name = '', '', concat(' - ', `real_name`))) as name")
                )
                ->where('inactive', 0)
                ->orderBy('real_name')
                ->get(),
            'employees' => Employee::active()->get(),
            'accessRoles' => AccessRole::active()->select('id', 'role', 'role as name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $inputs = $request->validate([
            'applicable_group_id' => 'bail|required|integer|exists:0_entity_groups,id',
            'task_type' => 'bail|required|integer|exists:0_task_types,id',
            'definitions' => 'required|array',
            'definitions.*.previous_state_id' => 'nullable|in:' . implode(',', TaskState::pluck('id')->toArray()),
            'definitions.*.state_id' => 'required|in:' . implode(',', TaskState::pluck('id')->toArray()),
            'definitions.*.entity_id' => 'bail|required|integer',
            'definitions.*.entity_type_id' => 'bail|required|integer',
            'definitions.*.next_state_id' => 'required|in:' . implode(',', TaskState::pluck('id')->toArray()),
        ]);
        
        $workflow = DB::transaction(function () use ($inputs) {
            $workflow = Workflow::create(Arr::only($inputs, ['applicable_group_id', 'task_type']));
            $workflow->definitions()->saveMany(array_map([WorkflowDefinition::class, 'make'], $inputs['definitions']));
            return $workflow;
        });

        return response()->json(compact('workflow'), 201);
    }

    public function update(Request $request, Workflow $workflow)
    {
        $inputs = $request->validate([
            'definitions' => 'required|array',
            'definitions.*.id' => 'bail|nullable|integer|exists:0_workflow_definitions,id',
            'definitions.*.previous_state_id' => 'nullable|in:' . implode(',', TaskState::pluck('id')->toArray()),
            'definitions.*.state_id' => 'required|in:' . implode(',', TaskState::pluck('id')->toArray()),
            'definitions.*.entity_id' => 'bail|required|integer',
            'definitions.*.entity_type_id' => 'bail|required|integer',
            'definitions.*.next_state_id' => 'required|in:' . implode(',', TaskState::pluck('id')->toArray()),
        ]);

        DB::transaction(function () use ($inputs, $workflow) {
            // Definitions having id means they are already in the database
            // because we are not allowing the line to be edited: but only deleted.
            $definitions = array_filter($inputs['definitions'], function ($definition) { return empty($definition['id']); });
            WorkflowDefinition::whereFlowId($workflow->id)
                ->whereNotIn('id', array_filter(array_column($inputs['definitions'], 'id')))->delete();
            if (!empty($definitions)) {
                $workflow->definitions()->saveMany(array_map([WorkflowDefinition::class, 'make'], $definitions));
            }

            return $workflow;
        });
        return response()->json([], 204);
    }

    public function find(Request $request)
    {
        $inputs = $request->validate([
            'applicable_group_id' => 'bail|required|integer|exists:0_entity_groups,id',
            'task_type' => 'bail|required|integer|exists:0_task_types,id'
        ]);
        
        $workflow = Workflow::whereApplicableGroupId($inputs['applicable_group_id'])
            ->whereTaskType($inputs['task_type'])
            ->with('definitions')
            ->first();

        return response()->json(compact('workflow'));
    }
}
