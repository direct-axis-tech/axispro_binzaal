<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\TaskRecord;
use App\Models\TaskTransition;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function index(Task $task)
    {
        abort_unless(
            TaskRecord::getBuilder([
                'task_type' => $task->task_type,
                'reference' => $task->reference
            ])->exists(),
            403,
            'You are not authorized to comment on this task'
        );

        $comments = TaskComment::with('user', 'transition', 'task')
            ->where('task_id', $task->id)
            ->latest()
            ->get();

        $comments->each(function ($comment) {
            $comment->user->append('avatar_url');
        });

        return ['data' => $comments->toArray()];
    }

    public function store(Request $request, TaskTransition $transition)
    {
        $inputs = $request->validate(['comment' => 'required|string','attachment' =>  'nullable|file|mimes:jpeg,png,pdf|max:2048']);

        abort_unless(
            TaskRecord::getBuilder([
                'task_type' => $transition->task->task_type,
                'reference' => $transition->task->reference
            ])->exists(),
            403,
            'You are not authorized to comment on this task'
        );
       
        $comment = TaskComment::make([
            'task_id' => $transition->task_id,
            'transition_id' => $transition->id,
            'comment' => $inputs['comment'],
            'commented_by' => authUser()->id
        ]);

        if (!empty($inputs['attachment'])) {
            $file = $inputs['attachment'];
            $uniqueName = $file->store($comment->attachment_path());
            $comment->attachment = $uniqueName;
        }
        $comment->save();
        $comment->load('user', 'transition', 'task');
        $comment->user->append('avatar_url');

        return response()->json([
            'message' => 'Comment Saved Successfully',
            'data' => $comment
        ], 201);
    }
}
