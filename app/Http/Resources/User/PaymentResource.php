<?php

namespace App\Http\Resources\User;

use App\Enums\PaymentStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'package_id' => $this->subscription_package_id,
            'amount' => $this->amountInMainUnit(),
            'currency' => $this->currency,
            'status' => $this->status,
            'status_label' => PaymentStatusEnum::label($this->status),
            'payment_url' => $this->when($this->isPending(), $this->payment_url),
            'return_url' => route('payments.moyasar.return', $this->resource),
            'paid_at' => $this->paid_at,
            'subscription' => $this->whenLoaded('subscription', fn () => $this->subscription ? new SubscriptionResource($this->subscription) : null),
        ];
    }
}
