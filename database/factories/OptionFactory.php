<?php

namespace Database\Factories;

use App\Models\Option;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

class OptionFactory extends Factory
{
    protected $model = Option::class;

    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'label' => $this->faker->sentence(2),
            'image_url' => null,
            'is_correct' => false,
            'sort_order' => 0,
        ];
    }

    public function correct(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_correct' => true,
        ]);
    }

    public function withImage(): static
    {
        return $this->state(fn (array $attributes) => [
            'image_url' => 'https://via.placeholder.com/150x150',
        ]);
    }
}
