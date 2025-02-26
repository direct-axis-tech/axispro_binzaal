<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use App\Models\EntityGroup;
use App\Models\EntityGroupCategory;
use App\Models\Hr\Department;
use App\Models\Hr\Employee;
use App\Models\SpecialEntities;
use App\Models\System\AccessRole;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\TaskRecord;
use App\Models\TaskTransition;
use App\Models\TaskType;
use App\Models\Workflow;
use App\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\QueryDataTable;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = authUser();
        abort_unless($user->hasAnyPermission(
            Permissions::SA_MANAGE_TASKS,
            Permissions::SA_MANAGE_TASKS_ALL
        ), 403);

        $taskTransitionId =  $request->has('TaskTransitionId')
            ? $request->input('TaskTransitionId')
            : '';

        $flowGroups = EntityGroup::select('id', 'name')
            ->whereCategory(EntityGroupCategory::WORK_FLOW_RELATED)
            ->get()
            ->keyBy('id');

        $users = DB::table('0_users')
            ->select(
                'id',
                DB::raw("concat(user_id, if(real_name = '', '', concat(' - ', `real_name`))) as name")
            )->get()
            ->keyBy('id');

        $employees = Employee::query()
            ->select(
                'id',
                DB::raw("concat(emp_ref, ' - ', `name`) as name")
            )->get()
            ->keyBy('id');

        $entityTypes = Entity::query()
            ->select('id', 'name')
            ->whereIn('id', [
                Entity::USER,
                Entity::GROUP,
                Entity::SPECIAL_GROUP,
                Entity::ACCESS_ROLE
            ])
            ->get();
        
        return view('system.tasks.index', [
            'flowGroups' => $flowGroups,
            'taskTypes' => TaskType::all(),
            'departments' => Department::all(),
            'accessRoles' => AccessRole::active()->select('id', 'role', 'role as name')->get(),
            'entityTypes' => $entityTypes,
            'employees' => $user->hasPermission(Permissions::SA_MANAGE_TASKS_ALL)
                ? $employees
                : $employees->only($user->employee_id ?: -1),
            'specialGroups' => SpecialEntities::all(),
            'initiators' => $users,
            'performers' => $user->hasPermission(Permissions::SA_MANAGE_TASKS_ALL)
                ? $users
                : $users->only($user->id),
            'assignedGroups' => $user->hasPermission(Permissions::SA_MANAGE_TASKS_ALL)
                ? $flowGroups
                : $flowGroups->only($user->flow_group_id),
            'statuses' => Task::getStatuses(),
            'taskTransitionId' => $taskTransitionId
        ]);
    }

    public function dataTable(Request $request)
    {
        abort_unless(authUser()->hasAnyPermission(
            Permissions::SA_MANAGE_TASKS,
            Permissions::SA_MANAGE_TASKS_ALL
        ), 403);

        $latestTasks = DB::query()
            ->select(
                DB::raw('MAX(t.task_transition_id) as latest_task_transition_id')
            )
            ->fromSub(TaskRecord::getBuilder($request->all()), 't')
            ->groupBy('t.task_id');
            
        $builder = DB::query()
            ->fromSub(TaskRecord::getBuilder($request->all()), 't')
            ->joinSub($latestTasks, 'latest', function ($join) {
                $join->on('t.task_transition_id', '=', 'latest.latest_task_transition_id');
            });
        
        $dataTable = (new QueryDataTable($builder))
            ->addColumn('_action', function ($task) {
                $actionsUi = [
                    'approve' => '<button type="button" class="dropdown-item" data-action="approve"><span class="fa fa-thumbs-up w-20px"></span>Approve</button>',
                    'reject' => '<button type="button" class="dropdown-item" data-action="reject"><span class="fa fa-minus-circle w-20px"></span>Reject</button>',
                    'cancel' => '<button type="button" class="dropdown-item" data-action="cancel"><span class="fa fa-ban w-20px"></span>Cancel</button>',
                    'comment' => '<button type="button" class="dropdown-item" data-btn="comment"><span class="fa fa-comments w-20px"></span><span>Comment</span></button>',
                    'view' => '<button type="button" class="dropdown-item" data-btn="view"><span class="fa fa-eye w-20px"></span><span>View Task</span></button>', 
                ];

                $authorizedActions = array_map(
                    function ($action) use ($actionsUi) {
                        return '<li >'.$actionsUi[$action] ?? ''.'</li>';
                    },
                    TaskRecord::make($task)->getAvailableActions()
                );

                $authorizedActions = implode("\n", $authorizedActions);
                return (
                    "<div class='dropdown dropdown-menu-sm'>
                        <button
                            class='btn dropdown-toggle p-0'
                            type='button'
                            id='actionsDropdown'
                            data-bs-toggle='dropdown'
                            aria-expanded='false'>
                            <span class='fa fa-ellipsis-v'></span>
                        </button>
                        <ul class='dropdown-menu' aria-labelledby='actionsDropdown'>
                            {$authorizedActions}
                        </ul>
                    </div>"
                );
            })
            ->addColumn('_display_data', function ($task) {
                $displayableData = call_user_func(
                    [TaskType::find($task->task_type_id)->class, 'getDataForDisplay'],
                    TaskRecord::make($task)
                );

                $data = [];
                foreach ($displayableData as $key => $value) {
                    $data[] = "<span class='text-wrap'>{$key}: {$value}</span>";
                }
                return implode('<br>', $data);
            })
            ->filterColumn('_display_data', function ($query, $keyword) {
                $query->where('data', 'like', ["%{$keyword}%"]);
            })
            ->rawColumns(['_action', '_display_data']);

        
        return $dataTable->toJson(); 
    }

    public function takeAction(TaskTransition $transition, $action)
    {
        abort_unless($transition->isActionValid($action), 403);

        DB::transaction(function () use ($transition, $action) {
            Workflow::handleTaskTransition($transition, $action);
        });

        return response()->json(['status'=>204,'msg'=>'Success'], 204);
    }

    public function show(TaskTransition $transition, Request $request)
    {
        $query = TaskRecord::getBuilder([
            'task_transition_id' => $transition->id
        ]);

        $taskRecord = TaskRecord::make($query->first());
        $taskType = TaskType::find($taskRecord->task_type_id);

        $authorizedActions = array_map(
            function ($action) {
                $actionsUi = [
                    'approve' => '<button type="button" class="btn btn-sm p-2 mx-3 btn-primary" data-action="approve">Approve</button>',
                    'reject'  => '<button type="button" class="btn btn-sm p-2 mx-3 btn-accent" data-action="reject">Reject</button>',
                    'cancel'  => '<button type="button" class="btn btn-sm p-2 mx-3 btn-accent" data-action="cancel">Cancel</button>',  
                ];

                return $actionsUi[$action] ?? '';
            },
            $taskRecord->getAvailableActions()
        );
        
        $query = tap(
            TaskRecord::getBuilder(['skip_authorisation' => true]),
            function ($q) { $q->orders = null; }
        );

        $transitions = $query
            ->where('task_id', $taskRecord->task_id)
            ->orderBy('taskTransition.id')
            ->get();

        $comments = TaskComment::with('user', 'transition')
            ->where('task_id', $transition->task_id)
            ->latest()
            ->get();

        $comments->each(function ($comment) {
            $comment->user->append('avatar_url');
        });

        $task = (object)$transition->task->toArray();
        $task->comments = $comments;
        $task->transitions = $transitions;
        $actions = implode("\n", $authorizedActions);
        $viewHtml = call_user_func([$taskType->class, 'view'], $taskRecord)->render();

        return response()->json(compact('taskRecord', 'task', 'viewHtml', 'actions'));
    }
}
