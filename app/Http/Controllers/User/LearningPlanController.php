<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\ChildLearningPlan;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class LearningPlanController extends Controller
{
    public function showProgressTree(Request $request, Child $child)
    {
        if ($child->parent_id !== auth('sanctum')->id()) {
            return $this->failedResponse(__('Data Not Found'), [], 404);
        }

        $progressTree = ChildLearningPlan::where('child_id', $child->id)
            ->whereIn('status', [\App\Enums\ChildLearningPlanStatusEnum::InProgress, \App\Enums\ChildLearningPlanStatusEnum::Completed])
            ->with([
                'learningPlan.goals.lessons.exercises'
            ])
            ->first();

        if (!$progressTree) {
            return $this->successResponse(__('No active learning plan found.'), null);
        }

        $user = auth('sanctum')->user();
        
        $isSubscribed = \App\Models\Subscription::where('parent_id', $user->id)
            ->where('status', \App\Enums\SubscriptionStatusEnum::ACTIVE)
            ->where('expiry_date', '>', now())
            ->exists();

        $gracePeriodDays = app(\App\Settings\GeneralSettings::class)->plan_grace_period_days;
        $planCreationTimestamp = $progressTree->created_at;
        $gracePeriodEndsAt = $planCreationTimestamp->copy()->addDays($gracePeriodDays);
        
        $isExpired = now()->greaterThan($gracePeriodEndsAt);
        $isBlocked = !$isSubscribed && $isExpired;

        $accessStatus = [
            'is_subscribed' => $isSubscribed,
            'is_blocked' => $isBlocked,
            'grace_period_ends_at' => !$isSubscribed && !$isExpired ? $gracePeriodEndsAt->toIso8601String() : null,
            'remaining_seconds' => !$isSubscribed && !$isExpired ? max(0, $gracePeriodEndsAt->diffInSeconds(now())) : 0,
        ];

        if ($isBlocked) {
            return $this->successResponse(__('Learning plan progress tree retrieved successfully.'), [
                'progress_tree' => null,
                'access_status' => $accessStatus,
            ]);
        }

        // Get completed IDs for quick lookup
        $completedGoalIds = $child->completedGoals()->pluck('learning_goals.id')->toArray();
        $completedLessonIds = $child->completedLessons()->pluck('learning_lessons.id')->toArray();
        $completedExerciseIds = $child->completedExercises()->pluck('learning_exercises.id')->toArray();

        // Transform the tree to inject statuses
        $plan = $progressTree->learningPlan;
        if ($plan && $plan->goals) {
            $plan->goals->transform(function ($goal) use ($completedGoalIds, $completedLessonIds, $completedExerciseIds) {
                $goal->is_completed = in_array($goal->id, $completedGoalIds);
                
                if ($goal->lessons) {
                    $goal->lessons->transform(function ($lesson) use ($completedLessonIds, $completedExerciseIds) {
                        $lesson->is_completed = in_array($lesson->id, $completedLessonIds);
                        
                        if ($lesson->exercises) {
                            $lesson->exercises->transform(function ($exercise) use ($completedExerciseIds) {
                                $exercise->is_completed = in_array($exercise->id, $completedExerciseIds);
                                return $exercise;
                            });
                        }
                        return $lesson;
                    });
                }
                return $goal;
            });
        }

        $unlockedRewards = $child->rewards()->pluck('reward_id')->toArray();

        return $this->successResponse(__('Learning plan progress tree retrieved successfully.'), [
            'progress_tree' => $progressTree,
            'access_status' => $accessStatus,
            'unlocked_rewards' => $unlockedRewards,
        ]);
    }
}
