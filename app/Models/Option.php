<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Option extends Model
{
    protected $fillable = [
        'question_id',
        'label',
        // alias for external APIs expecting `option_text`
        'option_text',
        'image_url',
        'is_correct',
        'sort_order',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the question this option belongs to
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Check if option has both text and image
     */
    public function hasMedia(): bool
    {
        return !is_null($this->image_url);
    }

    /**
     * Accessor for `option_text` - maps to `label` for external consumers
     */
    public function getOptionTextAttribute(): string
    {
        return (string) $this->label;
    }

    /**
     * Mutator for `option_text` - write through to `label`
     */
    public function setOptionTextAttribute($value): void
    {
        $this->attributes['label'] = $value;
    }
}
