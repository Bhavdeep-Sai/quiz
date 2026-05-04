<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\Option;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@quizmaster.test'],
            [
                'name' => 'Quiz Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ]
        );

        // Create a sample quiz only when it does not already exist.
        $quiz = Quiz::firstOrCreate([
            'title' => 'Dynamic Quiz System Demo',
        ], [
            'description' => 'A comprehensive quiz demonstrating all question types supported by the Strategy Pattern.',
            'is_published' => true,
            'pass_percentage' => 60,
        ]);

        if (! $quiz->wasRecentlyCreated) {
            return;
        }

        // 1. Boolean Question
        $q1 = Question::create([
            'quiz_id' => $quiz->id,
            'type' => 'boolean',
            'question_text' => 'Is Laravel a PHP framework?',
            'marks' => 1,
            'sort_order' => 1,
        ]);
        Option::create(['question_id' => $q1->id, 'label' => 'True', 'is_correct' => true, 'sort_order' => 1]);
        Option::create(['question_id' => $q1->id, 'label' => 'False', 'is_correct' => false, 'sort_order' => 2]);

        // 2. Single Choice Question
        $q2 = Question::create([
            'quiz_id' => $quiz->id,
            'type' => 'single_choice',
            'question_text' => 'What is the default port for MySQL?',
            'marks' => 2,
            'sort_order' => 2,
        ]);
        Option::create(['question_id' => $q2->id, 'label' => '80', 'is_correct' => false, 'sort_order' => 1]);
        Option::create(['question_id' => $q2->id, 'label' => '443', 'is_correct' => false, 'sort_order' => 2]);
        Option::create(['question_id' => $q2->id, 'label' => '3306', 'is_correct' => true, 'sort_order' => 3]);
        Option::create(['question_id' => $q2->id, 'label' => '5432', 'is_correct' => false, 'sort_order' => 4]);

        // 3. Multiple Choice Question
        $q3 = Question::create([
            'quiz_id' => $quiz->id,
            'type' => 'multiple_choice',
            'question_text' => 'Which of the following are design patterns?',
            'marks' => 3,
            'sort_order' => 3,
        ]);
        Option::create(['question_id' => $q3->id, 'label' => 'Strategy', 'is_correct' => true, 'sort_order' => 1]);
        Option::create(['question_id' => $q3->id, 'label' => 'Observer', 'is_correct' => true, 'sort_order' => 2]);
        Option::create(['question_id' => $q3->id, 'label' => 'Hammer', 'is_correct' => false, 'sort_order' => 3]);
        Option::create(['question_id' => $q3->id, 'label' => 'Factory', 'is_correct' => true, 'sort_order' => 4]);

        // 4. Number Question
        Question::create([
            'quiz_id' => $quiz->id,
            'type' => 'number',
            'question_text' => 'What is 15 + 27?',
            'marks' => 2,
            'settings' => ['correct_answer' => 42, 'tolerance' => 0],
            'sort_order' => 4,
        ]);

        // 5. Text Question
        Question::create([
            'quiz_id' => $quiz->id,
            'type' => 'text',
            'question_text' => 'Explain what the Strategy Pattern is in one sentence.',
            'marks' => 5,
            'settings' => [
                'auto_grade' => true, 
                'keywords' => ['algorithm', 'interchangeable', 'encapsulate', 'strategy']
            ],
            'sort_order' => 5,
        ]);
    }
}
