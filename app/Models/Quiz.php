<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    protected $fillable = [
        'title',
        'description',
        'is_published',
        'pass_percentage',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'pass_percentage' => 'integer',
    ];

    /**
     * Get all questions for this quiz
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('sort_order');
    }

    /**
     * Get all attempts for this quiz
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }

    /**
     * Get total marks for the quiz
     */
    public function getTotalMarks(): int
    {
        return $this->questions()->sum('marks');
    }

    /**
     * Get successful attempts count
     */
    public function getSuccessfulAttemptsCount(): int
    {
        return $this->attempts()->where('is_passed', true)->count();
    }

    /**
     * Get average score
     */
    public function getAverageScore(): float
    {
        return (float) $this->attempts()
            ->where('status', 'evaluated')
            ->avg('total_score');
    }
}
