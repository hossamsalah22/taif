<?php

namespace App\Models;

use App\Enums\AutismLevelEnum;
use App\Enums\GenderEnum;
use App\Enums\SpeechStatusEnum;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Guarded(['id'])]
class Child extends Model
{
    use HasFactory;

    protected $casts = [
        'gender' => GenderEnum::class,
        'autism_level' => AutismLevelEnum::class,
        'speech_status' => SpeechStatusEnum::class,
    ];

    protected $with = ['assessmentSubmissions'];

    /**
     * Get the parent that owns the child.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function assessmentSubmissions(): HasMany
    {
        return $this->hasMany(AssessmentSubmission::class);
    }
}
