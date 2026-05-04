<?php

namespace Tests\Unit\QuestionTypes;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\Option;
use App\QuestionTypes\Types\BooleanType;
use Tests\TestCase;

class BooleanTypeTest extends TestCase
{
    private BooleanType $booleanType;
    private Quiz $quiz;
    private Question $question;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->booleanType = new BooleanType();
        
        // Create test data
        $this->quiz = Quiz::factory()->create();
        $this->question = Question::factory()->create([
            'quiz_id' => $this->quiz->id,
            'type' => 'boolean',
            'marks' => 1,
        ]);
    }

    /**
     * Test correct true answer
     */
    public function test_correct_true_answer(): void
    {
        // Create correct option marked as true
        Option::factory()->create([
            'question_id' => $this->question->id,
            'label' => 'True',
            'is_correct' => true,
        ]);

        $result = $this->booleanType->evaluate($this->question, true);

        $this->assertTrue($result['is_correct']);
        $this->assertEquals(1, $result['score']);
    }

    /**
     * Test correct false answer
     */
    public function test_correct_false_answer(): void
    {
        Option::factory()->create([
            'question_id' => $this->question->id,
            'label' => 'False',
            'is_correct' => true,
        ]);

        $result = $this->booleanType->evaluate($this->question, false);

        $this->assertTrue($result['is_correct']);
        $this->assertEquals(1, $result['score']);
    }

    /**
     * Test incorrect answer
     */
    public function test_incorrect_answer(): void
    {
        Option::factory()->create([
            'question_id' => $this->question->id,
            'label' => 'True',
            'is_correct' => true,
        ]);

        $result = $this->booleanType->evaluate($this->question, false);

        $this->assertFalse($result['is_correct']);
        $this->assertEquals(0, $result['score']);
    }

    /**
     * Test validation with valid answer
     */
    public function test_validation_with_valid_answer(): void
    {
        $validation = $this->booleanType->validate(true, $this->question);

        $this->assertTrue($validation['valid']);
    }

    /**
     * Test validation with null answer
     */
    public function test_validation_with_null_answer(): void
    {
        $validation = $this->booleanType->validate(null, $this->question);

        $this->assertFalse($validation['valid']);
        $this->assertNotNull($validation['error']);
    }

    /**
     * Test string representation of answer
     */
    public function test_string_answer_true(): void
    {
        Option::factory()->create([
            'question_id' => $this->question->id,
            'label' => 'True',
            'is_correct' => true,
        ]);

        $result = $this->booleanType->evaluate($this->question, 'true');

        $this->assertTrue($result['is_correct']);
        $this->assertEquals(1, $result['score']);
    }

    /**
     * Test numeric answer 1 as true
     */
    public function test_numeric_answer_1_as_true(): void
    {
        Option::factory()->create([
            'question_id' => $this->question->id,
            'label' => 'True',
            'is_correct' => true,
        ]);

        $result = $this->booleanType->evaluate($this->question, 1);

        $this->assertTrue($result['is_correct']);
    }

    /**
     * Test render data
     */
    public function test_render_data(): void
    {
        $data = $this->booleanType->renderData($this->question);

        $this->assertEquals('boolean', $data['type']);
        $this->assertIsArray($data['options']);
        $this->assertCount(2, $data['options']);
    }

    /**
     * Test get type
     */
    public function test_get_type(): void
    {
        $this->assertEquals('boolean', $this->booleanType->getType());
    }

    /**
     * Test partial scoring not supported
     */
    public function test_partial_scoring_not_supported(): void
    {
        $this->assertFalse($this->booleanType->supportsPartialScoring());
    }
}
