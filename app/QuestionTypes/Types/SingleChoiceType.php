<?php

namespace App\QuestionTypes\Types;

use App\Models\Question;
use App\QuestionTypes\BaseQuestionType;

/**
 * Single Choice Type: Multiple choice with only one correct answer
 * 
 * User selects ONE option from multiple options
 * Only one option should be marked as correct
 */
class SingleChoiceType extends BaseQuestionType
{
    protected string $type = 'single_choice';

    /**
     * Evaluate single choice answer
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
        $userOptionId = is_array($userAnswer) ? $userAnswer[0] : $userAnswer;

        // Get user's selected option
        $selectedOption = $question->options()->find($userOptionId);
        
        if (!$selectedOption) {
            return [
                'score' => 0,
                'is_correct' => false,
                'feedback' => 'Invalid option selected',
            ];
        }

        // Check if answer is correct
        $isCorrect = (bool) $selectedOption->is_correct;
        $score = $this->calculateScore($question, $isCorrect);

        // Get correct option for feedback
        $correctOption = $question->options()->where('is_correct', true)->first();

        return [
            'score' => $score,
            'is_correct' => $isCorrect,
            'feedback' => $isCorrect 
                ? 'Correct answer!' 
                : "Incorrect. The correct answer is: {$correctOption?->label}",
        ];
    }

    /**
     * Validate single choice answer format
     */
    public function validate(mixed $userAnswer, Question $question): array
    {
        if (is_null($userAnswer)) {
            return ['valid' => false, 'error' => 'Please select an answer'];
        }

        // Should be single option ID
        $optionId = is_array($userAnswer) ? $userAnswer[0] : $userAnswer;

        if (!$question->options()->where('id', $optionId)->exists()) {
            return ['valid' => false, 'error' => 'Selected option does not exist'];
        }

        return ['valid' => true];
    }

    /**
     * Render single choice question
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
            'type' => 'single_choice',
            'options' => $options,
            'shuffle_options' => $question->settings['shuffle_options'] ?? false,
        ]);
    }
}
