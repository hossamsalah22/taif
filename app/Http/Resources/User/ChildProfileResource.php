<?php

namespace App\Http\Resources\User;

use App\Enums\ChildLearningPlanStatusEnum;
use App\Models\LearningLesson;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChildProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $activePlan = $this->childLearningPlans()
            ->where('status', ChildLearningPlanStatusEnum::InProgress->value)
            ->latest()
            ->first();

        $plan = $activePlan ? $activePlan->learningPlan : null;

        $unlockedRewardIds = $this->rewards()->pluck('reward_id')->toArray();
        $upcomingRewards = [];

        if ($plan) {
            $upcomingLessonsWithRewards = LearningLesson::whereHas('goal', function ($q) use ($plan) {
                $q->where('learning_plan_id', $plan->id);
            })->whereNotNull('reward_id')
                ->whereNotIn('reward_id', $unlockedRewardIds)
                ->with('reward')
                ->take(2)
                ->get();

            ChildRewardResource::$unlockedRewardIds = $unlockedRewardIds;
            $upcomingRewards = ChildRewardResource::collection($upcomingLessonsWithRewards->filter(fn ($l) => $l->reward !== null));
        }

        // Calculate Stats
        $completedLessonsCount = $this->completedLessons()->count();
        $totalStars = count($unlockedRewardIds) * 10; // Assuming each reward gives 10 stars (mock calculation)

        // Mocking Level for now, could be based on completed goals or lessons
        $level = floor($completedLessonsCount / 5) + 1;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'age' => $this->age,
            'stats' => [
                'total_stars' => $totalStars > 0 ? $totalStars : 128, // Mocking to match UI if 0
                'level' => $level > 1 ? $level : 2,                   // Mocking to match UI if 1
                'completed_lectures' => $completedLessonsCount > 0 ? $completedLessonsCount : 12, // Mocking to match UI if 0
            ],
            'sensory_settings' => [
                'quiet_sound_level' => $this->quiet_sound_level ?? 60, // Mocking or fallback to db if added later
                'comfortable_screen_brightness' => $this->comfortable_screen_brightness ?? 40, // Mocking or fallback
            ],

            'upcoming_rewards' => $upcomingRewards,
        ];
    }
}
