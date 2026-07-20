<?php

namespace App\Filament\Resources\Assessments\Schemas;

use App\Enums\AutismLevelEnum;
use App\Enums\ExerciseTypeEnum;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class AssessmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Section 1: Core Systemic Driver'))
                    ->schema([
                        ToggleButtons::make('autism_level')
                            ->label(__('Target Severity Level'))
                            ->options(AutismLevelEnum::options())
                            ->inline()
                            ->helperText(fn (string $operation): ?string => $operation === 'create' ? __('If there is an active assessment with this severity level, it will be automatically deprecated.') : null)
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->required(),
                        TextInput::make('title')
                            ->label(__('Title'))
                            ->required()
                            ->translatableTabs(),
                    ]),
                Section::make(__('Section 2: Question Nodes'))
                    ->schema([
                        Repeater::make('questions')
                            ->label(__('Questions'))
                            ->relationship()
                            ->schema([
                                TextInput::make('prompt')
                                    ->label(__('Instructional Prompt'))
                                    ->required()
                                    ->translatableTabs(),
                                Radio::make('exercise_type')
                                    ->label(__('Exercise Type Picker'))
                                    ->options(ExerciseTypeEnum::options())
                                    ->inline()
                                    ->live()
                                    ->required(),

                                SpatieMediaLibraryFileUpload::make('question_image')
                                    ->label(__('Target Image (Correct Object)'))
                                    ->collection('question_image')
                                    ->disk('public')
                                    ->image()
                                    ->maxSize(5120)
                                    ->visible(fn (Get $get) => in_array($get('exercise_type'), [ExerciseTypeEnum::IMAGE_SELECTION->value, ExerciseTypeEnum::AUDIO_FLASHCARDS->value, ExerciseTypeEnum::DISTINGUISHING->value]))
                                    ->required(fn (Get $get) => in_array($get('exercise_type'), [ExerciseTypeEnum::IMAGE_SELECTION->value, ExerciseTypeEnum::AUDIO_FLASHCARDS->value, ExerciseTypeEnum::DISTINGUISHING->value])),

                                SpatieMediaLibraryFileUpload::make('distractors')
                                    ->label(__('Distractor Objects Pool'))
                                    ->collection('distractors')
                                    ->disk('public')
                                    ->image()
                                    ->multiple()
                                    ->minFiles(2)
                                    ->maxFiles(5)
                                    ->maxSize(5120)
                                    ->visible(fn (Get $get) => $get('exercise_type') === ExerciseTypeEnum::IMAGE_SELECTION->value)
                                    ->required(fn (Get $get) => $get('exercise_type') === ExerciseTypeEnum::IMAGE_SELECTION->value),

                                Repeater::make('matchingPairs')
                                    ->relationship()
                                    ->label(__('Matching Matrix Rows Manager'))
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('left_element')
                                            ->disk('public')
                                            ->collection('left_element')
                                            ->label(__('Left element'))
                                            ->image()
                                            ->maxSize(5120)
                                            ->required(),
                                        SpatieMediaLibraryFileUpload::make('right_element')
                                            ->disk('public')
                                            ->collection('right_element')
                                            ->label(__('Right element'))
                                            ->image()
                                            ->maxSize(5120)
                                            ->required(),
                                    ])
                                    ->columns(2)
                                    ->minItems(3)
                                    ->visible(fn (Get $get) => $get('exercise_type') === ExerciseTypeEnum::MATCHING->value)
                                    ->required(fn (Get $get) => $get('exercise_type') === ExerciseTypeEnum::MATCHING->value),

                                Repeater::make('orderingSteps')
                                    ->relationship()
                                    ->label(__('Chronological Sequence Slider'))
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('image')
                                            ->disk('public')
                                            ->collection('image')
                                            ->label(__('Image'))
                                            ->image()
                                            ->maxSize(5120)
                                            ->required(),
                                    ])
                                    ->minItems(2)
                                    ->visible(fn (Get $get) => $get('exercise_type') === ExerciseTypeEnum::ORDERING->value)
                                    ->required(fn (Get $get) => $get('exercise_type') === ExerciseTypeEnum::ORDERING->value),

                                SpatieMediaLibraryFileUpload::make('shared_elements')
                                    ->label(__('Shared Characteristic Elements'))
                                    ->collection('shared_elements')
                                    ->disk('public')
                                    ->image()
                                    ->multiple()
                                    ->maxSize(5120)
                                    ->visible(fn (Get $get) => $get('exercise_type') === ExerciseTypeEnum::DISTINGUISHING->value)
                                    ->required(fn (Get $get) => $get('exercise_type') === ExerciseTypeEnum::DISTINGUISHING->value),

                                SpatieMediaLibraryFileUpload::make('question_audio')
                                    ->label(__('Audio File'))
                                    ->disk('public')
                                    ->collection('question_audio')
                                    ->acceptedFileTypes(['audio/mpeg', 'audio/wav'])
                                    ->visible(fn (Get $get) => $get('exercise_type') === ExerciseTypeEnum::AUDIO_FLASHCARDS->value)
                                    ->required(fn (Get $get) => $get('exercise_type') === ExerciseTypeEnum::AUDIO_FLASHCARDS->value),

                                TextInput::make('video_url')
                                    ->label(__('Video URL'))
                                    ->url()
                                    ->maxLength(500)
                                    ->visible(fn (Get $get) => $get('exercise_type') === ExerciseTypeEnum::INSTRUCTIONAL_VIDEO->value)
                                    ->required(fn (Get $get) => $get('exercise_type') === ExerciseTypeEnum::INSTRUCTIONAL_VIDEO->value),
                            ])
                            ->orderColumn('order')
                            ->defaultItems(1)
                            ->collapsed(false),
                    ]),
            ]);
    }
}
