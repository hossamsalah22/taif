<?php

namespace App\Models;

use App\Enums\RewardTypeEnum;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

#[Guarded(['id'])]
#[Hidden(['media'])]
#[Appends(['media_url'])]
class Reward extends Model implements HasMedia
{
    use HasTranslations, InteractsWithMedia, SoftDeletes;

    public $translatable = ['name'];

    protected $casts = [
        'type' => RewardTypeEnum::class,
    ];

    protected $with = ['media'];

    public function lessons()
    {
        return $this->hasMany(LearningLesson::class);
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('rewards')
            ->useDisk('public')
            ->singleFile();
    }

    public function getMediaUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('rewards');

        return $media ? $media->getFullUrl() : null;
    }
}
