<?php

namespace App\Http\Resources\User;

use App\Enums\SubscriptionStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
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
            'package_id' => $this->subscription_package_id,
            'amount_paid' => $this->amount_paid,
            'status' => $this->status,
            'status_label' => SubscriptionStatusEnum::label($this->status),
            'start_date' => $this->start_date,
            'expiry_date' => $this->expiry_date,
            'is_free' => (bool) $this->is_free,
        ];
    }
}
