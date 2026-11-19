<?php

namespace App\Filament\Resources\LearningPlans\Schemas;

use App\Enums\AutismLevelEnum;
use App\Enums\RewardTargetEnum;
use App\Filament\Resources\LearningGoals\Schemas\LearningGoalForm;
use App\Filament\Resources\Rewards\Schemas\RewardForm;
use App\Models\LearningGoal;
use App\Models\LearningLesson;
use App\Models\LearningPlan;
use App\Models\Reward;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class LearningPlanForm
{
    /**
     * The whole plan on one page, split into tabs so only one part is on screen at a time.
     * Used by both the create and edit pages; saving sends every tab together.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                LearningStructureHint::make('plan'),

                Tabs::make('plan')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make(__('Plan Details'))
                            ->icon('heroicon-o-document-text')
                            ->columns(2)
                            ->schema([
                                Text::make(__('Name, severity level and pace'))->columnSpanFull(),
                                ...self::detailsComponents(),
                            ]),
                        Tab::make(__('Goals'))
                            ->icon('heroicon-o-viewfinder-circle')
                            ->schema([
                                Text::make(self::contentDescription()),
                                self::goalsSelect(),
                            ]),
                        Tab::make(__('Lessons'))
                            ->icon('heroicon-o-play-circle')
                            ->schema([
                                Text::make(__('Lessons belong to a goal. Pick goals in the Goals tab, then add new lessons to them or choose existing ones.')),
                                Actions::make([self::addLessonsAction()])->key('lessonActions'),
                                View::make('filament.resources.learning-plans.lessons-overview')
                                    ->viewData(fn (Get $get): array => ['goals' => self::selectedGoalsWithLessons($get('goals'))]),
                            ]),
                        Tab::make(__('Reward'))
                            ->icon('heroicon-o-gift')
                            ->schema([
                                self::rewardSelect(),
                            ]),
                        Tab::make(__('Daily Limits'))
                            ->icon('heroicon-o-clock')
                            ->badge(__('Optional'))
                            ->badgeColor('gray')
                            ->columns(3)
                            ->schema([
                                Text::make(self::dailyLimitsDescription())->columnSpanFull(),
                                ...self::dailyLimitsComponents(),
                            ]),
                    ]),
            ]);
    }

    /**
     * @return array<int, Component>
     */
    private static function detailsComponents(): array
    {
        return [
            TextInput::make('name')
                ->label(__('name'))
                ->rule('required')
                ->rule(function (Get $get, ?LearningPlan $record) {
                    return function (string $attribute, $value, $fail) use ($record) {
                        if (! filled($value)) {
                            return;
                        }

                        $query = LearningPlan::query();
                        if ($record?->getKey()) {
                            $query->whereKeyNot($record->getKey());
                        }

                        $exists = $query
                            ->where(function ($q) use ($value) {
                                $q->where('name->en', $value)
                                    ->orWhere('name->ar', $value);
                            })
                            ->exists();

                        if ($exists) {
                            $fail(__('validation.unique', ['attribute' => __('name')]));
                        }
                    };
                })
                ->translatableTabs()
                ->columnSpanFull(),
            Select::make('autism_level')
                ->label(__('Severity Level'))
                ->options(AutismLevelEnum::options())
                ->disabled(fn (?LearningPlan $record) => $record !== null)
                ->helperText(__('The plan can only be assigned to children with this severity level. It cannot be changed after the plan is created.'))
                ->required(),
            TextInput::make('weekly_sessions_count')
                ->label(__('Weekly Sessions Count'))
                ->required()
                ->numeric()
                ->minValue(1)
                ->maxValue(7)
                ->suffix(__('per week'))
                ->helperText(__('How many learning sessions per week are recommended for the child.'))
                ->default(3),
            TextInput::make('phase_duration')
                ->label(__('Phase Duration'))
                ->placeholder(__('e.g. 3 months'))
                ->helperText(__('How long the plan is expected to take, shown to the parent.'))
                ->required(),
        ];
    }

    /**
     * Pick shared goals, or add a new goal (with its lessons) without leaving the plan.
     */
    private static function goalsSelect(): Select
    {
        return Select::make('goals')
            ->label(__('Goals'))
            ->relationship('goals', 'name')
            ->getOptionLabelFromRecordUsing(fn (LearningGoal $record) => $record->name)
            ->multiple()
            ->searchable()
            ->preload()
            ->live()
            ->createOptionForm([LearningGoalForm::tabs(withPlans: false)])
            ->createOptionAction(fn (Action $action): Action => $action
                ->label(__('Add a new goal'))
                ->modalHeading(__('Add a new goal'))
                ->modalWidth(Width::FiveExtraLarge))
            ->helperText(__('Goals can be shared between plans. Pick existing goals, or press + to add a new goal with its lessons.'));
    }

    /**
     * Adds lessons to one of the plan's goals: new lessons typed here, and/or copies of existing lessons
     * (with their exercises) chosen from other goals. Lessons are saved straight away, like a goal added from the Goals tab.
     */
    private static function addLessonsAction(): Action
    {
        return Action::make('addLessons')
            ->label(__('Add or choose lessons'))
            ->icon(Heroicon::OutlinedPlus)
            ->disabled(fn (Get $schemaGet): bool => blank($schemaGet('goals')))
            ->tooltip(fn (Get $schemaGet): ?string => blank($schemaGet('goals')) ? __('Pick goals in the Goals tab first.') : null)
            ->modalHeading(__('Add or choose lessons'))
            ->modalWidth(Width::ThreeExtraLarge)
            ->modalSubmitActionLabel(__('Add lessons'))
            ->schema(fn (Get $schemaGet): array => [
                Select::make('learning_goal_id')
                    ->label(__('Learning Goal'))
                    ->options(self::selectedGoalsWithLessons($schemaGet('goals'))->mapWithKeys(fn (LearningGoal $goal): array => [$goal->id => $goal->name]))
                    ->default(count($schemaGet('goals') ?? []) === 1 ? head($schemaGet('goals')) : null)
                    ->required()
                    ->live(),
                Tabs::make('lessons')
                    ->key('lessonTabs')
                    ->tabs([
                        Tab::make(__('Choose existing lessons'))
                            ->icon('heroicon-o-document-duplicate')
                            ->schema([
                                Select::make('existing_lessons')
                                    ->hiddenLabel()
                                    ->multiple()
                                    ->searchable()
                                    ->options(fn (Get $get): array => LearningLesson::query()
                                        ->with('goal')
                                        ->when($get('learning_goal_id'), fn (Builder $query, $goalId) => $query->where('learning_goal_id', '!=', $goalId))
                                        ->orderBy('learning_goal_id')
                                        ->orderBy('display_priority')
                                        ->get()
                                        ->mapWithKeys(fn (LearningLesson $lesson): array => [$lesson->id => $lesson->name.' — '.$lesson->goal?->name])
                                        ->all())
                                    ->helperText(__('A copy of each chosen lesson, with its exercises, is added to the goal. The original stays in its own goal.')),
                            ]),
                        Tab::make(__('New lessons'))
                            ->icon('heroicon-o-plus-circle')
                            ->schema([
                                Repeater::make('new_lessons')
                                    ->hiddenLabel()
                                    ->defaultItems(0)
                                    ->addActionLabel(__('Add lesson'))
                                    ->schema([
                                        TextInput::make('name')
                                            ->label(__('Lesson Name'))
                                            ->required()
                                            ->translatableTabs(),
                                        Grid::make(2)->schema([
                                            Select::make('reward_id')
                                                ->label(__('Reward'))
                                                ->options(fn () => Reward::query()->where('target_type', RewardTargetEnum::LESSON->value)->get()->pluck('name', 'id'))
                                                ->searchable()
                                                ->placeholder(__('No reward')),
                                            Toggle::make('is_locked')
                                                ->label(__('Locked'))
                                                ->default(true)
                                                ->inline(false),
                                        ]),
                                    ]),
                            ]),
                    ]),
            ])
            ->action(function (array $data, Action $action): void {
                $existingLessonIds = $data['existing_lessons'] ?? [];
                $newLessons = $data['new_lessons'] ?? [];

                if (blank($existingLessonIds) && blank($newLessons)) {
                    Notification::make()->title(__('Choose a lesson or add a new one.'))->danger()->send();
                    $action->halt();
                }

                $goal = LearningGoal::query()->findOrFail($data['learning_goal_id']);
                $nextPriority = (int) $goal->lessons()->max('display_priority') + 1;

                foreach (LearningLesson::query()->whereKey($existingLessonIds)->orderBy('display_priority')->get() as $lesson) {
                    $lesson->copyToGoal($goal, $nextPriority++);
                }

                foreach ($newLessons as $newLesson) {
                    $goal->lessons()->create([
                        'name' => $newLesson['name'],
                        'reward_id' => $newLesson['reward_id'] ?? null,
                        'is_locked' => $newLesson['is_locked'] ?? true,
                        'display_priority' => $nextPriority++,
                    ]);
                }

                Notification::make()->title(__('Lessons added to the goal.'))->success()->send();
            });
    }

    /**
     * Pick the plan's completion reward, or add a new one without leaving the plan.
     */
    private static function rewardSelect(): Select
    {
        return Select::make('reward_id')
            ->label(__('Plan Completion Reward'))
            ->relationship('reward', 'name', fn (Builder $query) => $query->where('target_type', RewardTargetEnum::PLAN->value))
            ->getOptionLabelFromRecordUsing(fn (Reward $record) => $record->name)
            ->searchable()
            ->preload()
            ->placeholder(__('No reward'))
            ->createOptionForm([RewardForm::tabs(RewardTargetEnum::PLAN)])
            ->createOptionAction(fn (Action $action): Action => $action
                ->label(__('Add a new reward'))
                ->modalHeading(__('Add a new reward'))
                ->modalWidth(Width::ThreeExtraLarge))
            ->helperText(__('Given to the child once every goal in the plan is completed. Pick a reward, or press + to add a new one.'));
    }

    /**
     * The goals picked in the Goals tab with their lessons, in the order the child follows them.
     *
     * @param  array<int, int|string>|null  $goalIds
     * @return Collection<int, LearningGoal>
     */
    private static function selectedGoalsWithLessons(?array $goalIds): Collection
    {
        if (blank($goalIds)) {
            return new Collection;
        }

        return LearningGoal::query()
            ->whereKey($goalIds)
            ->with(['lessons' => fn ($query) => $query->orderBy('display_priority')->orderBy('id')])
            ->orderBy('display_priority')
            ->orderBy('id')
            ->get();
    }

    /**
     * @return array<int, Component>
     */
    private static function dailyLimitsComponents(): array
    {
        return [
            TextInput::make('max_daily_goals')
                ->label(__('Max Daily Goals'))
                ->numeric()
                ->minValue(0)
                ->placeholder(__('No limit'))
                ->suffix(__('goals / day')),
            TextInput::make('max_daily_lessons')
                ->label(__('Max Daily Lessons'))
                ->numeric()
                ->minValue(0)
                ->placeholder(__('No limit'))
                ->suffix(__('lessons / day')),
            TextInput::make('max_daily_exercises')
                ->label(__('Max Daily Exercises'))
                ->numeric()
                ->minValue(0)
                ->placeholder(__('No limit'))
                ->suffix(__('exercises / day')),
        ];
    }

    private static function contentDescription(): string
    {
        return __('Pick the goals the child works on in this plan. They are shown in each goal\'s display priority order.');
    }

    private static function dailyLimitsDescription(): string
    {
        return __('The most a child can complete in one day. When a limit is reached, the rest is locked until the next day. Leave empty or 0 for no limit.');
    }
}
