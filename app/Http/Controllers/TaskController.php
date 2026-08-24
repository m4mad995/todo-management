<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Inertia\Inertia;

class TaskController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $tasks = Task::with('subTasks')->where('user_id', $userId)->whereNull('completed_at')->get();

        return Inertia::render('Focus/Index', [
            'unprocessedTasks' => $tasks->whereNull('matrix')->values(), // <-- Task dari Topbar
            'doFirst'          => $tasks->where('matrix', 'do_first')->values(),
            'schedule'         => $tasks->where('matrix', 'schedule')->values(),
            'delegate'         => $tasks->where('matrix', 'delegate')->values(),
            'drop'             => $tasks->where('matrix', 'drop')->values(),
            'unprocessedCount' => $tasks->whereNull('matrix')->count(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'  => 'required|string|max:255',
            'matrix' => 'nullable|in:do_first,schedule,delegate,drop',
        ]);

        Task::create([
            'user_id' => Auth::id(),
            'title'   => $request->title,
            'matrix'  => $request->matrix ?? null,
        ]);

        return back();
    }

    public function update(Request $request, Task $task)
    {
        // 2. Gunakan Auth::id() agar IDE tidak lagi menandai merah
        if ($task->user_id !== Auth::id()) {
            abort(403);
        }

        // 3. Gunakan if terpisah (tanpa elseif)
        if ($request->has('completed')) {
            $task->update(['completed_at' => $request->completed ? now() : null]);
        }

        if ($request->has('matrix')) {
            $task->update(['matrix' => $request->matrix]);
        }

        if ($request->has('title')) {
            $request->validate(['title' => 'required|string|max:255']);
            $task->update(['title' => $request->title]);
        }

        return back();
    }

    public function destroy(Task $task)
    {
        if ($task->user_id !== Auth::id()) abort(403);
        $task->delete();

        return back();
    }

    public function session(Request $request)
    {
        // Mengambil task spesifik jika dikirim ID-nya, atau task pertama yang belum selesai
        $taskId = $request->query('task_id');

        $activeTask = $taskId
            ? Task::where('user_id', Auth::id())->find($taskId)
            : Task::where('user_id', Auth::id())->whereNull('completed_at')->first();

        return Inertia::render('Focus/Session', [
            'activeTask' => $activeTask
        ]);
    }
}
