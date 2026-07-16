<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

#[Guarded(['id'])]
class Faq extends Model
{
    use HasTranslations;

    public array $translatable = ['question', 'answer'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    protected static function booted()
    {
        static::creating(function ($faq) {
            if (! $faq->order) {
                $faq->order = static::max('order') + 1;
            }
        });

        static::deleted(function ($faq) {
            static::where('order', '>', $faq->order)->decrement('order');
        });
    }
}
