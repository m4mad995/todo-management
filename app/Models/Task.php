<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Task extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'title',
        'matrix',
        'completed_at',
        'status',
        'urgency_score',
        'impact_score',
        'is_completed',
        'is_habit',
        'streak_count',
        'sort_order',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subTasks()
    {
        return $this->hasMany(SubTask::class)->orderBy('sort_order');
    }

    public function dailyTargets(): MorphMany
    {
        return $this->morphMany(DailyTarget::class, 'targetable');
    }
}
