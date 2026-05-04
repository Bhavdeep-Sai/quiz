<?php

namespace Tests\Unit\Services;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\Option;
use App\Services\EvaluationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit Tests — EvaluationService
 *
 * Tests EvaluationService with different question types.
 * Answers are passed as raw PHP values (not JSON-encoded strings).
 */
class EvaluationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EvaluationService $service;
    protected Quiz $quiz;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(EvaluationService::class);

        $this->quiz = Quiz::create([
            'title'           => 'Test Quiz',
            'pass_percentage' => 70,
            'is_published'    => true,
        ]);
    }

    /**
     * @test
     */
    public function test_evaluate_boolean_correct(): void
    {
        $question = Question::create([
            'quiz_id'       => $this->quiz->id,
            'type'          => 'boolean',
            'question_text' => 'Is this true?',
            'marks'         => 10,
            'settings'      => [],
            'sort_order'    => 0,
        ]);

        // Create correct option so the evaluator knows the answer is "true"
        Option::create([
            'question_id' => $question->id,
            'label'       => 'True',
            'is_correct'  => true,
            'sort_order'  => 0,
        ]);

        $result = $this->service->evaluateAnswer($question, true);

        $this->assertTrue($result['is_correct']);
        $this->assertEquals(10, $result['score']);
    }

    /**
     * @test
     */
    public function test_evaluate_boolean_incorrect(): void
    {
        $question = Question::create([
            'quiz_id'       => $this->quiz->id,
            'type'          => 'boolean',
            'question_text' => 'Is this true?',
            'marks'         => 10,
            'settings'      => [],
            'sort_order'    => 0,
        ]);

        // Correct answer is true; user answers false
        Option::create([
            'question_id' => $question->id,
            'label'       => 'True',
            'is_correct'  => true,
            'sort_order'  => 0,
        ]);

        $result = $this->service->evaluateAnswer($question, false);

        $this->assertFalse($result['is_correct']);
        $this->assertEquals(0, $result['score']);
    }

    /**
     * @test
     */
    public function test_evaluate_single_choice_correct(): void
    {
        $question = Question::create([
            'quiz_id'       => $this->quiz->id,
            'type'          => 'single_choice',
            'question_text' => 'Select one:',
            'marks'         => 5,
            'settings'      => [],
            'sort_order'    => 0,
        ]);

        $correctOption = Option::create([
            'question_id' => $question->id,
            'label'       => 'A',
            'is_correct'  => true,
            'sort_order'  => 0,
        ]);

        Option::create([
            'question_id' => $question->id,
            'label'       => 'B',
            'is_correct'  => false,
            'sort_order'  => 1,
        ]);

        // Pass the actual option ID (integer)
        $result = $this->service->evaluateAnswer($question, $correctOption->id);

        $this->assertTrue($result['is_correct']);
        $this->assertEquals(5, $result['score']);
    }

    /**
     * @test
     */
    public function test_evaluate_multiple_choice_partial(): void
    {
        $question = Question::create([
            'quiz_id'       => $this->quiz->id,
            'type'          => 'multiple_choice',
            'question_text' => 'Select all:',
            'marks'         => 20,
            'settings'      => ['strict_mode' => false],
            'sort_order'    => 0,
        ]);

        $option1 = Option::create([
            'question_id' => $question->id,
            'label'       => 'A',
            'is_correct'  => true,
            'sort_order'  => 0,
        ]);

        $option2 = Option::create([
            'question_id' => $question->id,
            'label'       => 'B',
            'is_correct'  => true,
            'sort_order'  => 1,
        ]);

        Option::create([
            'question_id' => $question->id,
            'label'       => 'C',
            'is_correct'  => false,
            'sort_order'  => 2,
        ]);

        // User selects only 1 of 2 correct options → partial score
        $result = $this->service->evaluateAnswer($question, [$option1->id]);

        $this->assertIsArray($result);
        $this->assertLessThan(20, $result['score']); // Partial score
    }

    /**
     * @test
     */
    public function test_evaluate_number_exact(): void
    {
        $question = Question::create([
            'quiz_id'       => $this->quiz->id,
            'type'          => 'number',
            'question_text' => 'What is the answer?',
            'marks'         => 10,
            'settings'      => ['correct_answer' => 42, 'tolerance' => 0],
            'sort_order'    => 0,
        ]);

        $result = $this->service->evaluateAnswer($question, 42);

        $this->assertTrue($result['is_correct']);
        $this->assertEquals(10, $result['score']);
    }

    /**
     * @test
     */
    public function test_evaluate_number_with_tolerance(): void
    {
        $question = Question::create([
            'quiz_id'       => $this->quiz->id,
            'type'          => 'number',
            'question_text' => 'Approximate value?',
            'marks'         => 10,
            'settings'      => ['correct_answer' => 42, 'tolerance' => 5],
            'sort_order'    => 0,
        ]);

        // 43 is within tolerance of 5 from 42
        $result = $this->service->evaluateAnswer($question, 43);

        $this->assertTrue($result['is_correct']);
    }

    /**
     * @test
     */
    public function test_evaluate_text_keyword_match(): void
    {
        $question = Question::create([
            'quiz_id'       => $this->quiz->id,
            'type'          => 'text',
            'question_text' => 'What is the capital of France?',
            'marks'         => 10,
            'settings'      => ['keywords' => ['Paris'], 'case_sensitive' => false],
            'sort_order'    => 0,
        ]);

        $result = $this->service->evaluateAnswer($question, 'paris is the capital');

        $this->assertTrue($result['is_correct']);
    }

    /**
     * @test
     */
    public function test_validate_answer_boolean(): void
    {
        $question = Question::create([
            'quiz_id'       => $this->quiz->id,
            'type'          => 'boolean',
            'question_text' => 'Valid?',
            'marks'         => 10,
            'settings'      => [],
            'sort_order'    => 0,
        ]);

        $validResult   = $this->service->validateAnswer($question, true);
        $invalidResult = $this->service->validateAnswer($question, 'invalid_value_xyz');

        $this->assertTrue($validResult['valid']);
        $this->assertFalse($invalidResult['valid']);
    }

    /**
     * @test
     */
    public function test_validate_answer_number(): void
    {
        $question = Question::create([
            'quiz_id'       => $this->quiz->id,
            'type'          => 'number',
            'question_text' => 'Number?',
            'marks'         => 10,
            'settings'      => [],
            'sort_order'    => 0,
        ]);

        $validResult   = $this->service->validateAnswer($question, 42);
        $invalidResult = $this->service->validateAnswer($question, 'not_a_number');

        $this->assertTrue($validResult['valid']);
        $this->assertFalse($invalidResult['valid']);
    }

    /**
     * @test
     */
    public function test_render_question_data(): void
    {
        $question = Question::create([
            'quiz_id'       => $this->quiz->id,
            'type'          => 'boolean',
            'question_text' => 'Render?',
            'marks'         => 10,
            'settings'      => [],
            'sort_order'    => 0,
        ]);

        $data = $this->service->renderQuestion($question);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('type', $data);
        $this->assertEquals('boolean', $data['type']);
    }

    /**
     * @test
     */
    public function test_all_question_types_can_be_evaluated(): void
    {
        $types = ['boolean', 'single_choice', 'multiple_choice', 'number', 'text'];

        foreach ($types as $type) {
            $question = Question::create([
                'quiz_id'       => $this->quiz->id,
                'type'          => $type,
                'question_text' => "Test {$type}",
                'marks'         => 10,
                'settings'      => [],
                'sort_order'    => 0,
            ]);

            // Should not throw an exception — just verify result is an array
            $result = $this->service->evaluateAnswer($question, null);
            $this->assertIsArray($result);
        }
    }

    /**
     * @test
     */
    public function test_evaluation_preserves_marks(): void
    {
        $question = Question::create([
            'quiz_id'       => $this->quiz->id,
            'type'          => 'boolean',
            'question_text' => 'Marks?',
            'marks'         => 50,
            'settings'      => [],
            'sort_order'    => 0,
        ]);

        Option::create([
            'question_id' => $question->id,
            'label'       => 'True',
            'is_correct'  => true,
            'sort_order'  => 0,
        ]);

        $result = $this->service->evaluateAnswer($question, true);

        $this->assertLessThanOrEqual(50, $result['score']);
    }
}
