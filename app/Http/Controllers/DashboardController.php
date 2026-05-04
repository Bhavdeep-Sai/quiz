<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Attempt;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard
     */
    public function index(): View
    {
        $quizzes = Quiz::where('is_published', true)
            ->withCount(['questions', 'attempts'])
            ->latest()
            ->take(6)
            ->get();

        $totalAttempts = Attempt::count();
        $totalQuizzes = Quiz::count();
        $totalQuestions = $quizzes->sum('questions_count');
        $successfulAttempts = Attempt::where('is_passed', true)->count();

        $attemptScore = Attempt::sum('total_score');
        $attemptMarks = Attempt::sum('total_marks');

        $avgScore = $totalAttempts > 0 && $attemptMarks > 0
            ? round(($attemptScore / $attemptMarks) * 100, 1)
            : 0;

        return view('dashboard', [
            'quizzes' => $quizzes,
            'stats' => [
                'total_quizzes' => $totalQuizzes,
                'total_questions' => $totalQuestions,
                'total_attempts' => $totalAttempts,
                'avg_score' => $avgScore,
            ],
        ]);
    }
}
