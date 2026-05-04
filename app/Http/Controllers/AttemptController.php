<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\Quiz;
use App\Services\EvaluationService;
use App\Services\QuizService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AttemptController extends Controller
{
    public function __construct(
        private QuizService $quizService,
        private EvaluationService $evaluationService
    ) {
    }

    /**
     * Show the quiz start page
     */
    public function start(Quiz $quiz): View
    {
        if (!$quiz->is_published) {
            abort(403, 'This quiz is not published');
        }

        $quiz->load('questions.options');

        return view('attempts.start-new', [
            'quiz' => $quiz,
        ]);
    }

    /**
     * Create a new quiz attempt
     */
    public function store(Request $request, Quiz $quiz): View
    {
        if (!$quiz->is_published) {
            abort(403, 'This quiz is not published');
        }

        // Validate user information
        $validated = $request->validate([
            'user_name' => 'required|string|max:255',
            'user_email' => 'nullable|email|max:255',
            'user_identifier' => 'nullable|string|max:255',
        ]);

        // Create attempt record
        $attempt = $this->quizService->startAttempt($quiz, $validated);

        // Load questions with options for display
        $quiz->load('questions.options');

        return view('attempts.show', [
            'quiz' => $quiz,
            'attempt' => $attempt,
            'questions' => $quiz->questions,
            'currentQuestion' => $quiz->questions->first(),
            'totalQuestions' => $quiz->questions->count(),
        ]);
    }

    /**
     * Display quiz form for an existing attempt
     */
    public function show(Attempt $attempt): View
    {
        if ($attempt->status !== 'in_progress') {
            return redirect()->route('attempts.result', $attempt);
        }

        $quiz = $attempt->quiz;
        $quiz->load('questions.options');

        return view('attempts.show-new', [
            'quiz' => $quiz,
            'attempt' => $attempt,
            'questions' => $quiz->questions,
            'currentQuestion' => $quiz->questions->first(),
            'totalQuestions' => $quiz->questions->count(),
        ]);
    }

    /**
     * Submit quiz answers
     */
    public function submit(Request $request, Attempt $attempt): RedirectResponse
    {
        $attempt->load('quiz');

        // Validate that attempt is still in progress
        if ($attempt->status !== 'in_progress') {
            return redirect()->route('attempts.result', $attempt)->with('error', 'This attempt has already been submitted');
        }

        // Get all answers from request
        $answers = $request->input('answers', []);

        // Validate that all questions have answers
        $requiredQuestions = $attempt->quiz->questions()->pluck('id')->toArray();
        $missingQuestions = array_diff($requiredQuestions, array_keys($answers));

        if (!empty($missingQuestions)) {
            return redirect()->back()->with('error', 'Please answer all questions before submitting');
        }

        // Evaluate attempt
        $result = $this->quizService->submitQuizAnswers($attempt, $answers);

        // Record time spent
        if ($request->has('time_spent')) {
            $attempt->update(['time_spent_seconds' => $request->input('time_spent')]);
        }

        return redirect()->route('attempts.result', $attempt)->with('success', 'Quiz submitted successfully!');
    }

    /**
     * Display quiz results
     */
    public function result(Attempt $attempt): View
    {
        if ($attempt->status !== 'evaluated') {
            abort(403, 'This attempt has not been evaluated yet');
        }

        $attempt->load('quiz', 'answers.question');

        // Get analytics
        $analytics = $this->evaluationService->getPerformanceAnalytics($attempt);

        return view('attempts.result-new', [
            'attempt' => $attempt,
            'quiz' => $attempt->quiz,
            'answers' => $attempt->answers,
            'analytics' => $analytics,
            'performanceLevel' => $attempt->getPerformanceLevel(),
            'percentage' => $attempt->getPercentage(),
        ]);
    }

    /**
     * Display a specific question during quiz
     */
    public function getQuestion(Request $request, Attempt $attempt): array
    {
        $questionId = $request->input('question_id');
        $question = $attempt->quiz->questions()->findOrFail($questionId);

        $handler = \App\QuestionTypes\QuestionTypeResolver::resolve($question->type);
        $renderData = $handler->renderData($question);

        return response()->json($renderData)->getData(true);
    }

    /**
     * Save answer for a question (auto-save feature)
     */
    public function saveAnswer(Request $request, Attempt $attempt): array
    {
        if ($attempt->status !== 'in_progress') {
            return response()->json(['error' => 'Attempt is not in progress'], 422)->getData(true);
        }

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
                'error' => $validation['error'],
            ])->getData(true);
        }

        return response()->json([
            'success' => true,
            'message' => 'Answer saved',
        ])->getData(true);
    }

    /**
     * List all attempts for a quiz (admin/results view)
     */
    public function listAttempts(Quiz $quiz)
    {
        $attempts = $quiz->attempts()
            ->where('status', 'evaluated')
            ->latest('submitted_at')
            ->paginate(20);

        return view('attempts.list', [
            'quiz' => $quiz,
            'attempts' => $attempts,
        ]);
    }

    /**
     * Get attempt statistics (JSON)
     */
    public function statistics(Attempt $attempt)
    {
        $analytics = $this->evaluationService->getPerformanceAnalytics($attempt);

        return response()->json([
            'attempt_id' => $attempt->id,
            'score' => $attempt->total_score,
            'marks' => $attempt->total_marks,
            'percentage' => $attempt->getPercentage(),
            'is_passed' => $attempt->is_passed,
            'performance_level' => $attempt->getPerformanceLevel(),
            'analytics' => $analytics,
        ]);
    }
}
