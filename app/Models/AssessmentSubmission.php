<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id',
        'assessment_id',
        'assessment_version',
        'status',
        'notes',
        'strengths',
        'improvements',
        'recommendations',
        'diagnosed_severity_level',
        'report_published_at'
    ];

    protected $casts = [
        'report_published_at' => 'datetime'
    ];

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
