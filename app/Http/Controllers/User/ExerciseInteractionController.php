<?php

namespace App\Http\Controllers\User;

use App\Enums\ChildLearningPlanStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\ChildLearningPlan;
use App\Models\ExerciseInteractionLog;
use App\Models\LearningExercise;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class ExerciseInteractionController extends Controller
{
    use ApiResponseTrait;

    public function store(Request $request)
    {
        $validated = $request->validate([
            'child_id' => 'required|exists:children,id',
            'learning_exercise_id' => 'required|exists:learning_exercises,id',
            'is_successful' => 'required|boolean',
            'duration_seconds' => 'required|integer|min:0',
            'trials_count' => 'required|integer|min:1',
            'interaction_type' => 'nullable|string',
        ]);

        $user = auth('sanctum')->user();

        $child = $user->children()->where('id', $validated['child_id'])->first();

        // Authorize child belongs to user
        if (! $child) {
            return $this->failedResponse(__('Data Not Found'), [], 404);
        }

        // Log the interaction
        $interaction = ExerciseInteractionLog::create([
            'child_id' => $validated['child_id'],
            'learning_exercise_id' => $validated['learning_exercise_id'],
            'is_successful' => $validated['is_successful'],
            'duration_seconds' => $validated['duration_seconds'],
            'trials_count' => $validated['trials_count'],
            'interaction_type' => $validated['interaction_type'],
        ]);

        if ($validated['is_successful']) {
            $child->completedExercises()->syncWithoutDetaching([$validated['learning_exercise_id']]);

            $exercise = LearningExercise::find($validated['learning_exercise_id']);
            $lesson = $exercise->lesson;

            // Check if all exercises in the lesson are completed
            $totalExercises = $lesson->exercises()->count();
            $completedExercisesCount = $child->completedExercises()->where('learning_lesson_id', $lesson->id)->count();

            if ($completedExercisesCount >= $totalExercises) {
                $child->completedLessons()->syncWithoutDetaching([$lesson->id]);

                $goal = $lesson->goal;
                // Check if all lessons in the goal are completed
                $totalLessons = $goal->lessons()->count();
                $completedLessonsCount = $child->completedLessons()->where('learning_goal_id', $goal->id)->count();

                if ($completedLessonsCount >= $totalLessons) {
                    $child->completedGoals()->syncWithoutDetaching([$goal->id]);

                    // Check if all goals in the plan are completed
                    $plan = $goal->plan;
                    $totalGoals = $plan->goals()->count();
                    $completedGoalsCount = $child->completedGoals()->where('learning_plan_id', $plan->id)->count();

                    if ($completedGoalsCount >= $totalGoals) {
                        $childLearningPlan = ChildLearningPlan::where('child_id', $child->id)
                            ->where('learning_plan_id', $plan->id)
                            ->first();

                        if ($childLearningPlan) {
                            $childLearningPlan->update(['status' => ChildLearningPlanStatusEnum::Completed]);
                        }
                    }
                }
            }
        }

        return $this->successResponse(__('Exercise interaction logged successfully.'), [
            'interaction_id' => $interaction->id,
            'is_successful' => $validated['is_successful'],
        ], 201);
    }
}
