<?php

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoutineController;
use App\Http\Controllers\FocusController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\SubTaskController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DailyTargetController;
use App\Http\Controllers\TodayController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public Route
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Protected Routes (Wajib Auth & Verified)
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Today (Hari Ini)
    Route::get('/today', [TodayController::class, 'index'])->name('today.index');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/theme', [ProfileController::class, 'updateTheme'])->name('profile.theme');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Focus Routes
    Route::get('/focus', [FocusController::class, 'index'])->name('focus.index');
    Route::get('/focus/session', [TaskController::class, 'session'])->name('focus.session');
    Route::post('/focus', [FocusController::class, 'store'])->name('focus.store');
    Route::patch('/focus/{focus}', [FocusController::class, 'update'])->name('focus.update');
    Route::delete('/focus/{focus}', [FocusController::class, 'destroy'])->name('focus.destroy');

    // Tasks Routes (For Focus Page CRUD)
    Route::patch('/tasks/reorder', [TaskController::class, 'reorder'])->name('tasks.reorder');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    // Sub-task Routes
    Route::post('/tasks/{task}/subtasks', [SubTaskController::class, 'store'])->name('subtasks.store');
    Route::patch('/subtasks/{subtask}', [SubTaskController::class, 'update'])->name('subtasks.update');
    Route::delete('/subtasks/{subtask}', [SubTaskController::class, 'destroy'])->name('subtasks.destroy');

    // Daily Target Routes (Hari Ini)
    Route::post('/daily-targets', [DailyTargetController::class, 'store'])->name('daily-targets.store');
    Route::patch('/daily-targets/{dailyTarget}', [DailyTargetController::class, 'toggleComplete'])->name('daily-targets.toggle');
    Route::delete('/daily-targets/{dailyTarget}', [DailyTargetController::class, 'destroy'])->name('daily-targets.destroy');

    // Routine Routes
    Route::get('/routines', [RoutineController::class, 'index'])->name('routines.index');
    Route::post('/routines', [RoutineController::class, 'store'])->name('routines.store');
    Route::patch('/routines/{routine}', [RoutineController::class, 'update'])->name('routines.update');
    Route::delete('/routines/{routine}', [RoutineController::class, 'destroy'])->name('routines.destroy');

    // Agenda Routes
    Route::get('/agenda', [AgendaController::class, 'index'])->name('agenda.index');
    Route::post('/agenda', [AgendaController::class, 'store'])->name('agenda.store');
    Route::patch('/agenda/{agenda}', [AgendaController::class, 'update'])->name('agenda.update');
    Route::delete('/agenda/{agenda}', [AgendaController::class, 'destroy'])->name('agenda.destroy');
});

require __DIR__ . '/auth.php';