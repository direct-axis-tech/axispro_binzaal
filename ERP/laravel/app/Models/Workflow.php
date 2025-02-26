<?php

namespace App\Models;

use App\Events\TaskInitialized;
use App\Events\TaskTransitioned;
use App\Models\System\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use LogicException;
use Illuminate\Support\Facades\Event;
use App\Models\Task;

class Workflow extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_workflows';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    /**
    * The definitions for this workflow
    *
    * @return Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function definitions() {
        return $this->hasMany(WorkflowDefinition::class, 'flow_id', 'id');
    }

    /**
    * Returns the initial defined state and its attributes for this workflow
    *
    * @return \App\Models\WorkflowDefinition
    */
    protected function getEntryPoint()
    {
        return $this->definitions()
            ->where('state_id', TaskState::STATE_1)
            ->first();
    }

    /**
     * Determines if a workflow is defined for the specified task
     *
     * @param int $taskType
     * @param User $applicant
     * @return static
     */
    public static function findByTaskType($taskType, User $applicant = null)
    {
        if (!$applicant) {
            $applicant = authUser();
        }

        if (!($applicableGroupId = authUser()->flow_group_id)) {
            return false;
        }

        return static::whereTaskType($taskType)
            ->whereApplicableGroupId($applicableGroupId)
            ->first();
    }

    /**
     * Initiates the workflow for the specified task with the specified user
     *
     * @param array $data
     * @param User $applicant
     * @return void
     */
    public function initiate($data = [], User $applicant = null)
    {
        $taskType = TaskType::find($this->task_type);

        if (!is_a($taskType->class, \App\Contracts\Flowable::class, true)) {
            throw new LogicException("{$taskType->class} doesn't implement App\\Contracts\\Flowable");
        }

        if (!$applicant) {
            $applicant = authUser();
        }

        $task = DB::transaction(function () use ($data, $taskType, $applicant) {
            $refNumber = MetaReference::getNext(
                Task::TASK,
                null,
                [
                    'task_type' => $this->task_type,
                    'type_prefix' => $taskType->type_prefix,
                    'date' => Today()
                ],
                true
            );

            $task = Task::make([
                'flow_id' => $this->id,
                'task_type' => $this->task_type,
                'initiated_group_id' => $this->applicable_group_id,
                'initiated_by' => $applicant->id,
                'created_by' => authUser()->id,
                'data' => $data,
                'reference' => $refNumber,
                'created_date' => date(DB_DATE_FORMAT)
            ]);
            $task->save();
            $task->transitions()->save(TaskTransition::makeFromDefinition($this->getEntryPoint()));
            
            $taskRecord = TaskRecord::findByTaskId($task->id);
            Event::dispatch(new TaskInitialized($taskRecord));
            
            self::handleAutoApproval($taskRecord->getTaskTransitionInstance());
            
            return $task;
        });
    }

    /**
     * Handle the task transition
     *
     * @param TaskTransition $transition
     * @param string $action
     * @return void
     */
    public static function handleTaskTransition(TaskTransition $transition, $action)
    {
        $user = authUser();

        $transition->update([
            'action_taken' => $transition->getActionId($action),
            'completed_by' => $user->id,
            'completed_at' => Carbon::now()->toDateTimeString()
        ]);
        
        if ($action == 'approve') {
            $nextDefinition = $transition
                ->task
                ->flow
                ->definitions()
                ->where('previous_state_id', $transition->state_id)
                ->where('state_id', $transition->next_state_id)
                ->first();

            $currentTaskRecord = TaskRecord::findByTaskId($transition->task_id);
            if (!empty($nextDefinition)) {
                $transition
                    ->task
                    ->transitions()
                    ->save(TaskTransition::makeFromDefinition($nextDefinition));
                $nextTaskRecord = TaskRecord::findByTaskId($transition->task_id);
                Event::dispatch(new TaskTransitioned($currentTaskRecord, $nextTaskRecord));
                
                self::handleAutoApproval($nextTaskRecord->getTaskTransitionInstance());
                
                return $transition;
            }
        }

        $transition->update(['next_state_id' => null]);
        $taskType = TaskType::find($transition->task->task_type);
        $taskRecord = TaskRecord::findByTaskId($transition->task_id);
        call_user_func([$taskType->class, $action == 'approve' ? 'resolve' : $action], $taskRecord);
        
        $eventArr = ['cancel' => '\App\Events\TaskCancelled','approve' => '\App\Events\TaskApproved','reject' => '\App\Events\TaskRejected'];
        Event::dispatch(new $eventArr[$action]($taskRecord));
    }

    /**
     * Handle the auto approval of a task
     *
     * @param TaskTransition $transition
     * @return void
     */
    public static function handleAutoApproval(TaskTransition $transition)
    {
        // Guard against unwanted approval
        if (
            $transition->assigned_entity_type_id != Entity::SPECIAL_GROUP
            || $transition->assigned_entity_id != SpecialEntities::AUTO_APPROVER
        ) {
            return;
        }

        $transition->assigned_entity_type_id = Entity::USER;
        $transition->assigned_entity_id = authUser()->id;
        $transition->save();

        self::handleTaskTransition($transition, 'approve');
    }
}
