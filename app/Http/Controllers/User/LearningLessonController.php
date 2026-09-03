<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\LearningLesson;
use Illuminate\Support\Facades\Storage;

class LearningLessonController extends Controller
{
    public function show(LearningLesson $lesson)
    {
        $lesson->load(['exercises']);

        return $this->successResponse(__('Lesson retrieved successfully'), [
            'lesson' => [
                'id' => $lesson->id,
                'name' => $lesson->name,
                'reward_id' => $lesson->reward_id,
                'is_locked' => $lesson->is_locked,
                'display_priority' => $lesson->display_priority,
                'exercises' => $lesson->exercises->map(function ($exercise) {
                    $configuration = $exercise->configuration;

                    if (is_array($configuration)) {
                        foreach (['options', 'matchingPairs', 'orderingSteps'] as $key) {
                            if (isset($configuration[$key]) && is_array($configuration[$key])) {
                                foreach ($configuration[$key] as &$item) {
                                    foreach (['image', 'audio', 'left_element', 'right_element'] as $fileKey) {
                                        if (! empty($item[$fileKey])) {
                                            $item[$fileKey] = Storage::disk('public')->url($item[$fileKey]);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    return [
                        'id' => $exercise->id,
                        'type' => $exercise->type,
                        'difficulty_level' => $exercise->difficulty_level,
                        'is_locked' => $exercise->is_locked,
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
