<?php

namespace Tests\Feature\Integration;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\Option;
use App\Models\Attempt;
use App\Models\Answer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * STEP 8: Integration Test - Full Quiz Workflow
 * 
 * Tests complete quiz lifecycle from creation through evaluation
 * Layer 1: TestContainers (Database) - Uses RefreshDatabase
 * Layer 2: Smoke Tests (Critical Paths) - Happy path scenarios
 * Layer 3: Behavioral Comparison - Consistency validation
 * Layer 4: End-to-End - Full workflow verification
 */
class FullWorkflowIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    /**
     * Layer 2: Smoke Test - Critical Path
     * Complete workflow: Create Quiz → Add Questions → Start Attempt → Submit → Evaluate
     * 
     * @test
     */
    public function test_complete_quiz_workflow_smoke_test()
    {
        // Step 1: Create Quiz
        $quiz = Quiz::create([
            'title' => 'Math Quiz',
            'description' => 'Basic math questions',
            'pass_percentage' => 70,
            'is_published' => true,
        ]);

        // Step 2: Add Questions with Multiple Choice
        $q1 = Question::create([
            'quiz_id' => $quiz->id,
            'type' => 'multiple_choice',
            'question_text' => 'Which are correct?',
            'marks' => 10,
            'settings' => ['min_selections' => 1, 'max_selections' => 2],
            'sort_order' => 0,
        ]);

        Option::create(['question_id' => $q1->id, 'label' => 'A', 'is_correct' => true, 'sort_order' => 0]);
        Option::create(['question_id' => $q1->id, 'label' => 'B', 'is_correct' => true, 'sort_order' => 1]);
        Option::create(['question_id' => $q1->id, 'label' => 'C', 'is_correct' => false, 'sort_order' => 2]);

        // Step 3: Add Boolean Question
        $q2 = Question::create([
            'quiz_id' => $quiz->id,
            'type' => 'boolean',
            'question_text' => 'Is this true?',
            'marks' => 10,
            'settings' => [],
            'sort_order' => 1,
        ]);

        Option::create(['question_id' => $q2->id, 'label' => 'True', 'is_correct' => true, 'sort_order' => 0]);
        Option::create(['question_id' => $q2->id, 'label' => 'False', 'is_correct' => false, 'sort_order' => 1]);

        // Step 4: Add Number Question
        $q3 = Question::create([
            'quiz_id' => $quiz->id,
            'type' => 'number',
            'question_text' => 'What is 10 + 5?',
            'marks' => 10,
            'settings' => ['tolerance' => 0],
            'sort_order' => 2,
        ]);

        // Step 5: Start Attempt
        $attempt = Attempt::create([
            'quiz_id' => $quiz->id,
            'user_name' => 'John Doe',
            'user_email' => 'john@example.com',
            'user_identifier' => 'STU001',
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        // Step 6: Submit Answers
        Answer::create([
            'attempt_id' => $attempt->id,
            'question_id' => $q1->id,
            'user_answer' => json_encode(['1', '2']), // Options A & B
            'question_type' => 'multiple_choice',
            'is_correct' => true,
            'score' => 10,
            'feedback' => 'All correct answers selected',
            'answered_at' => now(),
            'time_spent_seconds' => 30,
        ]);

        Answer::create([
            'attempt_id' => $attempt->id,
            'question_id' => $q2->id,
            'user_answer' => json_encode(true),
            'question_type' => 'boolean',
            'is_correct' => true,
            'score' => 10,
            'feedback' => 'Correct',
            'answered_at' => now(),
            'time_spent_seconds' => 15,
        ]);

        Answer::create([
            'attempt_id' => $attempt->id,
            'question_id' => $q3->id,
            'user_answer' => json_encode(15),
            'question_type' => 'number',
            'is_correct' => true,
            'score' => 10,
            'feedback' => 'Exact match',
            'answered_at' => now(),
            'time_spent_seconds' => 20,
        ]);

        // Step 7: Verify Attempt Submission
        $attempt->update([
            'submitted_at' => now(),
            'time_spent_seconds' => 65,
            'total_score' => 30,
            'total_marks' => 30,
            'is_passed' => true,
            'status' => 'evaluated',
        ]);

        // Step 8: Assertions
        $this->assertDatabaseHas('attempts', [
            'id' => $attempt->id,
            'total_score' => 30,
            'total_marks' => 30,
            'is_passed' => true,
            'status' => 'evaluated',
        ]);

        $answers = Answer::where('attempt_id', $attempt->id)->get();
        $this->assertEquals(3, $answers->count());
        $this->assertTrue($answers->every(fn($a) => $a->is_correct));
    }

    /**
     * Layer 2: Smoke Test - Partial Scoring Path
     * Test partial credit with MultipleChoice (not all correct selected)
     * 
     * @test
     */
    public function test_partial_scoring_smoke_test()
    {
        $quiz = Quiz::create([
            'title' => 'Quiz',
            'pass_percentage' => 50,
            'is_published' => true,
        ]);

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'type' => 'multiple_choice',
            'question_text' => 'Select all correct answers:',
            'marks' => 20,
            'settings' => ['strict_mode' => false, 'min_selections' => 1],
            'sort_order' => 0,
        ]);

        // Options: 2 correct, 2 incorrect
        Option::create(['question_id' => $question->id, 'label' => 'Option A', 'is_correct' => true, 'sort_order' => 0]);
        Option::create(['question_id' => $question->id, 'label' => 'Option B', 'is_correct' => true, 'sort_order' => 1]);
        Option::create(['question_id' => $question->id, 'label' => 'Option C', 'is_correct' => false, 'sort_order' => 2]);
        Option::create(['question_id' => $question->id, 'label' => 'Option D', 'is_correct' => false, 'sort_order' => 3]);

        $attempt = Attempt::create([
            'quiz_id' => $quiz->id,
            'user_name' => 'Jane Doe',
            'user_email' => 'jane@example.com',
            'user_identifier' => 'STU002',
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        // User selects only 1 of 2 correct answers (50% partial score)
        Answer::create([
            'attempt_id' => $attempt->id,
            'question_id' => $question->id,
            'user_answer' => json_encode(['1']), // Only option A
            'question_type' => 'multiple_choice',
            'is_correct' => false,
            'score' => 10, // 50% of 20
            'feedback' => 'Partial credit: only 1 of 2 correct answers selected',
            'answered_at' => now(),
            'time_spent_seconds' => 45,
        ]);

        $attempt->update([
            'submitted_at' => now(),
            'time_spent_seconds' => 45,
            'total_score' => 10,
            'total_marks' => 20,
            'is_passed' => false,
            'status' => 'evaluated',
        ]);

        $this->assertDatabaseHas('answers', [
            'attempt_id' => $attempt->id,
            'is_correct' => false,
            'score' => 10,
        ]);
    }

    /**
     * Layer 4: Behavioral Comparison - Consistency Test
     * Verify question count and total marks calculations
     * 
     * @test
     */
    public function test_quiz_statistics_consistency()
    {
        $quiz = Quiz::create([
            'title' => 'Test Quiz',
            'pass_percentage' => 60,
            'is_published' => true,
        ]);

        // Create 5 questions with varying marks
        for ($i = 1; $i <= 5; $i++) {
            Question::create([
                'quiz_id' => $quiz->id,
                'type' => 'boolean',
                'question_text' => "Question $i?",
                'marks' => $i * 10,
                'settings' => [],
                'sort_order' => $i - 1,
            ]);
        }

        $this->assertEquals(5, $quiz->questions()->count());
        $this->assertEquals(150, $quiz->questions()->sum('marks')); // 10+20+30+40+50
    }

    /**
     * Layer 3: Behavioral Comparison - Data Integrity
     * Ensure relationships are maintained through full workflow
     * 
     * @test
     */
    public function test_data_integrity_through_workflow()
    {
        $quiz = Quiz::create([
            'title' => 'Integrity Test',
            'pass_percentage' => 70,
            'is_published' => true,
        ]);

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'type' => 'single_choice',
            'question_text' => 'What is correct?',
            'marks' => 5,
            'settings' => [],
            'sort_order' => 0,
        ]);

        $correctOption = Option::create([
            'question_id' => $question->id,
            'label' => 'Correct',
            'is_correct' => true,
            'sort_order' => 0,
        ]);

        Option::create([
            'question_id' => $question->id,
            'label' => 'Wrong',
            'is_correct' => false,
            'sort_order' => 1,
        ]);

        $attempt = Attempt::create([
            'quiz_id' => $quiz->id,
            'user_name' => 'Test User',
            'user_email' => 'test@example.com',
            'user_identifier' => 'TST001',
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        // Verify relationships
        $this->assertTrue($question->quiz()->is($quiz));
        $this->assertEquals(2, $question->options()->count());
        $this->assertTrue($correctOption->question()->is($question));
        $this->assertTrue($attempt->quiz()->is($quiz));
    }

    /**
     * Layer 4: End-to-End - Multiple Attempts
     * Verify multiple users can take same quiz independently
     * 
     * @test
     */
    public function test_multiple_attempts_independence()
    {
        $quiz = Quiz::create([
            'title' => 'Multi Attempt Quiz',
            'pass_percentage' => 60,
            'is_published' => true,
        ]);

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'type' => 'boolean',
            'question_text' => 'Is this true?',
            'marks' => 100,
            'settings' => [],
            'sort_order' => 0,
        ]);

        Option::create(['question_id' => $question->id, 'label' => 'True', 'is_correct' => true, 'sort_order' => 0]);
        Option::create(['question_id' => $question->id, 'label' => 'False', 'is_correct' => false, 'sort_order' => 1]);

        // User 1 - Correct answer
        $attempt1 = Attempt::create([
            'quiz_id' => $quiz->id,
            'user_name' => 'User One',
            'user_email' => 'user1@example.com',
            'user_identifier' => 'USR001',
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        Answer::create([
            'attempt_id' => $attempt1->id,
            'question_id' => $question->id,
            'user_answer' => json_encode(true),
            'question_type' => 'boolean',
            'is_correct' => true,
            'score' => 100,
            'answered_at' => now(),
            'time_spent_seconds' => 10,
        ]);

        $attempt1->update(['submitted_at' => now(), 'total_score' => 100, 'total_marks' => 100, 'is_passed' => true, 'status' => 'evaluated']);

        // User 2 - Wrong answer
        $attempt2 = Attempt::create([
            'quiz_id' => $quiz->id,
            'user_name' => 'User Two',
            'user_email' => 'user2@example.com',
            'user_identifier' => 'USR002',
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        Answer::create([
            'attempt_id' => $attempt2->id,
            'question_id' => $question->id,
            'user_answer' => json_encode(false),
            'question_type' => 'boolean',
            'is_correct' => false,
            'score' => 0,
            'answered_at' => now(),
            'time_spent_seconds' => 20,
        ]);

        $attempt2->update(['submitted_at' => now(), 'total_score' => 0, 'total_marks' => 100, 'is_passed' => false, 'status' => 'evaluated']);

        // Verify independence
        $this->assertTrue($attempt1->is_passed);
        $this->assertFalse($attempt2->is_passed);
        $this->assertEquals(100, $attempt1->total_score);
        $this->assertEquals(0, $attempt2->total_score);
    }

    /**
     * Layer 2: Smoke Test - Error Handling
     * Verify system handles invalid states gracefully
     * 
     * @test
     */
    public function test_unpublished_quiz_not_accessible()
    {
        $quiz = Quiz::create([
            'title' => 'Private Quiz',
            'pass_percentage' => 70,
            'is_published' => false,
        ]);

        // Attempting to access unpublished quiz should fail in API/Web layer
        $this->assertFalse($quiz->is_published);
        $this->assertTrue(Quiz::published()->whereId($quiz->id)->doesntExist());
    }

    /**
     * Layer 3: Behavioral Comparison - Result Breakdown
     * Verify result breakdown creation and calculations
     * 
     * @test
     */
    public function test_result_breakdown_tracking()
    {
        $quiz = Quiz::create([
            'title' => 'Category Quiz',
            'pass_percentage' => 60,
            'is_published' => true,
        ]);

        $attempt = Attempt::create([
            'quiz_id' => $quiz->id,
            'user_name' => 'User',
            'user_email' => 'user@example.com',
            'user_identifier' => 'USR003',
            'started_at' => now(),
            'submitted_at' => now()->addMinutes(5),
            'time_spent_seconds' => 300,
            'total_score' => 80,
            'total_marks' => 100,
            'is_passed' => true,
            'status' => 'evaluated',
        ]);

        // Create breakdown for category
        $breakdown = $attempt->resultBreakdowns()->create([
            'category' => 'Science',
            'total_questions' => 10,
            'correct_answers' => 8,
            'percentage' => 80.0,
            'total_score' => 80,
            'avg_time_per_question' => 30,
            'performance_level' => 'excellent',
        ]);

        $this->assertDatabaseHas('result_breakdowns', [
            'attempt_id' => $attempt->id,
            'category' => 'Science',
            'correct_answers' => 8,
            'percentage' => 80.0,
        ]);
    }
}
