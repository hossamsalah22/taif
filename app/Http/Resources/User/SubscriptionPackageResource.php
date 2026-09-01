<?php

namespace App\Http\Resources\User;

use App\Enums\BillingCycleEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPackageResource extends JsonResource
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
            'billing_cycle' => $this->billing_cycle,
            'billing_cycle_label' => BillingCycleEnum::label($this->billing_cycle),
            'price' => $this->price,
            'features' => $this->features,
        ];
    }
}
