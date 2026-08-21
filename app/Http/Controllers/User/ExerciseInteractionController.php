<?php

namespace App\Http\Controllers\User;

use App\Enums\ChildLearningPlanStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\ChildLearningPlan;
use App\Models\ChildReward;
use App\Models\ExerciseInteractionLog;
use App\Models\LearningExercise;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'metadata' => 'nullable|array',
        ]);

        $user = auth('sanctum')->user();
        $child = $user->children()->where('id', $validated['child_id'])->first();

        if (! $child) {
            return $this->failedResponse(__('Data Not Found'), [], 404);
        }

        $interaction = ExerciseInteractionLog::create([
            'child_id' => $validated['child_id'],
            'learning_exercise_id' => $validated['learning_exercise_id'],
            'is_successful' => $validated['is_successful'],
            'duration_seconds' => $validated['duration_seconds'],
            'trials_count' => $validated['trials_count'],
            'interaction_type' => $validated['interaction_type'] ?? null,
            'metadata' => $validated['metadata'] ?? null,
        ]);

        if ($validated['is_successful']) {
            $this->processSuccessfulExercise($child, $validated['learning_exercise_id']);
        }

        return $this->successResponse(__('Exercise interaction logged successfully.'), [
            'interaction_id' => $interaction->id,
            'is_successful' => $validated['is_successful'],
        ], 201);
    }

    public function sync(Request $request)
    {
        $validated = $request->validate([
            'interactions' => 'required|array',
            'interactions.*.child_id' => 'required|exists:children,id',
            'interactions.*.learning_exercise_id' => 'required|exists:learning_exercises,id',
            'interactions.*.is_successful' => 'required|boolean',
            'interactions.*.duration_seconds' => 'required|integer|min:0',
            'interactions.*.trials_count' => 'required|integer|min:1',
            'interactions.*.interaction_type' => 'nullable|string',
            'interactions.*.metadata' => 'nullable|array',
        ]);

        $user = auth('sanctum')->user();

        DB::transaction(function () use ($validated, $user) {
            foreach ($validated['interactions'] as $interactionData) {
                $child = $user->children()->where('id', $interactionData['child_id'])->first();
                if (! $child) {
                    continue;
                }

                ExerciseInteractionLog::create([
                    'child_id' => $interactionData['child_id'],
                    'learning_exercise_id' => $interactionData['learning_exercise_id'],
                    'is_successful' => $interactionData['is_successful'],
                    'duration_seconds' => $interactionData['duration_seconds'],
                    'trials_count' => $interactionData['trials_count'],
                    'interaction_type' => $interactionData['interaction_type'],
                    'metadata' => $interactionData['metadata'] ?? null,
                ]);

                if ($interactionData['is_successful']) {
                    $this->processSuccessfulExercise($child, $interactionData['learning_exercise_id']);
                }
            }
        });

        return $this->successResponse(__('Exercise interactions synced successfully.'));
    }

    private function processSuccessfulExercise($child, $exerciseId)
    {
        $child->completedExercises()->syncWithoutDetaching([$exerciseId]);

        $exercise = LearningExercise::find($exerciseId);
        $lesson = $exercise->lesson;

        $totalExercises = $lesson->exercises()->count();
        $completedExercisesCount = $child->completedExercises()->where('learning_lesson_id', $lesson->id)->count();

        if ($completedExercisesCount >= $totalExercises) {
            $child->completedLessons()->syncWithoutDetaching([$lesson->id]);

            if ($lesson->reward_id) {
                ChildReward::firstOrCreate([
                    'child_id' => $child->id,
                    'reward_id' => $lesson->reward_id,
                ]);
            }

            $goal = $lesson->goal;
            $totalLessons = $goal->lessons()->count();
            $completedLessonsCount = $child->completedLessons()->where('learning_goal_id', $goal->id)->count();

            if ($completedLessonsCount >= $totalLessons) {
                $child->completedGoals()->syncWithoutDetaching([$goal->id]);

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
}
