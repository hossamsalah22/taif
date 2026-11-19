<?php

namespace Tests\Feature;

use App\Enums\AutismLevelEnum;
use App\Enums\DifficultyLevel;
use App\Enums\ExerciseTypeEnum;
use App\Enums\RewardTargetEnum;
use App\Enums\RewardTypeEnum;
use App\Filament\Resources\LearningGoals\LearningGoalResource;
use App\Filament\Resources\LearningGoals\Pages\CreateLearningGoal;
use App\Filament\Resources\LearningGoals\Pages\EditLearningGoal;
use App\Filament\Resources\LearningGoals\Pages\ListLearningGoals;
use App\Filament\Resources\LearningGoals\Schemas\LearningGoalForm;
use App\Filament\Resources\LearningLessons\LearningLessonResource;
use App\Filament\Resources\LearningLessons\Pages\CreateLearningLesson;
use App\Filament\Resources\LearningLessons\Pages\EditLearningLesson;
use App\Filament\Resources\LearningLessons\Pages\ListLearningLessons;
use App\Filament\Resources\LearningPlans\LearningPlanResource;
use App\Filament\Resources\LearningPlans\Pages\CreateLearningPlan;
use App\Filament\Resources\LearningPlans\Pages\EditLearningPlan;
use App\Filament\Resources\LearningPlans\Pages\ListLearningPlans;
use App\Filament\Resources\Rewards\Pages\CreateReward;
use App\Filament\Resources\Rewards\RewardResource;
use App\Filament\Resources\Rewards\Schemas\RewardForm;
use App\Models\Admin;
use App\Models\LearningExercise;
use App\Models\LearningGoal;
use App\Models\LearningLesson;
use App\Models\LearningPlan;
use App\Models\Reward;
use Filament\Actions\Testing\TestAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
            ->assertSee(__('How learning content is organized'))
            ->assertSee(__('Add exercise'));
    }

    public function test_sidebar_shows_learning_content_as_one_entry(): void
    {
        $this->assertSame(__('Learning content'), LearningPlanResource::getNavigationLabel());

        foreach ([LearningGoalResource::class, LearningLessonResource::class, RewardResource::class] as $resource) {
            $this->assertFalse($resource::shouldRegisterNavigation());
        }
    }

    public function test_learning_content_lists_share_one_tab_bar(): void
    {
        $tabs = [
            LearningPlanResource::getUrl('index'),
            LearningGoalResource::getUrl('index'),
            LearningLessonResource::getUrl('index'),
            RewardResource::getUrl('index'),
        ];

        foreach ($tabs as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee(__('Learning content'))
                ->assertSeeInOrder($tabs, false);
        }
    }

    public function test_learning_content_tab_bar_marks_the_open_list_and_counts_records(): void
    {
        $this->createGoal('Colors');
        $this->createGoal('Shapes');

        Livewire::test(ListLearningGoals::class)
            ->assertOk()
            ->assertSeeHtml('fi-tabs-item fi-active')
            ->assertSee(LearningGoalResource::getPluralModelLabel());

        $tabs = Livewire::test(ListLearningGoals::class)->instance();
        $goalsTab = collect((fn () => $this->learningContentTabs())->call($tabs))->firstWhere('active', true);

        $this->assertSame(LearningGoalResource::getUrl('index'), $goalsTab['url']);
        $this->assertSame(2, $goalsTab['count']);
    }

    public function test_sidebar_entry_stays_active_inside_every_content_tab(): void
    {
        $this->get(RewardResource::getUrl('index'))
            ->assertOk()
            ->assertSee(LearningPlanResource::getNavigationLabel());

        $this->assertContains(RewardResource::getRouteBaseName().'.*', LearningPlanResource::getNavigationItemActiveRoutePattern());
        $this->assertContains(LearningLessonResource::getRouteBaseName().'.*', LearningPlanResource::getNavigationItemActiveRoutePattern());
    }

    public function test_goal_form_edits_lessons_without_exercises(): void
    {
        Livewire::test(CreateLearningGoal::class)
            ->assertSee(__('Add lesson'))
            ->assertDontSee(__('Add exercise'));
    }

    public function test_goal_form_saves_lessons_in_the_dragged_order(): void
    {
        $undoRepeaterFake = Repeater::fake();

        Livewire::test(CreateLearningGoal::class)
            ->fillForm([
                'name' => ['en' => 'Colors', 'ar' => 'Colors'],
                'display_priority' => 1,
                'lessons' => [
                    ['name' => ['en' => 'Red', 'ar' => 'Red'], 'is_locked' => true],
                    ['name' => ['en' => 'Blue', 'ar' => 'Blue'], 'is_locked' => true],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $undoRepeaterFake();

        $this->assertSame(
            ['Red' => 1, 'Blue' => 2],
            LearningGoal::firstOrFail()->lessons()->get()
                ->mapWithKeys(fn (LearningLesson $lesson): array => [$lesson->getTranslation('name', 'en') => $lesson->display_priority])
                ->all(),
        );
    }

    public function test_saved_lessons_in_the_goal_form_link_to_their_exercises(): void
    {
        $goal = $this->createGoal('Shapes');
        $lesson = $this->createLesson($goal, 'Circle');

        Livewire::test(EditLearningGoal::class, ['record' => $goal->id])
            ->assertOk()
            ->assertSee(__('Manage exercises'))
            ->assertSee(LearningLessonResource::getUrl('edit', ['record' => $lesson]), escape: false);
    }

    public function test_lesson_form_creates_the_lesson_with_its_exercises(): void
    {
        Storage::fake('public');
        $goal = $this->createGoal('Daily routine');
        $undoRepeaterFake = Repeater::fake();

        Livewire::test(CreateLearningLesson::class)
            ->fillForm([
                'name' => ['en' => 'Morning', 'ar' => 'Morning'],
                'learning_goal_id' => $goal->id,
                'display_priority' => 1,
                'exercises' => [
                    [
                        'type' => ExerciseTypeEnum::ORDERING->value,
                        'prompt' => ['en' => 'Put the steps in order', 'ar' => 'Put the steps in order'],
                        'difficulty_level' => DifficultyLevel::LOW->value,
                        'max_attempts' => 3,
                        'is_locked' => true,
                        'configuration' => [
                            'orderingSteps' => [
                                ['image' => UploadedFile::fake()->image('wake-up.png')],
                                ['image' => UploadedFile::fake()->image('brush-teeth.png')],
                            ],
                        ],
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $undoRepeaterFake();

        $lesson = LearningLesson::firstOrFail();
        $exercise = $lesson->exercises()->firstOrFail();

        $this->assertSame($goal->id, $lesson->learning_goal_id);
        $this->assertSame(ExerciseTypeEnum::ORDERING, $exercise->type);
        $this->assertSame(1, $exercise->display_priority);
        $this->assertCount(2, $exercise->configuration['orderingSteps']);
    }

    public function test_audio_flashcard_cards_save_their_audio_files(): void
    {
        Storage::fake('public');
        $goal = $this->createGoal('Sounds');
        $undoRepeaterFake = Repeater::fake();

        Livewire::test(CreateLearningLesson::class)
            ->fillForm([
                'name' => ['en' => 'Animals', 'ar' => 'Animals'],
                'learning_goal_id' => $goal->id,
                'display_priority' => 1,
                'exercises' => [
                    [
                        'type' => ExerciseTypeEnum::AUDIO_FLASHCARDS->value,
                        'prompt' => ['en' => 'Which animal says meow?', 'ar' => 'Which animal says meow?'],
                        'difficulty_level' => DifficultyLevel::LOW->value,
                        'max_attempts' => 3,
                        'is_locked' => true,
                        'configuration' => [
                            'options' => [
                                [
                                    'title' => 'Cat',
                                    'is_correct' => true,
                                    'image' => UploadedFile::fake()->image('cat.png'),
                                    'audio' => UploadedFile::fake()->create('cat.mp3', 10, 'audio/mpeg'),
                                ],
                                [
                                    'title' => 'Dog',
                                    'is_correct' => false,
                                    'image' => UploadedFile::fake()->image('dog.png'),
                                    'audio' => UploadedFile::fake()->create('dog.mp3', 10, 'audio/mpeg'),
                                ],
                            ],
                        ],
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $undoRepeaterFake();

        $options = LearningLesson::firstOrFail()->exercises()->firstOrFail()->configuration['options'];

        $this->assertCount(2, $options);
        foreach ($options as $option) {
            $this->assertNotEmpty($option['audio'] ?? null);
            Storage::disk('public')->assertExists($option['audio']);
        }
    }

    public function test_audio_flashcard_cards_need_an_audio_file(): void
    {
        $goal = $this->createGoal('Sounds');
        $undoRepeaterFake = Repeater::fake();

        Livewire::test(CreateLearningLesson::class)
            ->fillForm([
                'name' => ['en' => 'Animals', 'ar' => 'Animals'],
                'learning_goal_id' => $goal->id,
                'display_priority' => 1,
                'exercises' => [
                    [
                        'type' => ExerciseTypeEnum::AUDIO_FLASHCARDS->value,
                        'prompt' => ['en' => 'Which animal says meow?', 'ar' => 'Which animal says meow?'],
                        'difficulty_level' => DifficultyLevel::LOW->value,
                        'configuration' => [
                            'options' => [
                                ['title' => 'Cat', 'is_correct' => true, 'image' => UploadedFile::fake()->image('cat.png')],
                                ['title' => 'Dog', 'is_correct' => false, 'image' => UploadedFile::fake()->image('dog.png')],
                            ],
                        ],
                    ],
                ],
            ])
            ->call('create')
            ->assertHasFormErrors(['exercises.0.configuration.options.0.audio' => 'required']);

        $undoRepeaterFake();

        $this->assertSame(0, LearningLesson::count());
    }

    public function test_ordering_exercise_needs_at_least_two_pictures(): void
    {
        $goal = $this->createGoal('Daily routine');
        $undoRepeaterFake = Repeater::fake();

        Livewire::test(CreateLearningLesson::class)
            ->fillForm([
                'name' => ['en' => 'Morning', 'ar' => 'Morning'],
                'learning_goal_id' => $goal->id,
                'display_priority' => 1,
                'exercises' => [
                    [
                        'type' => ExerciseTypeEnum::ORDERING->value,
                        'prompt' => ['en' => 'Put the steps in order', 'ar' => 'Put the steps in order'],
                        'difficulty_level' => DifficultyLevel::LOW->value,
                        'configuration' => ['orderingSteps' => []],
                    ],
                ],
            ])
            ->call('create')
            ->assertHasFormErrors();

        $undoRepeaterFake();

        $this->assertSame(0, LearningLesson::count());
    }

    public function test_lesson_edit_form_lists_its_exercises(): void
    {
        $lesson = $this->createLesson($this->createGoal('Animals'), 'Cats');
        LearningExercise::create([
            'learning_lesson_id' => $lesson->id,
            'type' => ExerciseTypeEnum::INSTRUCTIONAL_VIDEO,
            'prompt' => ['en' => 'Watch the cat', 'ar' => 'Watch the cat'],
            'difficulty_level' => DifficultyLevel::LOW,
            'is_locked' => true,
            'max_attempts' => 3,
            'display_priority' => 1,
            'configuration' => ['video_url' => 'https://example.com/cat.mp4'],
        ]);

        Livewire::test(EditLearningLesson::class, ['record' => $lesson->id])
            ->assertOk()
            ->assertSee('Watch the cat');
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

    public function test_plan_form_splits_the_plan_into_tabs(): void
    {
        Livewire::test(CreateLearningPlan::class)
            ->assertOk()
            ->assertSeeInOrder([__('Plan Details'), __('Goals'), __('Lessons'), __('Reward'), __('Daily Limits')])
            ->assertSee(__('Optional'))
            ->assertSee(__('Pick goals in the Goals tab to see their lessons here.'));

        Livewire::test(EditLearningPlan::class, ['record' => $this->createPlan('Tabbed plan', AutismLevelEnum::MILD)->id])
            ->assertOk()
            ->assertSeeInOrder([__('Plan Details'), __('Goals'), __('Lessons'), __('Reward'), __('Daily Limits')]);
    }

    public function test_lessons_tab_lists_the_lessons_of_the_picked_goals(): void
    {
        $colors = $this->createGoal('Colors');
        $this->createLesson($colors, 'Red and blue');
        $this->createLesson($colors, 'Green and yellow');
        $this->createLesson($this->createGoal('Shapes'), 'Circles');

        Livewire::test(CreateLearningPlan::class)
            ->fillForm(['goals' => [$colors->id]])
            ->assertSeeInOrder(['Colors', 'Red and blue', 'Green and yellow'])
            ->assertDontSee('Circles');
    }

    public function test_new_goal_with_lessons_can_be_added_from_the_plan_form(): void
    {
        $undoRepeaterFake = Repeater::fake();

        Livewire::test(CreateLearningPlan::class)
            ->fillForm([
                'name' => ['en' => 'Plan with new goal', 'ar' => 'خطة بهدف جديد'],
                'autism_level' => AutismLevelEnum::MILD->value,
                'weekly_sessions_count' => 3,
                'phase_duration' => '3 months',
            ])
            ->callAction(TestAction::make('createOption')->schemaComponent('goals'), data: [
                'name' => ['en' => 'Animals', 'ar' => 'الحيوانات'],
                'display_priority' => 1,
                'lessons' => [
                    ['name' => ['en' => 'Cats', 'ar' => 'القطط'], 'is_locked' => true],
                ],
            ])
            ->assertHasNoFormErrors()
            // The lessons tab now lists the new goal with its lesson.
            ->tap(fn ($component) => $this->assertStringContainsString('1. القطط', $component->html()))
            ->call('create')
            ->assertHasNoFormErrors();

        $undoRepeaterFake();

        $goal = LearningGoal::query()->where('name->en', 'Animals')->firstOrFail();
        $plan = LearningPlan::query()->where('name->en', 'Plan with new goal')->firstOrFail();

        $this->assertTrue($plan->goals()->whereKey($goal->id)->exists());
        $this->assertSame(['Cats'], $goal->lessons()->get()->map(fn (LearningLesson $lesson) => $lesson->getTranslation('name', 'en'))->all());
    }

    public function test_goal_form_splits_the_goal_into_tabs(): void
    {
        $tabs = [__('Goal Details'), __('Lessons'), __('Assessment Questions'), __('Access & Reward')];

        Livewire::test(CreateLearningGoal::class)
            ->assertOk()
            ->assertSeeInOrder($tabs);

        Livewire::test(CreateLearningPlan::class)
            ->mountAction(TestAction::make('createOption')->schemaComponent('goals'))
            ->assertActionMounted(TestAction::make('createOption')->schemaComponent('goals'));

        // The plan's "add a new goal" popup uses the same tabs, without the plans field: the new goal joins this plan.
        $popupTabs = LearningGoalForm::tabs(withPlans: false);

        $this->assertSame($tabs, array_map(fn (Tab $tab): string => $tab->getLabel(), $popupTabs->getDefaultChildComponents()));
        $this->assertFalse(collect($popupTabs->getDefaultChildComponents()[0]->getDefaultChildComponents())
            ->first(fn ($field): bool => $field instanceof Select && $field->getName() === 'plans')
            ->isVisible());
    }

    public function test_lesson_form_splits_the_lesson_into_tabs(): void
    {
        $tabs = [__('Lesson Details'), __('Exercises'), __('Access & Reward')];

        Livewire::test(CreateLearningLesson::class)
            ->assertOk()
            ->assertSeeInOrder($tabs);

        Livewire::test(EditLearningLesson::class, ['record' => $this->createLesson($this->createGoal('Colors'), 'Red')->id])
            ->assertOk()
            ->assertSeeInOrder($tabs);
    }

    public function test_reward_form_splits_the_reward_into_tabs(): void
    {
        $tabs = [__('Reward Details'), __('Media Assets')];

        Livewire::test(CreateReward::class)
            ->assertOk()
            ->assertSeeInOrder($tabs);

        // The plan's "add a new reward" popup uses the same tabs, with the target fixed to "plan".
        $popupTabs = RewardForm::tabs(RewardTargetEnum::PLAN);

        $this->assertSame($tabs, array_map(fn (Tab $tab): string => $tab->getLabel(), $popupTabs->getDefaultChildComponents()));

        $target = collect($popupTabs->getDefaultChildComponents()[0]->getDefaultChildComponents())
            ->first(fn ($field): bool => $field instanceof Select && $field->getName() === 'target_type');

        $this->assertTrue($target->isDisabled());
        $this->assertSame(RewardTargetEnum::PLAN->value, $target->getDefaultState());
    }

    public function test_lessons_popup_splits_choosing_and_adding_into_tabs(): void
    {
        $colors = $this->createGoal('Colors');

        Livewire::test(CreateLearningPlan::class)
            ->fillForm(['goals' => [$colors->id]])
            ->mountAction(TestAction::make('addLessons')->schemaComponent('lessonActions'))
            ->assertActionMounted(TestAction::make('addLessons')->schemaComponent('lessonActions'))
            ->assertSchemaComponentExists('lessonTabs', 'mountedActionSchema0');
    }

    public function test_lessons_can_be_added_or_chosen_for_a_goal_from_the_plan_form(): void
    {
        $undoRepeaterFake = Repeater::fake();

        $colors = $this->createGoal('Colors');
        $this->createLesson($colors, 'Red');
        $shapes = $this->createGoal('Shapes');
        $circles = $this->createLesson($shapes, 'Circles');
        $circles->exercises()->create([
            'type' => ExerciseTypeEnum::INSTRUCTIONAL_VIDEO,
            'prompt' => ['en' => 'Watch circles', 'ar' => 'Watch circles'],
            'difficulty_level' => DifficultyLevel::LOW,
            'is_locked' => true,
            'max_attempts' => 3,
            'display_priority' => 1,
            'configuration' => ['video_url' => 'https://example.com/circles.mp4'],
        ]);

        Livewire::test(CreateLearningPlan::class)
            ->fillForm(['goals' => [$colors->id]])
            ->callAction(TestAction::make('addLessons')->schemaComponent('lessonActions'), data: [
                'learning_goal_id' => $colors->id,
                'existing_lessons' => [$circles->id],
                'new_lessons' => [
                    ['name' => ['en' => 'Blue', 'ar' => 'Blue'], 'is_locked' => true],
                ],
            ])
            ->assertHasNoActionErrors()
            ->assertNotified(__('Lessons added to the goal.'));

        $undoRepeaterFake();

        $this->assertSame(
            ['Red' => 1, 'Circles' => 2, 'Blue' => 3],
            $colors->lessons()->orderBy('display_priority')->get()
                ->mapWithKeys(fn (LearningLesson $lesson): array => [$lesson->getTranslation('name', 'en') => $lesson->display_priority])
                ->all(),
        );

        $copy = $colors->lessons()->where('name->en', 'Circles')->firstOrFail();
        $this->assertNotSame($circles->id, $copy->id);
        $this->assertSame(1, $copy->exercises()->count());
        $this->assertSame($shapes->id, $circles->fresh()->learning_goal_id);
        $this->assertSame(1, $circles->exercises()->count());
    }

    public function test_adding_lessons_needs_a_lesson_to_choose_or_add(): void
    {
        $colors = $this->createGoal('Colors');

        Livewire::test(CreateLearningPlan::class)
            ->fillForm(['goals' => [$colors->id]])
            ->callAction(TestAction::make('addLessons')->schemaComponent('lessonActions'), data: [
                'learning_goal_id' => $colors->id,
            ])
            ->assertNotified(__('Choose a lesson or add a new one.'));

        $this->assertSame(0, $colors->lessons()->count());
    }

    public function test_new_plan_reward_can_be_added_from_the_plan_form(): void
    {
        Storage::fake('public');

        Livewire::test(CreateLearningPlan::class)
            ->fillForm([
                'name' => ['en' => 'Plan with reward', 'ar' => 'خطة بمكافأة'],
                'autism_level' => AutismLevelEnum::MILD->value,
                'weekly_sessions_count' => 3,
                'phase_duration' => '3 months',
            ])
            ->callAction(TestAction::make('createOption')->schemaComponent('reward_id'), data: [
                'name' => ['en' => 'Gold star', 'ar' => 'نجمة ذهبية'],
                'type' => RewardTypeEnum::SOUND->value,
                'sound' => UploadedFile::fake()->create('cheer.mp3', 10, 'audio/mpeg'),
            ])
            ->assertHasNoFormErrors()
            ->call('create')
            ->assertHasNoFormErrors();

        $reward = Reward::query()->where('name->en', 'Gold star')->firstOrFail();

        $this->assertSame(RewardTargetEnum::PLAN, $reward->target_type);
        $this->assertSame($reward->id, LearningPlan::query()->where('name->en', 'Plan with reward')->value('reward_id'));
    }

    public function test_plan_needs_its_details_before_it_is_saved(): void
    {
        Livewire::test(CreateLearningPlan::class)
            ->call('create')
            ->assertHasFormErrors(['autism_level', 'phase_duration']);
    }

    public function test_plan_is_created_from_every_tab_in_one_save(): void
    {
        $goal = $this->createGoal('Colors');

        Livewire::test(CreateLearningPlan::class)
            ->fillForm([
                'name' => ['en' => 'Tabbed plan', 'ar' => 'خطة بتبويبات'],
                'autism_level' => AutismLevelEnum::MILD->value,
                'weekly_sessions_count' => 4,
                'phase_duration' => '3 months',
                'goals' => [$goal->id],
                'max_daily_lessons' => 2,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect(LearningPlanResource::getUrl('index'));

        $plan = LearningPlan::query()->latest('id')->firstOrFail();

        $this->assertSame(4, (int) $plan->weekly_sessions_count);
        $this->assertSame(2, (int) $plan->max_daily_lessons);
        $this->assertTrue($plan->goals()->whereKey($goal->id)->exists());
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
