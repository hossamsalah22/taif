<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'display_priority',
        'reward_id',
    ];

    public $translatable = ['name', 'description', 'acquired_skills'];

    protected $casts = [
        'acquired_skills' => 'array',
        'is_locked' => 'boolean',
    ];

    /**
     * @deprecated Legacy single-plan link. Use plans().
     */
    public function plan()
    {
        return $this->belongsTo(LearningPlan::class, 'learning_plan_id');
    }

    public function plans()
    {
        return $this->belongsToMany(LearningPlan::class, 'learning_goal_learning_plan')
            ->withPivot('display_priority')
            ->withTimestamps();
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'learning_goal_question')
            ->withPivot('order')
            ->withTimestamps()
            ->orderBy('learning_goal_question.order');
    }

    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }

    public function lessons()
    {
        return $this->hasMany(LearningLesson::class);
    }
}
