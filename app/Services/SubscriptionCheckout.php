<?php

namespace App\Services;

use App\Enums\BillingCycleEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\SubscriptionStatusEnum;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionPackage;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Subscribing to a package. A free package activates at once; a paid one goes through a Moyasar
 * hosted invoice, and the subscription is only created after the invoice is verified as paid
 * against the Moyasar API (callback, return page or the app polling all end in confirm()).
 */
class SubscriptionCheckout
{
    /**
     * Invoice statuses after which the invoice can never be paid.
     *
     * @var array<int, string>
     */
    private const CLOSED_INVOICE_STATUSES = ['failed', 'canceled', 'expired', 'voided'];

    public function __construct(private MoyasarService $moyasar) {}

    public function activateFree(User $parent, SubscriptionPackage $package): Subscription
    {
        return $this->createSubscription($parent, $package, 0.0);
    }

    /**
     * Open a Moyasar invoice for the package and return the pending payment that holds its URL.
     *
     * @throws RequestException
     */
    public function startPayment(User $parent, SubscriptionPackage $package): Payment
    {
        $payment = Payment::create([
            'parent_id' => $parent->id,
            'subscription_package_id' => $package->id,
            'gateway' => 'moyasar',
            'amount' => (int) round((float) $package->price * 100),
            'currency' => (string) config('services.moyasar.currency'),
            'status' => PaymentStatusEnum::PENDING,
        ]);

        try {
            $invoice = $this->moyasar->createInvoice([
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'description' => __('Subscription').': '.$package->getTranslation('name', app()->getLocale(), true),
                'callback_url' => route('payments.moyasar.callback'),
                'success_url' => route('payments.moyasar.return', $payment),
                'back_url' => route('payments.moyasar.return', $payment),
                'expired_at' => now()->addMinutes((int) config('services.moyasar.invoice_lifetime'))->toIso8601String(),
                'metadata' => ['payment_uuid' => $payment->uuid],
            ]);
        } catch (Throwable $exception) {
            $payment->update(['status' => PaymentStatusEnum::FAILED]);
            Log::error('Moyasar invoice creation failed', ['payment' => $payment->uuid, 'error' => $exception->getMessage()]);

            throw $exception;
        }

        $payment->update([
            'gateway_invoice_id' => $invoice['id'],
            'payment_url' => $invoice['url'],
            'gateway_response' => $invoice,
        ]);

        return $payment;
    }

    /**
     * Check the payment's invoice on Moyasar and settle it: activate the subscription when paid,
     * mark it failed when the invoice is closed, leave it pending otherwise. Safe to call repeatedly.
     */
    public function confirm(Payment $payment): Payment
    {
        if (! $payment->isPending() || ! $payment->gateway_invoice_id) {
            return $payment;
        }

        try {
            $invoice = $this->moyasar->fetchInvoice($payment->gateway_invoice_id);
        } catch (Throwable $exception) {
            Log::warning('Moyasar invoice lookup failed', ['payment' => $payment->uuid, 'error' => $exception->getMessage()]);

            return $payment;
        }

        return DB::transaction(function () use ($payment, $invoice): Payment {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if (! $payment->isPending()) {
                return $payment;
            }

            $status = $invoice['status'] ?? null;

            if ($status === 'paid' && $this->matchesPayment($invoice, $payment)) {
                $subscription = $this->createSubscription(
                    $payment->parent,
                    $payment->subscriptionPackage,
                    $payment->amountInMainUnit(),
                );

                $payment->update([
                    'status' => PaymentStatusEnum::PAID,
                    'subscription_id' => $subscription->id,
                    'gateway_payment_id' => $this->paidPaymentId($invoice),
                    'gateway_response' => $invoice,
                    'paid_at' => now(),
                ]);
            } elseif ($status === 'paid' || in_array($status, self::CLOSED_INVOICE_STATUSES, true)) {
                if ($status === 'paid') {
                    Log::critical('Moyasar invoice paid with a mismatched amount', ['payment' => $payment->uuid, 'invoice' => $invoice]);
                }

                $payment->update(['status' => PaymentStatusEnum::FAILED, 'gateway_response' => $invoice]);
            }

            return $payment;
        });
    }

    public static function expiryDateFor(SubscriptionPackage $package, CarbonInterface $from): CarbonInterface
    {
        return match ($package->billing_cycle) {
            BillingCycleEnum::Quarterly => $from->copy()->addMonths(3),
            BillingCycleEnum::SemiAnnually => $from->copy()->addMonths(6),
            BillingCycleEnum::Annually => $from->copy()->addYear(),
            default => $from->copy()->addMonth(),
        };
    }

    private function createSubscription(User $parent, SubscriptionPackage $package, float $amountPaid): Subscription
    {
        $startDate = now();

        return Subscription::create([
            'parent_id' => $parent->id,
            'subscription_package_id' => $package->id,
            'amount_paid' => $amountPaid,
            'status' => SubscriptionStatusEnum::ACTIVE->value,
            'start_date' => $startDate,
            'expiry_date' => self::expiryDateFor($package, $startDate),
            'is_free' => $amountPaid <= 0,
        ]);
    }

    /**
     * @param  array<string, mixed>  $invoice
     */
    private function matchesPayment(array $invoice, Payment $payment): bool
    {
        return (int) ($invoice['amount'] ?? 0) === $payment->amount
            && strtoupper((string) ($invoice['currency'] ?? '')) === strtoupper($payment->currency);
    }

    /**
     * @param  array<string, mixed>  $invoice
     */
    private function paidPaymentId(array $invoice): ?string
    {
        foreach ($invoice['payments'] ?? [] as $attempt) {
            if (in_array($attempt['status'] ?? null, ['paid', 'captured'], true)) {
                return $attempt['id'] ?? null;
            }
        }

        return null;
    }
}
