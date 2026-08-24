<?php

namespace App\Http\Controllers;

use App\Models\Routine;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoutineController extends Controller
{
    public function index(Request $request)
    {
        $routines = $request->user()->routines()->latest()->get();

        return Inertia::render('Routines/Index', [
            'routines' => $routines,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'target_date' => 'nullable|date',
            'is_everyday' => 'nullable|boolean',
            'days_of_week' => 'nullable|array',
        ]);

        $data = [
            'title' => $validated['title'],
            'notes' => $validated['notes'] ?? null,
            'target_date' => $validated['target_date'] ?? null,
            'is_everyday' => $validated['is_everyday'] ?? false,
            'days_of_week' => $validated['days_of_week'] ?? null,
        ];

        $request->user()->routines()->create($data);

        return redirect()->back();
    }

    public function update(Request $request, Routine $routine)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'notes' => 'nullable|string',
            'target_date' => 'nullable|date',
            'is_everyday' => 'nullable|boolean',
            'days_of_week' => 'nullable|array',
            'is_completed_today' => 'nullable|boolean',
        ]);

        if (isset($validated['is_completed_today'])) {
            if ($validated['is_completed_today']) {
                $validated['last_completed_date'] = now()->toDateString();
            } else {
                $validated['last_completed_date'] = null;
            }
        }

        $routine->update($validated);

        return redirect()->back();
    }

    public function destroy(Routine $routine)
    {
        $routine->delete();

        return redirect()->back();
    }
}