<?php

namespace Database\Seeders;

use App\Enums\AutismLevelEnum;
use App\Enums\DifficultyLevel;
use App\Enums\ExerciseTypeEnum;
use App\Models\LearningPlan;
use Illuminate\Database\Seeder;

class LearningPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plan1 = LearningPlan::create([
            'name' => [
                'en' => 'Autism Early Intervention Plan',
                'ar' => 'خطة التدخل المبكر للتوحد',
            ],
            'autism_level' => AutismLevelEnum::MILD->value,
            'is_active' => true,
            'weekly_sessions_count' => 3,
            'phase_duration' => '3_months',
            'max_daily_goals' => 2,
            'max_daily_lessons' => 4,
            'max_daily_exercises' => 10,
        ]);

        $goal1 = $plan1->goals()->create([
            'name' => [
                'en' => 'Improve Visual Recognition',
                'ar' => 'تحسين التعرف البصري',
            ],
            'description' => [
                'en' => 'The child will be able to recognize basic shapes and colors.',
                'ar' => 'سيكون الطفل قادراً على التعرف على الأشكال والألوان الأساسية.',
            ],
            'acquired_skills' => [
                'en' => ['Visual Tracking', 'Color Matching'],
                'ar' => ['التتبع البصري', 'مطابقة الألوان'],
            ],
            'is_locked' => false,
            'display_priority' => 1,
        ]);

        $lesson1 = $goal1->lessons()->create([
            'name' => [
                'en' => 'Basic Colors',
                'ar' => 'الألوان الأساسية',
            ],
            'reward_id' => 'reward_001',
            'is_locked' => false,
            'display_priority' => 1,
        ]);

        $lesson1->exercises()->create([
            'difficulty_level' => DifficultyLevel::LOW->value,
            'is_locked' => false,
            'max_attempts' => 3,
            'display_priority' => 1,
            'type' => ExerciseTypeEnum::IMAGE_SELECTION->value,
            'configuration' => [
                'correct_image' => 'red_circle.png',
                'distractors' => ['blue_square.png', 'green_triangle.png'],
            ],
        ]);

        $lesson1->exercises()->create([
            'difficulty_level' => DifficultyLevel::MEDIUM->value,
            'is_locked' => true,
            'max_attempts' => 5,
            'display_priority' => 2,
            'type' => ExerciseTypeEnum::MATCHING->value,
            'configuration' => [
                'pairs' => [
                    ['source' => 'red.png', 'target' => 'red_apple.png'],
                    ['source' => 'blue.png', 'target' => 'blue_sky.png'],
                ],
            ],
        ]);
    }
}
