<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Routine extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'title',
        'notes',
        'target_date',
        'is_everyday',
        'days_of_week',
        'is_completed_today',
        'last_completed_date',
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'is_everyday' => 'boolean',
        'is_completed_today' => 'boolean',
        'last_completed_date' => 'date',
        'target_date' => 'date:Y-m-d',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dailyTargets(): MorphMany
    {
        return $this->morphMany(DailyTarget::class, 'targetable');
    }
}