<?php

namespace App\Models;

use App\Enums\ExerciseTypeEnum;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    protected $with = ['media', 'options'];

    protected $hidden = ['media'];

    protected $appends = ['audio', 'image', 'shared_elements', 'distractors'];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order');
    }

    public function matchingPairs(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order');
    }

    public function orderingSteps(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order');
    }

    public function audioCards(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('question_audio')
            ->acceptsMimeTypes(['audio/mpeg', 'audio/wav'])
            ->singleFile();

        $this->addMediaCollection('question_image')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif'])
            ->singleFile();

        $this->addMediaCollection('distractors')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif']);

        $this->addMediaCollection('shared_elements')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif']);
    }

    public function getAudioAttribute(): ?string
    {
        $media = $this->getFirstMedia('question_audio');
        return $media ? $media->getFullUrl() : null;
    }

    public function getImageAttribute(): ?string
    {
        $media = $this->getFirstMedia('question_image');
        return $media ? $media->getFullUrl() : null;
    }

    public function getSharedElementsAttribute()
    {
        return $this->getMedia('shared_elements')->map(fn ($media) => [
            'id' => $media->id,
            'url' => $media->getFullUrl(),
        ]);
    }

    public function getDistractorsAttribute()
    {
        return $this->getMedia('distractors')->map(fn ($media) => [
            'id' => $media->id,
            'url' => $media->getFullUrl(),
        ]);
    }
}
