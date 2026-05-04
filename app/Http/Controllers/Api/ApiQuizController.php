<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Services\QuizService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiQuizController extends Controller
{
    public function __construct(private QuizService $quizService)
    {
    }

    /**
     * GET /api/quizzes
     * List all published quizzes
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $quizzes = $this->quizService->getPublishedQuizzes($perPage);

            return response()->json([
                'success' => true,
                'data' => $quizzes->items(),
                'meta' => [
                    'total' => $quizzes->total(),
                    'per_page' => $quizzes->perPage(),
                    'current_page' => $quizzes->currentPage(),
                    'last_page' => $quizzes->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch quizzes',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/quizzes/{id}
     * Get quiz details with questions
     */
    public function show(Quiz $quiz): JsonResponse
    {
        try {
            if (!$quiz->is_published) {
                return response()->json([
                    'success' => false,
                    'error' => 'Quiz not available',
                    'message' => 'This quiz is not published',
                ], 403);
            }

            $quiz->load('questions.options');
            $statistics = $this->quizService->getQuizStatistics($quiz);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'description' => $quiz->description,
                    'pass_percentage' => $quiz->pass_percentage,
                    'total_marks' => $quiz->questions->sum('marks'),
                    'question_count' => $quiz->questions->count(),
                    'questions' => $quiz->questions->map(fn($q) => [
                        'id' => $q->id,
                        'type' => $q->type,
                        'question_text' => $q->question_text,
                        'image_url' => $q->image_url,
                        'video_url' => $q->video_url,
                        'marks' => $q->marks,
                        'options' => $q->options->map(fn($o) => [
                            'id' => $o->id,
                            'label' => $o->label,
                            'image_url' => $o->image_url,
                        ])->toArray(),
                    ])->toArray(),
                    'statistics' => $statistics,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch quiz',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/quizzes
     * Create a new quiz (admin only)
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'pass_percentage' => 'required|integer|min:0|max:100',
                'is_published' => 'nullable|boolean',
            ]);

            $quiz = $this->quizService->createQuiz($validated);

            return response()->json([
                'success' => true,
                'message' => 'Quiz created successfully',
                'data' => $quiz,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to create quiz',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * PUT /api/quizzes/{id}
     * Update quiz (admin only)
     */
    public function update(Request $request, Quiz $quiz): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'pass_percentage' => 'required|integer|min:0|max:100',
                'is_published' => 'nullable|boolean',
            ]);

            $quiz = $this->quizService->updateQuiz($quiz, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Quiz updated successfully',
                'data' => $quiz,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to update quiz',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /api/quizzes/{id}
     * Delete quiz (admin only)
     */
    public function destroy(Quiz $quiz): JsonResponse
    {
        try {
            $this->quizService->deleteQuiz($quiz);

            return response()->json([
                'success' => true,
                'message' => 'Quiz deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete quiz',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/quiz-types
     * Get available question types
     */
    public function getQuestionTypes(): JsonResponse
    {
        try {
            $types = $this->quizService->getAvailableQuestionTypes();

            return response()->json([
                'success' => true,
                'data' => $types,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch question types',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/quizzes/{id}/statistics
     * Get quiz statistics
     */
    public function statistics(Quiz $quiz): JsonResponse
    {
        try {
            $statistics = $this->quizService->getQuizStatistics($quiz);

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch statistics',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
