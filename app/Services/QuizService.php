<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\Option;
use App\Models\Attempt;
use Illuminate\Pagination\Paginator;

/**
 * Quiz Service
 * 
 * Handles business logic for quiz management
 */
class QuizService
{
    protected EvaluationService $evaluationService;

    public function __construct(EvaluationService $evaluationService)
    {
        $this->evaluationService = $evaluationService;
    }

    /**
     * Create a new quiz
     */
    public function createQuiz(array $data): Quiz
    {
        return Quiz::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'is_published' => $data['is_published'] ?? false,
            'pass_percentage' => $data['pass_percentage'] ?? 50,
        ]);
    }

    /**
     * Update a quiz
     */
    public function updateQuiz(Quiz $quiz, array $data): Quiz
    {
        $quiz->update([
            'title' => $data['title'] ?? $quiz->title,
            'description' => $data['description'] ?? $quiz->description,
            'is_published' => $data['is_published'] ?? $quiz->is_published,
            'pass_percentage' => $data['pass_percentage'] ?? $quiz->pass_percentage,
        ]);

        return $quiz;
    }

    /**
     * Delete a quiz
     */
    public function deleteQuiz(Quiz $quiz): bool
    {
        return $quiz->delete();
    }

    /**
     * Add question to quiz
     */
    public function addQuestion(Quiz $quiz, array $data): Question
    {
        // Validate type-specific requirements
        $type = $data['type'];
        $normalizedType = in_array($type, ['short_answer', 'long_answer'], true) ? Question::TYPE_SHORT_ANSWER : $type;

        if (in_array($type, ['short_answer', 'long_answer'], true)) {
            $data['settings'] = $data['settings'] ?? [];
            $data['settings']['answer_subtype'] = $type;
            $data['settings']['grade_mode'] = $type === 'short_answer' ? 'exact' : 'manual';
            $data['settings']['input_type'] = $type === 'short_answer' ? 'text' : 'textarea';
        }

        // Enforce objective question rules
        if (in_array($type, [
            Question::TYPE_MCQ_SINGLE,
            Question::TYPE_MCQ_MULTIPLE,
        ])) {
            if (empty($data['options']) || !is_array($data['options']) || count($data['options']) < 2) {
                throw new \InvalidArgumentException('MCQ questions require at least 2 options.');
            }

            $correctCount = 0;
            foreach ($data['options'] as $opt) {
                if (!empty($opt['is_correct'])) {
                    $correctCount++;
                }
            }

            if ($correctCount < 1) {
                throw new \InvalidArgumentException('At least one correct option must be provided.');
            }

            if ($type === Question::TYPE_MCQ_SINGLE && $correctCount !== 1) {
                throw new \InvalidArgumentException('Single choice questions must have exactly one correct option.');
            }
        }

        // For boolean type, auto-generate True/False options if options not provided
        if ($type === Question::TYPE_TRUE_FALSE && (empty($data['options']) || !is_array($data['options']))) {
            // settings may include correct value as boolean under settings.correct
            $correctValue = $data['settings']['correct'] ?? null; // true/false
            $data['options'] = [
                ['label' => 'True', 'is_correct' => $correctValue === true],
                ['label' => 'False', 'is_correct' => $correctValue === false],
            ];
        }

        $question = $quiz->questions()->create([
            'type' => $normalizedType,
            'question_text' => $data['question_text'],
            'image_url' => $data['image_url'] ?? null,
            'video_url' => $data['video_url'] ?? null,
            'marks' => $data['marks'] ?? 1,
            'settings' => $data['settings'] ?? [],
            'sort_order' => $quiz->questions()->count(),
        ]);

        // Add options if provided
        if (isset($data['options']) && is_array($data['options'])) {
            foreach ($data['options'] as $index => $optionData) {
                Option::create([
                    'question_id' => $question->id,
                    'label' => $optionData['label'],
                    'image_url' => $optionData['image_url'] ?? null,
                    'is_correct' => $optionData['is_correct'] ?? false,
                    'sort_order' => $index,
                ]);
            }
        }

        return $question;
    }

    /**
     * Update a question
     */
    public function updateQuestion(Question $question, array $data): Question
    {
        $type = $data['type'] ?? $question->type;

        if (isset($data['settings']['answer_subtype']) && in_array($data['settings']['answer_subtype'], ['short_answer', 'long_answer'], true)) {
            $data['settings']['grade_mode'] = $data['settings']['answer_subtype'] === 'short_answer' ? 'exact' : 'manual';
            $data['settings']['input_type'] = $data['settings']['answer_subtype'] === 'short_answer' ? 'text' : 'textarea';
        }

        if (in_array($type, ['short_answer', 'long_answer'], true)) {
            $data['settings'] = $data['settings'] ?? [];
            $data['settings']['answer_subtype'] = $type;
            $data['settings']['grade_mode'] = $type === 'short_answer' ? 'exact' : 'manual';
            $data['settings']['input_type'] = $type === 'short_answer' ? 'text' : 'textarea';
        }

        // If updating to a choice type, validate options rules
        if (isset($data['options']) && is_array($data['options']) && in_array($type, [Question::TYPE_MCQ_SINGLE, Question::TYPE_MCQ_MULTIPLE])) {
            if (count($data['options']) < 2) {
                throw new \InvalidArgumentException('MCQ questions require at least 2 options.');
            }

            $correctCount = 0;
            foreach ($data['options'] as $opt) {
                if (!empty($opt['is_correct'])) {
                    $correctCount++;
                }
            }

            if ($correctCount < 1) {
                throw new \InvalidArgumentException('At least one correct option must be provided.');
            }

            if ($type === Question::TYPE_MCQ_SINGLE && $correctCount !== 1) {
                throw new \InvalidArgumentException('Single choice questions must have exactly one correct option.');
            }
        }

        $question->update([
            'question_text' => $data['question_text'] ?? $question->question_text,
            'image_url' => $data['image_url'] ?? $question->image_url,
            'video_url' => $data['video_url'] ?? $question->video_url,
            'marks' => $data['marks'] ?? $question->marks,
            'settings' => $data['settings'] ?? $question->settings,
        ]);

        // Update options if provided
        if (isset($data['options'])) {
            $question->options()->delete();
            foreach ($data['options'] as $index => $optionData) {
                Option::create([
                    'question_id' => $question->id,
                    'label' => $optionData['label'],
                    'image_url' => $optionData['image_url'] ?? null,
                    'is_correct' => $optionData['is_correct'] ?? false,
                    'sort_order' => $index,
                ]);
            }
        }

        return $question;
    }

    /**
     * Delete a question
     */
    public function deleteQuestion(Question $question): bool
    {
        return $question->delete();
    }

    /**
     * Start a quiz attempt
     */
    public function startAttempt(Quiz $quiz, array $userData): Attempt
    {
        return Attempt::create([
            'quiz_id' => $quiz->id,
            'user_identifier' => $userData['user_identifier'] ?? null,
            'user_name' => $userData['user_name'] ?? null,
            'user_email' => $userData['user_email'] ?? null,
            'started_at' => now(),
            'status' => 'in_progress',
        ]);
    }

    /**
     * Submit quiz answers
     */
    public function submitQuizAnswers(Attempt $attempt, array $answers): array
    {
        return $this->evaluationService->evaluateAttempt($attempt, $answers);
    }

    /**
     * Get quiz statistics
     */
    public function getQuizStatistics(Quiz $quiz): array
    {
        $attempts = $quiz->attempts()->where('status', 'evaluated')->get();

        if ($attempts->isEmpty()) {
            return [
                'total_attempts' => 0,
                'successful_attempts' => 0,
                'success_rate' => 0,
                'average_score' => 0,
                'average_percentage' => 0,
            ];
        }

        $totalAttempts = $attempts->count();
        $successfulAttempts = $attempts->where('is_passed', true)->count();
        $averageScore = $attempts->avg('total_score');
        $totalMarks = $quiz->getTotalMarks();
        $averagePercentage = $totalMarks > 0 ? ($averageScore / $totalMarks) * 100 : 0;

        return [
            'total_attempts' => $totalAttempts,
            'successful_attempts' => $successfulAttempts,
            'success_rate' => round(($successfulAttempts / $totalAttempts) * 100, 2),
            'average_score' => round($averageScore, 2),
            'average_percentage' => round($averagePercentage, 2),
            'total_marks' => $totalMarks,
        ];
    }

    /**
     * Get all quizzes (paginated)
     */
    public function getAllQuizzes(int $perPage = 15)
    {
        return Quiz::paginate($perPage);
    }

    /**
     * Get published quizzes only
     */
    public function getPublishedQuizzes(int $perPage = 15)
    {
        return Quiz::where('is_published', true)
            ->withCount(['questions', 'attempts'])
            ->latest('created_at')
            ->paginate($perPage);
    }

    /**
     * Get available question types
     */
    public function getAvailableQuestionTypes(): array
    {
        return [
            [
                'value' => 'boolean',
                'label' => 'True / False',
                'description' => 'Select true or false and mark the correct one',
                'icon' => '📋',
            ],
            [
                'value' => 'single_choice',
                'label' => 'MCQ (Single)',
                'description' => 'One correct option among many',
                'icon' => '⭕',
            ],
            [
                'value' => 'multiple_choice',
                'label' => 'MCQ (Multiple)',
                'description' => 'One or more correct options',
                'icon' => '✓',
            ],
            [
                'value' => 'short_answer',
                'label' => 'Single Line Answer',
                'description' => 'Enter the expected short answer when creating the question',
                'icon' => '✎',
            ],
            [
                'value' => 'long_answer',
                'label' => 'Long Answer',
                'description' => 'Enter a model answer or rubric for manual grading',
                'icon' => '📝',
            ],
        ];
    }
}
