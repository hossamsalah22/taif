<?php

namespace App\Filament\Resources\LearningGoals\Schemas;

use App\Enums\DifficultyLevel;
use App\Enums\ExerciseTypeEnum;
use App\Enums\PriorityEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;

/**
 * Shared lessons -> exercises editor, used by the learning goal form.
 */
class LessonsRepeater
{
    public static function make(): Repeater
    {
        return Repeater::make('lessons')
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
                Select::make('reward_id')
                    ->label(__('Reward'))
                    ->options(fn () => \App\Models\Reward::query()->where('target_type', \App\Enums\RewardTargetEnum::LESSON->value)->get()->pluck('name', 'id'))
                    ->searchable()
                    ->placeholder(__('No reward')),
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
                        TextInput::make('prompt')
                            ->label(__('Instructional Prompt'))
                            ->required()
                            ->translatableTabs()
                            ->columnSpanFull(),
                        Select::make('type')
                            ->label(__('Exercise Type'))
                            ->options(ExerciseTypeEnum::options())
                            ->required()
                            ->live(),
                        Group::make()->statePath('configuration')->schema([
                            Repeater::make('matchingPairs')
                                ->label(__('Matching Matrix Rows Manager'))
                                ->schema([
                                    FileUpload::make('left_element')
                                        ->disk('public')
                                        ->directory('exercises/matching')
                                        ->label(__('Left element'))
                                        ->image()
                                        ->helperText(__('Recommended size: 500x500'))
                                        ->maxSize(5120)
                                        ->required(),
                                    FileUpload::make('right_element')
                                        ->disk('public')
                                        ->directory('exercises/matching')
                                        ->label(__('Right element'))
                                        ->image()
                                        ->helperText(__('Recommended size: 500x500'))
                                        ->maxSize(5120)
                                        ->required(),
                                ])
                                ->columns(2)
                                ->minItems(3)
                                ->visible(fn (Get $get) => $get('../type') === ExerciseTypeEnum::MATCHING->value)
                                ->required(fn (Get $get) => $get('../type') === ExerciseTypeEnum::MATCHING->value),

                            Repeater::make('orderingSteps')
                                ->label(__('Chronological Sequence Slider'))
                                ->schema([
                                    FileUpload::make('image')
                                        ->disk('public')
                                        ->directory('exercises/ordering')
                                        ->label(__('Image'))
                                        ->image()
                                        ->helperText(__('Recommended size: 500x500'))
                                        ->maxSize(5120)
                                        ->required(),
                                ])
                                ->minItems(2)
                                ->visible(fn (Get $get) => $get('../type') === ExerciseTypeEnum::ORDERING->value)
                                ->required(fn (Get $get) => $get('../type') === ExerciseTypeEnum::ORDERING->value),

                            Repeater::make('options')
                                ->label(__('Options'))
                                ->schema([
                                    TextInput::make('title')
                                        ->label(__('Card Title'))
                                        ->required(),
                                    FileUpload::make('image')
                                        ->disk('public')
                                        ->directory('exercises/options')
                                        ->label(__('Card Image'))
                                        ->image()
                                        ->helperText(__('Recommended size: 500x500'))
                                        ->maxSize(5120)
                                        ->required(),
                                    FileUpload::make('audio')
                                        ->disk('public')
                                        ->directory('exercises/audio')
                                        ->label(__('Card Audio File'))
                                        ->acceptedFileTypes(['audio/mpeg', 'audio/wav', 'audio/mp3', 'audio/m4a'])
                                        ->maxSize(5120)
                                        ->visible(fn (Get $get) => $get('../../type') === ExerciseTypeEnum::AUDIO_FLASHCARDS->value)
                                        ->required(fn (Get $get) => $get('../../type') === ExerciseTypeEnum::AUDIO_FLASHCARDS->value),
                                    Toggle::make('is_correct')
                                        ->label(__('Is Correct Answer'))
                                        ->default(false),
                                ])
                                ->minItems(2)
                                ->columns(2)
                                ->visible(fn (Get $get) => in_array($get('../type'), [ExerciseTypeEnum::AUDIO_FLASHCARDS->value, ExerciseTypeEnum::IMAGE_SELECTION->value, ExerciseTypeEnum::DISTINGUISHING->value]))
                                ->required(fn (Get $get) => in_array($get('../type'), [ExerciseTypeEnum::AUDIO_FLASHCARDS->value, ExerciseTypeEnum::IMAGE_SELECTION->value, ExerciseTypeEnum::DISTINGUISHING->value])),

                            TextInput::make('video_url')
                                ->label(__('Video URL'))
                                ->url()
                                ->maxLength(500)
                                ->visible(fn (Get $get) => $get('../type') === ExerciseTypeEnum::INSTRUCTIONAL_VIDEO->value)
                                ->required(fn (Get $get) => $get('../type') === ExerciseTypeEnum::INSTRUCTIONAL_VIDEO->value),
                        ])->columnSpanFull(),

                        SpatieMediaLibraryFileUpload::make('video_thumbnail')
                            ->disk('public')
                            ->collection('video_thumbnail')
                            ->label(__('Video Thumbnail'))
                            ->image()
                            ->helperText(__('Recommended size: 1920x1080'))
                            ->maxSize(5120)
                            ->visible(fn (Get $get) => $get('type') === ExerciseTypeEnum::INSTRUCTIONAL_VIDEO->value)
                            ->required(fn (Get $get) => $get('type') === ExerciseTypeEnum::INSTRUCTIONAL_VIDEO->value),
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
            ->itemLabel(fn (array $state): ?string => $state['name'][app()->getLocale()] ?? null);
    }
}

