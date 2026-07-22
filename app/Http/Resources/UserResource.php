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
            'has_children' => (bool) $this->hasChildren,
        ];
    }
}
