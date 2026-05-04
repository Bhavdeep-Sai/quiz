<?php

namespace App\Services;

use App\Models\Question;
use App\Models\Attempt;
use App\Models\Answer;
use App\QuestionTypes\QuestionTypeResolver;
use InvalidArgumentException;

/**
 * Evaluation Service
 * 
 * Handles evaluation of user answers using the Strategy Pattern
 * No hardcoded logic for different question types!
 */
class EvaluationService
{
    /**
     * Evaluate a single answer
     * 
     * @param Question $question The question being answered
     * @param mixed $userAnswer The user's submitted answer
     * @return array Evaluation result
     */
    public function evaluateAnswer(Question $question, mixed $userAnswer): array
    {
        // Get the handler for this question type
        $handler = QuestionTypeResolver::resolve($question->type);

        // Evaluate using the appropriate handler
        $result = $handler->evaluate($question, $userAnswer);

        return $result;
    }

    /**
     * Validate an answer before submission
     * 
     * @param Question $question The question
     * @param mixed $userAnswer The answer to validate
     * @return array Validation result ['valid' => bool, 'error' => string|null]
     */
    public function validateAnswer(Question $question, mixed $userAnswer): array
    {
        $handler = QuestionTypeResolver::resolve($question->type);
        return $handler->validate($userAnswer, $question);
    }

    /**
     * Get rendering data for a question
     * 
     * @param Question $question The question to render
     * @return array Data for frontend rendering
     */
    public function renderQuestion(Question $question): array
    {
        $handler = QuestionTypeResolver::resolve($question->type);
        return $handler->renderData($question);
    }

    /**
     * Evaluate an entire quiz attempt
     * 
     * @param Attempt $attempt The attempt to evaluate
     * @param array $answers Map of question_id => user_answer
     * @return array Evaluation summary
     */
    public function evaluateAttempt(Attempt $attempt, array $answers): array
    {
        $totalScore = 0;
        $totalMarks = 0;
        $correctCount = 0;
        $evaluatedAnswers = [];

        // Fetch all questions at once with options to avoid N+1 issues
        $questionIds = array_keys($answers);
        $questions = Question::with('options')->whereIn('id', $questionIds)->get()->keyBy('id');

        // Evaluate each answer
        foreach ($answers as $questionId => $userAnswer) {
            $question = $questions->get($questionId);
            
            if (!$question) {
                continue;
            }

            // Evaluate this answer
            $evaluation = $this->evaluateAnswer($question, $userAnswer);

            // Store answer record
            $answer = new Answer([
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'user_answer' => $userAnswer,
                'question_type' => $question->type,
                'score' => $evaluation['score'],
                'is_correct' => $evaluation['is_correct'],
                'feedback' => $evaluation['feedback'] ?? null,
                'answered_at' => now(),
            ]);
            $answer->save();

            $evaluatedAnswers[] = $answer;

            // Aggregate scores
            $totalScore += $evaluation['score'];
            $totalMarks += $question->marks;

            if ($evaluation['is_correct']) {
                $correctCount++;
            }
        }

        // Calculate pass/fail
        $percentage = $totalMarks > 0 ? ($totalScore / $totalMarks) * 100 : 0;
        $isPassed = $percentage >= $attempt->quiz->pass_percentage;

        // Update attempt
        $attempt->update([
            'total_score' => $totalScore,
            'total_marks' => $totalMarks,
            'is_passed' => $isPassed,
            'status' => 'evaluated',
            'submitted_at' => now(),
        ]);

        return [
            'attempt_id' => $attempt->id,
            'total_score' => $totalScore,
            'total_marks' => $totalMarks,
            'percentage' => round($percentage, 2),
            'is_passed' => $isPassed,
            'correct_count' => $correctCount,
            'total_questions' => count($answers),
            'answers' => $evaluatedAnswers,
        ];
    }

    /**
     * Get performance analytics
     * 
     * @param Attempt $attempt The attempt to analyze
     * @return array Performance data
     */
    public function getPerformanceAnalytics(Attempt $attempt): array
    {
        $answers = $attempt->answers()->with('question')->get();

        $byType = [];
        $correctCount = 0;
        $incorrectCount = 0;

        foreach ($answers as $answer) {
            $type = $answer->question->type;

            if (!isset($byType[$type])) {
                $byType[$type] = [
                    'total' => 0,
                    'correct' => 0,
                    'score' => 0,
                    'marks' => 0,
                ];
            }

            $byType[$type]['total']++;
            $byType[$type]['score'] += $answer->score;
            $byType[$type]['marks'] += $answer->question->marks;

            if ($answer->is_correct) {
                $byType[$type]['correct']++;
                $correctCount++;
            } else {
                $incorrectCount++;
            }
        }

        return [
            'by_type' => $byType,
            'correct' => $correctCount,
            'incorrect' => $incorrectCount,
            'total' => $answers->count(),
        ];
    }

    /**
     * Get list of available question types
     * 
     * @return array Available types with metadata
     */
    public function getAvailableQuestionTypes(): array
    {
        return QuestionTypeResolver::getAvailableTypes();
    }

    /**
     * Check if question type is valid
     * 
     * @param string $type The type to check
     * @return bool True if valid
     */
    public function isValidQuestionType(string $type): bool
    {
        return QuestionTypeResolver::isValid($type);
    }
}
