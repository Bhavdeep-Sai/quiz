<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'type' => $this->faker->randomElement(['boolean', 'single_choice', 'multiple_choice', 'number', 'text']),
            'question_text' => $this->faker->sentence(),
            'image_url' => null,
            'video_url' => null,
            'marks' => $this->faker->randomElement([1, 2, 5, 10]),
            'settings' => [],
            'sort_order' => 0,
        ];
    }

    public function withImage(): static
    {
        return $this->state(fn (array $attributes) => [
            'image_url' => 'https://via.placeholder.com/300x200',
        ]);
    }

    public function withVideo(): static
    {
        return $this->state(fn (array $attributes) => [
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);
    }
}
