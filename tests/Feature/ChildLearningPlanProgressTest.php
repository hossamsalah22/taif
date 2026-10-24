<?php

namespace Tests\Feature;

use App\Enums\AutismLevelEnum;
use App\Enums\ChildLearningPlanStatusEnum;
use App\Enums\ExerciseTypeEnum;
use App\Filament\Resources\ChildLearningPlans\Pages\ListChildLearningPlans;
use App\Models\Admin;
use App\Models\Child;
use App\Models\ChildLearningPlan;
use App\Models\LearningExercise;
use App\Models\LearningGoal;
use App\Models\LearningLesson;
use App\Models\LearningPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ChildLearningPlanProgressTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->createSuperAdmin(), 'web');
    }

    public function test_progress_is_zero_when_the_child_completed_no_exercises(): void
    {
        $plan = $this->createPlan();
        $this->createExercises($plan, 4);
        $assignment = $this->assignPlan(Child::factory()->create(), $plan);

        $this->assertSame(0, $assignment->progressPercentage());

        Livewire::test(ListChildLearningPlans::class)
            ->assertTableColumnStateSet('progress', 0, $assignment)
            ->assertSeeHtml('bg-red-600');
    }

    public function test_progress_is_the_share_of_completed_exercises_rounded_down(): void
    {
        $plan = $this->createPlan();
        $exercises = $this->createExercises($plan, 3);
        $child = Child::factory()->create();
        $child->completedExercises()->attach($exercises->first());
        $assignment = $this->assignPlan($child, $plan);

        $this->assertSame(33, $assignment->progressPercentage());

        Livewire::test(ListChildLearningPlans::class)
            ->assertTableColumnStateSet('progress', 33, $assignment)
            ->assertSeeHtml('bg-yellow-400')
            ->assertSeeHtml('width: 33%');
    }

    public function test_progress_is_one_hundred_when_every_exercise_is_completed(): void
    {
        $plan = $this->createPlan();
        $exercises = $this->createExercises($plan, 2);
        $child = Child::factory()->create();
        $child->completedExercises()->attach($exercises->pluck('id'));
        $assignment = $this->assignPlan($child, $plan);

        $this->assertSame(100, $assignment->progressPercentage());

        Livewire::test(ListChildLearningPlans::class)
            ->assertTableColumnStateSet('progress', 100, $assignment)
            ->assertSeeHtml('bg-green-500');
    }

    public function test_progress_is_zero_when_the_plan_has_no_exercises(): void
    {
        $plan = $this->createPlan();
        $this->createLesson($this->createGoal($plan));
        $assignment = $this->assignPlan(Child::factory()->create(), $plan);

        $this->assertSame(0, $assignment->progressPercentage());

        Livewire::test(ListChildLearningPlans::class)
            ->assertTableColumnStateSet('progress', 0, $assignment);
    }

    public function test_progress_ignores_other_children_and_exercises_outside_the_plan(): void
    {
        $plan = $this->createPlan();
        $planExercises = $this->createExercises($plan, 2);
        $otherPlanExercises = $this->createExercises($this->createPlan(), 2);

        $child = Child::factory()->create();
        $child->completedExercises()->attach($otherPlanExercises->pluck('id'));
        $otherChild = Child::factory()->create();
        $otherChild->completedExercises()->attach($planExercises->pluck('id'));

        $assignment = $this->assignPlan($child, $plan);

        $this->assertSame(0, $assignment->progressPercentage());

        Livewire::test(ListChildLearningPlans::class)
            ->assertTableColumnStateSet('progress', 0, $assignment);
    }

    public function test_progress_is_loaded_without_a_query_per_row(): void
    {
        $plan = $this->createPlan();
        $exercises = $this->createExercises($plan, 2);
        $assignments = Collection::times(5, function () use ($plan, $exercises): ChildLearningPlan {
            $child = Child::factory()->create();
            $child->completedExercises()->attach($exercises->first());

            return $this->assignPlan($child, $plan);
        });

        DB::enableQueryLog();

        Livewire::test(ListChildLearningPlans::class)
            ->assertCanSeeTableRecords($assignments)
            ->assertSeeHtml('width: 50%');

        $progressQueries = collect(DB::getQueryLog())
            ->filter(fn (array $query): bool => str_contains($query['query'], 'child_completed_exercises'));

        $this->assertCount(1, $progressQueries);
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

    private function createGoal(LearningPlan $plan): LearningGoal
    {
        $goal = LearningGoal::create([
            'name' => ['en' => 'Eye contact', 'ar' => 'التواصل البصري'],
            'is_locked' => true,
            'display_priority' => 1,
        ]);

        $plan->goals()->attach($goal, ['display_priority' => 1]);

        return $goal;
    }

    private function createLesson(LearningGoal $goal): LearningLesson
    {
        return LearningLesson::create([
            'learning_goal_id' => $goal->id,
            'name' => ['en' => 'Look at me', 'ar' => 'انظر إلي'],
            'is_locked' => true,
            'display_priority' => 1,
        ]);
    }

    /**
     * @return Collection<int, LearningExercise>
     */
    private function createExercises(LearningPlan $plan, int $count): Collection
    {
        $lesson = $this->createLesson($this->createGoal($plan));

        return Collection::times($count, fn (int $priority): LearningExercise => LearningExercise::create([
            'learning_lesson_id' => $lesson->id,
            'type' => ExerciseTypeEnum::INSTRUCTIONAL_VIDEO,
            'display_priority' => $priority,
            'configuration' => ['video_url' => 'https://example.com/video.mp4'],
        ]));
    }

    private function assignPlan(Child $child, LearningPlan $plan): ChildLearningPlan
    {
        return ChildLearningPlan::create([
            'child_id' => $child->id,
            'learning_plan_id' => $plan->id,
            'status' => ChildLearningPlanStatusEnum::InProgress,
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
