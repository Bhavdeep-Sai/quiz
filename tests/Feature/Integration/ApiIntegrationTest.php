<?php

namespace Tests\Feature\Integration;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\Option;
use App\Models\Attempt;
use App\Models\Answer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * STEP 8: Integration Test - API Layer
 * 
 * Tests API endpoints with realistic data flows
 */
class ApiIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    /**
     * @test
     * API: List published quizzes
     */
    public function test_api_list_published_quizzes()
    {
        Quiz::create(['title' => 'Published', 'pass_percentage' => 70, 'is_published' => true]);
        Quiz::create(['title' => 'Unpublished', 'pass_percentage' => 70, 'is_published' => false]);

        $response = $this->getJson('/api/v1/quizzes');

        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'data', 'meta'])
                 ->assertJsonCount(1, 'data');
    }

    /**
     * @test
     * API: Get quiz with questions
     */
    public function test_api_get_quiz_with_questions()
    {
        $quiz = Quiz::create(['title' => 'Quiz', 'pass_percentage' => 70, 'is_published' => true]);
        
        $question = Question::create([
            'quiz_id' => $quiz->id,
            'type' => 'multiple_choice',
            'question_text' => 'Question',
            'marks' => 10,
            'settings' => [],
            'sort_order' => 0,
        ]);

        Option::create(['question_id' => $question->id, 'label' => 'Option', 'is_correct' => true, 'sort_order' => 0]);

        $response = $this->getJson("/api/v1/quizzes/{$quiz->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'data' => ['id', 'questions']]);
    }

    /**
     * @test
     * API: Create quiz (admin)
     */
    public function test_api_create_quiz()
    {
        $response = $this->postJson('/api/v1/admin/quizzes', [
            'title' => 'New Quiz',
            'description' => 'Test',
            'pass_percentage' => 75,
            'is_published' => true,
        ]);

        $response->assertStatus(201)
                 ->assertJson(['success' => true]);
    }

    /**
     * @test
     * API: Create question with options
     */
    public function test_api_create_question_with_options()
    {
        $quiz = Quiz::create(['title' => 'Quiz', 'pass_percentage' => 70, 'is_published' => true]);

        $response = $this->postJson("/api/v1/admin/quizzes/{$quiz->id}/questions", [
            'type' => 'multiple_choice',
            'question_text' => 'What is correct?',
            'marks' => 10,
            'settings' => ['min_selections' => 1],
            'options' => [
                ['label' => 'A', 'is_correct' => true],
                ['label' => 'B', 'is_correct' => true],
            ],
        ]);

        $response->assertStatus(201)
                 ->assertJson(['success' => true]);
    }

    /**
     * @test
     * API: Start quiz attempt
     */
    public function test_api_start_quiz_attempt()
    {
        $quiz = Quiz::create(['title' => 'Quiz', 'pass_percentage' => 70, 'is_published' => true]);
        
        Question::create([
            'quiz_id' => $quiz->id,
            'type' => 'boolean',
            'question_text' => 'Question',
            'marks' => 10,
            'settings' => [],
            'sort_order' => 0,
        ]);

        $response = $this->postJson("/api/v1/quizzes/{$quiz->id}/attempts", [
            'user_name' => 'Test User',
            'user_email' => 'test@example.com',
        ]);

        $response->assertStatus(201)
                 ->assertJson(['success' => true])
                 ->assertJsonStructure(['data' => ['attempt_id', 'questions']]);
    }

    /**
     * @test
     * API: Submit quiz answers
     */
    public function test_api_submit_quiz_answers()
    {
        $quiz = Quiz::create(['title' => 'Quiz', 'pass_percentage' => 70, 'is_published' => true]);
        
        $question = Question::create([
            'quiz_id' => $quiz->id,
            'type' => 'boolean',
            'question_text' => 'Question',
            'marks' => 100,
            'settings' => [],
            'sort_order' => 0,
        ]);

        Option::create(['question_id' => $question->id, 'label' => 'True', 'is_correct' => true, 'sort_order' => 0]);

        $attempt = Attempt::create([
            'quiz_id' => $quiz->id,
            'user_name' => 'User',
            'user_email' => 'user@example.com',
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        $response = $this->postJson("/api/v1/attempts/{$attempt->id}/submit", [
            'answers' => [
                $question->id => json_encode(true),
            ],
            'time_spent' => 120,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonStructure(['data' => ['attempt_id', 'score', 'marks', 'percentage']]);
    }

    /**
     * @test
     * API: Get attempt details
     */
    public function test_api_get_attempt_details()
    {
        $quiz = Quiz::create(['title' => 'Quiz', 'pass_percentage' => 70, 'is_published' => true]);
        
        $question = Question::create([
            'quiz_id' => $quiz->id,
            'type' => 'boolean',
            'question_text' => 'Question',
            'marks' => 10,
            'settings' => [],
            'sort_order' => 0,
        ]);

        $attempt = Attempt::create([
            'quiz_id' => $quiz->id,
            'user_name' => 'User',
            'user_email' => 'user@example.com',
            'started_at' => now(),
            'submitted_at' => now(),
            'total_marks' => 10,
            'total_score' => 10,
            'is_passed' => true,
            'status' => 'evaluated',
        ]);

        Answer::create([
            'attempt_id' => $attempt->id,
            'question_id' => $question->id,
            'user_answer' => json_encode(true),
            'question_type' => 'boolean',
            'is_correct' => true,
            'score' => 10,
            'answered_at' => now(),
        ]);

        $response = $this->getJson("/api/v1/attempts/{$attempt->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'data' => ['id', 'answers']]);
    }

    /**
     * @test
     * API: Get attempt statistics
     */
    public function test_api_get_attempt_statistics()
    {
        $quiz = Quiz::create(['title' => 'Quiz', 'pass_percentage' => 70, 'is_published' => true]);
        
        $attempt = Attempt::create([
            'quiz_id' => $quiz->id,
            'user_name' => 'User',
            'user_email' => 'user@example.com',
            'started_at' => now(),
            'submitted_at' => now(),
            'total_marks' => 100,
            'total_score' => 80,
            'is_passed' => true,
            'status' => 'evaluated',
        ]);

        $response = $this->getJson("/api/v1/attempts/{$attempt->id}/statistics");

        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'data' => ['score', 'percentage', 'analytics']]);
    }

    /**
     * @test
     * API: Health check
     */
    public function test_api_health_check()
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonStructure(['checks' => ['database', 'cache', 'queue']]);
    }

    /**
     * @test
     * API: System status
     */
    public function test_api_system_status()
    {
        $response = $this->getJson('/api/status');

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonStructure(['status', 'metrics', 'components']);
    }

    /**
     * @test
     * API: Validation error handling
     */
    public function test_api_validation_error()
    {
        $response = $this->postJson('/api/v1/admin/quizzes', [
            'pass_percentage' => 150, // Invalid
        ]);

        $response->assertStatus(422)
                 ->assertJson(['success' => false])
                 ->assertJsonStructure(['errors']);
    }

    /**
     * @test
     * API: Resource not found
     */
    public function test_api_resource_not_found()
    {
        $response = $this->getJson('/api/v1/quizzes/99999');

        $response->assertStatus(404);
    }

    /**
     * @test
     * API: Cannot access unpublished quiz
     */
    public function test_api_unpublished_quiz_access_denied()
    {
        $quiz = Quiz::create(['title' => 'Quiz', 'pass_percentage' => 70, 'is_published' => false]);

        $response = $this->getJson("/api/v1/quizzes/{$quiz->id}");

        $response->assertStatus(403);
    }
}
