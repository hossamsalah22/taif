<?php

namespace App\Http\Controllers\User;

use App\Enums\ChildLearningPlanStatusEnum;
use App\Enums\SubscriptionStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\ChildLearningPlan;
use App\Models\ExerciseInteractionLog;
use App\Models\Subscription;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;

class LearningPlanController extends Controller
{
    public function showProgressTree(Request $request, Child $child)
    {
        if ($child->parent_id !== auth('sanctum')->id()) {
            return $this->failedResponse(__('Data Not Found'), [], 404);
        }

        $progressTree = ChildLearningPlan::where('child_id', $child->id)
            ->whereIn('status', [ChildLearningPlanStatusEnum::InProgress, ChildLearningPlanStatusEnum::Completed])
            ->with([
                'learningPlan.goals.lessons.exercises',
            ])
            ->first();

        if (! $progressTree) {
            return $this->successResponse(__('No active learning plan found.'), null);
        }

        $user = auth('sanctum')->user();

        $isSubscribed = Subscription::where('parent_id', $user->id)
            ->where('status', SubscriptionStatusEnum::ACTIVE)
            ->where('expiry_date', '>', now())
            ->exists();

        $gracePeriodDays = app(GeneralSettings::class)->plan_grace_period_days;
        $planCreationTimestamp = $progressTree->created_at;
        $gracePeriodEndsAt = $planCreationTimestamp->copy()->addDays($gracePeriodDays);

        $isExpired = now()->greaterThan($gracePeriodEndsAt);
        $isBlocked = ! $isSubscribed && $isExpired;

        $plan = $progressTree->learningPlan;

        $exercisesCompletedToday = $child->completedExercises()->whereDate('child_completed_exercises.created_at', today())->count();
        $lessonsCompletedToday = $child->completedLessons()->whereDate('child_completed_lessons.created_at', today())->count();
        $goalsCompletedToday = $child->completedGoals()->whereDate('child_completed_goals.created_at', today())->count();

        $dailyExercisesLimitReached = $plan && $plan->max_daily_exercises > 0 && $exercisesCompletedToday >= $plan->max_daily_exercises;
        $dailyLessonsLimitReached = $plan && $plan->max_daily_lessons > 0 && $lessonsCompletedToday >= $plan->max_daily_lessons;
        $dailyGoalsLimitReached = $plan && $plan->max_daily_goals > 0 && $goalsCompletedToday >= $plan->max_daily_goals;

        $anyDailyLimitReached = $dailyExercisesLimitReached || $dailyLessonsLimitReached || $dailyGoalsLimitReached;

        $accessStatus = [
            'is_subscribed' => $isSubscribed,
            'is_blocked' => $isBlocked,
            'grace_period_ends_at' => ! $isSubscribed && ! $isExpired ? $gracePeriodEndsAt->toIso8601String() : null,
            'remaining_seconds' => ! $isSubscribed && ! $isExpired ? max(0, $gracePeriodEndsAt->diffInSeconds(now())) : 0,
            'daily_limit_reached' => $anyDailyLimitReached,
            'daily_limits' => [
                'exercises_completed' => $exercisesCompletedToday,
                'exercises_max' => $plan ? $plan->max_daily_exercises : 0,
                'lessons_completed' => $lessonsCompletedToday,
                'lessons_max' => $plan ? $plan->max_daily_lessons : 0,
                'goals_completed' => $goalsCompletedToday,
                'goals_max' => $plan ? $plan->max_daily_goals : 0,
            ],
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

        $interactedExerciseIds = ExerciseInteractionLog::where('child_id', $child->id)
            ->pluck('learning_exercise_id')->unique()->toArray();

        // Transform the tree to inject statuses
        if ($plan && $plan->goals) {
            $previousGoalCompleted = true;

            $plan->goals->transform(function ($goal) use (
                $completedGoalIds, $completedLessonIds, $completedExerciseIds, $interactedExerciseIds,
                $dailyGoalsLimitReached, $dailyLessonsLimitReached, $dailyExercisesLimitReached,
                &$previousGoalCompleted
            ) {
                $goal->is_completed = in_array($goal->id, $completedGoalIds);

                if ($goal->is_completed) {
                    $goal->is_locked = false;
                } elseif ($goal->is_locked) {
                    $goal->is_locked = $dailyGoalsLimitReached || ! $previousGoalCompleted;
                }

                $previousGoalCompleted = $goal->is_completed;

                if ($goal->lessons) {
                    $previousLessonCompleted = true;

                    $goal->lessons->transform(function ($lesson) use (
                        $completedLessonIds, $completedExerciseIds, $interactedExerciseIds,
                        $dailyLessonsLimitReached, $dailyExercisesLimitReached,
                        $goal, &$previousLessonCompleted
                    ) {
                        $lesson->is_completed = in_array($lesson->id, $completedLessonIds);

                        if ($lesson->is_completed) {
                            $lesson->is_locked = false;
                        } elseif ($lesson->is_locked) {
                            $lesson->is_locked = $goal->is_locked || $dailyLessonsLimitReached || ! $previousLessonCompleted;
                        }

                        $previousLessonCompleted = $lesson->is_completed;

                        if ($lesson->exercises) {
                            $previousExerciseCompleted = true;

                            $lesson->exercises->transform(function ($exercise) use (
                                $completedExerciseIds, $interactedExerciseIds,
                                $dailyExercisesLimitReached,
                                $lesson, &$previousExerciseCompleted
                            ) {
                                $exercise->is_completed = in_array($exercise->id, $completedExerciseIds);

                                if ($exercise->is_completed) {
                                    $exercise->is_locked = false;
                                } elseif ($exercise->is_locked) {
                                    $exercise->is_locked = $lesson->is_locked || $dailyExercisesLimitReached || ! $previousExerciseCompleted;
                                }

                                $previousExerciseCompleted = $exercise->is_completed;
                                $exercise->is_in_progress = in_array($exercise->id, $interactedExerciseIds);

                                return $exercise;
                            });

                            $lesson->is_in_progress = $lesson->exercises->where('is_in_progress', true)->count() > 0 || $lesson->exercises->where('is_completed', true)->count() > 0;
                        }

                        return $lesson;
                    });

                    $goal->is_in_progress = $goal->lessons->where('is_in_progress', true)->count() > 0 || $goal->lessons->where('is_completed', true)->count() > 0;
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
