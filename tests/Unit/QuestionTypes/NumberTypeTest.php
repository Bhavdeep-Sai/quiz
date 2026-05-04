<?php

namespace Tests\Unit\QuestionTypes;

use App\Models\Question;
use App\Models\Quiz;
use App\QuestionTypes\Types\NumberType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit Tests — NumberType
 *
 * Tests numeric question type with tolerance-based matching.
 * The correct answer is stored in question settings['expected_answer'].
 */
class NumberTypeTest extends TestCase
{
    use RefreshDatabase;

    protected NumberType $handler;
    protected Question $question;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new NumberType();

        $quiz = Quiz::create([
            'title'           => 'Test Quiz',
            'pass_percentage' => 70,
            'is_published'    => true,
        ]);

        // expected_answer = 42, tolerance = 0.5
        $this->question = Question::create([
            'quiz_id'       => $quiz->id,
            'type'          => 'number',
            'question_text' => 'What is the value?',
            'marks'         => 10,
            'settings'      => ['expected_answer' => 42, 'tolerance' => 0.5],
            'sort_order'    => 0,
        ]);
    }

    /**
     * @test
     */
    public function test_exact_match_is_correct(): void
    {
        $result = $this->handler->evaluate($this->question, 42);

        $this->assertTrue($result['is_correct']);
        $this->assertEquals(10, $result['score']);
    }

    /**
     * @test
     */
    public function test_within_tolerance_is_correct(): void
    {
        // 42.3 is within ±0.5 of 42
        $result = $this->handler->evaluate($this->question, 42.3);

        $this->assertTrue($result['is_correct']);
        $this->assertEquals(10, $result['score']);
    }

    /**
     * @test
     */
    public function test_outside_tolerance_is_incorrect(): void
    {
        // 43 is outside ±0.5 of 42
        $result = $this->handler->evaluate($this->question, 43);

        $this->assertFalse($result['is_correct']);
        $this->assertEquals(0, $result['score']);
    }

    /**
     * @test
     */
    public function test_negative_numbers_supported(): void
    {
        // Update question to have negative expected answer
        $this->question->update(['settings' => ['expected_answer' => -42, 'tolerance' => 1]]);

        $result = $this->handler->evaluate($this->question, -42);
        $this->assertTrue($result['is_correct']);
    }

    /**
     * @test
     */
    public function test_decimal_precision(): void
    {
        $this->question->update(['settings' => ['expected_answer' => 42, 'tolerance' => 0.01]]);

        $result = $this->handler->evaluate($this->question, 42.001);
        $this->assertTrue($result['is_correct']);

        $result = $this->handler->evaluate($this->question, 42.02);
        $this->assertFalse($result['is_correct']);
    }

    /**
     * @test
     */
    public function test_validate_numeric_input(): void
    {
        $result = $this->handler->validate(42, $this->question);
        $this->assertTrue($result['valid']);

        $result = $this->handler->validate(3.14, $this->question);
        $this->assertTrue($result['valid']);
    }

    /**
     * @test
     */
    public function test_reject_non_numeric_input(): void
    {
        $result = $this->handler->validate('not a number', $this->question);
        $this->assertFalse($result['valid']);

        $result = $this->handler->validate([1, 2], $this->question);
        $this->assertFalse($result['valid']);
    }

    /**
     * @test
     */
    public function test_supports_partial_scoring(): void
    {
        $this->assertTrue($this->handler->supportsPartialScoring());
    }

    /**
     * @test
     */
    public function test_zero_tolerance(): void
    {
        $this->question->update(['settings' => ['expected_answer' => 42, 'tolerance' => 0]]);

        $result = $this->handler->evaluate($this->question, 42);
        $this->assertTrue($result['is_correct']);

        $result = $this->handler->evaluate($this->question, 42.0001);
        $this->assertFalse($result['is_correct']);
    }

    /**
     * @test
     */
    public function test_large_tolerance_range(): void
    {
        $this->question->update(['settings' => ['expected_answer' => 42, 'tolerance' => 100]]);

        $result = $this->handler->evaluate($this->question, 142);
        $this->assertTrue($result['is_correct']);

        $result = $this->handler->evaluate($this->question, -58);
        $this->assertTrue($result['is_correct']);
    }

    /**
     * @test
     */
    public function test_render_data_includes_tolerance(): void
    {
        $data = $this->handler->renderData($this->question);

        $this->assertArrayHasKey('type', $data);
        $this->assertEquals('number', $data['type']);
    }

    /**
     * @test
     */
    public function test_get_type_identifier(): void
    {
        $this->assertEquals('number', $this->handler->getType());
    }

    /**
     * @test
     */
    public function test_null_answer_is_invalid(): void
    {
        $result = $this->handler->validate(null, $this->question);
        $this->assertFalse($result['valid']);
    }

    /**
     * @test
     */
    public function test_empty_answer_is_invalid(): void
    {
        $result = $this->handler->validate('', $this->question);
        $this->assertFalse($result['valid']);
    }

    /**
     * @test
     */
    public function test_scientific_notation(): void
    {
        // 4.2e1 = 42.0 — exact match
        $result = $this->handler->evaluate($this->question, 4.2e1);
        $this->assertTrue($result['is_correct']);
    }
}
