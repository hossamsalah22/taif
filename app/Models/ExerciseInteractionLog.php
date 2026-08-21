<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExerciseInteractionLog extends Model
{
    protected $fillable = [
        'child_id', 
        'learning_exercise_id', 
        'is_successful', 
        'duration_seconds', 
        'trials_count', 
        'interaction_type', 
        'metadata'
    ];

    protected $casts = [
        'is_successful' => 'boolean',
        'metadata' => 'array',
    ];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function learningExercise()
    {
        return $this->belongsTo(LearningExercise::class);
    }
}
