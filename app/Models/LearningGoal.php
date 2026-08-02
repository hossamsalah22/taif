<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;

class LearningGoal extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'learning_plan_id',
        'name',
        'description',
        'acquired_skills',
        'is_locked',
        'display_priority'
    ];

    public $translatable = ['name', 'description', 'acquired_skills'];

    protected $casts = [
        'acquired_skills' => 'array',
        'is_locked' => 'boolean'
    ];

    public function plan()
    {
        return $this->belongsTo(LearningPlan::class, 'learning_plan_id');
    }

    public function lessons()
    {
        return $this->hasMany(LearningLesson::class);
    }
}
