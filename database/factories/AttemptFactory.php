<?php

namespace Database\Factories;

use App\Models\Attempt;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttemptFactory extends Factory
{
    protected $model = Attempt::class;

    public function definition(): array
    {
        $startedAt = $this->faker->dateTimeThisMonth();

        return [
            'quiz_id' => Quiz::factory(),
            'user_identifier' => $this->faker->uuid(),
            'user_name' => $this->faker->name(),
            'user_email' => $this->faker->email(),
            'started_at' => $startedAt,
            'submitted_at' => null,
            'time_spent_seconds' => $this->faker->numberBetween(60, 3600),
            'total_score' => $this->faker->randomFloat(2, 0, 100),
            'total_marks' => 100,
            'is_passed' => $this->faker->boolean(),
            'status' => 'in_progress',
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);
    }

    public function evaluated(): static
    {
        return $this->state(fn (array $attributes) => [
            'submitted_at' => now()->subHour(),
            'status' => 'evaluated',
        ]);
    }
}
