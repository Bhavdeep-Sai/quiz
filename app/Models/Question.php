<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'quiz_id',
        'type',
        'question_text',
        'image_url',
        'video_url',
        'marks',
        'settings',
        'sort_order',
    ];

    protected $casts = [
        'settings' => 'array',
        'marks' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get the quiz this question belongs to
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get all options for this question
     */
    public function options(): HasMany
    {
        return $this->hasMany(Option::class)->orderBy('sort_order');
    }

    /**
     * Get all answers submitted for this question
     */
    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * Check if question has media
     */
    public function hasMedia(): bool
    {
        return !is_null($this->image_url) || !is_null($this->video_url);
    }

    /**
     * Get correct option(s)
     */
    public function getCorrectOptions()
    {
        return $this->options()->where('is_correct', true)->get();
    }
}
