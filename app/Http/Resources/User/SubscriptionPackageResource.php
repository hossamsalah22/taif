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
            'features' => collect($this->features ?? [])
                ->map(fn (mixed $item): array => ['feature' => $this->translateFeature($item['feature'] ?? $item)])
                ->values(),
        ];
    }

    /**
     * Pick the feature text in the current locale, falling back to any language that has it.
     *
     * @param  array<string, string|null>|string|null  $feature
     */
    private function translateFeature(array|string|null $feature): ?string
    {
        if (! is_array($feature)) {
            return $feature;
        }

        return collect([app()->getLocale(), config('app.fallback_locale')])
            ->map(fn (string $locale): ?string => $feature[$locale] ?? null)
            ->merge($feature)
            ->first(fn (mixed $text): bool => filled($text));
    }
}
