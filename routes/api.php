<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiQuizController;
use App\Http\Controllers\Api\ApiQuestionController;
use App\Http\Controllers\Api\ApiAttemptController;
use App\Http\Controllers\Api\ApiHealthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Health & Status Endpoints
Route::get('/health', [ApiHealthController::class, 'check']);
Route::get('/status', [ApiHealthController::class, 'status']);

// Public API Routes
Route::prefix('v1')->group(function () {
    
    // Quiz endpoints (public read)
    Route::get('/quizzes', [ApiQuizController::class, 'index']);
    Route::get('/quizzes/{quiz}', [ApiQuizController::class, 'show']);
    Route::get('/quiz-types', [ApiQuizController::class, 'getQuestionTypes']);
    
    // Quiz statistics
    Route::get('/quizzes/{quiz}/statistics', [ApiQuizController::class, 'statistics']);
    
    // Question endpoints (read)
    Route::get('/quizzes/{quiz}/questions', [ApiQuestionController::class, 'index']);
    Route::get('/quizzes/{quiz}/questions/{question}', [ApiQuestionController::class, 'show']);
    
    // Attempt endpoints
    Route::post('/quizzes/{quiz}/attempts', [ApiAttemptController::class, 'start']);
    Route::post('/attempts/{attempt}/submit', [ApiAttemptController::class, 'submit']);
    Route::get('/attempts/{attempt}', [ApiAttemptController::class, 'show']);
    Route::get('/attempts/{attempt}/statistics', [ApiAttemptController::class, 'statistics']);
    Route::post('/attempts/{attempt}/save-answer', [ApiAttemptController::class, 'saveAnswer']);
    
    // Admin endpoints (quiz management)
    Route::prefix('admin')->group(function () {
        Route::post('/quizzes', [ApiQuizController::class, 'store']);
        Route::put('/quizzes/{quiz}', [ApiQuizController::class, 'update']);
        Route::delete('/quizzes/{quiz}', [ApiQuizController::class, 'destroy']);
        
        // Questions
        Route::post('/quizzes/{quiz}/questions', [ApiQuestionController::class, 'store']);
        Route::put('/quizzes/{quiz}/questions/{question}', [ApiQuestionController::class, 'update']);
        Route::delete('/quizzes/{quiz}/questions/{question}', [ApiQuestionController::class, 'destroy']);
        
        // Attempts
        Route::get('/quizzes/{quiz}/attempts', [ApiAttemptController::class, 'listAttempts']);
    });
});

// API Documentation
Route::get('/docs', function (Request $request) {
    $docs = [
        'name' => 'Dynamic Quiz System API',
        'version' => '1.0.0',
        'base_url' => config('app.url') . '/api/v1',
        'endpoints' => [
            'health' => [
                'GET /api/health' => 'Check application health',
                'GET /api/status' => 'Get detailed system status',
            ],
            'quizzes' => [
                'GET /api/v1/quizzes' => 'List all published quizzes',
                'GET /api/v1/quizzes/{id}' => 'Get quiz details with questions',
                'POST /api/v1/admin/quizzes' => 'Create new quiz (admin)',
                'PUT /api/v1/admin/quizzes/{id}' => 'Update quiz (admin)',
                'DELETE /api/v1/admin/quizzes/{id}' => 'Delete quiz (admin)',
                'GET /api/v1/quizzes/{id}/statistics' => 'Get quiz statistics',
            ],
            'questions' => [
                'GET /api/v1/quizzes/{quiz_id}/questions' => 'List all questions for quiz',
                'GET /api/v1/quizzes/{quiz_id}/questions/{id}' => 'Get question details',
                'POST /api/v1/admin/quizzes/{quiz_id}/questions' => 'Create question (admin)',
                'PUT /api/v1/admin/quizzes/{quiz_id}/questions/{id}' => 'Update question (admin)',
                'DELETE /api/v1/admin/quizzes/{quiz_id}/questions/{id}' => 'Delete question (admin)',
            ],
            'attempts' => [
                'POST /api/v1/quizzes/{quiz_id}/attempts' => 'Start new quiz attempt',
                'POST /api/v1/attempts/{id}/submit' => 'Submit quiz answers',
                'GET /api/v1/attempts/{id}' => 'Get attempt details',
                'GET /api/v1/attempts/{id}/statistics' => 'Get attempt statistics',
                'POST /api/v1/attempts/{id}/save-answer' => 'Auto-save an answer',
                'GET /api/v1/admin/quizzes/{quiz_id}/attempts' => 'List all attempts for quiz (admin)',
            ],
            'types' => [
                'GET /api/v1/quiz-types' => 'Get available question types',
            ],
        ],
        'request_format' => 'application/json',
        'response_format' => 'application/json',
        'authentication' => 'None (public API)',
        'example_response' => [
            'success' => true,
            'message' => 'Operation completed',
            'data' => [],
            'meta' => [
                'timestamp' => '2026-05-02T10:00:00Z',
            ],
        ],
    ];

    return response()->json($docs);
});
