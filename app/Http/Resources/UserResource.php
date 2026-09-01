<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'country_code' => $this->country_code,
            'image' => $this->image,
            'status' => $this->status,
            'is_active' => $this->is_active,
            'is_verified' => $this->is_verified,
            'receive_notifications' => $this->receive_notifications,
            'language'  => $this->locale,
            'apple_id' => $this->apple_id,
            'google_id' => $this->google_id,
            'has_children' => (bool) $this->hasChildren,
            'has_active_subscription' => $this->subscriptions()
                ->where('status', \App\Enums\SubscriptionStatusEnum::ACTIVE->value)
                ->where('expiry_date', '>', now())
                ->exists(),
        ];
    }
}
