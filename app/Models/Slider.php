<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Slider extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = ['id'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('slider_en_large')->singleFile();
        $this->addMediaCollection('slider_en_small')->singleFile();
        $this->addMediaCollection('slider_ar_large')->singleFile();
        $this->addMediaCollection('slider_ar_small')->singleFile();
    }
}

