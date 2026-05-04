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
        $question = $quiz->questions()->create([
            'type' => $data['type'],
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
        $types = \App\QuestionTypes\QuestionTypeResolver::getAvailableTypes();
        $formatted = [];

        foreach ($types as $key => $data) {
            $formatted[] = [
                'value' => $key,
                'label' => $data['name'],
                'description' => $data['description'],
                'icon' => $data['icon'] ?? null,
            ];
        }

        return $formatted;
    }
}
