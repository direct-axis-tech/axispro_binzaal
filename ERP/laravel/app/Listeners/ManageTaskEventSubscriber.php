<?php
 
namespace App\Listeners;

use App\Events\TaskInitialized;
use App\Events\TaskTransitioned;
use App\Events\TaskApproved;
use App\Events\TaskRejected;
use App\Events\TaskCancelled;
use App\Models\Entity;
use App\Models\System\User;
use App\Models\TaskRecord;
use App\Models\TaskTransition;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TaskInitializedNotification;
use App\Notifications\TaskTransitionedNotification;
use App\Notifications\TaskApprovedNotification;
use App\Notifications\TaskRejectedNotification;
use App\Notifications\TaskCancelledNotification;
use Illuminate\Database\Eloquent\Collection;

class ManageTaskEventSubscriber
{ 
    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(TaskInitialized::class, [$this, 'handleTaskInitialized']);
        $events->listen(TaskTransitioned::class, [$this, 'handleTaskTransitioned']);
        $events->listen(TaskApproved::class, [$this, 'handleTaskApproved']);
        $events->listen(TaskRejected::class, [$this, 'handleTaskRejected']);
        $events->listen(TaskCancelled::class, [$this, 'handleTaskCancelled']);
    }

    /**
     * Get notifiables against the rejection/cancellation
     *
     * @param TaskRecord $taskRecord
     * @return Collection
     */
    private function getNotifiablesForTermination($taskRecord)
    {
        return User::active()->whereIn('id', array_merge(
            [$taskRecord->initiated_by],
            TaskTransition::whereTaskId($taskRecord->task_id)
                ->where('completed_by', '<>', $taskRecord->completed_by)
                ->whereNotNull('completed_by')
                ->pluck('completed_by')
                ->toArray()
        ))->get();
    }

    /**
     * Get notifiables against the transition
     *
     * @param TaskRecord $taskRecord
     * @return Collection
     */
    private function getNotifiablesForTransition($taskRecord)
    {
        $assignedEntity = Entity::find($taskRecord->assigned_entity_type_id);
        $initiatedUser = User::find($taskRecord->initiated_by);

        $notifiables = (new Collection([$initiatedUser]))
            ->merge($assignedEntity->resolveUsers($taskRecord->assigned_entity_id, $initiatedUser) ?: [])
            ->unique()
            ->where('inactive', '0');

        return $notifiables;
    }

    /**
     * Handles the event when a user initiate a task
     * 
     * @param TaskInitialized $event
     * @return void
     */
    public function handleTaskInitialized($event) {
        Notification::send(
            $this->getNotifiablesForTransition($event->taskRecord),
            new TaskInitializedNotification($event)
        );
    }

    /**
     * Handles the event when a user transfer a task
     * 
     * @param TaskTransitioned $event
     * @return void
     */
    public function handleTaskTransitioned($event) {
        Notification::send(
            $this->getNotifiablesForTransition($event->nextTaskRecord),
            new TaskTransitionedNotification($event)
        );
    }

    /**
     * Handles the event when a user approve a task
     * 
     * @param TaskApproved $event
     * @return void
     */
    public function handleTaskApproved($event) {
        Notification::send(
            User::find($event->taskRecord->initiated_by),
            new TaskApprovedNotification($event)
        );
    }

    /**
     * Handles the event when a user reject a task
     * 
     * @param TaskRejected $event
     * @return void
     */
    public function handleTaskRejected($event) {
        Notification::send(
            $this->getNotifiablesForTermination($event->taskRecord),
            new TaskRejectedNotification($event)
        );
    }

    /**
     * Handles the event when a user cancel a task
     * 
     * @param TaskCancelled $event
     * @return void
     */
    public function handleTaskCancelled($event) {
        Notification::send(
            $this->getNotifiablesForTermination($event->taskRecord),
            new TaskCancelledNotification($event)
        );
    }
}