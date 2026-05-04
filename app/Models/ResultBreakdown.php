<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultBreakdown extends Model
{
    protected $table = 'result_breakdowns';

    protected $fillable = [
        'attempt_id',
        'category',
        'total_questions',
        'correct_answers',
        'percentage',
        'total_score',
        'avg_time_per_question',
        'performance_level',
    ];

    protected $casts = [
        'total_questions' => 'integer',
        'correct_answers' => 'integer',
        'percentage' => 'float',
        'total_score' => 'float',
        'avg_time_per_question' => 'integer',
    ];

    /**
     * Get the attempt this breakdown belongs to
     */
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(Attempt::class);
    }

    /**
     * Get performance badge
     */
    public function getPerformanceBadge(): string
    {
        return match ($this->performance_level) {
            'excellent' => '🌟',
            'good' => '✅',
            'average' => '⚠️',
            'poor' => '❌',
            default => '•'
        };
    }
}
