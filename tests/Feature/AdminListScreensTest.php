<?php

namespace Tests\Feature;

use App\Enums\AutismLevelEnum;
use App\Enums\ChildLearningPlanStatusEnum;
use App\Enums\SupportTicketStatusEnum;
use App\Filament\Resources\ChildLearningPlans\Pages\ListChildLearningPlans;
use App\Filament\Resources\Children\Pages\ListChildren;
use App\Filament\Resources\ClinicalProgressReports\Pages\ListClinicalProgressReports;
use App\Filament\Resources\SupportTickets\Pages\ListSupportTickets;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Admin;
use App\Models\Child;
use App\Models\ChildLearningPlan;
use App\Models\ClinicalProgressReport;
use App\Models\LearningPlan;
use App\Models\SupportTicket;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminListScreensTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createSuperAdmin();
        $this->actingAs($this->admin, 'web');
    }

    public function test_children_can_be_found_by_parent_name(): void
    {
        $child = Child::factory()->for(User::factory()->state(['name' => 'Huda Alharbi']), 'parent')->create();
        $other = Child::factory()->create();

        Livewire::test(ListChildren::class)
            ->searchTable('Huda')
            ->assertCanSeeTableRecords([$child])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_children_can_be_filtered_by_level_and_by_missing_learning_plan(): void
    {
        $plan = $this->createPlan();
        $withPlan = Child::factory()->create(['autism_level' => AutismLevelEnum::MILD->value]);
        $withoutPlan = Child::factory()->create(['autism_level' => AutismLevelEnum::MILD->value]);
        $severe = Child::factory()->create(['autism_level' => AutismLevelEnum::SEVERE->value]);
        $this->assignPlan($withPlan, $plan);

        Livewire::test(ListChildren::class)
            ->assertTableColumnStateSet('child_learning_plans_count', 1, $withPlan)
            ->filterTable('has_learning_plan', false)
            ->assertCanSeeTableRecords([$withoutPlan, $severe])
            ->assertCanNotSeeTableRecords([$withPlan])
            ->resetTableFilters()
            ->filterTable('autism_level', AutismLevelEnum::SEVERE->value)
            ->assertCanSeeTableRecords([$severe])
            ->assertCanNotSeeTableRecords([$withPlan, $withoutPlan]);
    }

    public function test_tickets_can_be_filtered_to_unassigned_and_by_several_statuses(): void
    {
        $open = SupportTicket::factory()->create(['status' => SupportTicketStatusEnum::OPEN]);
        $assigned = SupportTicket::factory()->create([
            'status' => SupportTicketStatusEnum::IN_PROGRESS,
            'assigned_admin_id' => $this->admin->id,
        ]);
        $closed = SupportTicket::factory()->create(['status' => SupportTicketStatusEnum::CLOSED]);

        Livewire::test(ListSupportTickets::class, ['activeTab' => 'all'])
            ->assertCanSeeTableRecords([$open, $assigned, $closed])
            ->filterTable('unassigned', true)
            ->assertCanSeeTableRecords([$open, $closed])
            ->assertCanNotSeeTableRecords([$assigned])
            ->resetTableFilters()
            ->filterTable('status', [SupportTicketStatusEnum::OPEN->value, SupportTicketStatusEnum::IN_PROGRESS->value])
            ->assertCanSeeTableRecords([$open, $assigned])
            ->assertCanNotSeeTableRecords([$closed]);
    }

    public function test_tickets_can_be_filtered_by_specialist(): void
    {
        $colleague = Admin::create([
            'name' => 'Colleague',
            'email' => 'colleague@example.com',
            'phone' => '500000003',
            'password' => 'password',
            'is_active' => true,
        ]);
        $mine = SupportTicket::factory()->create(['assigned_admin_id' => $this->admin->id]);
        $theirs = SupportTicket::factory()->create(['assigned_admin_id' => $colleague->id]);

        Livewire::test(ListSupportTickets::class, ['activeTab' => 'all'])
            ->filterTable('assigned_admin_id', $colleague->id)
            ->assertCanSeeTableRecords([$theirs])
            ->assertCanNotSeeTableRecords([$mine]);
    }

    public function test_open_tickets_stand_out_and_closed_ones_do_not(): void
    {
        $this->assertSame('danger', SupportTicketStatusEnum::OPEN->getColor());
        $this->assertSame('gray', SupportTicketStatusEnum::CLOSED->getColor());
    }

    public function test_suspending_a_parent_asks_for_confirmation_and_revokes_app_sessions(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->createToken('mobile');

        Livewire::test(ListUsers::class)
            ->assertActionHidden(TestAction::make('activate')->table($user))
            ->mountAction(TestAction::make('suspend')->table($user))
            ->assertActionMounted(TestAction::make('suspend')->table($user))
            ->callMountedAction()
            ->assertNotified(__('Account Suspended'));

        $this->assertFalse($user->refresh()->is_active);
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_suspended_parent_can_be_activated_again(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        Livewire::test(ListUsers::class)
            ->assertActionHidden(TestAction::make('suspend')->table($user))
            ->callAction(TestAction::make('activate')->table($user))
            ->assertNotified(__('Account Activated'));

        $this->assertTrue($user->refresh()->is_active);
    }

    public function test_assigned_plans_can_be_filtered_by_status_and_child_level(): void
    {
        $plan = $this->createPlan();
        $inProgress = $this->assignPlan(Child::factory()->create(['autism_level' => AutismLevelEnum::MILD->value]), $plan);
        $completed = $this->assignPlan(Child::factory()->create(['autism_level' => AutismLevelEnum::SEVERE->value]), $plan, ChildLearningPlanStatusEnum::Completed);

        Livewire::test(ListChildLearningPlans::class)
            ->filterTable('status', ChildLearningPlanStatusEnum::Completed->value)
            ->assertCanSeeTableRecords([$completed])
            ->assertCanNotSeeTableRecords([$inProgress])
            ->resetTableFilters()
            ->filterTable('autism_level', AutismLevelEnum::MILD->value)
            ->assertCanSeeTableRecords([$inProgress])
            ->assertCanNotSeeTableRecords([$completed]);
    }

    public function test_progress_reports_can_be_filtered_by_visibility_to_parent(): void
    {
        $plan = $this->createPlan();
        $child = Child::factory()->create();
        $shared = $this->createReport($child, $plan, visibleToParent: true);
        $hidden = $this->createReport($child, $plan, visibleToParent: false);

        Livewire::test(ListClinicalProgressReports::class)
            ->filterTable('is_visible_to_parent', false)
            ->assertCanSeeTableRecords([$hidden])
            ->assertCanNotSeeTableRecords([$shared]);
    }

    public function test_progress_reports_can_be_filtered_by_child(): void
    {
        $plan = $this->createPlan();
        $ownReport = $this->createReport($child = Child::factory()->create(), $plan, visibleToParent: true);
        $otherReport = $this->createReport(Child::factory()->create(), $plan, visibleToParent: true);

        Livewire::test(ListClinicalProgressReports::class)
            ->filterTable('child_id', $child->id)
            ->assertCanSeeTableRecords([$ownReport])
            ->assertCanNotSeeTableRecords([$otherReport]);
    }

    private function createPlan(): LearningPlan
    {
        return LearningPlan::create([
            'name' => ['en' => 'Communication basics', 'ar' => 'أساسيات التواصل'],
            'autism_level' => AutismLevelEnum::MILD,
            'weekly_sessions_count' => 3,
            'phase_duration' => '3 months',
        ]);
    }

    private function assignPlan(Child $child, LearningPlan $plan, ChildLearningPlanStatusEnum $status = ChildLearningPlanStatusEnum::InProgress): ChildLearningPlan
    {
        return ChildLearningPlan::create([
            'child_id' => $child->id,
            'learning_plan_id' => $plan->id,
            'status' => $status,
        ]);
    }

    private function createReport(Child $child, LearningPlan $plan, bool $visibleToParent): ClinicalProgressReport
    {
        return ClinicalProgressReport::create([
            'child_id' => $child->id,
            'learning_plan_id' => $plan->id,
            'reportable_type' => LearningPlan::class,
            'reportable_id' => $plan->id,
            'title' => ['en' => 'Monthly report', 'ar' => 'تقرير شهري'],
            'is_visible_to_parent' => $visibleToParent,
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
