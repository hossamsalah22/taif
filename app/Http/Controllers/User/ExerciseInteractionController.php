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
            'is_successful' => 'nullable|boolean',
            'answer' => 'nullable',
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

        $exercise = LearningExercise::findOrFail($validated['learning_exercise_id']);
        
        $isSuccessful = $validated['is_successful'] ?? false;
        
        if (!isset($validated['is_successful']) && isset($validated['answer'])) {
            $type = $exercise->type->value ?? $exercise->type;
            
            if (in_array($type, [\App\Enums\ExerciseTypeEnum::IMAGE_SELECTION->value, \App\Enums\ExerciseTypeEnum::DISTINGUISHING->value, \App\Enums\ExerciseTypeEnum::AUDIO_FLASHCARDS->value])) {
                $options = $exercise->configuration['options'] ?? [];
                $correctOptionIds = [];
                foreach ($options as $uuid => $opt) {
                    if (!empty($opt['is_correct'])) {
                        $correctOptionIds[] = $uuid;
                    }
                }
                $submittedOptionIds = (array) $validated['answer'];
                if (count($correctOptionIds) === count($submittedOptionIds) && empty(array_diff($correctOptionIds, $submittedOptionIds))) {
                    $isSuccessful = true;
                }
            } elseif ($type === \App\Enums\ExerciseTypeEnum::MATCHING->value) {
                $submittedPairs = (array) $validated['answer'];
                $allMatched = true;
                foreach ($submittedPairs as $pair) {
                    if (($pair['left_option_id'] ?? null) !== ($pair['right_option_id'] ?? null)) {
                        $allMatched = false;
                        break;
                    }
                }
                $matchingPairsCount = count($exercise->configuration['matchingPairs'] ?? []);
                if ($allMatched && count($submittedPairs) === $matchingPairsCount && $matchingPairsCount > 0) {
                    $isSuccessful = true;
                }
            } elseif ($type === \App\Enums\ExerciseTypeEnum::ORDERING->value) {
                $correctOrderIds = array_keys($exercise->configuration['orderingSteps'] ?? []);
                $submittedOrderIds = (array) $validated['answer'];
                if ($correctOrderIds === $submittedOrderIds) {
                    $isSuccessful = true;
                }
            }
        }

        $interaction = ExerciseInteractionLog::create([
            'child_id' => $validated['child_id'],
            'learning_exercise_id' => $validated['learning_exercise_id'],
            'is_successful' => $isSuccessful,
            'duration_seconds' => $validated['duration_seconds'],
            'trials_count' => $validated['trials_count'],
            'interaction_type' => $validated['interaction_type'] ?? null,
            'metadata' => array_merge($validated['metadata'] ?? [], ['submitted_answer' => $validated['answer'] ?? null]),
        ]);

        $this->processCompletedExercise($child, $validated['learning_exercise_id']);

        return $this->successResponse(__('Exercise interaction logged successfully.'), [
            'interaction_id' => $interaction->id,
            'is_successful' => $isSuccessful,
        ], 201);
    }

    public function sync(Request $request)
    {
        $validated = $request->validate([
            'interactions' => 'required|array',
            'interactions.*.child_id' => 'required|exists:children,id',
            'interactions.*.learning_exercise_id' => 'required|exists:learning_exercises,id',
            'interactions.*.is_successful' => 'nullable|boolean',
            'interactions.*.answer' => 'nullable',
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

                $exercise = LearningExercise::find($interactionData['learning_exercise_id']);
                if (!$exercise) {
                    continue;
                }

                $isSuccessful = $interactionData['is_successful'] ?? false;
                
                if (!isset($interactionData['is_successful']) && isset($interactionData['answer'])) {
                    $type = $exercise->type->value ?? $exercise->type;
                    
                    if (in_array($type, [\App\Enums\ExerciseTypeEnum::IMAGE_SELECTION->value, \App\Enums\ExerciseTypeEnum::DISTINGUISHING->value, \App\Enums\ExerciseTypeEnum::AUDIO_FLASHCARDS->value])) {
                        $options = $exercise->configuration['options'] ?? [];
                        $correctOptionIds = [];
                        foreach ($options as $uuid => $opt) {
                            if (!empty($opt['is_correct'])) {
                                $correctOptionIds[] = $uuid;
                            }
                        }
                        $submittedOptionIds = (array) $interactionData['answer'];
                        if (count($correctOptionIds) === count($submittedOptionIds) && empty(array_diff($correctOptionIds, $submittedOptionIds))) {
                            $isSuccessful = true;
                        }
                    } elseif ($type === \App\Enums\ExerciseTypeEnum::MATCHING->value) {
                        $submittedPairs = (array) $interactionData['answer'];
                        $allMatched = true;
                        foreach ($submittedPairs as $pair) {
                            if (($pair['left_option_id'] ?? null) !== ($pair['right_option_id'] ?? null)) {
                                $allMatched = false;
                                break;
                            }
                        }
                        $matchingPairsCount = count($exercise->configuration['matchingPairs'] ?? []);
                        if ($allMatched && count($submittedPairs) === $matchingPairsCount && $matchingPairsCount > 0) {
                            $isSuccessful = true;
                        }
                    } elseif ($type === \App\Enums\ExerciseTypeEnum::ORDERING->value) {
                        $correctOrderIds = array_keys($exercise->configuration['orderingSteps'] ?? []);
                        $submittedOrderIds = (array) $interactionData['answer'];
                        if ($correctOrderIds === $submittedOrderIds) {
                            $isSuccessful = true;
                        }
                    }
                }

                ExerciseInteractionLog::create([
                    'child_id' => $interactionData['child_id'],
                    'learning_exercise_id' => $interactionData['learning_exercise_id'],
                    'is_successful' => $isSuccessful,
                    'duration_seconds' => $interactionData['duration_seconds'],
                    'trials_count' => $interactionData['trials_count'],
                    'interaction_type' => $interactionData['interaction_type'] ?? null,
                    'metadata' => array_merge($interactionData['metadata'] ?? [], ['submitted_answer' => $interactionData['answer'] ?? null]),
                ]);

                $this->processCompletedExercise($child, $interactionData['learning_exercise_id']);
            }
        });

        return $this->successResponse(__('Exercise interactions synced successfully.'));
    }

    private function processCompletedExercise($child, $exerciseId)
    {
        $child->completedExercises()->syncWithoutDetaching([$exerciseId]);

        $exercise = LearningExercise::find($exerciseId);
        $lesson = $exercise->lesson;

        $totalExercises = $lesson->exercises()->count();
        $completedExercisesCount = $child->completedExercises()->where('learning_exercises.learning_lesson_id', $lesson->id)->count();

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
