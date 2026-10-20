<?php

namespace App\Filament\Resources\LearningPlans\Schemas;

use App\Enums\AutismLevelEnum;
use App\Enums\RewardTargetEnum;
use App\Models\LearningGoal;
use App\Models\LearningPlan;
use App\Models\Reward;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class LearningPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                LearningStructureHint::make('plan'),

                Section::make(__('Plan Details'))
                    ->icon('heroicon-o-document-text')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
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
                    ]),

                Section::make(__('Daily Limits'))
                    ->description(__('The most a child can complete in one day. When a limit is reached, the rest is locked until the next day. Leave empty or 0 for no limit.'))
                    ->icon('heroicon-o-clock')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
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
                    ]),

                Section::make(__('Content & Reward'))
                    ->description(__('Pick the goals the child works on in this plan. They are shown in each goal\'s display priority order.'))
                    ->icon('heroicon-o-sparkles')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('goals')
                            ->label(__('Goals'))
                            ->relationship('goals', 'name')
                            ->getOptionLabelFromRecordUsing(fn (LearningGoal $record) => $record->name)
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText(__('Goals can be shared between plans. Create and edit goals from the Learning Goals page.'))
                            ->columnSpanFull(),
                        Select::make('reward_id')
                            ->label(__('Plan Completion Reward'))
                            ->options(fn () => Reward::query()->where('target_type', RewardTargetEnum::PLAN->value)->get()->pluck('name', 'id'))
                            ->searchable()
                            ->placeholder(__('No reward'))
                            ->helperText(__('Given to the child once every goal in the plan is completed. Rewards are managed from the Rewards page.')),
                    ]),
            ]);
    }
}
