<?php

namespace App\Models;

use App\Enums\BillingCycleEnum;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

#[Guarded(['id'])]
class SubscriptionPackage extends Model
{
    use HasTranslations;

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
}
