<?php

namespace Tests\Feature;

use App\Enums\AutismLevelEnum;
use App\Enums\NavigationGroupEnum;
use App\Enums\SupportTicketStatusEnum;
use App\Filament\Pages\Dashboard;
use App\Filament\Resources\AssessmentSubmissions\AssessmentSubmissionResource;
use App\Filament\Resources\ChildLearningPlans\ChildLearningPlanResource;
use App\Filament\Resources\Children\ChildResource;
use App\Filament\Resources\Permissions\PermissionResource;
use App\Filament\Resources\SubscriptionPackages\SubscriptionPackageResource;
use App\Filament\Resources\SupportTickets\SupportTicketResource;
use App\Models\Admin;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Child;
use App\Models\SupportTicket;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sidebar_lists_groups_with_daily_work_first(): void
    {
        $this->actingAs($this->createSuperAdmin(), 'web');

        $this->get(Dashboard::getUrl())
            ->assertOk()
            ->assertSeeInOrder(array_map(
                fn (NavigationGroupEnum $group): string => $group->getLabel(),
                NavigationGroupEnum::cases(),
            ));
    }

    public function test_every_resource_in_the_sidebar_belongs_to_a_group(): void
    {
        $labels = array_map(fn (NavigationGroupEnum $group): string => $group->getLabel(), NavigationGroupEnum::cases());

        foreach (Filament::getPanel('admin')->getResources() as $resource) {
            if (! $resource::shouldRegisterNavigation()) {
                continue;
            }

            $group = $resource::getNavigationGroup();

            $this->assertTrue(
                $group instanceof NavigationGroupEnum || in_array($group, $labels, true),
                "{$resource} is not in a sidebar group.",
            );
        }
    }

    public function test_resources_are_grouped_by_the_work_they_belong_to(): void
    {
        $this->assertSame(NavigationGroupEnum::FAMILIES, ChildResource::getNavigationGroup());
        $this->assertSame(NavigationGroupEnum::FAMILIES, ChildLearningPlanResource::getNavigationGroup());
        $this->assertSame(NavigationGroupEnum::OPERATIONS, SupportTicketResource::getNavigationGroup());
        $this->assertSame(NavigationGroupEnum::SETTINGS->getLabel(), PermissionResource::getNavigationGroup());
    }

    public function test_only_setup_groups_start_collapsed(): void
    {
        $collapsed = collect(NavigationGroupEnum::navigationGroups())
            ->filter(fn ($group): bool => $group->isCollapsed())
            ->keys()
            ->all();

        $this->assertSame(['APP_CONTENT', 'LOCATIONS', 'SETTINGS'], $collapsed);
    }

    public function test_ticket_badge_counts_only_open_tickets(): void
    {
        $this->assertNull(SupportTicketResource::getNavigationBadge());

        SupportTicket::factory()->count(2)->create(['status' => SupportTicketStatusEnum::OPEN]);
        SupportTicket::factory()->create(['status' => SupportTicketStatusEnum::IN_PROGRESS]);
        SupportTicket::factory()->create(['status' => SupportTicketStatusEnum::CLOSED]);

        $this->assertSame('2', SupportTicketResource::getNavigationBadge());
        $this->assertSame('danger', SupportTicketResource::getNavigationBadgeColor());
    }

    public function test_submission_badge_counts_only_submissions_waiting_for_review(): void
    {
        $this->assertNull(AssessmentSubmissionResource::getNavigationBadge());

        $child = Child::factory()->create();
        $assessment = Assessment::create([
            'autism_level' => AutismLevelEnum::MILD->value,
            'title' => ['en' => 'Screening', 'ar' => 'فحص'],
        ]);

        foreach (['pending_review', 'pending_review', 'reviewed_with_report', 'published'] as $status) {
            AssessmentSubmission::create([
                'child_id' => $child->id,
                'assessment_id' => $assessment->id,
                'status' => $status,
            ]);
        }

        $this->assertSame('2', AssessmentSubmissionResource::getNavigationBadge());
    }

    public function test_resources_without_pending_work_show_no_count_badge(): void
    {
        Child::factory()->count(3)->create();

        $this->assertNull(ChildResource::getNavigationBadge());
    }

    public function test_create_pages_do_not_offer_create_another(): void
    {
        $this->actingAs($this->createSuperAdmin(), 'web');

        $this->get(SubscriptionPackageResource::getUrl('create'))
            ->assertOk()
            ->assertDontSee('createAnother', false);
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
