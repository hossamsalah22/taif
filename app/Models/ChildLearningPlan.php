<?php

namespace App\Models;

use App\Enums\ChildLearningPlanStatusEnum;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

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

    /**
     * Add the plan's exercise count and how many of them the child completed, in the same query.
     */
    public function scopeWithExerciseProgress(Builder $query): Builder
    {
        return $query->addSelect([
            'exercises_count' => self::planExercisesQuery()->selectRaw('count(*)'),
            'completed_exercises_count' => self::planExercisesQuery()
                ->whereExists(fn (QueryBuilder $completed) => $completed
                    ->from('child_completed_exercises')
                    ->whereColumn('child_completed_exercises.learning_exercise_id', 'learning_exercises.id')
                    ->whereColumn('child_completed_exercises.child_id', 'child_learning_plans.child_id'))
                ->selectRaw('count(*)'),
        ]);
    }

    /**
     * Completed exercises of the plan as a whole percentage (rounded down), 0 when the plan has no exercises.
     */
    public function progressPercentage(): int
    {
        if (! array_key_exists('exercises_count', $this->attributes)) {
            $counts = self::query()->select('id')->whereKey($this->getKey())->withExerciseProgress()->first();

            $this->setRawAttributes(array_merge($this->attributes, [
                'exercises_count' => $counts?->exercises_count ?? 0,
                'completed_exercises_count' => $counts?->completed_exercises_count ?? 0,
            ]), true);
        }

        $total = (int) $this->exercises_count;

        if ($total === 0) {
            return 0;
        }

        return intdiv((int) $this->completed_exercises_count * 100, $total);
    }

    /**
     * Exercises belonging to the row's plan, through its goals and their lessons.
     */
    private static function planExercisesQuery(): QueryBuilder
    {
        return DB::table('learning_exercises')
            ->join('learning_lessons', 'learning_lessons.id', '=', 'learning_exercises.learning_lesson_id')
            ->join('learning_goal_learning_plan', 'learning_goal_learning_plan.learning_goal_id', '=', 'learning_lessons.learning_goal_id')
            ->whereColumn('learning_goal_learning_plan.learning_plan_id', 'child_learning_plans.learning_plan_id');
    }
}
