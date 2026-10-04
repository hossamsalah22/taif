<?php

namespace App\Services;

use App\Models\Child;
use App\Models\ChildReward;
use App\Models\Reward;

class RewardService
{
    /**
     * Grant a reward to a child once.
     * Returns the reward only when it was newly unlocked.
     */
    public function grant(Child $child, ?Reward $reward): ?Reward
    {
        if (! $reward) {
            return null;
        }

        $childReward = ChildReward::firstOrCreate([
            'child_id' => $child->id,
            'reward_id' => $reward->id,
        ]);

        return $childReward->wasRecentlyCreated ? $reward : null;
    }

    /**
     * Format a reward for API "unlocked_rewards" responses.
     */
    public function format(Reward $reward): array
    {
        return [
            'id' => $reward->id,
            'name' => $reward->name,
            'target_type' => $reward->target_type?->value,
            'image' => $reward->media_url,
            'icon' => $reward->icon_url,
        ];
    }
}
