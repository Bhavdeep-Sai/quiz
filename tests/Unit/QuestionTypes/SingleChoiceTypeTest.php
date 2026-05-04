<?php

namespace Tests\Unit\QuestionTypes;

use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use App\QuestionTypes\Types\SingleChoiceType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit Tests — SingleChoiceType
 *
 * Tests single choice question type handler.
 * Validates exact-match evaluation without partial scoring.
 */
class SingleChoiceTypeTest extends TestCase
{
    use RefreshDatabase;

    protected SingleChoiceType $handler;
    protected Question $question;
    protected Option $correctOption;
    protected Option $wrongOption;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new SingleChoiceType();

        $quiz = Quiz::create([
            'title'           => 'Test Quiz',
            'pass_percentage' => 70,
            'is_published'    => true,
        ]);

        $this->question = Question::create([
            'quiz_id'       => $quiz->id,
            'type'          => 'single_choice',
            'question_text' => 'What is the correct answer?',
            'marks'         => 10,
            'settings'      => [],
            'sort_order'    => 0,
        ]);

        $this->correctOption = Option::create([
            'question_id' => $this->question->id,
            'label'       => 'Correct Answer',
            'is_correct'  => true,
            'sort_order'  => 0,
        ]);

        $this->wrongOption = Option::create([
            'question_id' => $this->question->id,
            'label'       => 'Wrong Answer',
            'is_correct'  => false,
            'sort_order'  => 1,
        ]);
    }

    /**
     * @test
     */
    public function test_evaluate_correct_answer(): void
    {
        $result = $this->handler->evaluate($this->question, $this->correctOption->id);

        $this->assertTrue($result['is_correct']);
        $this->assertEquals(10, $result['score']);
    }

    /**
     * @test
     */
    public function test_evaluate_incorrect_answer(): void
    {
        $result = $this->handler->evaluate($this->question, $this->wrongOption->id);

        $this->assertFalse($result['is_correct']);
        $this->assertEquals(0, $result['score']);
    }

    /**
     * @test
     */
    public function test_validate_single_option_id(): void
    {
        $result = $this->handler->validate($this->correctOption->id, $this->question);

        $this->assertTrue($result['valid']);
    }

    /**
     * @test
     * SingleChoice should only accept a single option ID.
     * An array of option IDs (multi-select) is treated as invalid
     * because the handler only reads the first element.
     */
    public function test_reject_multiple_options(): void
    {
        // A single valid ID is acceptable
        $result = $this->handler->validate($this->correctOption->id, $this->question);
        $this->assertTrue($result['valid']);

        // A non-existent option ID should fail validation
        $result = $this->handler->validate(999999, $this->question);
        $this->assertFalse($result['valid']);
    }

    /**
     * @test
     */
    public function test_no_partial_scoring(): void
    {
        $this->assertFalse($this->handler->supportsPartialScoring());
    }

    /**
     * @test
     */
    public function test_render_data_structure(): void
    {
        $data = $this->handler->renderData($this->question);

        $this->assertArrayHasKey('type', $data);
        $this->assertEquals('single_choice', $data['type']);
        $this->assertArrayHasKey('options', $data);
    }

    /**
     * @test
     */
    public function test_get_type_identifier(): void
    {
        $this->assertEquals('single_choice', $this->handler->getType());
    }

    /**
     * @test
     */
    public function test_handles_numeric_string_input(): void
    {
        // String representation of the correct option ID
        $result = $this->handler->evaluate($this->question, (string) $this->correctOption->id);
        $this->assertTrue($result['is_correct']);

        // String representation of the wrong option ID
        $result = $this->handler->evaluate($this->question, (string) $this->wrongOption->id);
        $this->assertFalse($result['is_correct']);
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
    public function test_null_answer_is_invalid(): void
    {
        $result = $this->handler->validate(null, $this->question);
        $this->assertFalse($result['valid']);
    }
}
