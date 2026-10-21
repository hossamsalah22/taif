<?php

namespace Tests\Feature;

use App\Enums\AutismLevelEnum;
use App\Enums\SubscriptionStatusEnum;
use App\Enums\SupportTicketStatusEnum;
use App\Filament\Resources\AssessmentSubmissions\Pages\ListAssessmentSubmissions;
use App\Filament\Resources\Children\Pages\ListChildren;
use App\Filament\Resources\Subscriptions\Pages\ListSubscriptions;
use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Filament\Resources\SupportTickets\Pages\ListSupportTickets;
use App\Models\Admin;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Child;
use App\Models\ChildLearningPlan;
use App\Models\LearningPlan;
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
        $this->assertSame(['index'], array_keys(SubscriptionResource::getPages()));

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
