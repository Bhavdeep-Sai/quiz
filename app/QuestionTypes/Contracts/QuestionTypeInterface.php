<?php

namespace App\QuestionTypes\Contracts;

use App\Models\Question;

/**
 * Interface for all question types
 * 
 * Every question type must implement this contract
 * This enables the Strategy Pattern - each type is a separate strategy
 */
interface QuestionTypeInterface
{
    /**
     * Evaluate the user's answer and return score
     * 
     * @param Question $question The question being answered
     * @param mixed $userAnswer The user's submitted answer
     * @return array ['score' => float, 'is_correct' => bool, 'feedback' => string|null]
     */
    public function evaluate(Question $question, mixed $userAnswer): array;

    /**
     * Validate if the answer format is correct
     * 
     * @param mixed $userAnswer The answer to validate
     * @param Question $question The question context
     * @return array ['valid' => bool, 'error' => string|null]
     */
    public function validate(mixed $userAnswer, Question $question): array;

    /**
     * Render question data for frontend display
     * 
     * @param Question $question The question to render
     * @return array Data needed to display this question type on frontend
     */
    public function renderData(Question $question): array;

    /**
     * Get the type name
     * 
     * @return string The type identifier (e.g., 'boolean', 'single_choice')
     */
    public function getType(): string;

    /**
     * Supports partial scoring
     * 
     * @return bool Whether this question type can award partial marks
     */
    public function supportsPartialScoring(): bool;
}
