<?php

namespace App\QuestionTypes\Types;

use App\Models\Question;
use App\QuestionTypes\BaseQuestionType;

/**
 * Boolean Type: True/False questions
 * 
 * User must select either True or False
 * Evaluates to full marks or zero
 */
class BooleanType extends BaseQuestionType
{
    protected string $type = 'boolean';

    /**
     * Evaluate boolean answer
     */
    public function evaluate(Question $question, mixed $userAnswer): array
    {
        // Validate first
        $validation = $this->validate($userAnswer, $question);
        
        if (!$validation['valid']) {
            return [
                'score' => 0,
                'is_correct' => false,
                'feedback' => $validation['error'],
            ];
        }

        // Normalize to boolean
        $userAnswer = $this->normalizeToBoolean($userAnswer);
        
        // Get correct answer from first option marked as correct
        $correctOption = $question->options()->where('is_correct', true)->first();
        $correctAnswer = $correctOption ? $this->normalizeToBoolean($correctOption->label) : false;

        // Check if answer is correct
        $isCorrect = $userAnswer === $correctAnswer;
        $score = $this->calculateScore($question, $isCorrect);

        return [
            'score' => $score,
            'is_correct' => $isCorrect,
            'feedback' => $isCorrect 
                ? 'Correct answer!' 
                : "Incorrect. The correct answer is: " . ($correctAnswer ? 'True' : 'False'),
        ];
    }

    /**
     * Validate boolean answer format
     */
    public function validate(mixed $userAnswer, Question $question): array
    {
        if (is_null($userAnswer)) {
            return ['valid' => false, 'error' => 'Answer is required'];
        }

        // Accept: true/false, 'true'/'false', 1/0, 'yes'/'no'
        $validAnswers = [true, false, 'true', 'false', 'True', 'False', 'TRUE', 'FALSE', 'yes', 'no', 'Yes', 'No', 1, 0, '1', '0'];
        
        if (!in_array($userAnswer, $validAnswers, true)) {
            return ['valid' => false, 'error' => 'Answer must be true or false'];
        }

        return ['valid' => true];
    }

    /**
     * Render boolean question
     */
    public function renderData(Question $question): array
    {
        return array_merge($this->renderCommonData($question), [
            'type' => 'boolean',
            'options' => [
                ['id' => 1, 'label' => 'True', 'value' => true],
                ['id' => 2, 'label' => 'False', 'value' => false],
            ],
        ]);
    }

    /**
     * Convert various formats to boolean
     */
    private function normalizeToBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            return in_array(strtolower($value), ['true', 'yes', '1'], true);
        }

        if (is_numeric($value)) {
            return (bool) $value;
        }

        return false;
    }
}
