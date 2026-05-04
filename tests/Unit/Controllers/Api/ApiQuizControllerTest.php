<?php

namespace Tests\Unit\Controllers\Api;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiQuizControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    /** @test */
    public function test_list_published_quizzes()
    {
        Quiz::factory()->create(['is_published' => true]);
        Quiz::factory()->create(['is_published' => false]);

        $response = $this->getJson('/api/v1/quizzes');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => ['id', 'title', 'description', 'pass_percentage']
                     ],
                     'meta' => ['total', 'per_page', 'current_page', 'last_page']
                 ]);
    }

    /** @test */
    public function test_get_quiz_details()
    {
        $quiz = Quiz::factory()->create(['is_published' => true]);
        $question = Question::factory()->create(['quiz_id' => $quiz->id]);
        Option::factory()->create(['question_id' => $question->id]);

        $response = $this->getJson("/api/v1/quizzes/{$quiz->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'id', 'title', 'description', 'pass_percentage',
                         'total_marks', 'question_count', 'questions'
                     ]
                 ]);
    }

    /** @test */
    public function test_cannot_get_unpublished_quiz()
    {
        $quiz = Quiz::factory()->create(['is_published' => false]);

        $response = $this->getJson("/api/v1/quizzes/{$quiz->id}");

        $response->assertStatus(403)
                 ->assertJson(['success' => false, 'error' => 'Quiz not available']);
    }

    /** @test */
    public function test_create_quiz()
    {
        $data = [
            'title' => 'New Test Quiz',
            'description' => 'Test description',
            'pass_percentage' => 70,
            'is_published' => true,
        ];

        $response = $this->postJson('/api/v1/admin/quizzes', $data);

        $response->assertStatus(201)
                 ->assertJson(['success' => true, 'message' => 'Quiz created successfully'])
                 ->assertJsonStructure(['data' => ['id', 'title', 'pass_percentage']]);

        $this->assertDatabaseHas('quizzes', ['title' => 'New Test Quiz']);
    }

    /** @test */
    public function test_create_quiz_validation()
    {
        $response = $this->postJson('/api/v1/admin/quizzes', [
            'pass_percentage' => 150, // Invalid
        ]);

        $response->assertStatus(422)
                 ->assertJson(['success' => false, 'error' => 'Validation failed']);
    }

    /** @test */
    public function test_update_quiz()
    {
        $quiz = Quiz::factory()->create();

        $response = $this->putJson("/api/v1/admin/quizzes/{$quiz->id}", [
            'title' => 'Updated Title',
            'pass_percentage' => 80,
            'is_published' => true,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true, 'message' => 'Quiz updated successfully']);

        $this->assertDatabaseHas('quizzes', ['id' => $quiz->id, 'title' => 'Updated Title']);
    }

    /** @test */
    public function test_delete_quiz()
    {
        $quiz = Quiz::factory()->create();

        $response = $this->deleteJson("/api/v1/admin/quizzes/{$quiz->id}");

        $response->assertStatus(200)
                 ->assertJson(['success' => true, 'message' => 'Quiz deleted successfully']);

        $this->assertDatabaseMissing('quizzes', ['id' => $quiz->id]);
    }

    /** @test */
    public function test_get_quiz_statistics()
    {
        $quiz = Quiz::factory()->create();

        $response = $this->getJson("/api/v1/quizzes/{$quiz->id}/statistics");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'total_attempts',
                         'successful_attempts',
                         'success_rate',
                         'average_score',
                         'average_percentage'
                     ]
                 ]);
    }

    /** @test */
    public function test_get_question_types()
    {
        $response = $this->getJson('/api/v1/quiz-types');

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['value', 'label', 'description']
                     ]
                 ]);
    }
}
