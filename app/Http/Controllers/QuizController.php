<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Quiz;
use App\Services\QuizService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function __construct(private QuizService $quizService)
    {
    }

    /**
     * Display a listing of published quizzes
     */
    public function index(): View
    {
        $quizzes = $this->quizService->getPublishedQuizzes(perPage: 15);

        return view('quizzes.index', [
            'quizzes' => $quizzes,
        ]);
    }

    /**
     * Show the form for creating a new quiz
     */
    public function create(): View
    {
        return view('quizzes.create');
    }

    /**
     * Store a newly created quiz in storage
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_published' => 'nullable|boolean',
            'pass_percentage' => 'required|integer|min:0|max:100',
        ]);

        $quiz = $this->quizService->createQuiz($validated);

        return redirect()->route('quizzes.show', $quiz)->with('success', 'Quiz created successfully!');
    }

    /**
     * Display the specified quiz with its questions
     */
    public function show(Quiz $quiz): View
    {
        $quiz->load('questions.options');
        $statistics = $this->quizService->getQuizStatistics($quiz);

        return view('quizzes.show', [
            'quiz' => $quiz,
            'questions' => $quiz->questions,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Show the form for editing the specified quiz
     */
    public function edit(Quiz $quiz): View
    {
        return view('quizzes.edit', [
            'quiz' => $quiz,
        ]);
    }

    /**
     * Update the specified quiz in storage
     */
    public function update(Request $request, Quiz $quiz): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_published' => 'nullable|boolean',
            'pass_percentage' => 'required|integer|min:0|max:100',
        ]);

        $quiz = $this->quizService->updateQuiz($quiz, $validated);

        return redirect()->route('quizzes.show', $quiz)->with('success', 'Quiz updated successfully!');
    }

    /**
     * Remove the specified quiz from storage
     */
    public function destroy(Quiz $quiz): RedirectResponse
    {
        $this->quizService->deleteQuiz($quiz);

        return redirect()->route('quizzes.index')->with('success', 'Quiz deleted successfully!');
    }

    /**
     * Display quiz management page (admin view)
     */
    public function manage(): View
    {
        $quizzes = Quiz::withCount(['questions', 'attempts'])
            ->latest('created_at')
            ->paginate(15);

        return view('quizzes.manage', [
            'quizzes' => $quizzes,
        ]);
    }

    /**
     * Store a new question for the quiz
     */
    public function storeQuestion(Request $request, Quiz $quiz): RedirectResponse
    {
        $settings = $request->input('settings', []);
        if (isset($settings['keywords_input']) && !isset($settings['keywords'])) {
            $settings['keywords'] = array_values(array_filter(array_map('trim', explode(',', (string) $settings['keywords_input']))));
        }

        $options = $request->input('options');
        if (!$options && $request->filled('options_payload')) {
            $decodedOptions = json_decode($request->input('options_payload'), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $options = $decodedOptions;
            }
        }

        $validated = $request->validate([
            'type' => 'required|in:boolean,single_choice,multiple_choice,short_answer,long_answer,text',
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

        $validated['settings'] = $settings;
        if ($options) {
            $validated['options'] = $options;
        }

        $question = $this->quizService->addQuestion($quiz, $validated);

        return redirect()->route('quizzes.show', $quiz)->with('success', 'Question added successfully!');
    }

    /**
     * Update a question
     */
    public function updateQuestion(Request $request, Quiz $quiz, Question $question): RedirectResponse
    {
        abort_unless($question->quiz_id === $quiz->id, 404);

        $settings = $request->input('settings', []);
        if (isset($settings['keywords_input']) && !isset($settings['keywords'])) {
            $settings['keywords'] = array_values(array_filter(array_map('trim', explode(',', (string) $settings['keywords_input']))));
        }

        $options = $request->input('options');
        if (!$options && $request->filled('options_payload')) {
            $decodedOptions = json_decode($request->input('options_payload'), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $options = $decodedOptions;
            }
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

        $validated['settings'] = $settings;
        if ($options) {
            $validated['options'] = $options;
        }

        $this->quizService->updateQuestion($question, $validated);

        return redirect()->route('quizzes.show', $quiz)->with('success', 'Question updated successfully!');
    }

    /**
     * Delete a question
     */
    public function destroyQuestion(Quiz $quiz, Question $question): RedirectResponse
    {
        abort_unless($question->quiz_id === $quiz->id, 404);
        $this->quizService->deleteQuestion($question);

        return redirect()->route('quizzes.show', $quiz)->with('success', 'Question deleted successfully!');
    }

    /**
     * Get available question types
     */
    public function getQuestionTypes()
    {
        return response()->json($this->quizService->getAvailableQuestionTypes());
    }
}
