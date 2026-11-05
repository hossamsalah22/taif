<?php

namespace Tests\Feature;

use App\Enums\PaymentStatusEnum;
use App\Enums\SubscriptionStatusEnum;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Paying for a subscription package through a Moyasar hosted invoice.
 */
class MoyasarPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.moyasar.secret_key' => 'sk_test_secret',
            'services.moyasar.base_url' => 'https://api.moyasar.com/v1',
            'services.moyasar.currency' => 'SAR',
            'services.moyasar.invoice_lifetime' => 60,
        ]);
    }

    public function test_subscribing_to_a_paid_package_opens_a_moyasar_invoice_without_activating(): void
    {
        $parent = User::factory()->create();
        $package = SubscriptionPackage::factory()->create(['price' => 115]);

        Http::fake([
            'api.moyasar.com/v1/invoices' => Http::response($this->invoice('inv_1', 'initiated'), 201),
        ]);

        $response = $this->actingAs($parent, 'user')
            ->postJson('/api/user/subscriptions/subscribe', ['package_id' => $package->id])
            ->assertOk()
            ->assertJsonPath('data.requires_payment', true)
            ->assertJsonPath('data.payment.status', PaymentStatusEnum::PENDING->value)
            ->assertJsonPath('data.payment.amount', 115)
            ->assertJsonPath('data.payment.payment_url', 'https://checkout.moyasar.com/invoices/inv_1');

        $payment = Payment::firstOrFail();
        $this->assertSame('inv_1', $payment->gateway_invoice_id);
        $this->assertSame(11500, $payment->amount);
        $this->assertSame($payment->uuid, $response->json('data.payment.id'));
        $this->assertSame(0, Subscription::count());

        Http::assertSent(function (Request $request) use ($payment): bool {
            return $request->url() === 'https://api.moyasar.com/v1/invoices'
                && $request->hasHeader('Authorization', 'Basic '.base64_encode('sk_test_secret:'))
                && $request['amount'] === 11500
                && $request['currency'] === 'SAR'
                && $request['callback_url'] === route('payments.moyasar.callback')
                && $request['success_url'] === route('payments.moyasar.return', $payment)
                && $request['metadata'] === ['payment_uuid' => $payment->uuid];
        });
    }

    public function test_subscribing_to_a_free_package_activates_at_once_without_moyasar(): void
    {
        Http::fake();
        $parent = User::factory()->create();
        $package = SubscriptionPackage::factory()->free()->create();

        $this->actingAs($parent, 'user')
            ->postJson('/api/user/subscriptions/subscribe', ['package_id' => $package->id])
            ->assertOk()
            ->assertJsonPath('data.requires_payment', false)
            ->assertJsonPath('data.subscription.is_free', true);

        $this->assertSame(1, Subscription::where('parent_id', $parent->id)->count());
        $this->assertSame(0, Payment::count());
        Http::assertNothingSent();
    }

    public function test_an_inactive_package_can_not_be_bought(): void
    {
        Http::fake();
        $package = SubscriptionPackage::factory()->inactive()->create();

        $this->actingAs(User::factory()->create(), 'user')
            ->postJson('/api/user/subscriptions/subscribe', ['package_id' => $package->id])
            ->assertNotFound();

        Http::assertNothingSent();
    }

    public function test_a_moyasar_error_marks_the_payment_failed(): void
    {
        $package = SubscriptionPackage::factory()->create();

        Http::fake(['api.moyasar.com/*' => Http::response(['message' => 'Invalid authorization credentials'], 401)]);

        $this->actingAs(User::factory()->create(), 'user')
            ->postJson('/api/user/subscriptions/subscribe', ['package_id' => $package->id])
            ->assertStatus(502)
            ->assertJsonPath('status', false);

        $this->assertSame(PaymentStatusEnum::FAILED, Payment::firstOrFail()->status);
        $this->assertSame(0, Subscription::count());
    }

    public function test_the_callback_activates_the_subscription_after_verifying_the_invoice_with_moyasar(): void
    {
        $payment = Payment::factory()->create(['gateway_invoice_id' => 'inv_1']);

        Http::fake([
            'api.moyasar.com/v1/invoices/inv_1' => Http::response($this->invoice('inv_1', 'paid')),
        ]);

        $this->postJson('/api/payments/moyasar/callback', ['id' => 'inv_1', 'status' => 'paid'])->assertOk();

        $payment->refresh();
        $this->assertSame(PaymentStatusEnum::PAID, $payment->status);
        $this->assertSame('pay_1', $payment->gateway_payment_id);
        $this->assertNotNull($payment->paid_at);

        $subscription = $payment->subscription;
        $this->assertSame($payment->parent_id, $subscription->parent_id);
        $this->assertSame(SubscriptionStatusEnum::ACTIVE, $subscription->status);
        $this->assertEquals(115.0, (float) $subscription->amount_paid);
        $this->assertFalse((bool) $subscription->is_free);
    }

    public function test_a_forged_callback_does_not_activate_anything_while_moyasar_says_unpaid(): void
    {
        $payment = Payment::factory()->create(['gateway_invoice_id' => 'inv_1']);

        Http::fake([
            'api.moyasar.com/v1/invoices/inv_1' => Http::response($this->invoice('inv_1', 'initiated')),
        ]);

        $this->postJson('/api/payments/moyasar/callback', ['id' => 'inv_1', 'status' => 'paid'])->assertOk();

        $this->assertSame(PaymentStatusEnum::PENDING, $payment->refresh()->status);
        $this->assertSame(0, Subscription::count());
    }

    public function test_a_callback_for_an_unknown_invoice_is_rejected(): void
    {
        Http::fake();

        $this->postJson('/api/payments/moyasar/callback', ['id' => 'inv_unknown'])->assertNotFound();

        Http::assertNothingSent();
    }

    public function test_a_paid_invoice_with_a_different_amount_fails_the_payment(): void
    {
        $payment = Payment::factory()->create(['gateway_invoice_id' => 'inv_1']);

        Http::fake([
            'api.moyasar.com/v1/invoices/inv_1' => Http::response($this->invoice('inv_1', 'paid', amount: 100)),
        ]);

        $this->postJson('/api/payments/moyasar/callback', ['id' => 'inv_1'])->assertOk();

        $this->assertSame(PaymentStatusEnum::FAILED, $payment->refresh()->status);
        $this->assertSame(0, Subscription::count());
    }

    public function test_an_expired_invoice_fails_the_payment(): void
    {
        $payment = Payment::factory()->create(['gateway_invoice_id' => 'inv_1']);

        Http::fake([
            'api.moyasar.com/v1/invoices/inv_1' => Http::response($this->invoice('inv_1', 'expired')),
        ]);

        $this->get(route('payments.moyasar.return', $payment))
            ->assertOk()
            ->assertSee('data-payment-status="failed"', false);

        $this->assertSame(PaymentStatusEnum::FAILED, $payment->refresh()->status);
    }

    public function test_confirming_twice_creates_only_one_subscription(): void
    {
        $payment = Payment::factory()->create(['gateway_invoice_id' => 'inv_1']);

        Http::fake([
            'api.moyasar.com/v1/invoices/inv_1' => Http::response($this->invoice('inv_1', 'paid')),
        ]);

        $this->postJson('/api/payments/moyasar/callback', ['id' => 'inv_1'])->assertOk();
        $this->get(route('payments.moyasar.return', $payment))
            ->assertOk()
            ->assertSee('data-payment-status="paid"', false);
        $this->postJson('/api/payments/moyasar/callback', ['id' => 'inv_1'])->assertOk();

        $this->assertSame(1, Subscription::count());
        Http::assertSentCount(1);
    }

    public function test_the_parent_can_check_their_payment_and_get_the_subscription(): void
    {
        $payment = Payment::factory()->create(['gateway_invoice_id' => 'inv_1']);

        Http::fake([
            'api.moyasar.com/v1/invoices/inv_1' => Http::response($this->invoice('inv_1', 'paid')),
        ]);

        $this->actingAs($payment->parent, 'user')
            ->getJson("/api/user/payments/{$payment->uuid}")
            ->assertOk()
            ->assertJsonPath('data.payment.status', PaymentStatusEnum::PAID->value)
            ->assertJsonPath('data.payment.subscription.status', SubscriptionStatusEnum::ACTIVE->value)
            ->assertJsonMissingPath('data.payment.payment_url');
    }

    public function test_a_parent_can_not_see_another_parents_payment(): void
    {
        Http::fake();
        $payment = Payment::factory()->create();

        $this->actingAs(User::factory()->create(), 'user')
            ->getJson("/api/user/payments/{$payment->uuid}")
            ->assertNotFound();

        Http::assertNothingSent();
    }

    /**
     * @return array<string, mixed>
     */
    private function invoice(string $id, string $status, int $amount = 11500): array
    {
        return [
            'id' => $id,
            'status' => $status,
            'amount' => $amount,
            'currency' => 'SAR',
            'description' => 'Subscription',
            'url' => "https://checkout.moyasar.com/invoices/{$id}",
            'payments' => $status === 'paid'
                ? [['id' => 'pay_1', 'status' => 'paid', 'amount' => $amount]]
                : [],
            'metadata' => ['payment_uuid' => (string) Str::uuid()],
        ];
    }
}
