<?php

namespace Database\Factories;

use App\Enums\AutismLevelEnum;
use App\Enums\GenderEnum;
use App\Enums\SpeechStatusEnum;
use App\Models\Child;
use App\Models\User;
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
            'parent_id' => User::factory(),
            'name' => fake()->name(),
            'age' => fake()->numberBetween(3, 12),
            'gender' => GenderEnum::MALE->value,
            'autism_level' => AutismLevelEnum::MILD->value,
            'speech_status' => SpeechStatusEnum::VERBAL->value,
        ];
    }
}
