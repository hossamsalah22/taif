<?php

namespace Tests\Feature;

use App\Enums\AutismLevelEnum;
use App\Enums\NavigationGroupEnum;
use App\Enums\SupportTicketStatusEnum;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\ManagePages;
use App\Filament\Resources\Admins\AdminResource;
use App\Filament\Resources\Assessments\AssessmentResource;
use App\Filament\Resources\AssessmentSubmissions\AssessmentSubmissionResource;
use App\Filament\Resources\ChildLearningPlans\ChildLearningPlanResource;
use App\Filament\Resources\Children\ChildResource;
use App\Filament\Resources\Cities\CityResource;
use App\Filament\Resources\ClinicalProgressReports\ClinicalProgressReportResource;
use App\Filament\Resources\Countries\CountryResource;
use App\Filament\Resources\Districts\DistrictResource;
use App\Filament\Resources\Faqs\FaqResource;
use App\Filament\Resources\LearningPlans\LearningPlanResource;
use App\Filament\Resources\Permissions\PermissionResource;
use App\Filament\Resources\Regions\RegionResource;
use App\Filament\Resources\Sliders\SliderResource;
use App\Filament\Resources\SubscriptionPackages\SubscriptionPackageResource;
use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Filament\Resources\SupportTickets\SupportTicketResource;
use App\Filament\Resources\Users\UserResource;
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

    public function test_sidebar_lists_the_sections_in_order(): void
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
        $this->assertSame(NavigationGroupEnum::CONTENT, LearningPlanResource::getNavigationGroup());
        $this->assertSame(NavigationGroupEnum::CONTENT, AssessmentResource::getNavigationGroup());
        $this->assertSame(NavigationGroupEnum::STUDENTS, ChildResource::getNavigationGroup());
        $this->assertSame(NavigationGroupEnum::STUDENTS, UserResource::getNavigationGroup());
        $this->assertSame(NavigationGroupEnum::STUDENTS, AssessmentSubmissionResource::getNavigationGroup());
        $this->assertSame(NavigationGroupEnum::STUDENTS, ChildLearningPlanResource::getNavigationGroup());
        $this->assertSame(NavigationGroupEnum::STUDENTS, SupportTicketResource::getNavigationGroup());
        $this->assertSame(NavigationGroupEnum::FINANCE, SubscriptionResource::getNavigationGroup());
        $this->assertSame(NavigationGroupEnum::FINANCE, SubscriptionPackageResource::getNavigationGroup());
        $this->assertSame(NavigationGroupEnum::SETTINGS, AdminResource::getNavigationGroup());
        $this->assertSame(NavigationGroupEnum::SETTINGS, ManagePages::getNavigationGroup());
        $this->assertSame(NavigationGroupEnum::SETTINGS->getLabel(), PermissionResource::getNavigationGroup());
        $this->assertSame(NavigationGroupEnum::REPORTS, ClinicalProgressReportResource::getNavigationGroup());
    }

    public function test_sliders_and_faqs_are_hidden_from_the_sidebar(): void
    {
        $this->actingAs($this->createSuperAdmin(), 'web');

        $this->assertFalse(SliderResource::shouldRegisterNavigation());
        $this->assertFalse(FaqResource::shouldRegisterNavigation());

        $this->get(Dashboard::getUrl())
            ->assertOk()
            ->assertDontSee(SliderResource::getUrl('index'))
            ->assertDontSee(FaqResource::getUrl('index'));

        $this->get(SliderResource::getUrl('index'))->assertOk();
        $this->get(FaqResource::getUrl('index'))->assertOk();
    }

    public function test_location_resources_are_hidden_from_the_sidebar(): void
    {
        $this->actingAs($this->createSuperAdmin(), 'web');

        $dashboard = $this->get(Dashboard::getUrl())->assertOk();

        foreach ([CountryResource::class, RegionResource::class, CityResource::class, DistrictResource::class] as $resource) {
            $this->assertFalse($resource::shouldRegisterNavigation());
            $dashboard->assertDontSee($resource::getUrl('index'));
            $this->get($resource::getUrl('index'))->assertOk();
        }
    }

    public function test_only_settings_start_collapsed(): void
    {
        $collapsed = collect(NavigationGroupEnum::navigationGroups())
            ->filter(fn ($group): bool => $group->isCollapsed())
            ->keys()
            ->all();

        $this->assertSame(['SETTINGS'], $collapsed);
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
