<?php

namespace App\Models;

use App\Enums\AssessmentStatusEnum;
use App\Enums\AutismLevelEnum;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

#[Guarded(['id'])]
class Assessment extends Model
{
    use HasFactory, HasTranslations;

    public array $translatable = ['title'];

    protected $casts = [
        'autism_level' => AutismLevelEnum::class,
        'status' => AssessmentStatusEnum::class,
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function submissions()
    {
        return $this->hasMany(AssessmentSubmission::class);
    }
}
