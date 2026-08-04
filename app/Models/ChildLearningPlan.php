<?php

namespace App\Models;

use App\Enums\ChildLearningPlanStatusEnum;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;

#[Guarded(['id'])]
class ChildLearningPlan extends Model
{
    protected $casts = [
        'status' => ChildLearningPlanStatusEnum::class,
    ];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function learningPlan()
    {
        return $this->belongsTo(LearningPlan::class);
    }
}
