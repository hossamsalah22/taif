<?php

namespace Database\Factories;

use App\Models\Child;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Child>
 */
class ChildFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parent_id' => \App\Models\User::factory(),
            'name' => fake()->name(),
            'age' => fake()->numberBetween(3, 12),
            'gender' => \App\Enums\GenderEnum::MALE->value,
            'autism_level' => \App\Enums\AutismLevelEnum::MILD->value,
            'speech_status' => \App\Enums\SpeechStatusEnum::VERBAL->value,
        ];
    }
}
