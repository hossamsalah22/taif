<?php

namespace Tests\Feature;

use App\Enums\AutismLevelEnum;
use App\Enums\SubscriptionStatusEnum;
use App\Enums\SupportTicketStatusEnum;
use App\Filament\Resources\AssessmentSubmissions\Pages\ListAssessmentSubmissions;
use App\Filament\Resources\Children\Pages\ListChildren;
use App\Filament\Resources\Subscriptions\Pages\ListSubscriptions;
use App\Filament\Resources\Subscriptions\Pages\SubscriptionInvoice;
use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Filament\Resources\SupportTickets\Pages\ListSupportTickets;
use App\Models\Admin;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Child;
use App\Models\ChildLearningPlan;
use App\Models\LearningPlan;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The read-only subscriptions list and the filtered lists the dashboard's "Needs attention" cards open.
 */
class SubscriptionsAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-20 12:00:00');
        $this->actingAs($this->createSuperAdmin(), 'web');
    }

    public function test_subscriptions_list_is_read_only(): void
    {
        $this->createSubscription(SubscriptionStatusEnum::ACTIVE, '2026-11-20');

        $this->assertFalse(SubscriptionResource::canCreate());
        $this->assertSame(['index', 'view', 'invoice'], array_keys(SubscriptionResource::getPages()));

        $this->get(SubscriptionResource::getUrl('index'))->assertOk();
    }

    public function test_subscriptions_can_be_filtered_to_running_and_ending_soon(): void
    {
        $endingSoon = $this->createSubscription(SubscriptionStatusEnum::ACTIVE, '2026-10-24');
        $runningLonger = $this->createSubscription(SubscriptionStatusEnum::ACTIVE, '2026-12-20');
        $expired = $this->createSubscription(SubscriptionStatusEnum::EXPIRED, '2026-10-01');

        Livewire::test(ListSubscriptions::class)
            ->assertCanSeeTableRecords([$endingSoon, $runningLonger, $expired])
            ->filterTable('running_now', true)
            ->assertCanSeeTableRecords([$endingSoon, $runningLonger])
            ->assertCanNotSeeTableRecords([$expired])
            ->resetTableFilters()
            ->filterTable('ending_soon', true)
            ->assertCanSeeTableRecords([$endingSoon])
            ->assertCanNotSeeTableRecords([$runningLonger, $expired]);
    }

    public function test_paid_subscriptions_link_to_their_invoice_page(): void
    {
        $paid = $this->createSubscription(SubscriptionStatusEnum::ACTIVE, '2026-12-20');
        $free = $this->createSubscription(SubscriptionStatusEnum::ACTIVE, '2026-12-20');
        $payment = Payment::factory()->paid()->create([
            'parent_id' => $paid->parent_id,
            'subscription_id' => $paid->id,
            'payment_url' => 'https://checkout.moyasar.com/invoices/inv_1',
        ]);

        Livewire::test(ListSubscriptions::class)
            ->assertCanSeeTableRecords([$paid, $free])
            ->assertTableColumnStateSet('payment.payment_url', $payment->payment_url, $paid)
            ->assertTableColumnStateNotSet('payment.payment_url', $payment->payment_url, $free)
            ->assertTableActionExists('view');
    }

    public function test_viewing_a_paid_subscription_shows_the_payment_and_card_details(): void
    {
        $subscription = $this->createSubscription(SubscriptionStatusEnum::ACTIVE, '2026-12-20');
        Payment::factory()->paid()->create([
            'parent_id' => $subscription->parent_id,
            'subscription_id' => $subscription->id,
            'gateway_invoice_id' => 'inv_1',
            'gateway_payment_id' => 'pay_1',
            'payment_url' => 'https://checkout.moyasar.com/invoices/inv_1',
            'gateway_response' => [
                'id' => 'inv_1',
                'status' => 'paid',
                'payments' => [[
                    'id' => 'pay_1',
                    'status' => 'paid',
                    'fee' => 2755,
                    'source' => [
                        'name' => 'Card Holder Name',
                        'number' => '4111-11XX-XXXX-1111',
                        'company' => 'visa',
                        'issuer_name' => 'MOYASAR PAY SANDBOX',
                        'message' => 'APPROVED',
                        'reference_number' => '992630968450',
                        'authorization_code' => '004792',
                    ],
                ]],
            ],
        ]);

        $this->get(SubscriptionResource::getUrl('view', ['record' => $subscription]))
            ->assertOk()
            ->assertSee($subscription->parent->name)
            ->assertSee('inv_1')
            ->assertSee('pay_1')
            ->assertSee(SubscriptionResource::getUrl('invoice', ['record' => $subscription]), false)
            ->assertSee('Card Holder Name')
            ->assertSee('4111-11XX-XXXX-1111')
            ->assertSee('992630968450');
    }

    public function test_invoice_page_shows_the_paid_subscription_invoice(): void
    {
        $subscription = $this->createSubscription(SubscriptionStatusEnum::ACTIVE, '2026-12-20');
        $payment = Payment::factory()->paid()->create([
            'parent_id' => $subscription->parent_id,
            'subscription_id' => $subscription->id,
            'amount' => 70000,
            'gateway_invoice_id' => 'inv_1',
            'gateway_payment_id' => 'pay_1',
            'payment_url' => 'https://checkout.moyasar.com/invoices/inv_1',
            'gateway_response' => [
                'payments' => [[
                    'id' => 'pay_1',
                    'status' => 'paid',
                    'source' => [
                        'name' => 'Card Holder Name',
                        'number' => '4111-11XX-XXXX-1111',
                        'company' => 'visa',
                    ],
                ]],
            ],
        ]);

        $this->get(SubscriptionResource::getUrl('invoice', ['record' => $subscription]))
            ->assertOk()
            ->assertSee(SubscriptionInvoice::invoiceNumber($payment))
            ->assertSee($subscription->parent->name)
            ->assertSee($payment->subscriptionPackage->name)
            ->assertSee('700')
            ->assertSee('inv_1')
            ->assertSee('pay_1')
            ->assertSee('VISA')
            ->assertSee('4111-11XX-XXXX-1111')
            ->assertSee('Card Holder Name')
            ->assertDontSee('https://checkout.moyasar.com/invoices/inv_1', false);
    }

    public function test_invoice_page_offers_the_payment_link_only_while_unpaid(): void
    {
        $subscription = $this->createSubscription(SubscriptionStatusEnum::ACTIVE, '2026-12-20');
        Payment::factory()->create([
            'parent_id' => $subscription->parent_id,
            'subscription_id' => $subscription->id,
            'payment_url' => 'https://checkout.moyasar.com/invoices/inv_pending',
        ]);

        $this->get(SubscriptionResource::getUrl('invoice', ['record' => $subscription]))
            ->assertOk()
            ->assertSee('https://checkout.moyasar.com/invoices/inv_pending', false);
    }

    public function test_invoice_page_is_not_found_for_a_free_subscription(): void
    {
        $subscription = $this->createSubscription(SubscriptionStatusEnum::ACTIVE, '2026-12-20');
        $subscription->update(['is_free' => true, 'amount_paid' => 0]);

        $this->get(SubscriptionResource::getUrl('invoice', ['record' => $subscription]))
            ->assertNotFound();
    }

    public function test_viewing_a_free_subscription_hides_the_payment_sections(): void
    {
        $subscription = $this->createSubscription(SubscriptionStatusEnum::ACTIVE, '2026-12-20');
        $subscription->update(['is_free' => true, 'amount_paid' => 0]);

        $this->get(SubscriptionResource::getUrl('view', ['record' => $subscription]))
            ->assertOk()
            ->assertSee($subscription->parent->name)
            ->assertDontSee(__('Transaction ID'))
            ->assertDontSee(__('Card Number'));
    }

    public function test_subscriptions_can_be_filtered_by_status_and_free_access(): void
    {
        $paid = $this->createSubscription(SubscriptionStatusEnum::ACTIVE, '2026-12-20');
        $free = $this->createSubscription(SubscriptionStatusEnum::ACTIVE, '2026-12-20');
        $free->update(['is_free' => true, 'amount_paid' => 0]);
        $cancelled = $this->createSubscription(SubscriptionStatusEnum::CANCELLED, '2026-12-20');

        Livewire::test(ListSubscriptions::class)
            ->filterTable('status', SubscriptionStatusEnum::CANCELLED->value)
            ->assertCanSeeTableRecords([$cancelled])
            ->assertCanNotSeeTableRecords([$paid, $free])
            ->resetTableFilters()
            ->filterTable('is_free', true)
            ->assertCanSeeTableRecords([$free])
            ->assertCanNotSeeTableRecords([$paid, $cancelled]);
    }

    public function test_ending_soon_link_opens_the_list_already_filtered(): void
    {
        $endingSoon = $this->createSubscription(SubscriptionStatusEnum::ACTIVE, '2026-10-24');
        $runningLonger = $this->createSubscription(SubscriptionStatusEnum::ACTIVE, '2026-12-20');

        Livewire::withQueryParams(['filters' => ['ending_soon' => ['isActive' => '1']]])
            ->test(ListSubscriptions::class)
            ->assertCanSeeTableRecords([$endingSoon])
            ->assertCanNotSeeTableRecords([$runningLonger]);
    }

    public function test_children_without_plan_link_opens_the_list_already_filtered(): void
    {
        $plan = LearningPlan::create([
            'name' => ['en' => 'Basics', 'ar' => 'أساسيات'],
            'autism_level' => AutismLevelEnum::MILD,
            'weekly_sessions_count' => 3,
            'phase_duration' => '3 months',
        ]);
        $withPlan = Child::factory()->create();
        $withoutPlan = Child::factory()->create();
        ChildLearningPlan::create(['child_id' => $withPlan->id, 'learning_plan_id' => $plan->id, 'status' => 'in_progress']);

        Livewire::withQueryParams(['filters' => ['has_learning_plan' => ['value' => '0']]])
            ->test(ListChildren::class)
            ->assertCanSeeTableRecords([$withoutPlan])
            ->assertCanNotSeeTableRecords([$withPlan]);
    }

    public function test_review_and_ticket_links_open_the_lists_already_filtered(): void
    {
        $child = Child::factory()->create();
        $assessment = Assessment::create(['autism_level' => AutismLevelEnum::MILD->value, 'title' => ['en' => 'Screening', 'ar' => 'فحص']]);
        $waiting = AssessmentSubmission::create(['child_id' => $child->id, 'assessment_id' => $assessment->id, 'status' => 'pending_review']);
        $published = AssessmentSubmission::create(['child_id' => $child->id, 'assessment_id' => $assessment->id, 'status' => 'published']);

        Livewire::withQueryParams(['tab' => 'waiting'])
            ->test(ListAssessmentSubmissions::class)
            ->assertCanSeeTableRecords([$waiting])
            ->assertCanNotSeeTableRecords([$published]);

        $open = SupportTicket::factory()->create(['status' => SupportTicketStatusEnum::OPEN]);
        $closed = SupportTicket::factory()->create(['status' => SupportTicketStatusEnum::CLOSED]);

        Livewire::withQueryParams(['tab' => 'open'])
            ->test(ListSupportTickets::class)
            ->assertCanSeeTableRecords([$open])
            ->assertCanNotSeeTableRecords([$closed]);
    }

    private function createSubscription(SubscriptionStatusEnum $status, string $expiresAt): Subscription
    {
        return Subscription::create([
            'parent_id' => User::factory()->create()->id,
            'amount_paid' => 115,
            'status' => $status,
            'start_date' => '2026-09-01',
            'expiry_date' => $expiresAt,
        ]);
    }

    private function createSuperAdmin(): Admin
    {
        $admin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'phone' => '500000001',
            'password' => 'password',
            'is_active' => true,
        ]);

        $admin->assignRole(Role::findOrCreate('Super Admin', 'web'));

        return $admin;
    }
}
