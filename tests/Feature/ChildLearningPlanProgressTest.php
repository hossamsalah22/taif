<?php

namespace Tests\Feature;

use App\Enums\AutismLevelEnum;
use App\Enums\ChildLearningPlanStatusEnum;
use App\Enums\ExerciseTypeEnum;
use App\Exports\ChildPlanInteractionLogsExport;
use App\Filament\Resources\ChildLearningPlans\Pages\ListChildLearningPlans;
use App\Filament\Resources\ChildLearningPlans\Pages\ViewChildLearningPlanProgress;
use App\Livewire\LearningPlanExecutionMap;
use App\Models\Admin;
use App\Models\Child;
use App\Models\ChildLearningPlan;
use App\Models\ExerciseInteractionLog;
use App\Models\LearningExercise;
use App\Models\LearningGoal;
use App\Models\LearningLesson;
use App\Models\LearningPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
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

    public function test_export_logs_downloads_only_this_childs_attempts_on_this_plan(): void
    {
        Excel::fake();
        Carbon::setTestNow('2026-10-20 09:30:00');
        app()->setLocale('ar');

        $plan = $this->createPlan();
        [$first, $second] = $this->createExercises($plan, 2)->all();
        $otherPlanExercise = $this->createExercises($this->createPlan(), 1)->first();
        $child = Child::factory()->create();
        $assignment = $this->assignPlan($child, $plan);

        $success = $this->logAttempt($child, $first, ['is_successful' => true, 'trials_count' => 2, 'duration_seconds' => 40, 'interaction_type' => 'tap']);
        $this->logAttempt($child, $second, ['is_successful' => false]);
        $this->logAttempt($child, $otherPlanExercise);
        $this->logAttempt(Child::factory()->create(), $first);

        Livewire::test(ViewChildLearningPlanProgress::class, ['record' => $assignment->id])
            ->callAction('export_logs')
            ->assertNotNotified(__('No logs to export yet.'));

        Excel::assertDownloaded("interaction-logs_{$assignment->id}_2026-10-20.xlsx", function (ChildPlanInteractionLogsExport $export) use ($success): bool {
            $logs = $export->query()->get();

            $this->assertCount(2, $logs);
            $this->assertSame([__('Date'), 'مرحلة', 'درس', 'تدريب', 'نوع التفاعل', 'النتيجة', 'المحاولات', 'المدة (بالثواني)'], $export->headings());
            $this->assertSame(
                ['2026-10-20 09:30', 'التواصل البصري', 'انظر إلي', null, 'tap', 'ناجحة', 2, 40],
                $export->map($logs->firstWhere('id', $success->id)),
            );
            $this->assertContains('غير ناجحة', $export->map($logs->firstWhere('id', '!=', $success->id)));

            return true;
        });
    }

    public function test_export_logs_explains_when_there_is_nothing_to_export(): void
    {
        Excel::fake();
        $plan = $this->createPlan();
        $this->createExercises($plan, 1);
        $assignment = $this->assignPlan(Child::factory()->create(), $plan);

        Livewire::test(ViewChildLearningPlanProgress::class, ['record' => $assignment->id])
            ->callAction('export_logs')
            ->assertNotified(__('No logs to export yet.'))
            ->assertNoFileDownloaded();
    }

    public function test_execution_map_marks_completed_tried_and_untouched_exercises(): void
    {
        $plan = $this->createPlan();
        [$completed, $tried, $untouched] = $this->createExercises($plan, 3)->all();
        $child = Child::factory()->create();
        $child->completedExercises()->attach($completed);
        $this->logAttempt($child, $completed, ['is_successful' => true]);
        $this->logAttempt($child, $tried);
        $assignment = $this->assignPlan($child, $plan);

        $map = Livewire::test(LearningPlanExecutionMap::class, ['record' => $assignment])->instance();

        $this->assertSame('completed', $map->getExerciseStatus($completed->id));
        $this->assertSame('in_progress', $map->getExerciseStatus($tried->id));
        $this->assertSame('locked', $map->getExerciseStatus($untouched->id));

        $lesson = $completed->lesson()->with('exercises')->first();
        $this->assertSame('in_progress', $map->getLessonStatus($lesson));

        $child->completedExercises()->attach([$tried->id, $untouched->id]);
        $map = Livewire::test(LearningPlanExecutionMap::class, ['record' => $assignment->fresh()])->instance();

        $this->assertSame('completed', $map->getLessonStatus($lesson));
        $this->assertSame('completed', $map->getGoalStatus($lesson->goal()->with('lessons.exercises')->first()));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function logAttempt(Child $child, LearningExercise $exercise, array $attributes = []): ExerciseInteractionLog
    {
        return ExerciseInteractionLog::create([
            'child_id' => $child->id,
            'learning_exercise_id' => $exercise->id,
            ...$attributes,
        ]);
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
