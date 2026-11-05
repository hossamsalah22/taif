<?php

namespace App\Models;

use App\Enums\BillingCycleEnum;
use App\Enums\SubscriptionStatusEnum;
use Database\Factories\SubscriptionPackageFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

#[Guarded(['id'])]
class SubscriptionPackage extends Model
{
    /** @use HasFactory<SubscriptionPackageFactory> */
    use HasFactory, HasTranslations;

    protected $translatable = [
        'name',
    ];

    protected $casts = [
        'billing_cycle' => BillingCycleEnum::class,
        'price' => 'decimal:2',
        'features' => 'array',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * A package that parents are subscribed to right now must not be deleted.
     */
    public function hasActiveSubscribers(): bool
    {
        return $this->subscriptions()->where('status', SubscriptionStatusEnum::ACTIVE)->exists();
    }
}
