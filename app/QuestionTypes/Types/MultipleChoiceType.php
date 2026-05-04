<?php

namespace App\QuestionTypes\Types;

use App\Models\Question;
use App\QuestionTypes\BaseQuestionType;

/**
 * Multiple Choice Type: User selects multiple correct answers
 * 
 * Multiple options can be marked as correct
 * Supports partial scoring based on correct selections
 */
class MultipleChoiceType extends BaseQuestionType
{
    protected string $type = 'multiple_choice';
    protected bool $partialScoringSupported = true;

    /**
     * Evaluate multiple choice answer with partial scoring
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

        $userAnswer = $this->normalizeAnswer($userAnswer);
        
        // Convert array to integers
        $userOptionIds = array_map(fn($id) => (int) $id, $userAnswer);

        // Get all correct options for this question
        $correctOptions = $question->options()
            ->where('is_correct', true)
            ->pluck('id')
            ->toArray();

        // Calculate score
        $result = $this->calculatePartialScore($question, $userOptionIds, $correctOptions);

        return $result;
    }

    /**
     * Calculate partial score for multiple choice
     */
    private function calculatePartialScore(Question $question, array $userOptionIds, array $correctOptionIds): array
    {
        $totalCorrect = count($correctOptionIds);
        
        if ($totalCorrect === 0) {
            return [
                'score' => 0,
                'is_correct' => false,
                'feedback' => 'No correct answers defined',
            ];
        }

        // Find correct and incorrect selections
        $correctSelected = array_intersect($userOptionIds, $correctOptionIds);
        $incorrectSelected = array_diff($userOptionIds, $correctOptionIds);
        $missedOptions = array_diff($correctOptionIds, $userOptionIds);

        // Calculate percentage
        $correctCount = count($correctSelected);
        $totalAttempted = count($userOptionIds);

        // Scoring logic:
        // - Full marks if all correct options selected and no wrong ones
        // - Partial marks for each correct selection minus penalty for wrong selections
        // - Zero if any wrong option selected (strict mode)

        $useStrictMode = $question->settings['strict_mode'] ?? true;

        if ($useStrictMode && count($incorrectSelected) > 0) {
            // Strict mode: any wrong selection = 0 marks
            $score = 0;
            $isCorrect = false;
            $percentage = 0;
        } else {
            // Partial scoring mode
            $percentage = $totalCorrect > 0 ? $correctCount / $totalCorrect : 0;
            $score = $this->calculateScore($question, true, $percentage);
            $isCorrect = $percentage === 1.0 && count($userOptionIds) === $totalCorrect;
        }

        // Build feedback
        $feedback = $this->buildMultipleChoiceFeedback(
            $correctCount,
            $totalCorrect,
            $incorrectSelected,
            $missedOptions,
            $question,
            $useStrictMode
        );

        return [
            'score' => $score,
            'is_correct' => $isCorrect,
            'feedback' => $feedback,
        ];
    }

    /**
     * Build feedback message for multiple choice
     */
    private function buildMultipleChoiceFeedback(
        int $correctCount,
        int $totalCorrect,
        array $incorrectSelected,
        array $missedOptions,
        Question $question,
        bool $strictMode
    ): string
    {
        $parts = [];

        if ($correctCount === $totalCorrect && empty($incorrectSelected)) {
            return 'Perfect! All correct answers selected.';
        }

        $parts[] = "You selected $correctCount out of $totalCorrect correct options.";

        if (!empty($incorrectSelected)) {
            $incorrectLabels = $question->options()
                ->whereIn('id', $incorrectSelected)
                ->pluck('label')
                ->toArray();
            $parts[] = "❌ Incorrect selections: " . implode(', ', $incorrectLabels);
        }

        if (!empty($missedOptions)) {
            $missedLabels = $question->options()
                ->whereIn('id', $missedOptions)
                ->pluck('label')
                ->toArray();
            $parts[] = "⏭️ You missed: " . implode(', ', $missedLabels);
        }

        return implode(' | ', $parts);
    }

    /**
     * Validate multiple choice answer format
     */
    public function validate(mixed $userAnswer, Question $question): array
    {
        if (is_null($userAnswer) || (is_array($userAnswer) && empty(array_filter($userAnswer)))) {
            return ['valid' => false, 'error' => 'Please select at least one answer'];
        }

        // Convert to array if needed
        $answerArray = is_array($userAnswer) ? $userAnswer : [$userAnswer];

        // Check all selected options exist
        $existingOptionIds = $question->options()->pluck('id')->toArray();
        
        foreach ($answerArray as $optionId) {
            if (!in_array($optionId, $existingOptionIds)) {
                return ['valid' => false, 'error' => 'One or more selected options do not exist'];
            }
        }

        // Check minimum selections if required
        $minSelections = $question->settings['min_selections'] ?? 1;
        if (count($answerArray) < $minSelections) {
            return ['valid' => false, 'error' => "Please select at least $minSelections options"];
        }

        // Check maximum selections if set
        $maxSelections = $question->settings['max_selections'] ?? null;
        if ($maxSelections && count($answerArray) > $maxSelections) {
            return ['valid' => false, 'error' => "Please select at most $maxSelections options"];
        }

        return ['valid' => true];
    }

    /**
     * Render multiple choice question
     */
    public function renderData(Question $question): array
    {
        $options = $question->options()
            ->select('id', 'label', 'image_url', 'sort_order')
            ->orderBy('sort_order')
            ->get()
            ->map(fn($option) => [
                'id' => $option->id,
                'label' => $option->label,
                'image_url' => $option->image_url,
                'has_image' => !is_null($option->image_url),
            ])
            ->toArray();

        return array_merge($this->renderCommonData($question), [
            'type' => 'multiple_choice',
            'options' => $options,
            'shuffle_options' => $question->settings['shuffle_options'] ?? false,
            'strict_mode' => $question->settings['strict_mode'] ?? true,
            'min_selections' => $question->settings['min_selections'] ?? 1,
            'max_selections' => $question->settings['max_selections'] ?? null,
        ]);
    }
}
