<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class QuestionOption extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia;

    public array $translatable = ['title'];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    protected $guarded = ['id'];

    protected $with = ['media'];

    protected $hidden = ['media'];

    protected $appends = ['left_element', 'right_element', 'image', 'audio'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('left_element')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif'])
            ->singleFile();

        $this->addMediaCollection('right_element')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif'])
            ->singleFile();

        $this->addMediaCollection('image')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif'])
            ->singleFile();

        $this->addMediaCollection('audio')
            ->acceptsMimeTypes(['audio/mpeg', 'audio/wav'])
            ->singleFile();
    }

    public function getLeftElementAttribute(): ?string
    {
        $media = $this->getFirstMedia('left_element');

        return $media ? $media->getFullUrl() : null;
    }

    public function getRightElementAttribute(): ?string
    {
        $media = $this->getFirstMedia('right_element');

        return $media ? $media->getFullUrl() : null;
    }

    public function getImageAttribute(): ?string
    {
        $media = $this->getFirstMedia('image');

        return $media ? $media->getFullUrl() : null;
    }

    public function getAudioAttribute(): ?string
    {
        $media = $this->getFirstMedia('audio');

        return $media ? $media->getFullUrl() : null;
    }
}
