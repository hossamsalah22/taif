<?php

namespace App\Filament\Resources\LearningPlans\Schemas;

use App\Enums\AutismLevelEnum;
use App\Enums\DifficultyLevel;
use App\Enums\ExerciseTypeEnum;
use App\Enums\PriorityEnum;
use App\Models\LearningPlan;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class LearningPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                    ->translatableTabs(),
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
                TextInput::make('max_daily_goals')
                    ->label(__('Max Daily Goals'))
                    ->numeric(),
                TextInput::make('max_daily_lessons')
                    ->label(__('Max Daily Lessons'))
                    ->numeric(),
                TextInput::make('max_daily_exercises')
                    ->label(__('Max Daily Exercises'))
                    ->numeric(),
                Repeater::make('goals')
                    ->label(__('Goals'))
                    ->relationship('goals')
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Goal Name'))
                            ->required()
                            ->translatableTabs(),
                        Textarea::make('description')
                            ->label(__('Goal Description'))
                            ->translatableTabs(),
                        TextInput::make('acquired_skills')
                            ->label(__('Acquired Skills'))
                            ->translatableTabs(),
                        Toggle::make('is_locked')
                            ->label(__('Locked (Sequential)'))
                            ->default(true),
                        Select::make('display_priority')
                            ->label(__('Display Priority'))
                            ->options(array_combine(range(1, 30), range(1, 30))),
                        Repeater::make('lessons')
                            ->label(__('Lessons'))
                            ->relationship('lessons')
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('Lesson Name'))
                                    ->required()
                                    ->translatableTabs(),
                                Toggle::make('is_locked')
                                    ->label(__('Locked'))
                                    ->default(true),
                                Select::make('display_priority')
                                    ->label(__('Display Priority'))
                                    ->options(array_combine(range(1, 30), range(1, 30))),
                                Repeater::make('exercises')
                                    ->label(__('Exercises'))
                                    ->relationship('exercises')
                                    ->schema([
                                        Select::make('difficulty_level')
                                            ->label(__('Difficulty Level'))
                                            ->options(DifficultyLevel::options())
                                            ->required(),
                                        Toggle::make('is_locked')
                                            ->label(__('Locked'))
                                            ->default(true),
                                        TextInput::make('max_attempts')
                                            ->label(__('Max Attempts Bound'))
                                            ->numeric()
                                            ->default(3)
                                            ->minValue(1)
                                            ->maxValue(10),
                                        Select::make('display_priority')
                                            ->label(__('Display Priority'))
                                            ->options(PriorityEnum::options()),
                                        Select::make('type')
                                            ->label(__('Exercise Type'))
                                            ->options(ExerciseTypeEnum::options())
                                            ->required()
                                            ->live(),
                                        TextInput::make('video_url')
                                            ->label(__('Video URL'))
                                            ->url()
                                            ->visible(fn (Get $get) => $get('type') === ExerciseTypeEnum::INSTRUCTIONAL_VIDEO->value),

                                        \Filament\Forms\Components\SpatieMediaLibraryFileUpload::make('video_thumbnail')
                                            ->disk('public')
                                            ->collection('video_thumbnail')
                                            ->label(__('Video Thumbnail'))
                                            ->image()
                                            ->helperText(__('Recommended size: 1920x1080'))
                                            ->visible(fn (Get $get) => $get('type') === ExerciseTypeEnum::INSTRUCTIONAL_VIDEO->value),
                                    ])
                                    ->columnSpanFull()
                                    ->itemLabel(function (array $state): string {
                                        return match (true) {
                                            empty($state['type']) => __('New Exercise'),
                                            $state['type'] instanceof ExerciseTypeEnum => ExerciseTypeEnum::label($state['type']),
                                            default => ExerciseTypeEnum::from($state['type'])->value,
                                        };
                                    }),
                            ])
                            ->columnSpanFull()
                            ->itemLabel(fn (array $state): ?string => $state['name'][app()->getLocale()] ?? null),
                    ])
                    ->columnSpanFull()
                    ->itemLabel(fn (array $state): ?string => $state['name'][app()->getLocale()] ?? null),
            ]);
    }
}

