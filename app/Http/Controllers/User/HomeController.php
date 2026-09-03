<?php

namespace App\Http\Controllers\User;

use App\Enums\ChildLearningPlanStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChildResource;
use App\Models\ChildLearningPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('user')->user();

        $children = $user->children()->get();

        $activeChildId = $request->header(
            'X-Active-Child-Id',
            $children->first()?->id
        );

        $activeChild = $activeChildId
            ? $children->firstWhere('id', $activeChildId)
            : null;

        $planProgress = null;

        if ($activeChild) {
            $childLearningPlan = ChildLearningPlan::where('child_id', $activeChild->id)
                ->where('status', ChildLearningPlanStatusEnum::InProgress->value)
                ->with('learningPlan.goals.lessons')
                ->oldest()
                ->first();

            if ($childLearningPlan && $childLearningPlan->learningPlan) {
                $plan = $childLearningPlan->learningPlan;
                $completedLessonIds = $activeChild->completedLessons()->pluck('learning_lessons.id')->toArray();

                $completedToday = $activeChild->completedLessons()
                    ->whereDate('child_completed_lessons.created_at', now()->toDateString())
                    ->count();

                $dailyProgress = [
                    'completed' => $completedToday,
                    'total' => $plan->max_daily_lessons ?: 5,
                ];

                $allLessons = collect();
                if ($plan->goals) {
                    foreach ($plan->goals->sortBy('display_priority') as $goal) {
                        if ($goal->lessons) {
                            foreach ($goal->lessons->sortBy('display_priority') as $lesson) {
                                $allLessons->push($lesson);
                            }
                        }
                    }
                }

                $dailyLessonsLimitReached = $plan->max_daily_lessons > 0 && $completedToday >= $plan->max_daily_lessons;

                $pendingLessons = $allLessons->filter(fn ($l) => ! in_array($l->id, $completedLessonIds))->values();
                $nextSession = null;
                $otherSessions = [];

                if ($pendingLessons->isNotEmpty()) {
                    $next = $pendingLessons->first();
                    $nextSession = [
                        'id' => $next->id,
                        'name' => $next->name,
                        'is_locked' => $dailyLessonsLimitReached,
                    ];

                    $otherSessions = $pendingLessons->slice(1, 3)->map(function ($lesson) {
                        return [
                            'id' => $lesson->id,
                            'name' => $lesson->name,
                            'is_locked' => true,
                        ];
                    })->values()->toArray();
                }

                $planProgress = [
                    'id' => $childLearningPlan->id,
                    'status' => $childLearningPlan->status,
                    'daily_progress' => $dailyProgress,
                    'next_session' => $nextSession,
                    'other_sessions' => $otherSessions,
                ];
            }
        }

        $recentNotifications = DB::table('notifications')
            ->where('notifiable_id', $user->id)
            ->where('notifiable_type', $user->getMorphClass())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return $this->successResponse(__('Home data retrieved successfully'), [
            'children' => ChildResource::collection($children),
            'active_child' => $activeChild
                ? ChildResource::make($activeChild)
                : null,

            'current_plan_progress' => $planProgress,

            'recent_notifications' => $recentNotifications,
        ]
        );
    }
}
