<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Question;
use App\Services\QuizService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiQuestionController extends Controller
{
    public function __construct(private QuizService $quizService)
    {
    }

    /**
     * GET /api/quizzes/{quiz}/questions
     * List all questions for a quiz
     */
    public function index(Quiz $quiz): JsonResponse
    {
        try {
            $questions = $quiz->questions()
                ->with('options')
                ->orderBy('sort_order')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $questions->map(fn($q) => [
                    'id' => $q->id,
                    'type' => $q->type,
                    'question_text' => $q->question_text,
                    'image_url' => $q->image_url,
                    'video_url' => $q->video_url,
                    'marks' => $q->marks,
                    'settings' => $q->settings,
                    'sort_order' => $q->sort_order,
                    'options' => $q->options->map(fn($o) => [
                        'id' => $o->id,
                        'label' => $o->label,
                        'image_url' => $o->image_url,
                        'is_correct' => $o->is_correct,
                        'sort_order' => $o->sort_order,
                    ])->toArray(),
                ])->toArray(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch questions',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/quizzes/{quiz}/questions/{question}
     * Get single question details
     */
    public function show(Quiz $quiz, Question $question): JsonResponse
    {
        try {
            // Verify question belongs to quiz
            if ($question->quiz_id !== $quiz->id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Not found',
                    'message' => 'Question does not belong to this quiz',
                ], 404);
            }

            $question->load('options');

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $question->id,
                    'type' => $question->type,
                    'question_text' => $question->question_text,
                    'image_url' => $question->image_url,
                    'video_url' => $question->video_url,
                    'marks' => $question->marks,
                    'settings' => $question->settings,
                    'sort_order' => $question->sort_order,
                    'options' => $question->options->map(fn($o) => [
                        'id' => $o->id,
                        'label' => $o->label,
                        'image_url' => $o->image_url,
                        'is_correct' => $o->is_correct,
                        'sort_order' => $o->sort_order,
                    ])->toArray(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch question',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/quizzes/{quiz}/questions
     * Create a new question
     */
    public function store(Request $request, Quiz $quiz): JsonResponse
    {
        try {
            $validated = $request->validate([
                'type' => 'required|in:boolean,single_choice,multiple_choice,number,text',
                'question_text' => 'required|string',
                'image_url' => 'nullable|url',
                'video_url' => 'nullable|url',
                'marks' => 'required|integer|min:1',
                'settings' => 'nullable|array',
                'options' => 'nullable|array',
                'options.*.label' => 'required_with:options|string',
                'options.*.is_correct' => 'nullable|boolean',
                'options.*.image_url' => 'nullable|url',
            ]);

            $validated['quiz_id'] = $quiz->id;
            $question = $this->quizService->addQuestion($quiz, $validated);
            $question->load('options');

            return response()->json([
                'success' => true,
                'message' => 'Question created successfully',
                'data' => [
                    'id' => $question->id,
                    'type' => $question->type,
                    'question_text' => $question->question_text,
                    'marks' => $question->marks,
                    'options' => $question->options->toArray(),
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
                'error' => 'Failed to create question',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * PUT /api/quizzes/{quiz}/questions/{question}
     * Update a question
     */
    public function update(Request $request, Quiz $quiz, Question $question): JsonResponse
    {
        try {
            // Verify question belongs to quiz
            if ($question->quiz_id !== $quiz->id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Not found',
                    'message' => 'Question does not belong to this quiz',
                ], 404);
            }

            $validated = $request->validate([
                'question_text' => 'required|string',
                'image_url' => 'nullable|url',
                'video_url' => 'nullable|url',
                'marks' => 'required|integer|min:1',
                'settings' => 'nullable|array',
                'options' => 'nullable|array',
                'options.*.label' => 'required_with:options|string',
                'options.*.is_correct' => 'nullable|boolean',
                'options.*.image_url' => 'nullable|url',
            ]);

            $this->quizService->updateQuestion($question, $validated);
            $question->load('options');

            return response()->json([
                'success' => true,
                'message' => 'Question updated successfully',
                'data' => [
                    'id' => $question->id,
                    'type' => $question->type,
                    'question_text' => $question->question_text,
                    'marks' => $question->marks,
                    'options' => $question->options->toArray(),
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
                'error' => 'Failed to update question',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /api/quizzes/{quiz}/questions/{question}
     * Delete a question
     */
    public function destroy(Quiz $quiz, Question $question): JsonResponse
    {
        try {
            // Verify question belongs to quiz
            if ($question->quiz_id !== $quiz->id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Not found',
                    'message' => 'Question does not belong to this quiz',
                ], 404);
            }

            $this->quizService->deleteQuestion($question);

            return response()->json([
                'success' => true,
                'message' => 'Question deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete question',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
