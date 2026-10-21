<?php

namespace App\Models;

use App\Enums\SubscriptionStatusEnum;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Guarded(['id'])]
class Subscription extends Model
{
    protected $casts = [
        'status' => SubscriptionStatusEnum::class,
    ];

    public function subscriptionPackage(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPackage::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    /**
     * Active subscriptions that give access right now.
     */
    public function scopeRunningNow(Builder $query): Builder
    {
        return $query->where('status', SubscriptionStatusEnum::ACTIVE)
            ->where('start_date', '<=', now())
            ->where('expiry_date', '>=', now());
    }

    /**
     * Subscriptions running now whose access ends within the given number of days.
     */
    public function scopeExpiringWithin(Builder $query, int $days): Builder
    {
        return $query->runningNow()->where('expiry_date', '<=', now()->addDays($days));
    }
}
