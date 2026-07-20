<?php

namespace App\Models;

use App\Enums\ExerciseTypeEnum;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

#[Guarded(['id'])]
class Question extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia;

    public array $translatable = ['prompt'];

    protected $casts = [
        'exercise_type' => ExerciseTypeEnum::class,
        'payload' => 'array',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('question_audio')
            // mp3 or wav
            ->acceptsMimeTypes(['audio/mpeg', 'audio/wav'])
            ->singleFile();

        $this->addMediaCollection('question_image')
            // png or jpg or jpeg or gif or svg
            ->acceptsMimeTypes(['image/png', 'image/jpg', 'image/jpeg', 'image/gif', 'image/svg+xml', 'image/webp'])
            ->singleFile();
    }

    public function getAudioAttribute(): string
    {
        return $this->getFirstMediaUrl('question_audio');
    }

    public function getImageAttribute(): string
    {
        return $this->getFirstMediaUrl('question_image');
    }
}
