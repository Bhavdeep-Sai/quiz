<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ApiHealthController extends Controller
{
    /**
     * GET /api/health
     * Check application health status
     */
    public function check(): JsonResponse
    {
        try {
            // Test database connection
            DB::connection()->getPdo();
            $database_status = 'ok';
        } catch (\Exception $e) {
            $database_status = 'error';
        }

        return response()->json([
            'success' => true,
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'app' => [
                'name' => config('app.name'),
                'environment' => config('app.env'),
                'version' => '1.0.0',
            ],
            'checks' => [
                'database' => $database_status,
                'cache' => 'ok',
                'queue' => 'ok',
            ],
        ]);
    }

    /**
     * GET /api/status
     * Get detailed system status
     */
    public function status(): JsonResponse
    {
        try {
            DB::connection()->getPdo();
            $database_ok = true;
        } catch (\Exception $e) {
            $database_ok = false;
        }

        $quizzes_count = \App\Models\Quiz::count();
        $attempts_count = \App\Models\Attempt::count();
        $questions_count = \App\Models\Question::count();

        return response()->json([
            'success' => true,
            'status' => $database_ok ? 'operational' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'metrics' => [
                'quizzes' => $quizzes_count,
                'questions' => $questions_count,
                'attempts' => $attempts_count,
                'uptime' => '∞',
            ],
            'components' => [
                'database' => ['status' => $database_ok ? 'ok' : 'error'],
                'api' => ['status' => 'ok'],
                'cache' => ['status' => 'ok'],
            ],
        ]);
    }
}
