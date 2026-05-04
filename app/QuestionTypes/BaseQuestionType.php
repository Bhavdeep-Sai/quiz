<?php

namespace App\QuestionTypes;

use App\Models\Question;
use App\QuestionTypes\Contracts\QuestionTypeInterface;

/**
 * Abstract base class for all question types
 * 
 * Provides common functionality and enforces contract implementation
 */
abstract class BaseQuestionType implements QuestionTypeInterface
{
    /**
     * The question type identifier
     */
    protected string $type;

    /**
     * Whether this type supports partial scoring
     */
    protected bool $partialScoringSupported = false;

    /**
     * Get the type name
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Check if type supports partial scoring
     */
    public function supportsPartialScoring(): bool
    {
        return $this->partialScoringSupported;
    }

    /**
     * Helper to calculate score based on marks
     * 
     * @param Question $question The question object
     * @param bool $isCorrect Whether answer is correct
     * @param float $percentage Percentage of correctness (for partial scoring)
     * @return float The score earned
     */
    protected function calculateScore(Question $question, bool $isCorrect = true, float $percentage = 1.0): float
    {
        $marks = $question->marks ?? 1;
        
        if ($isCorrect) {
            return (float) ($marks * $percentage);
        }
        
        return 0.0;
    }

    /**
     * Helper to normalize answer input
     * 
     * @param mixed $answer The raw answer input
     * @return mixed Normalized answer
     */
    protected function normalizeAnswer(mixed $answer): mixed
    {
        if (is_array($answer)) {
            // Remove empty values and re-index
            return array_values(array_filter($answer, fn($val) => !is_null($val) && $val !== ''));
        }

        if (is_string($answer)) {
            return trim($answer);
        }

        return $answer;
    }

    /**
     * Render common question structure
     */
    protected function renderCommonData(Question $question): array
    {
        return [
            'id' => $question->id,
            'type' => $question->type,
            'question_text' => $question->question_text,
            'image_url' => $question->image_url,
            'video_url' => $question->video_url,
            'marks' => $question->marks,
            'has_media' => $question->hasMedia(),
        ];
    }
}
