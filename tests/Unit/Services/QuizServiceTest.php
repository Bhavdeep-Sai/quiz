<?php

namespace Tests\Unit\Services;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\Option;
use App\Models\Attempt;
use App\Services\QuizService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit Tests — QuizService
 *
 * Tests QuizService methods in isolation.
 */
class QuizServiceTest extends TestCase
{
    use RefreshDatabase;

    protected QuizService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(QuizService::class);
    }

    /**
     * @test
     */
    public function test_create_quiz_with_valid_data(): void
    {
        $data = [
            'title'           => 'New Quiz',
            'description'     => 'A test description',
            'pass_percentage' => 75,
            'is_published'    => true,
        ];

        $quiz = $this->service->createQuiz($data);

        $this->assertNotNull($quiz->id);
        $this->assertDatabaseHas('quizzes', ['title' => 'New Quiz', 'pass_percentage' => 75]);
    }

    /**
     * @test
     */
    public function test_update_quiz(): void
    {
        $quiz = Quiz::create([
            'title'           => 'Original Title',
            'pass_percentage' => 70,
            'is_published'    => false,
        ]);

        $updated = $this->service->updateQuiz($quiz, [
            'title'        => 'Updated Title',
            'is_published' => true,
        ]);

        $this->assertEquals('Updated Title', $updated->title);
        $this->assertTrue($updated->is_published);
    }

    /**
     * @test
     */
    public function test_delete_quiz(): void
    {
        $quiz = Quiz::create([
            'title'           => 'Delete Me',
            'pass_percentage' => 70,
            'is_published'    => true,
        ]);

        $quizId = $quiz->id;
        $this->service->deleteQuiz($quiz);

        $this->assertDatabaseMissing('quizzes', ['id' => $quizId]);
    }

    /**
     * @test
     */
    public function test_add_question_with_options(): void
    {
        $quiz = Quiz::create([
            'title'           => 'Test Quiz',
            'pass_percentage' => 70,
            'is_published'    => true,
        ]);

        $data = [
            'type'          => 'single_choice',
            'question_text' => 'Test question?',
            'marks'         => 5,
            'settings'      => [],
            'options'       => [
                ['label' => 'Option A', 'is_correct' => true],
                ['label' => 'Option B', 'is_correct' => false],
            ],
        ];

        $question = $this->service->addQuestion($quiz, $data);

        $this->assertEquals(2, $question->options()->count());
        $this->assertTrue($question->options()->where('is_correct', true)->exists());
    }

    /**
     * @test
     */
    public function test_update_question(): void
    {
        $quiz = Quiz::create([
            'title'           => 'Test Quiz',
            'pass_percentage' => 70,
            'is_published'    => true,
        ]);

        $question = Question::create([
            'quiz_id'       => $quiz->id,
            'type'          => 'boolean',
            'question_text' => 'Original question?',
            'marks'         => 5,
            'settings'      => [],
            'sort_order'    => 0,
        ]);

        $updated = $this->service->updateQuestion($question, [
            'question_text' => 'Updated question?',
            'marks'         => 10,
            'options'       => [],
        ]);

        $this->assertEquals('Updated question?', $updated->question_text);
        $this->assertEquals(10, $updated->marks);
    }

    /**
     * @test
     */
    public function test_delete_question(): void
    {
        $quiz = Quiz::create([
            'title'           => 'Test Quiz',
            'pass_percentage' => 70,
            'is_published'    => true,
        ]);

        $question = Question::create([
            'quiz_id'       => $quiz->id,
            'type'          => 'boolean',
            'question_text' => 'Delete me',
            'marks'         => 5,
            'settings'      => [],
            'sort_order'    => 0,
        ]);

        $questionId = $question->id;
        $this->service->deleteQuestion($question);

        // SoftDeletes: record still exists in DB but with deleted_at set
        $this->assertSoftDeleted('questions', ['id' => $questionId]);
    }

    /**
     * @test
     */
    public function test_get_quiz_statistics(): void
    {
        $quiz = Quiz::create([
            'title'           => 'Stats Quiz',
            'pass_percentage' => 70,
            'is_published'    => true,
        ]);

        Attempt::create([
            'quiz_id'        => $quiz->id,
            'user_name'      => 'User 1',
            'user_email'     => 'user1@test.com',
            'started_at'     => now(),
            'submitted_at'   => now(),
            'total_marks'    => 100,
            'total_score'    => 80,
            'is_passed'      => true,
            'status'         => 'evaluated',
        ]);

        Attempt::create([
            'quiz_id'        => $quiz->id,
            'user_name'      => 'User 2',
            'user_email'     => 'user2@test.com',
            'started_at'     => now(),
            'submitted_at'   => now(),
            'total_marks'    => 100,
            'total_score'    => 50,
            'is_passed'      => false,
            'status'         => 'evaluated',
        ]);

        $stats = $this->service->getQuizStatistics($quiz);

        $this->assertEquals(2, $stats['total_attempts']);
        $this->assertEquals(1, $stats['successful_attempts']);
        $this->assertEquals(50.0, $stats['success_rate']);
    }

    /**
     * @test
     */
    public function test_get_published_quizzes(): void
    {
        Quiz::create(['title' => 'Published 1', 'pass_percentage' => 70, 'is_published' => true]);
        Quiz::create(['title' => 'Published 2', 'pass_percentage' => 70, 'is_published' => true]);
        Quiz::create(['title' => 'Unpublished',  'pass_percentage' => 70, 'is_published' => false]);

        $published = $this->service->getPublishedQuizzes();

        $this->assertEquals(2, $published->count());
    }

    /**
     * @test
     *
     * getAvailableQuestionTypes() returns an array of associative arrays:
     * [['value' => 'boolean', 'label' => '...', 'description' => '...', 'icon' => '...'], ...]
     */
    public function test_get_available_question_types(): void
    {
        $types = $this->service->getAvailableQuestionTypes();

        $this->assertIsArray($types);
        $this->assertCount(5, $types);

        // Extract just the 'value' keys for easy assertion
        $typeValues = array_column($types, 'value');
        $this->assertContains('boolean', $typeValues);
        $this->assertContains('single_choice', $typeValues);
        $this->assertContains('multiple_choice', $typeValues);
        $this->assertContains('number', $typeValues);
        $this->assertContains('text', $typeValues);
    }

    /**
     * @test
     *
     * submitQuizAnswers($attempt, $answers) — only 2 parameters.
     */
    public function test_submit_quiz_answers(): void
    {
        $quiz = Quiz::create([
            'title'           => 'Submit Test',
            'pass_percentage' => 70,
            'is_published'    => true,
        ]);

        $question = Question::create([
            'quiz_id'       => $quiz->id,
            'type'          => 'boolean',
            'question_text' => 'Question?',
            'marks'         => 10,
            'settings'      => [],
            'sort_order'    => 0,
        ]);

        Option::create([
            'question_id' => $question->id,
            'label'       => 'True',
            'is_correct'  => true,
            'sort_order'  => 0,
        ]);

        $attempt = Attempt::create([
            'quiz_id'    => $quiz->id,
            'user_name'  => 'Test User',
            'user_email' => 'test@test.com',
            'started_at' => now(),
            'status'     => 'in_progress',
        ]);

        $answers = [
            $question->id => true, // raw boolean, not json-encoded
        ];

        $result = $this->service->submitQuizAnswers($attempt, $answers);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('total_score', $result);
        $this->assertArrayHasKey('total_marks', $result);
        $this->assertArrayHasKey('percentage', $result);
        $this->assertArrayHasKey('is_passed', $result);
    }

    /**
     * @test
     */
    public function test_quiz_with_zero_questions(): void
    {
        $quiz = Quiz::create([
            'title'           => 'Empty Quiz',
            'pass_percentage' => 70,
            'is_published'    => true,
        ]);

        $this->assertEquals(0, $quiz->questions()->count());
        $this->assertEquals(0, $quiz->questions()->sum('marks'));
    }

    /**
     * @test
     */
    public function test_soft_delete_question(): void
    {
        $quiz = Quiz::create([
            'title'           => 'Test Quiz',
            'pass_percentage' => 70,
            'is_published'    => true,
        ]);

        $question = Question::create([
            'quiz_id'       => $quiz->id,
            'type'          => 'boolean',
            'question_text' => 'Soft delete me',
            'marks'         => 5,
            'settings'      => [],
            'sort_order'    => 0,
        ]);

        $this->service->deleteQuestion($question);

        // Question uses SoftDeletes — it should be trashed, not hard deleted
        $this->assertTrue($question->trashed());
    }
}
