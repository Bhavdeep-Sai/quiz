<?php

namespace App\QuestionTypes\Types;

use App\Models\Question;
use App\QuestionTypes\BaseQuestionType;

/**
 * Number Type: Numeric input questions
 * 
 * User enters a numeric value
 * Can support exact match or range-based answers
 */
class NumberType extends BaseQuestionType
{
    protected string $type = 'number';
    protected bool $partialScoringSupported = true;

    /**
     * Evaluate numeric answer
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

        $userNumber = (float) $userAnswer;

        // Get expected answer from settings
        $expectedAnswer = (float) ($question->settings['expected_answer'] ?? null);
        $tolerance = (float) ($question->settings['tolerance'] ?? 0); // Acceptable range
        $acceptRange = $question->settings['accept_range'] ?? false;

        if ($acceptRange) {
            $minValue = (float) ($question->settings['min_value'] ?? $expectedAnswer - $tolerance);
            $maxValue = (float) ($question->settings['max_value'] ?? $expectedAnswer + $tolerance);
            $isCorrect = $userNumber >= $minValue && $userNumber <= $maxValue;
        } else {
            // Exact or tolerance-based match
            $isCorrect = abs($userNumber - $expectedAnswer) <= $tolerance;
        }

        $score = $this->calculateScore($question, $isCorrect);

        // Build feedback
        if ($isCorrect) {
            $feedback = "Correct! The answer is $expectedAnswer";
        } else {
            if ($acceptRange) {
                $minValue = $question->settings['min_value'] ?? ($expectedAnswer - $tolerance);
                $maxValue = $question->settings['max_value'] ?? ($expectedAnswer + $tolerance);
                $feedback = "Incorrect. The accepted range is $minValue to $maxValue";
            } else {
                $feedback = "Incorrect. The correct answer is $expectedAnswer (within ±$tolerance)";
            }
        }

        return [
            'score' => $score,
            'is_correct' => $isCorrect,
            'feedback' => $feedback,
        ];
    }

    /**
     * Validate numeric answer format
     */
    public function validate(mixed $userAnswer, Question $question): array
    {
        if (is_null($userAnswer) || $userAnswer === '') {
            return ['valid' => false, 'error' => 'Please enter a number'];
        }

        if (!is_numeric($userAnswer)) {
            return ['valid' => false, 'error' => 'Please enter a valid number'];
        }

        $number = (float) $userAnswer;

        // Check min/max constraints if set
        $minValue = isset($question->settings['input_min']) ? (float) $question->settings['input_min'] : null;
        $maxValue = isset($question->settings['input_max']) ? (float) $question->settings['input_max'] : null;

        if (!is_null($minValue) && $number < $minValue) {
            return ['valid' => false, 'error' => "Number must be at least $minValue"];
        }

        if (!is_null($maxValue) && $number > $maxValue) {
            return ['valid' => false, 'error' => "Number must be at most $maxValue"];
        }

        // Check decimal places if restricted
        $maxDecimalPlaces = $question->settings['decimal_places'] ?? null;
        if (!is_null($maxDecimalPlaces)) {
            $decimalCount = strlen(substr(strrchr((string) $userAnswer, "."), 1));
            if ($decimalCount > $maxDecimalPlaces) {
                return ['valid' => false, 'error' => "Maximum $maxDecimalPlaces decimal places allowed"];
            }
        }

        return ['valid' => true];
    }

    /**
     * Render number question
     */
    public function renderData(Question $question): array
    {
        return array_merge($this->renderCommonData($question), [
            'type' => 'number',
            'input_min' => $question->settings['input_min'] ?? null,
            'input_max' => $question->settings['input_max'] ?? null,
            'decimal_places' => $question->settings['decimal_places'] ?? null,
            'unit' => $question->settings['unit'] ?? '', // e.g., "meters", "kg"
            'placeholder' => $question->settings['placeholder'] ?? 'Enter a number',
            'step' => $question->settings['step'] ?? 'any', // For HTML input step attribute
        ]);
    }
}
