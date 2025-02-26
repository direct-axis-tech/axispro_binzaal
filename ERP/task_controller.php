<?php

use App\Models\TaskTransition;
use App\Models\TaskType;
use App\Models\Workflow;
use App\Permissions;

$path_to_root = '.';
$page_security = 'SA_OPEN';

require_once __DIR__ . "/includes/session.inc";
require_once __DIR__ . "/API/AxisPro.php";

config()->set('database.default', 'fa');
$request = request();

if (!authUser()->hasAnyPermission(
    Permissions::SA_MANAGE_TASKS,
    Permissions::SA_MANAGE_TASKS_ALL
)) {
    return AxisPro::ValidationError("You are not authorized to access this function", 403);
}

if (
    empty($request->input('transition'))
    || empty($transition = TaskTransition::find($request->input('transition')))
    || empty($action = $request->input('action'))
) {
    return AxisPro::ValidationError("Could not find the requested resource", 404);
}

if (! $transition->isActionValid($action)) {
    return AxisPro::ValidationError("You are not authorized to access this function", 403);
}

// Load dependencies
$taskType = TaskType::find($transition->task->task_type);
if (is_callable($callback = [$taskType->class, 'dependencies'])) {
    foreach (call_user_func($callback) as $dependency) {
        require_once join_paths(__DIR__, $dependency);
    }
}

begin_transaction();
Workflow::handleTaskTransition($transition, $action);
commit_transaction();

response()->json(['status' => 204, 'msg' => 'Success'], 200)->send();