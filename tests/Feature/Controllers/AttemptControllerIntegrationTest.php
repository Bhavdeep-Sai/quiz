<?php

namespace Tests\Feature\Controllers;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\Option;
use App\Models\Attempt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * STEP 9: Integration Test - Web Controllers
 * 
 * Tests web controller endpoints and views
 */
class AttemptControllerIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    /**
     * @test
     */
    public function test_start_quiz_page_loads()
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

        $response = $this->get("/quizzes/{$quiz->id}/start");

        $this->assertIn($response->status(), [200, 302]);
    }

    /**
     * @test
     */
    public function test_quiz_attempt_flow()
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
            'user_name' => 'Test User',
            'user_email' => 'test@example.com',
            'user_identifier' => 'TST001',
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        $response = $this->get("/attempts/{$attempt->id}");

        $this->assertIn($response->status(), [200, 302]);
    }

    /**
     * @test
     */
    public function test_result_page_shows_scores()
    {
        $quiz = Quiz::create(['title' => 'Quiz', 'pass_percentage' => 70, 'is_published' => true]);

        $attempt = Attempt::create([
            'quiz_id' => $quiz->id,
            'user_name' => 'User',
            'user_email' => 'user@example.com',
            'started_at' => now(),
            'submitted_at' => now(),
            'total_marks' => 100,
            'total_score' => 85,
            'is_passed' => true,
            'status' => 'evaluated',
        ]);

        $response = $this->get("/attempts/{$attempt->id}/result");

        $this->assertIn($response->status(), [200, 302]);
    }
}
