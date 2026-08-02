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
        'severity_level',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'severity_level' => AutismLevelEnum::class,
    ];

    public function goals()
    {
        return $this->hasMany(LearningGoal::class);
    }

    public function childLearningPlans()
    {
        return $this->hasMany(ChildLearningPlan::class);
    }
}
