<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\Quiz;
use App\Services\EvaluationService;
use App\Services\QuizService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiAttemptController extends Controller
{
    public function __construct(
        private QuizService $quizService,
        private EvaluationService $evaluationService
    ) {
    }

    /**
     * POST /api/quizzes/{quiz}/attempts
     * Start a new quiz attempt
     */
    public function start(Request $request, Quiz $quiz): JsonResponse
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

            $validated = $request->validate([
                'user_name' => 'required|string|max:255',
                'user_email' => 'nullable|email|max:255',
                'user_identifier' => 'nullable|string|max:255',
            ]);

            $attempt = $this->quizService->startAttempt($quiz, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Attempt started',
                'data' => [
                    'attempt_id' => $attempt->id,
                    'quiz_id' => $quiz->id,
                    'started_at' => $attempt->started_at,
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
                ],
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
                'error' => 'Failed to start attempt',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/attempts/{attempt}/submit
     * Submit quiz answers
     */
    public function submit(Request $request, Attempt $attempt): JsonResponse
    {
        try {
            $attempt->load('quiz');

            if ($attempt->status !== 'in_progress') {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid attempt status',
                    'message' => 'This attempt has already been submitted',
                ], 422);
            }

            $answers = $request->input('answers', []);

            // Validate all questions answered
            $requiredQuestions = $attempt->quiz->questions()->pluck('id')->toArray();
            $missingQuestions = array_diff($requiredQuestions, array_keys($answers));

            if (!empty($missingQuestions)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Incomplete submission',
                    'message' => 'All questions must be answered',
                    'missing_questions' => $missingQuestions,
                ], 422);
            }

            // Evaluate
            $result = $this->quizService->submitQuizAnswers($attempt, $answers);

            // Record time if provided
            if ($request->has('time_spent')) {
                $attempt->update(['time_spent_seconds' => $request->input('time_spent')]);
            }

            // Refresh to get updated data
            $attempt->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Quiz submitted successfully',
                'data' => [
                    'attempt_id' => $attempt->id,
                    'score' => $attempt->total_score,
                    'marks' => $attempt->total_marks,
                    'percentage' => $attempt->getPercentage(),
                    'is_passed' => $attempt->is_passed,
                    'performance_level' => $attempt->getPerformanceLevel(),
                    'time_spent_seconds' => $attempt->time_spent_seconds,
                    'submitted_at' => $attempt->submitted_at,
                ],
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
                'error' => 'Failed to submit quiz',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/attempts/{attempt}
     * Get attempt details
     */
    public function show(Attempt $attempt): JsonResponse
    {
        try {
            if ($attempt->status !== 'evaluated') {
                return response()->json([
                    'success' => false,
                    'error' => 'Attempt not evaluated',
                    'message' => 'This attempt has not been evaluated yet',
                ], 422);
            }

            $attempt->load('quiz', 'answers.question');

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $attempt->id,
                    'quiz_id' => $attempt->quiz_id,
                    'user_name' => $attempt->user_name,
                    'user_email' => $attempt->user_email,
                    'user_identifier' => $attempt->user_identifier,
                    'score' => $attempt->total_score,
                    'marks' => $attempt->total_marks,
                    'percentage' => $attempt->getPercentage(),
                    'is_passed' => $attempt->is_passed,
                    'performance_level' => $attempt->getPerformanceLevel(),
                    'time_spent_seconds' => $attempt->time_spent_seconds,
                    'started_at' => $attempt->started_at,
                    'submitted_at' => $attempt->submitted_at,
                    'answers' => $attempt->answers->map(fn($a) => [
                        'question_id' => $a->question_id,
                        'question_text' => $a->question->question_text,
                        'question_type' => $a->question_type,
                        'user_answer' => $a->user_answer,
                        'score' => $a->score,
                        'marks' => $a->question->marks,
                        'is_correct' => $a->is_correct,
                        'feedback' => $a->feedback,
                    ])->toArray(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch attempt',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/attempts/{attempt}/statistics
     * Get attempt analytics
     */
    public function statistics(Attempt $attempt): JsonResponse
    {
        try {
            $attempt->load('answers');

            $analytics = $this->evaluationService->getPerformanceAnalytics($attempt);

            return response()->json([
                'success' => true,
                'data' => [
                    'score' => $attempt->total_score,
                    'marks' => $attempt->total_marks,
                    'percentage' => $attempt->getPercentage(),
                    'is_passed' => $attempt->is_passed,
                    'performance_level' => $attempt->getPerformanceLevel(),
                    'time_spent_seconds' => $attempt->time_spent_seconds,
                    'analytics' => $analytics,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch statistics',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/attempts/{attempt}/save-answer
     * Auto-save an answer
     */
    public function saveAnswer(Request $request, Attempt $attempt): JsonResponse
    {
        try {
            if ($attempt->status !== 'in_progress') {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid status',
                    'message' => 'Attempt is not in progress',
                ], 422);
            }

            $attempt->load('quiz');

            $validated = $request->validate([
                'question_id' => 'required|exists:questions,id',
                'answer' => 'nullable',
            ]);

            $question = $attempt->quiz->questions()->findOrFail($validated['question_id']);

            // Validate answer
            $validation = $this->evaluationService->validateAnswer($question, $validated['answer']);

            if (!$validation['valid']) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid answer',
                    'message' => $validation['error'],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Answer saved',
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
                'error' => 'Failed to save answer',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/quizzes/{quiz}/attempts
     * List all attempts for a quiz (admin)
     */
    public function listAttempts(Request $request, Quiz $quiz): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 20);

            $attempts = $quiz->attempts()
                ->where('status', 'evaluated')
                ->latest('submitted_at')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $attempts->items(),
                'meta' => [
                    'total' => $attempts->total(),
                    'per_page' => $attempts->perPage(),
                    'current_page' => $attempts->currentPage(),
                    'last_page' => $attempts->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch attempts',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
