<?php

namespace Tests\Feature;

use App\Enums\AutismLevelEnum;
use App\Filament\Resources\LearningGoals\Pages\CreateLearningGoal;
use App\Filament\Resources\LearningGoals\Pages\ListLearningGoals;
use App\Filament\Resources\LearningLessons\Pages\CreateLearningLesson;
use App\Filament\Resources\LearningLessons\Pages\ListLearningLessons;
use App\Filament\Resources\LearningPlans\Pages\CreateLearningPlan;
use App\Filament\Resources\LearningPlans\Pages\ListLearningPlans;
use App\Models\Admin;
use App\Models\LearningGoal;
use App\Models\LearningLesson;
use App\Models\LearningPlan;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LearningContentAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->createSuperAdmin(), 'web');
    }

    public function test_learning_forms_explain_how_content_is_organized(): void
    {
        Livewire::test(CreateLearningPlan::class)
            ->assertOk()
            ->assertSee(__('How learning content is organized'))
            ->assertSee(__('No limit'));

        Livewire::test(CreateLearningGoal::class)
            ->assertOk()
            ->assertSee(__('How learning content is organized'))
            ->assertSee(__('Add lesson'));

        Livewire::test(CreateLearningLesson::class)
            ->assertOk()
            ->assertSee(__('How learning content is organized'));
    }

    public function test_plan_daily_limits_cannot_be_negative(): void
    {
        Livewire::test(CreateLearningPlan::class)
            ->fillForm([
                'name' => 'Plan A',
                'autism_level' => AutismLevelEnum::MILD->value,
                'weekly_sessions_count' => 3,
                'phase_duration' => '3 months',
                'max_daily_lessons' => -1,
            ])
            ->call('create')
            ->assertHasFormErrors(['max_daily_lessons']);
    }

    public function test_plans_list_shows_goal_counts_and_filters_by_severity_level(): void
    {
        $mildPlan = $this->createPlan('Mild plan', AutismLevelEnum::MILD);
        $severePlan = $this->createPlan('Severe plan', AutismLevelEnum::SEVERE);
        $mildPlan->goals()->attach($this->createGoal('Colors'));

        Livewire::test(ListLearningPlans::class)
            ->assertCanSeeTableRecords([$mildPlan, $severePlan])
            ->assertTableColumnStateSet('goals_count', 1, $mildPlan)
            ->assertTableColumnStateSet('goals_count', 0, $severePlan)
            ->filterTable('autism_level', AutismLevelEnum::MILD->value)
            ->assertCanSeeTableRecords([$mildPlan])
            ->assertCanNotSeeTableRecords([$severePlan]);
    }

    public function test_plan_without_goals_cannot_be_assigned_to_children(): void
    {
        $emptyPlan = $this->createPlan('Empty plan', AutismLevelEnum::MILD);
        $readyPlan = $this->createPlan('Ready plan', AutismLevelEnum::MILD);
        $readyPlan->goals()->attach($this->createGoal('Shapes'));

        Livewire::test(ListLearningPlans::class)
            ->assertActionDisabled(TestAction::make('assign_to_children')->table($emptyPlan))
            ->assertActionEnabled(TestAction::make('assign_to_children')->table($readyPlan));
    }

    public function test_goals_list_shows_lesson_counts_and_filters_by_plan(): void
    {
        $plan = $this->createPlan('Plan', AutismLevelEnum::MILD);
        $goalInPlan = $this->createGoal('In plan');
        $otherGoal = $this->createGoal('Other');
        $plan->goals()->attach($goalInPlan);
        $this->createLesson($goalInPlan, 'Lesson 1');

        Livewire::test(ListLearningGoals::class)
            ->assertTableColumnStateSet('lessons_count', 1, $goalInPlan)
            ->assertTableColumnStateSet('lessons_count', 0, $otherGoal)
            ->filterTable('plans', $plan->id)
            ->assertCanSeeTableRecords([$goalInPlan])
            ->assertCanNotSeeTableRecords([$otherGoal]);
    }

    public function test_lessons_list_shows_exercise_counts_and_filters_by_goal(): void
    {
        $lesson = $this->createLesson($this->createGoal('Goal A'), 'Lesson A');
        $otherLesson = $this->createLesson($this->createGoal('Goal B'), 'Lesson B');

        Livewire::test(ListLearningLessons::class)
            ->assertTableColumnStateSet('exercises_count', 0, $lesson)
            ->filterTable('learning_goal_id', $lesson->learning_goal_id)
            ->assertCanSeeTableRecords([$lesson])
            ->assertCanNotSeeTableRecords([$otherLesson]);
    }

    public function test_learning_lists_explain_each_level(): void
    {
        Livewire::test(ListLearningPlans::class)->assertSee(__('No learning plans yet'));
        Livewire::test(ListLearningGoals::class)->assertSee(__('No learning goals yet'));
        Livewire::test(ListLearningLessons::class)->assertSee(__('No lessons yet'));
    }

    private function createPlan(string $name, AutismLevelEnum $level): LearningPlan
    {
        return LearningPlan::create([
            'name' => ['en' => $name, 'ar' => $name],
            'autism_level' => $level,
            'weekly_sessions_count' => 3,
            'phase_duration' => '3 months',
        ]);
    }

    private function createGoal(string $name): LearningGoal
    {
        return LearningGoal::create([
            'name' => ['en' => $name, 'ar' => $name],
            'is_locked' => true,
            'display_priority' => 1,
        ]);
    }

    private function createLesson(LearningGoal $goal, string $name): LearningLesson
    {
        return LearningLesson::create([
            'learning_goal_id' => $goal->id,
            'name' => ['en' => $name, 'ar' => $name],
            'is_locked' => true,
            'display_priority' => 1,
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
