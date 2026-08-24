<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FocusController extends Controller
{
    public function index()
    {
        // Ganti dengan query model Focus/Task kamu jika sudah ada
        return Inertia::render('Focus/Index', [
            'tasks' => []
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'quadrant' => 'required|in:q1,q2,q3,none',
        ]);

        $matrixMap = [
            'q1' => 'do_first',
            'q2' => 'schedule',
            'q3' => 'delegate',
        ];

        Task::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'matrix' => $matrixMap[$validated['quadrant']] ?? null,
        ]);
        return redirect()->back();
    }
}
