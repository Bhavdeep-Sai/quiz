<?php

namespace Database\Factories;

use App\Models\Answer;
use App\Models\Attempt;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnswerFactory extends Factory
{
    protected $model = Answer::class;

    public function definition(): array
    {
        return [
            'attempt_id' => Attempt::factory(),
            'question_id' => Question::factory(),
            'user_answer' => $this->faker->word(),
            'question_type' => 'text',
            'score' => $this->faker->randomFloat(2, 0, 10),
            'is_correct' => $this->faker->boolean(),
            'feedback' => $this->faker->sentence(),
            'answered_at' => now(),
            'time_spent_seconds' => $this->faker->numberBetween(5, 300),
        ];
    }
}
