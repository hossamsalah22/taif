<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_submission_id',
        'question_id',
        'answer_data',
    ];

    protected $casts = [
        'answer_data' => 'array',
    ];

    public function submission()
    {
        return $this->belongsTo(AssessmentSubmission::class, 'assessment_submission_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
