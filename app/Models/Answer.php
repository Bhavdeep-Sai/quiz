<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    protected $fillable = [
        'attempt_id',
        'question_id',
        'user_answer',
        'question_type',
        'score',
        'is_correct',
        'feedback',
        'answered_at',
        'time_spent_seconds',
    ];

    protected $casts = [
        'user_answer' => 'array',
        'score' => 'float',
        'is_correct' => 'boolean',
        'answered_at' => 'datetime',
        'time_spent_seconds' => 'integer',
    ];

    /**
     * Get the attempt this answer belongs to
     */
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(Attempt::class);
    }

    /**
     * Get the question this answer is for
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the user answer as string (for display)
     */
    public function getUserAnswerText(): string
    {
        if (is_null($this->user_answer)) {
            return 'Not answered';
        }

        if (is_array($this->user_answer)) {
            if (count($this->user_answer) === 1 && isset($this->user_answer[0])) {
                return (string) $this->user_answer[0];
            }

            return implode(', ', $this->user_answer);
        }

        return (string) $this->user_answer;
    }
}
