<?php

namespace App\QuestionTypes\Types;

use App\Models\Question;
use App\QuestionTypes\BaseQuestionType;

/**
 * Text Type: Free-form text input questions
 * 
 * User enters text
 * Can be auto-graded using keyword matching or manually graded
 */
class TextType extends BaseQuestionType
{
    protected string $type = 'text';
    protected bool $partialScoringSupported = true;

    /**
     * Evaluate text answer
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

        $userText = $this->normalizeAnswer($userAnswer);
        $gradeMode = $question->settings['grade_mode'] ?? 'manual'; // 'manual' or 'keyword'

        if ($gradeMode === 'keyword') {
            return $this->autoGradeByKeyword($question, $userText);
        } else {
            // Manual grading - mark as pending
            return [
                'score' => 0,
                'is_correct' => false,
                'feedback' => 'Your answer has been submitted and is pending manual review.',
            ];
        }
    }

    /**
     * Auto-grade using keyword matching
     */
    private function autoGradeByKeyword(Question $question, string $userText): array
    {
        $keywords = $question->settings['keywords'] ?? [];
        $matchMode = $question->settings['match_mode'] ?? 'any'; // 'any', 'all', 'exact'
        $caseSensitive = $question->settings['case_sensitive'] ?? false;
        $partialMatching = $question->settings['partial_matching'] ?? false;

        if (empty($keywords)) {
            return [
                'score' => 0,
                'is_correct' => false,
                'feedback' => 'No grading criteria defined',
            ];
        }

        // Normalize text
        $testText = $caseSensitive ? $userText : strtolower($userText);
        $normalizedKeywords = $caseSensitive 
            ? $keywords 
            : array_map(fn($k) => strtolower($k), $keywords);

        // Check matches based on mode
        $matches = [];
        foreach ($normalizedKeywords as $keyword) {
            if ($partialMatching) {
                $isMatch = strpos($testText, $keyword) !== false;
            } else {
                $isMatch = $testText === $keyword;
            }
            
            if ($isMatch) {
                $matches[] = $keyword;
            }
        }

        // Determine if correct based on match mode
        $isCorrect = match ($matchMode) {
            'exact' => $testText === $normalizedKeywords[0],
            'all' => count($matches) === count($normalizedKeywords),
            'any' => count($matches) > 0,
            default => false,
        };

        // Calculate score with partial credit
        $percentage = count($normalizedKeywords) > 0 ? count($matches) / count($normalizedKeywords) : 0;
        $score = $this->calculateScore($question, true, $percentage);

        // Build feedback
        $feedback = $isCorrect 
            ? 'Correct! Your answer matches the expected response.'
            : 'Your answer does not contain all required keywords.';

        return [
            'score' => $score,
            'is_correct' => $isCorrect,
            'feedback' => $feedback,
        ];
    }

    /**
     * Validate text answer format
     */
    public function validate(mixed $userAnswer, Question $question): array
    {
        if (is_null($userAnswer) || (is_string($userAnswer) && trim($userAnswer) === '')) {
            return ['valid' => false, 'error' => 'Please enter an answer'];
        }

        $userText = (string) $userAnswer;
        $minLength = $question->settings['min_length'] ?? 1;
        $maxLength = $question->settings['max_length'] ?? 10000;

        if (strlen($userText) < $minLength) {
            return ['valid' => false, 'error' => "Answer must be at least $minLength characters"];
        }

        if (strlen($userText) > $maxLength) {
            return ['valid' => false, 'error' => "Answer must not exceed $maxLength characters"];
        }

        return ['valid' => true];
    }

    /**
     * Render text question
     */
    public function renderData(Question $question): array
    {
        $inputType = $question->settings['input_type'] ?? 'textarea'; // 'textarea' or 'text'
        $rows = $question->settings['rows'] ?? 4;

        return array_merge($this->renderCommonData($question), [
            'type' => 'text',
            'input_type' => $inputType,
            'rows' => $rows,
            'placeholder' => $question->settings['placeholder'] ?? 'Enter your answer here',
            'min_length' => $question->settings['min_length'] ?? 1,
            'max_length' => $question->settings['max_length'] ?? 10000,
            'grade_mode' => $question->settings['grade_mode'] ?? 'manual',
            'character_count_display' => $question->settings['character_count_display'] ?? false,
        ]);
    }
}
