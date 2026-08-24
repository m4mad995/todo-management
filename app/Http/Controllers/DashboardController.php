<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Routine;
use App\Models\Agenda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        // === Core Stats ===
        $allTasks = Task::where('user_id', $userId)->get();
        $completedTasks = $allTasks->whereNotNull('completed_at');
        $uncompletedTasks = $allTasks->whereNull('completed_at');

        $totalCompleted = $completedTasks->count();
        $totalUncompleted = $uncompletedTasks->count();
        $totalAll = $totalCompleted + $totalUncompleted;
        $completionRate = $totalAll > 0 ? round(($totalCompleted / $totalAll) * 100) : 0;

        // Completed by matrix
        $completedByMatrix = [
            'do_first' => $completedTasks->where('matrix', 'do_first')->count(),
            'schedule' => $completedTasks->where('matrix', 'schedule')->count(),
            'delegate' => $completedTasks->where('matrix', 'delegate')->count(),
            'drop'     => $completedTasks->where('matrix', 'drop')->count(),
        ];

        // Task completion by matrix (for task rate chart)
        $taskCompletionByMatrix = [
            'do_first' => Task::where('user_id', $userId)->where('matrix', 'do_first')->whereNotNull('completed_at')->count(),
            'do_next'  => Task::where('user_id', $userId)->where('matrix', 'schedule')->whereNotNull('completed_at')->count(),
            'hand_off' => Task::where('user_id', $userId)->where('matrix', 'delegate')->whereNotNull('completed_at')->count(),
            'ignore'   => Task::where('user_id', $userId)->where('matrix', 'drop')->whereNotNull('completed_at')->count(),
        ];

        $taskTotalByMatrix = [
            'do_first' => Task::where('user_id', $userId)->where('matrix', 'do_first')->count(),
            'do_next'  => Task::where('user_id', $userId)->where('matrix', 'schedule')->count(),
            'hand_off' => Task::where('user_id', $userId)->where('matrix', 'delegate')->count(),
            'ignore'   => Task::where('user_id', $userId)->where('matrix', 'drop')->count(),
        ];

        // Completed by month (last 6 months)
        $completedByMonth = DB::table('tasks')
            ->where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', now()->subMonths(5)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(completed_at, '%Y-%m') as month_key"),
                DB::raw("DATE_FORMAT(completed_at, '%m') as month_num"),
                DB::raw('count(*) as count')
            )
            ->groupBy('month_key', 'month_num')
            ->orderBy('month_key')
            ->get()
            ->map(function ($row) {
                $monthNames = [
                    '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
                    '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agu',
                    '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',
                ];
                return [
                    'month' => $monthNames[$row->month_num] ?? $row->month_key,
                    'count' => (int) $row->count,
                ];
            })
            ->values();

        // Completed by day of week (last 7 days)
        $completedByDay = DB::table('tasks')
            ->where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', now()->subDays(6)->startOfDay())
            ->select(
                DB::raw("DATE_FORMAT(completed_at, '%w') as day_num"),
                DB::raw('count(*) as count')
            )
            ->groupBy('day_num')
            ->orderBy('day_num')
            ->get()
            ->map(function ($row) {
                $dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
                return [
                    'day'   => $dayNames[(int) $row->day_num] ?? '',
                    'count' => (int) $row->count,
                ];
            })
            ->values();

        // === Today's Progress ===
        $todayDayNum = (int) now()->format('w');

        $completedTasksToday = Task::where('user_id', $userId)
            ->whereDate('completed_at', now()->toDateString())
            ->get(['id', 'title', 'matrix']);

        $completedRoutinesToday = Routine::where('user_id', $userId)
            ->whereDate('last_completed_date', now()->toDateString())
            ->get(['id', 'title', 'is_everyday', 'days_of_week']);

        $incompleteTasksToday = Task::where('user_id', $userId)
            ->whereNull('completed_at')
            ->take(5)
            ->get(['id', 'title', 'matrix']);

        $todayRoutines = Routine::where('user_id', $userId)
            ->where(function ($q) use ($todayDayNum) {
                $q->where('is_everyday', true)
                  ->orWhereJsonContains('days_of_week', $todayDayNum);
            })
            ->where(function ($q) {
                $q->whereNull('last_completed_date')
                  ->orWhereDate('last_completed_date', '!=', now()->toDateString());
            })
            ->take(5)
            ->get()
            ->map(function ($routine) {
                return [
                    'id'    => $routine->id,
                    'title' => $routine->title,
                    'is_everyday' => $routine->is_everyday,
                    'days_of_week' => $routine->days_of_week,
                ];
            });

        $completedTodayCount = $completedTasksToday->count() + $completedRoutinesToday->count();
        $totalToday = $completedTasksToday->count() + $incompleteTasksToday->count() + $completedRoutinesToday->count() + $todayRoutines->count();

        // === Weekly History (last 7 days) ===
        $weeklyHistory = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayNum = (int) $date->format('w');

            $dayTasksCompleted = Task::where('user_id', $userId)
                ->whereDate('completed_at', $date->toDateString())
                ->count();

            $dayRoutinesCompleted = Routine::where('user_id', $userId)
                ->whereDate('last_completed_date', $date->toDateString())
                ->count();

            $dayRoutinesTotal = Routine::where('user_id', $userId)
                ->where(function ($q) use ($dayNum) {
                    $q->where('is_everyday', true)
                      ->orWhereJsonContains('days_of_week', $dayNum);
                })
                ->count();

            // Total active tasks: tasks completed that day + tasks still incomplete (that existed by end of day)
            $dayTasksIncomplete = Task::where('user_id', $userId)
                ->whereNull('completed_at')
                ->where('created_at', '<=', $date->endOfDay())
                ->count();

            $weeklyHistory[] = [
                'date'               => $date->toDateString(),
                'day_name'           => $date->format('D'),
                'day_num'            => (int) $date->format('d'),
                'month_name'         => $date->format('M'),
                'month_full'         => $date->format('F'),
                'year'               => $date->format('Y'),
                'week_number'        => (int) $date->format('W'),
                'tasks_completed'    => $dayTasksCompleted,
                'routines_completed' => $dayRoutinesCompleted,
                'routines_total'     => $dayRoutinesTotal,
                'total_completed'    => $dayTasksCompleted + $dayRoutinesCompleted,
                'total_active'       => $dayTasksCompleted + $dayRoutinesCompleted + $dayTasksIncomplete,
            ];
        }

        // Determine week/month boundaries for separators
        $prevWeek = null;
        $prevMonth = null;
        foreach ($weeklyHistory as &$day) {
            $day['is_week_start'] = $prevWeek !== null && $day['week_number'] !== $prevWeek;
            $day['is_month_start'] = $prevMonth !== null && $day['month_full'] !== $prevMonth;
            $prevWeek = $day['week_number'];
            $prevMonth = $day['month_full'];
        }
        unset($day);

        // Daily combined for bar chart
        $dailyCombined = array_map(function ($day) {
            return [
                'day' => $day['day_name'],
                'tasks' => $day['tasks_completed'],
                'routines' => $day['routines_completed'],
            ];
        }, $weeklyHistory);

        // === Streak (task + routine) ===
        $streak = 0;
        $checkDate = now()->startOfDay();

        while (true) {
            $hasTask = $completedTasks->contains(fn($t) =>
                $t->completed_at->startOfDay()->eq($checkDate)
            );

            $hasRoutine = Routine::where('user_id', $userId)
                ->whereDate('last_completed_date', $checkDate)
                ->exists();

            if ($hasTask || $hasRoutine) {
                $streak++;
                $checkDate = $checkDate->subDay();
            } else {
                break;
            }
        }

        // === Discovery Hub ===

        // Urgent tasks (Do First, incomplete)
        $urgentTasks = Task::where('user_id', $userId)
            ->whereNull('completed_at')
            ->where('matrix', 'do_first')
            ->orderBy('created_at', 'asc')
            ->take(5)
            ->get()
            ->map(function ($task) {
                return [
                    'id'    => $task->id,
                    'title' => $task->title,
                ];
            });

        // Upcoming agendas (today onwards, not completed)
        $upcomingAgendas = Agenda::where('user_id', $userId)
            ->where('event_date', '>=', now()->toDateString())
            ->where('is_completed', false)
            ->orderBy('event_date', 'asc')
            ->orderBy('event_time', 'asc')
            ->take(5)
            ->get()
            ->map(function ($agenda) {
                $date = \Carbon\Carbon::parse($agenda->event_date);
                return [
                    'id'         => $agenda->id,
                    'title'      => $agenda->title,
                    'event_date' => $agenda->event_date,
                    'event_time' => $agenda->event_time,
                    'is_today'   => $agenda->event_date === now()->toDateString(),
                    'date_label' => $date->isToday() ? 'Hari Ini' : $date->diffForHumans(),
                ];
            });

        // Recent completed tasks (last 5)
        $recentCompleted = $completedTasks
            ->sortByDesc('completed_at')
            ->take(5)
            ->map(function ($task) {
                return [
                    'id'           => $task->id,
                    'title'        => $task->title,
                    'matrix'       => $task->matrix,
                    'completed_at' => $task->completed_at->toISOString(),
                    'completed_diff' => $task->completed_at->diffForHumans(),
                ];
            })
            ->values();

        // === Chart 2: Task Selesai vs Belum per Matrix (Stacked Horizontal Bar) ===
        // Fallback: semua incomplete tasks per matrix
        $matrixStatus = [];
        foreach (['do_first' => 'Do First', 'schedule' => 'Do Next', 'delegate' => 'Hand Off'] as $matrixKey => $matrixLabel) {
            $total = Task::where('user_id', $userId)
                ->where('matrix', $matrixKey)
                ->count();
            $completed = Task::where('user_id', $userId)
                ->where('matrix', $matrixKey)
                ->whereNotNull('completed_at')
                ->count();
            $matrixStatus[] = [
                'matrix'    => $matrixKey,
                'label'     => $matrixLabel,
                'completed' => $completed,
                'pending'   => $total - $completed,
                'total'     => $total,
            ];
        }

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalCompleted'    => $totalCompleted,
                'totalUncompleted'  => $totalUncompleted,
                'completionRate'    => $completionRate,
                'completedByMatrix' => $completedByMatrix,
                'completedByMonth'  => $completedByMonth,
                'completedByDay'    => $completedByDay,
                'currentStreak'     => $streak,
                'recentCompleted'   => $recentCompleted,
                'completedToday'    => $completedTodayCount,
                'completedTasksToday' => $completedTasksToday->count(),
                'completedRoutinesToday' => $completedRoutinesToday->count(),
                'totalToday'        => $totalToday,
                'taskCompletionByMatrix' => $taskCompletionByMatrix,
                'taskTotalByMatrix' => $taskTotalByMatrix,
                'dailyCombined'     => $dailyCombined,
                'matrixStatus'      => $matrixStatus,
            ],
            'urgentTasks'          => $urgentTasks,
            'todayRoutines'        => $todayRoutines,
            'completedRoutinesToday' => $completedRoutinesToday,
            'upcomingAgendas'      => $upcomingAgendas,
            'completedTasksToday'  => $completedTasksToday,
            'incompleteTasksToday' => $incompleteTasksToday,
                'weeklyHistory'        => $weeklyHistory,
                'isFirstTime'          => $allTasks->isEmpty() && $todayRoutines->isEmpty() && $upcomingAgendas->isEmpty(),
        ]);
    }
}
