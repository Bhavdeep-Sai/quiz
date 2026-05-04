<?php

namespace Tests\Feature\Integration;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\Option;
use App\Models\Attempt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * STEP 8: Integration Test - Smoke Tests
 * 
 * Layer 2: Smoke Tests - Critical path validation
 * Tests all critical business flows with minimal assertions
 */
class SmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    /**
     * @test
     * Critical Path: Create and publish quiz
     */
    public function test_smoke_create_publish_quiz()
    {
        $quiz = Quiz::create([
            'title' => 'Critical Quiz',
            'description' => 'Critical path test',
            'pass_percentage' => 70,
            'is_published' => true,
        ]);

        $this->assertTrue($quiz->is_published);
        $this->assertDatabaseHas('quizzes', ['id' => $quiz->id, 'is_published' => true]);
    }

    /**
     * @test
     * Critical Path: Add questions to quiz
     */
    public function test_smoke_add_questions()
    {
        $quiz = Quiz::create(['title' => 'Quiz', 'pass_percentage' => 70, 'is_published' => true]);

        for ($i = 0; $i < 5; $i++) {
            Question::create([
                'quiz_id' => $quiz->id,
                'type' => 'boolean',
                'question_text' => "Question $i",
                'marks' => 10,
                'settings' => [],
                'sort_order' => $i,
            ]);
        }

        $this->assertEquals(5, $quiz->questions()->count());
    }

    /**
     * @test
     * Critical Path: Start and submit attempt
     */
    public function test_smoke_start_submit_attempt()
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

        $attempt->update([
            'submitted_at' => now(),
            'total_marks' => 100,
            'total_score' => 100,
            'is_passed' => true,
            'status' => 'evaluated',
        ]);

        $this->assertTrue($attempt->is_passed);
        $this->assertEquals(100, $attempt->total_score);
    }

    /**
     * @test
     * Critical Path: API quiz endpoint accessible
     */
    public function test_smoke_api_quiz_endpoint()
    {
        $quiz = Quiz::create(['title' => 'Quiz', 'pass_percentage' => 70, 'is_published' => true]);

        $response = $this->getJson('/api/v1/quizzes');

        $this->assertTrue($response->ok());
    }

    /**
     * @test
     * Critical Path: API create attempt endpoint
     */
    public function test_smoke_api_start_attempt()
    {
        $quiz = Quiz::create(['title' => 'Quiz', 'pass_percentage' => 70, 'is_published' => true]);

        Question::create([
            'quiz_id' => $quiz->id,
            'type' => 'boolean',
            'question_text' => 'Q',
            'marks' => 10,
            'settings' => [],
            'sort_order' => 0,
        ]);

        $response = $this->postJson("/api/v1/quizzes/{$quiz->id}/attempts", [
            'user_name' => 'User',
            'user_email' => 'user@example.com',
        ]);

        $this->assertTrue($response->ok() || $response->status() === 201);
    }

    /**
     * @test
     * Critical Path: Database connectivity
     */
    public function test_smoke_database_connectivity()
    {
        $quiz = Quiz::create(['title' => 'DB Test', 'pass_percentage' => 70, 'is_published' => true]);

        $retrieved = Quiz::find($quiz->id);
        $this->assertNotNull($retrieved);
        $this->assertEquals('DB Test', $retrieved->title);
    }

    /**
     * @test
     * Critical Path: Multiple question types
     */
    public function test_smoke_multiple_question_types()
    {
        $quiz = Quiz::create(['title' => 'Types Quiz', 'pass_percentage' => 70, 'is_published' => true]);

        $types = ['boolean', 'single_choice', 'multiple_choice', 'number', 'text'];

        foreach ($types as $type) {
            Question::create([
                'quiz_id' => $quiz->id,
                'type' => $type,
                'question_text' => "Question $type",
                'marks' => 20,
                'settings' => [],
                'sort_order' => 0,
            ]);
        }

        $this->assertEquals(5, $quiz->questions()->count());
    }

    /**
     * @test
     * Critical Path: Application bootstrap
     */
    public function test_smoke_app_bootstrap()
    {
        $response = $this->getJson('/api/health');
        $this->assertTrue($response->ok());
    }

    /**
     * @test
     * Critical Path: Web controller access
     */
    public function test_smoke_web_dashboard()
    {
        $response = $this->get('/dashboard');
        $this->assertIn($response->status(), [200, 302]); // 302 if redirected
    }

    /**
     * @test
     * Critical Path: Error handling
     */
    public function test_smoke_error_handling()
    {
        $response = $this->getJson('/api/v1/quizzes/99999');
        $this->assertEquals(404, $response->status());
    }
}
