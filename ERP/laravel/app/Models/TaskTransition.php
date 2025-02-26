<?php

namespace App\Models;

use App\Permissions;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use App\Models\Entity;
use App\Models\SpecialEntities;

class TaskTransition extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_task_transitions';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    public function getIsCompletedAttribute()
    {
        return !empty($this->action_taken);
    }

    /**
     * The task associated with this transition
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function task()
    {
        return $this->belongsTo(\App\Models\Task::class);
    }

    /**
     * Make a new instance from the provided definition
     *
     * @param \App\Models\WorkflowDefinition $definition
     * @return static
     */
    public static function makeFromDefinition(WorkflowDefinition $definition)
    {
        return static::make([
            'previous_state_id' => $definition->previous_state_id,
            'state_id' => $definition->state_id,
            'assigned_entity_id' => $definition->entity_id,
            'assigned_entity_type_id' => $definition->entity_type_id,
            'next_state_id' => $definition->next_state_id
        ]);
    }

    /**
     * Determines if the action is valid for current state
     *
     * @param string $action
     * @return boolean
     */
    public function isActionValid($action)
    {
        return in_array($action, $this->getAvailableActions());
    }

    /**
     * Returns the available actions for this state
     *
     * @return array
     */
    public function getAvailableActions()
    {
        $availableActions = ['comment', 'view'];
        $user = authUser();
        
        if ($this->is_completed || $user->hasPermission(Permissions::SA_VIEW_ONLY_TASK)) {
            return $availableActions;
        }

        if ($this->task->initiated_by == $user->id) {

            if($this->task->created_by == $user->id) {
                $availableActions[] = 'cancel';
            }

            if ($user->hasPermission(Permissions::SA_MANAGE_OWN_TASKS)) {
                $availableActions[] = 'approve';
            }

            if($this->assigned_entity_type_id == Entity::SPECIAL_GROUP && $this->assigned_entity_id == SpecialEntities::APPLICANT && $this->task->initiated_by == $user->id ) {
                $availableActions = array_merge($availableActions, ['approve', 'reject']);
            }

            return $availableActions;
        }

        if ($user->hasPermission(Permissions::SA_CANCEL_OTHERS_TASK)) {
            $availableActions[] = 'cancel';
        }

        return array_merge($availableActions, ['approve', 'reject']);
    }

    /**
     * Returns the action id for the given string identifier
     *
     * @return int
     */
    public function getActionId($action)
    {
        $actions = [
            'approve' => Task::APPROVED,
            'reject' => Task::REJECTED,
            'cancel' => Task::CANCELLED
        ];

        if (!isset($actions[$action])) {
            throw new InvalidArgumentException("Unknown action '{$action}'");
        }

        return $actions[$action];
    }
}
