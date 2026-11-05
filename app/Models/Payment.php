<?php

namespace App\Models;

use App\Enums\PaymentStatusEnum;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One checkout attempt of a paid subscription package. The amount is stored in the smallest
 * currency unit (halalas for SAR), exactly as the gateway expects it.
 */
#[Guarded(['id'])]
class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory, HasUuids;

    protected $casts = [
        'status' => PaymentStatusEnum::class,
        'amount' => 'integer',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
    ];

    /**
     * The public identifier is the uuid, so payment ids can not be guessed from callback URLs.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function subscriptionPackage(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPackage::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function isPending(): bool
    {
        return $this->status === PaymentStatusEnum::PENDING;
    }

    /**
     * The successful attempt on the invoice as Moyasar returned it (card source, fee, ...).
     *
     * @return array<string, mixed>|null
     */
    public function paidAttempt(): ?array
    {
        $attempts = collect($this->gateway_response['payments'] ?? []);

        return $attempts->firstWhere('id', $this->gateway_payment_id)
            ?? $attempts->first(fn (array $attempt): bool => in_array($attempt['status'] ?? null, ['paid', 'captured'], true));
    }

    /**
     * The amount in the main currency unit (e.g. 115.00 SAR for 11500 halalas).
     */
    public function amountInMainUnit(): float
    {
        return round($this->amount / 100, 2);
    }
}
