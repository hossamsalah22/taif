<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExerciseInteractionLog extends Model
{
    protected $fillable = ['child_id', 'learning_exercise_id', 'status', 'attempts', 'score'];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function learningExercise()
    {
        return $this->belongsTo(LearningExercise::class);
    }
}
