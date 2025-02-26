<?php

namespace App\Models;

use App\Permissions;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

/**
 * Represents task data retrieved from the database.
 */
class TaskRecord
{
    /**
     * @var int The task ID.
     */
    public $task_id;

    /**
     * @var int The flow ID.
     */
    public $flow_id;

    /**
     * @var int The task type ID.
     */
    public $task_type_id;

    /**
     * @var string The name of the task type.
     */
    public $task_type_name;

    /**
     * @var int The initiated group ID.
     */
    public $initiated_group_id;

    /**
     * @var string The name of the initiated group.
     */
    public $initiated_group_name;

    /**
     * @var int The ID of the user who initiated the task.
     */
    public $initiated_by;

    /**
     * @var string The name of the initiator.
     */
    public $initiator_name;

    /**
     * @var int The ID of the department of the initiator.
     */
    public $initiator_department_id;

    /**
     * @var string The name of the department of the initiator.
     */
    public $initiator_department_name;

    /**
     * @var mixed The task data.
     */
    public $data;

    /**
     * @var string The date and time when the task was initiated.
     */
    public $initiated_at;

    /**
     * @var string The formatted date and time when the task was initiated.
     */
    public $formatted_initiated_at;

    /**
     * @var int The ID of the task transition.
     */
    public $task_transition_id;

    /**
     * @var int The ID of the previous state.
     */
    public $previous_state_id;

    /**
     * @var int The ID of the current state.
     */
    public $state_id;

    /**
     * @var int The ID of the next state.
     */
    public $next_state_id;

    /**
     * @var string The name of the assigned entity.
     */
    public $assigned_entity_name;

    /**
     * @var int The ID of the assigned entity.
     */
    public $assigned_entity_id;

    /**
     * @var string The name of the assigned entity type.
     */
    public $assigned_entity_type_id;

    /**
     * @var string The action taken on the task.
     */
    public $action_taken;

    /**
     * @var int The ID of the user who completed the task.
     */
    public $completed_by;

    /**
     * @var string The name of the performer who completed the task.
     */
    public $performer_name;

    /**
     * @var string The date and time when the task was completed.
     */
    public $completed_at;

    /**
     * @var string The date and time when the task was assigned.
     */
    public $assigned_at;

    /**
     * @var string The formatted date and time when the task was assigned.
     */
    public $formatted_assigned_at;

    /**
     * @var string The status of the task.
     */
    public $status;

    /**
     * @var string The formatted date and time when the task was completed.
     */
    public $formatted_completed_at;

    /**
     * @var bool Indicates if the task is completed.
     */
    public $is_completed;

    /**
     * @var string reference of the task.
     */
    public $reference;
    
    /**
     * @var int The ID of the user who created the task.
     */
    public $created_by;

    /**
     * TaskData constructor.
     *
     * @param object|array $data The data object retrieved from the database.
     */
    public function __construct($data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }

        if ($this->data) {
            $this->data = json_decode($this->data, true);
        }
    }

    /**
     * An alias function to constructor to allow fluent syntax.
     *
     * @param object|array $data The data object retrieved from the database.
     * @return static
     */
    public static function make($data)
    {
        return new static($data);
    }

    /**
     * Get the instance of Task model
     *
     * @return \App\Models\Task
     */
    public function getTaskInstance() {
        return Task::make()->newFromBuilder([
            'id' => $this->task_id,
            'flow_id' => $this->flow_id,
            'task_type' => $this->task_type_id,
            'initiated_group_id' => $this->initiated_group_id,
            'initiated_by' => $this->initiated_by,
            'created_by' => $this->created_by,
            'data' => $this->data
        ]);
    }

    /**
     * Get the instance of the TaskTransition model
     *
     * @return \App\Models\TaskTransition
     */
    public function getTaskTransitionInstance() {
        return TaskTransition::make()->newFromBuilder([
            'id' => $this->task_transition_id,
            'task_id' => $this->task_id,
            'previous_state_id' => $this->previous_state_id,
            'state_id' => $this->state_id,
            'next_state_id' => $this->next_state_id,
            'assigned_entity_id' => $this->assigned_entity_id,
            'assigned_entity_type_id' => $this->assigned_entity_type_id,
            'action_taken' => $this->action_taken,
            'completed_by' => $this->completed_by,
            'completed_at' => $this->completed_at
        ]);
    }

    /**
     * Returns the available actions for this record
     *
     * @return array
     */
    public function getAvailableActions()
    {
        $transition = $this->getTaskTransitionInstance();
        $transition->setRelation('task', $this->getTaskInstance());

        return $transition->getAvailableActions();
    }

    /**
     * Get the builder instance for generating this record
     *
     * @param array $filters
     * @return \Illuminate\Database\Query\Builder
     */
    public static function getBuilder($filters = [])
    {
        $builder = DB::query()
            ->select(
                'task.id as task_id',
                'task.flow_id',
                'task.task_type as task_type_id',
                'taskType.name as task_type_name',
                'task.initiated_group_id',
                'initiatorGroup.name as initiated_group_name',
                'task.initiated_by',
                'initiator.real_name as initiator_name',
                'dep.id as initiator_department_id',
                'dep.name as initiator_department_name',
                'task.data',
                'task.created_at as initiated_at',
                DB::raw("date_format(`task`.`created_at`, '". dateformat('mySQL') ."') as formatted_initiated_at"),
                'taskTransition.id as task_transition_id',
                'taskTransition.previous_state_id',
                'taskTransition.state_id',
                'taskTransition.next_state_id',
                'taskTransition.assigned_entity_id',
                'taskTransition.assigned_entity_type_id',
                'taskTransition.action_taken',
                'taskTransition.completed_by',
                'performer.real_name as performer_name',
                'taskTransition.completed_at',
                'taskTransition.created_at as assigned_at',
                DB::raw("date_format(`taskTransition`.`created_at`, '". dateformat('mySQL') ."') as formatted_assigned_at"),
                DB::raw(
                    "case"
                        . " when `taskTransition`.`action_taken` is null then 'Pending'"
                        . " when `taskTransition`.`action_taken` = '".Task::APPROVED."' then 'Approved'"
                        . " when `taskTransition`.`action_taken` = '".Task::REJECTED."' then 'Rejected'"
                        . " else 'Cancelled'"
                    . " end as status"
                ),
                DB::raw("date_format(`taskTransition`.`completed_at`, '". dateformat('mySQL') ."') as formatted_completed_at"),
                DB::raw("not isnull(`taskTransition`.`action_taken`) as is_completed"),
                DB::raw(
                    'COALESCE(
                        user.real_name, 
                        employee.name, 
                        entity_group.name,
                        CONVERT(accessRole.`role` USING utf8mb4) COLLATE utf8mb4_general_ci,
                        CASE
                            WHEN `taskTransition`.`assigned_entity_type_id` = '. Entity::SPECIAL_GROUP .'
                                THEN
                                    CASE
                                        WHEN `taskTransition`.`assigned_entity_id` = '. SpecialEntities::LINE_SUPERVISOR .' THEN "'. SpecialEntities::find(SpecialEntities::LINE_SUPERVISOR)->name .'"
                                        WHEN `taskTransition`.`assigned_entity_id` = '. SpecialEntities::DEPARTMENT_HEAD .' THEN "'. SpecialEntities::find(SpecialEntities::DEPARTMENT_HEAD)->name .'"
                                        WHEN `taskTransition`.`assigned_entity_id` = '. SpecialEntities::WORKING_COM_IN_CHARGE .' THEN "'. SpecialEntities::find(SpecialEntities::WORKING_COM_IN_CHARGE)->name .'"
                                        WHEN `taskTransition`.`assigned_entity_id` = '. SpecialEntities::VISA_COM_IN_CHARGE .' THEN "'. SpecialEntities::find(SpecialEntities::VISA_COM_IN_CHARGE)->name .'"
                                        WHEN `taskTransition`.`assigned_entity_id` = '. SpecialEntities::APPLICANT .' THEN "'. SpecialEntities::find(SpecialEntities::APPLICANT)->name .'"
                                        ELSE NULL
                                    END
                                ELSE NULL
                        END,
                        "Not Assigned"
                    ) AS assigned_entity_name'
                    ),
                'task.reference',
                'task.created_by'
            )
            ->from('0_tasks as task')
            ->leftJoin('0_task_transitions as taskTransition', 'task.id', 'taskTransition.task_id')
            ->leftJoin('0_task_types as taskType', 'taskType.id', 'task.task_type')
            ->leftJoin('0_entity_groups as initiatorGroup', 'initiatorGroup.id', 'task.initiated_group_id')
            ->leftJoin('0_users as performer', 'performer.id', 'taskTransition.completed_by')
            ->leftJoin('0_users as initiator', 'initiator.id', 'task.initiated_by')
            ->leftJoin('0_employees as emp', 'emp.id', 'initiator.employee_id')
            ->leftJoin('0_emp_jobs as empJob', function ($join) {
                $join->on('emp.id', 'empJob.employee_id')
                    ->where('empJob.is_current', '1');
            })
            ->leftJoin('0_companies as workingCompany', 'workingCompany.id', 'empJob.working_company_id')
            ->leftJoin('0_companies as visaCompany', 'visaCompany.id', 'empJob.visa_company_id')
            ->leftJoin('0_departments as dep', 'dep.id', 'empJob.department_id')
            ->leftJoin('0_users as user', function ($join) {
                $join->on('user.id', '=', 'taskTransition.assigned_entity_id')
                    ->where('taskTransition.assigned_entity_type_id', '=', Entity::USER);
            })
            ->leftJoin('0_employees as employee', function ($join) {
                $join->on('employee.id', '=', 'taskTransition.assigned_entity_id')
                    ->where('taskTransition.assigned_entity_type_id', '=', Entity::EMPLOYEE);
            })
            ->leftJoin('0_entity_groups as entity_group', function ($join) {
                $join->on('entity_group.id', '=', 'taskTransition.assigned_entity_id')
                    ->where('taskTransition.assigned_entity_type_id', '=', Entity::GROUP);
            })
            ->leftJoin('0_security_roles as accessRole', function ($join) {
                $join->on('accessRole.id', '=', 'taskTransition.assigned_entity_id')
                    ->where('taskTransition.assigned_entity_type_id', '=', Entity::ACCESS_ROLE);
            })
            ->groupBy('taskTransition.id')
            ->orderByDesc('taskTransition.id');

        if (!authUser()->hasPermission(Permissions::SA_MANAGE_TASKS_ALL) && !isset($filters['skip_authorisation'])) {
            $user = authUser();
            $builder->whereRaw(
                "("
                    . "(task.initiated_by = ?)"
                    . " OR (taskTransition.assigned_entity_type_id = ? AND taskTransition.assigned_entity_id = ?)"
                    . " OR (taskTransition.assigned_entity_type_id = ? AND taskTransition.assigned_entity_id = ?)"
                    . " OR (taskTransition.assigned_entity_type_id = ? AND taskTransition.assigned_entity_id = ?)"
                    . " OR (taskTransition.assigned_entity_type_id = ? AND taskTransition.assigned_entity_id = ?)"
                    . " OR ("
                        . "taskTransition.assigned_entity_type_id = ?"
                        . " AND taskTransition.assigned_entity_id = ?"
                        . " AND JSON_CONTAINS(empJob.supervisor_id, JSON_QUOTE(concat('', ?)))"
                    . ")"
                    . " OR ("
                        . "taskTransition.assigned_entity_type_id = ?"
                        . " AND taskTransition.assigned_entity_id = ?"
                        . " AND JSON_CONTAINS(dep.hod_id, JSON_QUOTE(concat('', ?)))"
                    . ")"
                    . " OR ("
                        . "taskTransition.assigned_entity_type_id = ?"
                        . " AND taskTransition.assigned_entity_id = ?"
                        . " AND json_contains(workingCompany.in_charge_id, json_quote(concat('', ?)))"
                    .")"
                    . " OR ("
                        . "taskTransition.assigned_entity_type_id = ?"
                        . " AND taskTransition.assigned_entity_id = ?"
                        . " AND json_contains(visaCompany.in_charge_id, json_quote(concat('', ?)))"
                    .")"
                    . " OR ("
                        . "taskTransition.assigned_entity_type_id = ?"
                        . " AND taskTransition.assigned_entity_id = ?"
                        . " AND initiator.employee_id = ?"
                    .")"
                . ")",
                [
                    $user->id,
                    Entity::USER, $user->id,
                    Entity::EMPLOYEE, $user->employee_id,
                    Entity::GROUP, $user->flow_group_id ?: '-1',
                    Entity::ACCESS_ROLE, $user->role_id,
                    Entity::SPECIAL_GROUP, SpecialEntities::LINE_SUPERVISOR, $user->employee_id,
                    Entity::SPECIAL_GROUP, SpecialEntities::DEPARTMENT_HEAD, $user->employee_id,
                    Entity::SPECIAL_GROUP, SpecialEntities::WORKING_COM_IN_CHARGE, $user->employee_id,
                    Entity::SPECIAL_GROUP, SpecialEntities::VISA_COM_IN_CHARGE, $user->employee_id,
                    Entity::SPECIAL_GROUP, SpecialEntities::APPLICANT, $user->employee_id,
                ]
            );
        }

        if (!empty($filters['department_id'])) {
            $builder->where('empJob.department_id', $filters['department_id']);
        }

        if (!empty($filters['task_transition_id'])) {
            $builder->where('taskTransition.id', $filters['task_transition_id']);
        }

        if (!empty($filters['initiated_by'])) {
            $builder->where('task.initiated_by', $filters['initiated_by']);
        }

        if (!empty($filters['completed_by'])) {
            $builder->where('taskTransition.completed_by', $filters['completed_by']);
        }

        if (!empty($filters['date_from'])) {
            $builder->whereRaw('date(`task`.`created_at`) >= ?', [Carbon::createFromFormat(dateformat(), $filters['date_from'])->toDateString()]);
        }

        if (!empty($filters['date_till'])) {
            $builder->whereRaw('date(`task`.`created_at`) <= ?', [Carbon::createFromFormat(dateformat(), $filters['date_till'])->toDateString()]);
        }

        if (!empty($filters['initiated_group_id'])) {
            $builder->where('task.initiated_group_id', $filters['initiated_group_id']);
        }

        if (!empty($filters['assigned_entity_type_id'])) {
            $builder->where('taskTransition.assigned_entity_type_id', $filters['assigned_entity_type_id']);
        }

        if (!empty($filters['assigned_entity_id'])) {
            $builder->where('taskTransition.assigned_entity_id', $filters['assigned_entity_id']);
        }

        if (!empty($filters['task_type'])) {
            $builder->where('task.task_type', $filters['task_type']);
        }

        if (!empty($filters['status'])) {
            switch ($filters['status']) {
                case 'Pending':
                    $builder->whereNull('taskTransition.action_taken');
                    break;
                case 'Completed':
                    $builder->whereNotNull('taskTransition.action_taken');
                    break;
                case 'Approved':
                    $builder->where('taskTransition.action_taken', Task::APPROVED);
                    break;
                case 'Rejected':
                    $builder->where('taskTransition.action_taken', Task::REJECTED);
                    break;
                case 'Cancelled':
                    $builder->where('taskTransition.action_taken', Task::CANCELLED);
                    break;
            }
        }

        if (!empty($filters['reference'])) {
            $builder->where('task.reference', $filters['reference']);
        }
        
        return $builder;
    }

    /**
     * Find the task record for the specified taskId
     *
     * @param int $taskId
     * @return static
     */
    public static function findByTaskId($taskId)
    {
        $taskRecord = static::getBuilder(['skip_authorisation' => true])
            ->where('task.id', $taskId)
            ->first();
        return new static($taskRecord);
    }
}
