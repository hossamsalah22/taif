<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class LearningLesson extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'learning_goal_id',
        'name',
        'reward_id',
        'is_locked',
        'display_priority',
    ];

    public $translatable = ['name'];

    protected $casts = [
        'is_locked' => 'boolean',
    ];

    public function goal()
    {
        return $this->belongsTo(LearningGoal::class, 'learning_goal_id');
    }

    public function exercises()
    {
        return $this->hasMany(LearningExercise::class);
    }

    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }
}
