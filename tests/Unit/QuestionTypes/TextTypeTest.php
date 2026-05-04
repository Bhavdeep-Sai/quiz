<?php

namespace Tests\Unit\QuestionTypes;

use App\Models\Question;
use App\Models\Quiz;
use App\QuestionTypes\Types\TextType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit Tests — TextType
 *
 * Tests free-text question type with keyword matching.
 * Validates case-insensitive matching and partial scoring.
 */
class TextTypeTest extends TestCase
{
    use RefreshDatabase;

    protected TextType $handler;
    protected Question $question;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new TextType();

        $quiz = Quiz::create([
            'title'           => 'Test Quiz',
            'pass_percentage' => 70,
            'is_published'    => true,
        ]);

        $this->question = Question::create([
            'quiz_id'       => $quiz->id,
            'type'          => 'text',
            'question_text' => 'What is the capital of France?',
            'marks'         => 10,
            'settings'      => ['keywords' => ['Paris', 'paris'], 'case_sensitive' => false],
            'sort_order'    => 0,
        ]);
    }

    /**
     * @test
     */
    public function test_exact_keyword_match(): void
    {
        $result = $this->handler->evaluate($this->question, 'Paris');

        $this->assertTrue($result['is_correct']);
        $this->assertEquals(10, $result['score']);
    }

    /**
     * @test
     */
    public function test_case_insensitive_match(): void
    {
        $result = $this->handler->evaluate($this->question, 'PARIS');

        $this->assertTrue($result['is_correct']);
    }

    /**
     * @test
     */
    public function test_keyword_in_longer_text(): void
    {
        $result = $this->handler->evaluate($this->question, 'The city is Paris in France');

        $this->assertTrue($result['is_correct']);
    }

    /**
     * @test
     */
    public function test_no_matching_keywords(): void
    {
        $result = $this->handler->evaluate($this->question, 'London');

        $this->assertFalse($result['is_correct']);
        $this->assertEquals(0, $result['score']);
    }

    /**
     * @test
     */
    public function test_partial_word_not_matching(): void
    {
        // 'Par' does not match 'Paris' when matching whole words
        $result = $this->handler->evaluate($this->question, 'Par');

        $this->assertFalse($result['is_correct']);
    }

    /**
     * @test
     */
    public function test_validate_non_empty_text(): void
    {
        $result = $this->handler->validate('Paris', $this->question);
        $this->assertTrue($result['valid']);
    }

    /**
     * @test
     */
    public function test_reject_empty_text(): void
    {
        $result = $this->handler->validate('', $this->question);
        $this->assertFalse($result['valid']);

        $result = $this->handler->validate(null, $this->question);
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
    public function test_multiple_keywords_any_match(): void
    {
        $result = $this->handler->evaluate($this->question, 'paris');

        $this->assertTrue($result['is_correct']);
    }

    /**
     * @test
     */
    public function test_whitespace_trimming(): void
    {
        $result = $this->handler->evaluate($this->question, '  Paris  ');

        $this->assertTrue($result['is_correct']);
    }

    /**
     * @test
     */
    public function test_render_data_structure(): void
    {
        $data = $this->handler->renderData($this->question);

        $this->assertArrayHasKey('type', $data);
        $this->assertEquals('text', $data['type']);
    }

    /**
     * @test
     */
    public function test_get_type_identifier(): void
    {
        $this->assertEquals('text', $this->handler->getType());
    }

    /**
     * @test
     */
    public function test_unicode_characters_supported(): void
    {
        $this->question->update(['settings' => ['keywords' => ['Москва'], 'case_sensitive' => false]]);

        $result = $this->handler->evaluate($this->question, 'Москва');
        $this->assertTrue($result['is_correct']);
    }

    /**
     * @test
     */
    public function test_special_characters_in_keyword(): void
    {
        $this->question->update(['settings' => ['keywords' => ['C++', 'C#'], 'case_sensitive' => false]]);

        $result = $this->handler->evaluate($this->question, 'I code in C++');
        $this->assertTrue($result['is_correct']);
    }

    /**
     * @test
     */
    public function test_case_sensitive_matching(): void
    {
        $this->question->update(['settings' => ['keywords' => ['Paris'], 'case_sensitive' => true]]);

        $result = $this->handler->evaluate($this->question, 'Paris');
        $this->assertTrue($result['is_correct']);

        $result = $this->handler->evaluate($this->question, 'paris');
        $this->assertFalse($result['is_correct']);
    }

    /**
     * @test
     */
    public function test_very_long_text(): void
    {
        $longText = str_repeat('Lorem ipsum ', 1000) . 'Paris is a city';

        $result = $this->handler->evaluate($this->question, $longText);
        $this->assertTrue($result['is_correct']);
    }

    /**
     * @test
     */
    public function test_numeric_text_input(): void
    {
        $result = $this->handler->validate('12345', $this->question);
        $this->assertTrue($result['valid']);
    }
}
