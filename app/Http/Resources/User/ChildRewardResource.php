<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChildRewardResource extends JsonResource
{
    public static $unlockedRewardIds = [];

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->reward->id,
            'name' => $this->reward->name,
            'image' => $this->reward->media_url,
            'icon' => $this->reward->icon_url,
            'lesson_name' => $this->name,
            'is_unlocked' => in_array($this->reward->id, self::$unlockedRewardIds),
        ];
    }
}
