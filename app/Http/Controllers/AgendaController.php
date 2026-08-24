<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AgendaController extends Controller
{
    public function index(Request $request)
    {
        $agendas = $request->user()->agendas()
            ->orderBy('event_date', 'asc')
            ->orderBy('event_time', 'asc')
            ->get();

        return Inertia::render('Agenda/Index', [
            'agendas' => $agendas,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'event_date' => 'nullable|date',
            'event_time' => 'nullable',
        ]);

        $validated['event_date'] = $validated['event_date'] ?? now()->toDateString();

        $request->user()->agendas()->create($validated);

        return redirect()->back();
    }

    public function update(Request $request, Agenda $agenda)
    {
        if ($agenda->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'notes' => 'nullable|string',
            'event_date' => 'sometimes|required|date',
            'event_time' => 'nullable',
            'is_completed' => 'sometimes|boolean',
        ]);

        $agenda->update($validated);

        return redirect()->back();
    }

    public function destroy(Request $request, Agenda $agenda)
    {
        if ($agenda->user_id !== $request->user()->id) {
            abort(403);
        }

        $agenda->delete();

        return redirect()->back();
    }
}