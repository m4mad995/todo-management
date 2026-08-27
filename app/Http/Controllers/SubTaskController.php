<?php

namespace App\Http\Controllers;

use App\Models\SubTask;
use App\Models\Task;
use App\Models\DailyTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SubTaskController extends Controller
{
    public function store(Request $request, Task $task)
    {
        if ($task->user_id !== Auth::id()) abort(403);

        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $maxOrder = $task->subTasks()->max('sort_order') ?? 0;

        $task->subTasks()->create([
            'title' => $request->title,
            'sort_order' => $maxOrder + 1,
        ]);

        return back();
    }

    public function update(Request $request)
    {
        $routeId = $request->route('subtask');
        $subTask = SubTask::find($routeId);

        if (!$subTask) abort(404);

        $task = $subTask->task;

        if (!$task || $task->user_id !== Auth::id()) abort(403);

        Log::info('[SubTask UPDATE] subtask_id=' . $subTask->id . ' task_id=' . $task->id . ' auth_id=' . Auth::id());

        if ($request->has('is_completed')) {
            $subTask->update([
                'is_completed' => $request->is_completed,
                'completed_at' => $request->is_completed ? now() : null,
            ]);

            $total = $task->subTasks()->count();
            $completed = $task->subTasks()->where('is_completed', true)->count();
            $isComplete = $total > 0 && $total === $completed;
            $task->update([
                'completed_at' => $isComplete ? now() : null,
            ]);

            DailyTarget::where('user_id', Auth::id())
                ->where('targetable_type', Task::class)
                ->where('targetable_id', $task->id)
                ->update([
                    'is_completed' => $isComplete,
                    'completed_at' => $isComplete ? now() : null,
                ]);
        }

        if ($request->has('title')) {
            $request->validate(['title' => 'required|string|max:255']);
            $subTask->update(['title' => $request->title]);
        }

        return back();
    }

    public function destroy(Request $request)
    {
        $routeId = $request->route('subtask');
        $subTask = SubTask::find($routeId);

        if (!$subTask) abort(404);

        $task = $subTask->task;

        if (!$task || $task->user_id !== Auth::id()) abort(403);

        Log::info('[SubTask DESTROY] subtask_id=' . $subTask->id . ' task_id=' . $task->id . ' auth_id=' . Auth::id());

        $subTask->delete();

        $total = $task->subTasks()->count();
        $completed = $task->subTasks()->where('is_completed', true)->count();
        if ($total === 0 || $total !== $completed) {
            $task->update(['completed_at' => null]);

            DailyTarget::where('user_id', Auth::id())
                ->where('targetable_type', Task::class)
                ->where('targetable_id', $task->id)
                ->update([
                    'is_completed' => false,
                    'completed_at' => null,
                ]);
        }

        return back();
    }
}
