<?php

namespace App\Http\Controllers;

use App\Models\DailyTarget;
use App\Models\Task;
use App\Models\Routine;
use App\Models\SubTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyTargetController extends Controller
{
    private $typeMap = [
        'Task'    => Task::class,
        'Routine' => Routine::class,
        'SubTask' => SubTask::class,
    ];

    public function store(Request $request)
    {
        $request->validate([
            'targetable_type' => 'required|in:Task,Routine,SubTask',
            'targetable_id'   => 'required|string',
        ]);

        $modelClass = $this->typeMap[$request->targetable_type] ?? null;
        if (!$modelClass) abort(400);

        $model = $modelClass::find($request->targetable_id);
        if (!$model) abort(404);

        $exists = DailyTarget::where('user_id', Auth::id())
            ->where('targetable_type', $modelClass)
            ->where('targetable_id', $request->targetable_id)
            ->where('date', now()->toDateString())
            ->exists();

        if ($exists) return back();

        DailyTarget::create([
            'user_id'         => Auth::id(),
            'targetable_type' => $modelClass,
            'targetable_id'   => $request->targetable_id,
            'date'            => now()->toDateString(),
        ]);

        return back();
    }

    public function toggleComplete(Request $request)
    {
        $dailyTarget = DailyTarget::find($request->route('dailyTarget'));
        if (!$dailyTarget || $dailyTarget->user_id !== Auth::id()) abort(403);

        $newStatus = !$dailyTarget->is_completed;

        $dailyTarget->update([
            'is_completed' => $newStatus,
            'completed_at' => $newStatus ? now() : null,
        ]);

        $model = $dailyTarget->targetable;
        if ($model) {
            if ($model instanceof Task) {
                $model->update(['completed_at' => $newStatus ? now() : null]);
            } elseif ($model instanceof SubTask) {
                $model->update([
                    'is_completed' => $newStatus,
                    'completed_at' => $newStatus ? now() : null,
                ]);
            } elseif ($model instanceof Routine) {
                $model->update(['is_completed_today' => $newStatus]);
            }
        }

        return back();
    }

    public function destroy(Request $request)
    {
        $dailyTarget = DailyTarget::find($request->route('dailyTarget'));
        if (!$dailyTarget || $dailyTarget->user_id !== Auth::id()) abort(403);

        $dailyTarget->delete();

        return back();
    }
}
