<?php

namespace App\Models;

use App\Enums\AssessmentSubmissionStatusEnum;
use App\Notifications\ReportPublishedNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class AssessmentSubmission extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'child_id',
        'assessment_id',
        'assessment_version',
        'status',
        'specialist_notes',
        'strengths',
        'improvements',
        'recommendations',
        'diagnosed_severity_level',
        'report_published_at',
        'attempt_number',
        'performance_accuracy',
    ];

    protected $casts = [
        'report_published_at' => 'datetime',
        'status' => AssessmentSubmissionStatusEnum::class,
    ];

    /**
     * A specialist's evaluation moves the submission to "reviewed"; a published report stays published.
     */
    /**
     * Makes the report visible to the parent and tells them, unless they have deleted their account.
     */
    public function publish(): void
    {
        $this->update([
            'status' => AssessmentSubmissionStatusEnum::PUBLISHED,
            'report_published_at' => now(),
        ]);

        $this->child?->parent?->notify(new ReportPublishedNotification($this));
    }

    public function statusAfterEvaluation(): AssessmentSubmissionStatusEnum
    {
        return $this->status === AssessmentSubmissionStatusEnum::PUBLISHED
            ? AssessmentSubmissionStatusEnum::PUBLISHED
            : AssessmentSubmissionStatusEnum::REVIEWED;
    }

    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function answers()
    {
        return $this->hasMany(AssessmentAnswer::class);
    }
}
