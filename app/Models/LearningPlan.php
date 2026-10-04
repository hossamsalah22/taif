<?php

namespace App\Models;

use App\Enums\AutismLevelEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class LearningPlan extends Model
{
    use HasFactory, HasTranslations;

    public $translatable = ['name'];

    protected $fillable = [
        'name',
        'weekly_sessions_count',
        'phase_duration',
        'max_daily_goals',
        'max_daily_lessons',
        'max_daily_exercises',
        'autism_level',
        'is_active',
        'reward_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'autism_level' => AutismLevelEnum::class,
    ];

    public function goals()
    {
        return $this->belongsToMany(LearningGoal::class, 'learning_goal_learning_plan')
            ->withPivot('display_priority')
            ->withTimestamps()
            ->orderBy('learning_goals.display_priority');
    }

    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }

    public function childLearningPlans()
    {
        return $this->hasMany(ChildLearningPlan::class);
    }
}
