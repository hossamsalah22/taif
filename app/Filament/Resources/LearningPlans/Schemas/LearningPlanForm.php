<?php

namespace App\Filament\Resources\LearningPlans\Schemas;

use App\Enums\AutismLevelEnum;
use App\Enums\DifficultyLevel;
use App\Enums\ExerciseTypeEnum;
use App\Enums\PriorityEnum;
use App\Enums\RewardTargetEnum;
use App\Models\LearningGoal;
use App\Models\LearningPlan;
use App\Models\Reward;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
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
                            ->required(),
                        TextInput::make('weekly_sessions_count')
                            ->label(__('Weekly Sessions Count'))
                            ->required()
                            ->numeric()
                            ->default(3),
                        TextInput::make('phase_duration')
                            ->label(__('Phase Duration'))
                            ->required(),
                    ]),

                Section::make(__('Daily Limits'))
                    ->icon('heroicon-o-clock')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('max_daily_goals')
                            ->label(__('Max Daily Goals'))
                            ->numeric(),
                        TextInput::make('max_daily_lessons')
                            ->label(__('Max Daily Lessons'))
                            ->numeric(),
                        TextInput::make('max_daily_exercises')
                            ->label(__('Max Daily Exercises'))
                            ->numeric(),
                    ]),

                Section::make(__('Content & Reward'))
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
                            ->placeholder(__('No reward')),
                    ]),
            ]);
    }
}