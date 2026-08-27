<?php

namespace App\Http\Controllers;

use App\Models\DailyTarget;
use App\Models\Task;
use App\Models\SubTask;
use App\Models\Routine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TodayController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $today = now()->toDateString();
        $todayDayOfWeek = now()->dayOfWeek;

        $dailyTargets = DailyTarget::where('user_id', $userId)
            ->where('date', $today)
            ->with('targetable')
            ->get();

        // Load task relationship hanya untuk SubTask instances
        $dailyTargets->filter(fn($dt) => $dt->targetable_type === SubTask::class)
            ->each(fn($dt) => $dt->targetable->load('task'));

        $dailyTargetTaskIds = $dailyTargets
            ->filter(fn($dt) => $dt->targetable_type === Task::class)
            ->pluck('targetable_id')
            ->toArray();

        $dailyTargetSubTaskIds = $dailyTargets
            ->filter(fn($dt) => $dt->targetable_type === \App\Models\SubTask::class)
            ->pluck('targetable_id')
            ->toArray();

        $dailyTargetRoutineIds = $dailyTargets
            ->filter(fn($dt) => $dt->targetable_type === Routine::class)
            ->pluck('targetable_id')
            ->toArray();

        $availableTasks = Task::where('user_id', $userId)
            ->whereNull('completed_at')
            ->whereNotIn('id', $dailyTargetTaskIds)
            ->with(['subTasks' => function ($q) use ($dailyTargetSubTaskIds) {
                $q->whereNull('completed_at')->whereNotIn('id', $dailyTargetSubTaskIds);
            }])
            ->get();

        // Auto-create DailyTarget untuk rutinitas yang sesuai jadwal hari ini
        $todayRoutines = Routine::where('user_id', $userId)
            ->where(function ($q) use ($todayDayOfWeek) {
                $q->where('is_everyday', true)
                    ->orWhereJsonContains('days_of_week', $todayDayOfWeek);
            })
            ->get();

        $routinesToAdd = $todayRoutines->whereNotIn('id', $dailyTargetRoutineIds);

        foreach ($routinesToAdd as $routine) {
            DailyTarget::create([
                'user_id'         => $userId,
                'targetable_type' => Routine::class,
                'targetable_id'   => $routine->id,
                'date'            => $today,
            ]);
        }

        // Re-fetch dailyTargets setelah auto-create
        if ($routinesToAdd->isNotEmpty()) {
            $dailyTargets = DailyTarget::where('user_id', $userId)
                ->where('date', $today)
                ->with('targetable')
                ->get();

            // Load task relationship hanya untuk SubTask instances
            $dailyTargets->filter(fn($dt) => $dt->targetable_type === SubTask::class)
                ->each(fn($dt) => $dt->targetable->load('task'));
        }

        // Tandai sub-tasks: nested (parent ada di hari ini) atau orphan
        $parentTaskIds = $dailyTargets
            ->filter(fn($dt) => $dt->targetable_type === Task::class)
            ->pluck('targetable_id')
            ->toArray();

        $dailyTargets = $dailyTargets->map(function ($dt) use ($parentTaskIds) {
            if ($dt->targetable_type === SubTask::class && $dt->targetable) {
                $dt->is_nested = in_array($dt->targetable->task_id, $parentTaskIds);
            } else {
                $dt->is_nested = false;
            }
            return $dt;
        });

        return Inertia::render('Today/Index', [
            'dailyTargets' => $dailyTargets,
            'availableTasks' => $availableTasks,
        ]);
    }
}
