<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\DailyTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class FocusController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $tasks = Task::with('subTasks')->where('user_id', $userId)->whereNull('completed_at')->get();

        $dailyTargets = DailyTarget::where('user_id', $userId)
            ->where('date', now()->toDateString())
            ->with('targetable')
            ->get();

        $activeTargets = $dailyTargets->where('is_completed', false)->values();
        $completedTargets = $dailyTargets->where('is_completed', true)->values();

        return Inertia::render('Focus/Index', [
            'unprocessedTasks' => $tasks->whereNull('matrix')->values(),
            'doFirst'          => $tasks->where('matrix', 'do_first')->values(),
            'schedule'         => $tasks->where('matrix', 'schedule')->values(),
            'delegate'         => $tasks->where('matrix', 'delegate')->values(),
            'drop'             => $tasks->where('matrix', 'drop')->values(),
            'unprocessedCount' => $tasks->whereNull('matrix')->count(),
            'activeTargets'    => $activeTargets,
            'completedTargets' => $completedTargets,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'  => 'required|string|max:255',
            'matrix' => 'nullable|in:do_first,schedule,delegate,drop',
            'quadrant' => 'nullable|in:q1,q2,q3,none',
        ]);

        $matrix = $request->matrix;
        if ($request->has('quadrant')) {
            $matrixMap = ['q1' => 'do_first', 'q2' => 'schedule', 'q3' => 'delegate', 'none' => null];
            $matrix = $matrixMap[$request->quadrant] ?? null;
        }

        Task::create([
            'user_id' => Auth::id(),
            'title'   => $request->title,
            'matrix'  => $matrix,
        ]);

        return redirect()->back();
    }

    public function update(Request $request, Task $focus) // Using Task model here
    {
        if ($focus->user_id !== Auth::id()) {
            abort(403);
        }

        if ($request->has('completed')) {
            $focus->update(['completed_at' => $request->completed ? now() : null]);

            DailyTarget::where('user_id', Auth::id())
                ->where('targetable_type', Task::class)
                ->where('targetable_id', $focus->id)
                ->update([
                    'is_completed' => $request->completed,
                    'completed_at' => $request->completed ? now() : null,
                ]);
        }

        if ($request->has('matrix')) {
            $focus->update(['matrix' => $request->matrix]);
        }

        if ($request->has('title')) {
            $request->validate(['title' => 'required|string|max:255']);
            $focus->update(['title' => $request->title]);
        }

        return redirect()->back();
    }

    public function destroy(Task $focus)
    {
        if ($focus->user_id !== Auth::id()) {
            abort(403);
        }
        
        $focus->delete();

        return redirect()->back();
    }
}
