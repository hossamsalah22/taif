<?php

namespace App\Models;

use App\Enums\DifficultyLevel;
use App\Enums\ExerciseTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningExercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'learning_lesson_id',
        'difficulty_level',
        'is_locked',
        'max_attempts',
        'display_priority',
        'type',
        'configuration',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'configuration' => 'array',
        'difficulty_level' => DifficultyLevel::class,
        'type' => ExerciseTypeEnum::class,
    ];

    public function lesson()
    {
        return $this->belongsTo(LearningLesson::class, 'learning_lesson_id');
    }
}
