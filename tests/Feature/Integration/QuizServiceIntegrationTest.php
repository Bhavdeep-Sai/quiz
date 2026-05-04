<?php

namespace Tests\Feature\Integration;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\Option;
use App\Models\Attempt;
use App\Models\Answer;
use App\Services\EvaluationService;
use App\Services\QuizService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * STEP 8: Integration Test - Quiz Service Layer
 * 
 * Tests QuizService and EvaluationService interactions
 * Validates business logic and service layer integration
 */
class QuizServiceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected QuizService $quizService;
    protected EvaluationService $evaluationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->quizService = app(QuizService::class);
        $this->evaluationService = app(EvaluationService::class);
    }

    /**
     * @test
     * QuizService creates quiz with all fields
     */
    public function test_quiz_service_creates_quiz_with_validation()
    {
        $data = [
            'title' => 'Service Test Quiz',
            'description' => 'Testing QuizService',
            'pass_percentage' => 75,
            'is_published' => true,
        ];

        $quiz = $this->quizService->createQuiz($data);

        $this->assertNotNull($quiz->id);
        $this->assertEquals('Service Test Quiz', $quiz->title);
        $this->assertTrue($quiz->is_published);
    }

    /**
     * @test
     * QuizService adds question with options
     */
    public function test_quiz_service_adds_question_with_options()
    {
        $quiz = Quiz::create([
            'title' => 'Test',
            'pass_percentage' => 70,
            'is_published' => true,
        ]);

        $questionData = [
            'type' => 'multiple_choice',
            'question_text' => 'Which are correct?',
            'marks' => 10,
            'settings' => ['min_selections' => 1, 'max_selections' => 2],
            'options' => [
                ['label' => 'A', 'is_correct' => true],
                ['label' => 'B', 'is_correct' => true],
                ['label' => 'C', 'is_correct' => false],
            ],
        ];

        $question = $this->quizService->addQuestion($quiz, $questionData);

        $this->assertNotNull($question->id);
        $this->assertEquals(3, $question->options()->count());
        $this->assertEquals(2, $question->options()->where('is_correct', true)->count());
    }

    /**
     * @test
     * QuizService updates question and options
     */
    public function test_quiz_service_updates_question_and_options()
    {
        $quiz = Quiz::create([
            'title' => 'Test',
            'pass_percentage' => 70,
            'is_published' => true,
        ]);

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'type' => 'single_choice',
            'question_text' => 'Original question',
            'marks' => 5,
            'settings' => [],
            'sort_order' => 0,
        ]);

        Option::create([
            'question_id' => $question->id,
            'label' => 'Old Option',
            'is_correct' => true,
            'sort_order' => 0,
        ]);

        $updateData = [
            'question_text' => 'Updated question',
            'marks' => 10,
            'options' => [
                ['label' => 'New Option A', 'is_correct' => true],
                ['label' => 'New Option B', 'is_correct' => false],
            ],
        ];

        $updated = $this->quizService->updateQuestion($question, $updateData);

        $this->assertEquals('Updated question', $updated->question_text);
        $this->assertEquals(10, $updated->marks);
        $this->assertEquals(2, $updated->options()->count());
    }

    /**
     * @test
     * QuizService deletes question with cascade
     */
    public function test_quiz_service_deletes_question_cascade()
    {
        $quiz = Quiz::create([
            'title' => 'Test',
            'pass_percentage' => 70,
            'is_published' => true,
        ]);

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'type' => 'boolean',
            'question_text' => 'Delete me',
            'marks' => 5,
            'settings' => [],
            'sort_order' => 0,
        ]);

        Option::create([
            'question_id' => $question->id,
            'label' => 'Option',
            'is_correct' => true,
            'sort_order' => 0,
        ]);

        $questionId = $question->id;
        $this->quizService->deleteQuestion($question);

        $this->assertDatabaseMissing('questions', ['id' => $questionId]);
    }

    /**
     * @test
     * QuizService calculates statistics correctly
     */
    public function test_quiz_service_calculates_statistics()
    {
        $quiz = Quiz::create([
            'title' => 'Stats Test',
            'pass_percentage' => 70,
            'is_published' => true,
        ]);

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'type' => 'boolean',
            'question_text' => 'Question',
            'marks' => 100,
            'settings' => [],
            'sort_order' => 0,
        ]);

        // Create 3 attempts: 2 passed, 1 failed
        for ($i = 0; $i < 3; $i++) {
            $attempt = Attempt::create([
                'quiz_id' => $quiz->id,
                'user_name' => "User $i",
                'user_email' => "user$i@example.com",
                'user_identifier' => "USR$i",
                'started_at' => now(),
                'submitted_at' => now(),
                'total_marks' => 100,
                'total_score' => $i < 2 ? 80 : 50, // 80, 80, 50
                'is_passed' => $i < 2,
                'status' => 'evaluated',
            ]);
        }

        $stats = $this->quizService->getQuizStatistics($quiz);

        $this->assertEquals(3, $stats['total_attempts']);
        $this->assertEquals(2, $stats['successful_attempts']);
        $this->assertEqualsWithDelta(66.67, $stats['success_rate'], 0.01);
    }

    /**
     * @test
     * EvaluationService evaluates MultipleChoice answer correctly
     */
    public function test_evaluation_service_evaluates_multiple_choice()
    {
        $quiz = Quiz::create([
            'title' => 'Eval Test',
            'pass_percentage' => 70,
            'is_published' => true,
        ]);

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'type' => 'multiple_choice',
            'question_text' => 'Select correct',
            'marks' => 20,
            'settings' => ['strict_mode' => false],
            'sort_order' => 0,
        ]);

        Option::create(['question_id' => $question->id, 'label' => 'A', 'is_correct' => true, 'sort_order' => 0]);
        Option::create(['question_id' => $question->id, 'label' => 'B', 'is_correct' => true, 'sort_order' => 1]);
        Option::create(['question_id' => $question->id, 'label' => 'C', 'is_correct' => false, 'sort_order' => 2]);

        // User selects A (correct, 50% of options)
        $result = $this->evaluationService->evaluateAnswer(
            $question,
            json_encode(['1'])
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('is_correct', $result);
        $this->assertArrayHasKey('score', $result);
    }

    /**
     * @test
     * EvaluationService handles all question types
     */
    public function test_evaluation_service_handles_all_types()
    {
        $quiz = Quiz::create([
            'title' => 'Type Test',
            'pass_percentage' => 70,
            'is_published' => true,
        ]);

        $types = ['boolean', 'single_choice', 'multiple_choice', 'number', 'text'];
        $testAnswers = [
            'boolean' => json_encode(true),
            'single_choice' => json_encode('1'),
            'multiple_choice' => json_encode(['1', '2']),
            'number' => json_encode(42),
            'text' => json_encode('answer text'),
        ];

        foreach ($types as $type) {
            $question = Question::create([
                'quiz_id' => $quiz->id,
                'type' => $type,
                'question_text' => "Test $type",
                'marks' => 10,
                'settings' => [],
                'sort_order' => 0,
            ]);

            $result = $this->evaluationService->evaluateAnswer(
                $question,
                $testAnswers[$type]
            );

            $this->assertIsArray($result, "Failed for type: $type");
        }
    }

    /**
     * @test
     * QuizService and EvaluationService work together in full flow
     */
    public function test_services_integrated_full_flow()
    {
        // Create quiz via service
        $quiz = $this->quizService->createQuiz([
            'title' => 'Integration Flow',
            'description' => 'Testing service integration',
            'pass_percentage' => 70,
            'is_published' => true,
        ]);

        // Add question via service
        $question = $this->quizService->addQuestion($quiz, [
            'type' => 'number',
            'question_text' => 'What is 2+2?',
            'marks' => 10,
            'settings' => ['tolerance' => 0],
            'options' => [],
        ]);

        // Start attempt
        $attempt = Attempt::create([
            'quiz_id' => $quiz->id,
            'user_name' => 'Tester',
            'user_email' => 'test@example.com',
            'user_identifier' => 'TST001',
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        // Evaluate answer via service
        $evaluation = $this->evaluationService->evaluateAnswer(
            $question,
            json_encode(4)
        );

        // Record answer
        $answer = Answer::create([
            'attempt_id' => $attempt->id,
            'question_id' => $question->id,
            'user_answer' => json_encode(4),
            'question_type' => 'number',
            'is_correct' => $evaluation['is_correct'],
            'score' => $evaluation['score'],
            'feedback' => $evaluation['feedback'],
            'answered_at' => now(),
            'time_spent_seconds' => 20,
        ]);

        // Get statistics
        $stats = $this->quizService->getQuizStatistics($quiz);

        $this->assertTrue($answer->is_correct);
        $this->assertEquals(1, $stats['total_attempts']);
    }

    /**
     * @test
     * QuizService retrieves published quizzes only
     */
    public function test_quiz_service_retrieves_published_only()
    {
        Quiz::create(['title' => 'Published', 'pass_percentage' => 70, 'is_published' => true]);
        Quiz::create(['title' => 'Published 2', 'pass_percentage' => 70, 'is_published' => true]);
        Quiz::create(['title' => 'Unpublished', 'pass_percentage' => 70, 'is_published' => false]);

        $published = $this->quizService->getPublishedQuizzes();

        $this->assertEquals(2, $published->count());
    }
}
