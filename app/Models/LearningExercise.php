<?php

namespace App\Models;

use App\Enums\DifficultyLevel;
use App\Enums\ExerciseTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class LearningExercise extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $appends = ['video_thumbnail'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('video_thumbnail')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif'])
            ->singleFile();
    }

    public function getVideoThumbnailAttribute(): ?string
    {
        $media = $this->getFirstMedia('video_thumbnail');
        return $media ? $media->getFullUrl() : null;
    }

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

