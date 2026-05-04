<?php

namespace Tests\Unit\QuestionTypes;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\Option;
use App\QuestionTypes\Types\MultipleChoiceType;
use Tests\TestCase;

class MultipleChoiceTypeTest extends TestCase
{
    private MultipleChoiceType $multipleChoiceType;
    private Quiz $quiz;
    private Question $question;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->multipleChoiceType = new MultipleChoiceType();
        
        // Create test data
        $this->quiz = Quiz::factory()->create();
        $this->question = Question::factory()->create([
            'quiz_id' => $this->quiz->id,
            'type' => 'multiple_choice',
            'marks' => 1,
            'settings' => ['strict_mode' => true],
        ]);
    }

    /**
     * Test all correct selections in strict mode
     */
    public function test_all_correct_selections_strict_mode(): void
    {
        $option1 = Option::factory()->create([
            'question_id' => $this->question->id,
            'label' => 'Option 1',
            'is_correct' => true,
        ]);
        
        $option2 = Option::factory()->create([
            'question_id' => $this->question->id,
            'label' => 'Option 2',
            'is_correct' => true,
        ]);

        $result = $this->multipleChoiceType->evaluate(
            $this->question,
            [$option1->id, $option2->id]
        );

        $this->assertTrue($result['is_correct']);
        $this->assertEquals(1, $result['score']);
    }

    /**
     * Test partial correct with one wrong selection in strict mode
     */
    public function test_wrong_selection_strict_mode(): void
    {
        $correctOption = Option::factory()->create([
            'question_id' => $this->question->id,
            'label' => 'Correct',
            'is_correct' => true,
        ]);
        
        $wrongOption = Option::factory()->create([
            'question_id' => $this->question->id,
            'label' => 'Wrong',
            'is_correct' => false,
        ]);

        $result = $this->multipleChoiceType->evaluate(
            $this->question,
            [$correctOption->id, $wrongOption->id]
        );

        $this->assertFalse($result['is_correct']);
        $this->assertEquals(0, $result['score']); // Strict mode penalizes
    }

    /**
     * Test partial scoring (non-strict mode)
     */
    public function test_partial_scoring_non_strict_mode(): void
    {
        $this->question->update(['settings' => ['strict_mode' => false]]);

        $option1 = Option::factory()->create([
            'question_id' => $this->question->id,
            'label' => 'Option 1',
            'is_correct' => true,
        ]);
        
        $option2 = Option::factory()->create([
            'question_id' => $this->question->id,
            'label' => 'Option 2',
            'is_correct' => true,
        ]);

        // Select only 1 out of 2 correct
        $result = $this->multipleChoiceType->evaluate(
            $this->question,
            [$option1->id]
        );

        $this->assertFalse($result['is_correct']);
        $this->assertEquals(0.5, $result['score']); // Partial score
    }

    /**
     * Test validation with no selection
     */
    public function test_validation_no_selection(): void
    {
        $validation = $this->multipleChoiceType->validate(null, $this->question);

        $this->assertFalse($validation['valid']);
    }

    /**
     * Test validation with invalid option
     */
    public function test_validation_invalid_option(): void
    {
        Option::factory()->create([
            'question_id' => $this->question->id,
            'label' => 'Option',
            'is_correct' => true,
        ]);

        $validation = $this->multipleChoiceType->validate(
            [9999], // Non-existent option ID
            $this->question
        );

        $this->assertFalse($validation['valid']);
    }

    /**
     * Test partial scoring is supported
     */
    public function test_partial_scoring_supported(): void
    {
        $this->assertTrue($this->multipleChoiceType->supportsPartialScoring());
    }

    /**
     * Test render data includes options
     */
    public function test_render_data(): void
    {
        Option::factory()->count(3)->create([
            'question_id' => $this->question->id,
            'is_correct' => true,
        ]);

        $data = $this->multipleChoiceType->renderData($this->question);

        $this->assertEquals('multiple_choice', $data['type']);
        $this->assertCount(3, $data['options']);
        $this->assertTrue($data['strict_mode']);
    }

    /**
     * Test get type
     */
    public function test_get_type(): void
    {
        $this->assertEquals('multiple_choice', $this->multipleChoiceType->getType());
    }

    /**
     * Test missed options feedback
     */
    public function test_missed_options_feedback(): void
    {
        $correctOption = Option::factory()->create([
            'question_id' => $this->question->id,
            'label' => 'Correct Option',
            'is_correct' => true,
        ]);
        
        $missedOption = Option::factory()->create([
            'question_id' => $this->question->id,
            'label' => 'Missed Option',
            'is_correct' => true,
        ]);

        $this->question->update(['settings' => ['strict_mode' => false]]);

        $result = $this->multipleChoiceType->evaluate(
            $this->question,
            [$correctOption->id] // Only select one correct
        );

        $this->assertStringContainsString('missed', $result['feedback']);
    }
}
