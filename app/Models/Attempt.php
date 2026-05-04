<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attempt extends Model
{
    protected $fillable = [
        'quiz_id',
        'user_identifier',
        'user_name',
        'user_email',
        'started_at',
        'submitted_at',
        'time_spent_seconds',
        'total_score',
        'total_marks',
        'is_passed',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'time_spent_seconds' => 'integer',
        'total_score' => 'float',
        'total_marks' => 'integer',
        'is_passed' => 'boolean',
    ];

    /**
     * Get the quiz for this attempt
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get all answers for this attempt
     */
    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * Get result breakdown
     */
    public function resultBreakdown(): HasMany
    {
        return $this->hasMany(ResultBreakdown::class);
    }

    /**
     * Calculate percentage
     */
    public function getPercentage(): float
    {
        if ($this->total_marks === 0) {
            return 0;
        }

        return round(($this->total_score / $this->total_marks) * 100, 2);
    }

    /**
     * Get performance level
     */
    public function getPerformanceLevel(): string
    {
        $percentage = $this->getPercentage();

        if ($percentage >= 90) {
            return 'excellent';
        } elseif ($percentage >= 75) {
            return 'good';
        } elseif ($percentage >= 50) {
            return 'average';
        } else {
            return 'poor';
        }
    }

    /**
     * Get duration in human format
     */
    public function getDurationFormatted(): string
    {
        $seconds = $this->time_spent_seconds;
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}m {$secs}s";
        } elseif ($minutes > 0) {
            return "{$minutes}m {$secs}s";
        } else {
            return "{$secs}s";
        }
    }
}
