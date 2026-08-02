<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChildLearningPlan extends Model
{
    protected $fillable = ['child_id', 'learning_plan_id', 'status'];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function learningPlan()
    {
        return $this->belongsTo(LearningPlan::class);
    }
}
