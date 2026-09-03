<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\LearningLesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LearningLessonController extends Controller
{
    public function show(LearningLesson $lesson, Request $request)
    {
        $childId = $request->query('child_id');
        $child = null;
        
        if ($childId) {
            $child = auth('sanctum')->user()->children()->where('id', $childId)->first();
        }

        $lesson->load(['exercises']);
        
        $dailyExercisesLimitReached = false;
        $completedExerciseIds = [];
        $previousExerciseCompleted = true;

        if ($child) {
            $completedExerciseIds = $child->completedExercises()->pluck('learning_exercises.id')->toArray();
            
            $plan = $lesson->goal->plan ?? null;
            if ($plan) {
                $exercisesCompletedToday = $child->completedExercises()->whereDate('child_completed_exercises.created_at', today())->count();
                $dailyExercisesLimitReached = $plan->max_daily_exercises > 0 && $exercisesCompletedToday >= $plan->max_daily_exercises;
            }
        }

        return $this->successResponse(__('Lesson retrieved successfully'), [
            'lesson' => [
                'id' => $lesson->id,
                'name' => $lesson->name,
                'reward_id' => $lesson->reward_id,
                'is_locked' => $lesson->is_locked,
                'display_priority' => $lesson->display_priority,
                'exercises' => $lesson->exercises->map(function ($exercise) use ($child, $completedExerciseIds, $dailyExercisesLimitReached, &$previousExerciseCompleted) {
                    $configuration = $exercise->configuration;

                    if (is_array($configuration)) {
                        foreach (['options', 'matchingPairs', 'orderingSteps'] as $key) {
                            if (isset($configuration[$key]) && is_array($configuration[$key])) {
                                $formattedItems = [];
                                foreach ($configuration[$key] as $uuid => $item) {
                                    $item['id'] = $uuid;
                                    foreach (['image', 'audio', 'left_element', 'right_element'] as $fileKey) {
                                        if (!empty($item[$fileKey])) {
                                            $item[$fileKey] = Storage::disk('public')->url($item[$fileKey]);
                                        }
                                    }
                                    $formattedItems[] = $item;
                                }
                                $configuration[$key] = $formattedItems;
                            }
                        }
                    }

                    $isCompleted = false;
                    $isLocked = $exercise->is_locked;

                    if ($child) {
                        $isCompleted = in_array($exercise->id, $completedExerciseIds);
                        
                        if ($isCompleted) {
                            $isLocked = false;
                        } elseif ($isLocked) {
                            $isLocked = $dailyExercisesLimitReached || !$previousExerciseCompleted;
                        }
                        
                        $previousExerciseCompleted = $isCompleted;
                    }

                    return [
                        'id' => $exercise->id,
                        'type' => $exercise->type,
                        'difficulty_level' => $exercise->difficulty_level,
                        'is_locked' => $isLocked,
                        'is_completed' => $isCompleted,
                        'max_attempts' => $exercise->max_attempts,
                        'display_priority' => $exercise->display_priority,
                        'configuration' => $configuration,
                        'video_thumbnail' => $exercise->video_thumbnail,
                    ];
                }),
            ],
        ]);
    }
}
