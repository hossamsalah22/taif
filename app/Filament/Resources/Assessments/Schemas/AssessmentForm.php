<?php

namespace App\Filament\Resources\Assessments\Schemas;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use App\Enums\AutismLevelEnum;
use App\Enums\ExerciseTypeEnum;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
                        TextInput::make('max_attempts')
                            ->label(__('Max Allowed Attempts'))
                            ->numeric()
                            ->default(3)
                            ->minValue(1)
                            ->required(),
                    ])->columnSpanFull(),
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

                                Repeater::make('options')
                                    ->relationship()
                                    ->label(__('Options'))
                                    ->schema([
                                        TranslatableTabs::make()
                                            ->schema([
                                                TextInput::make('title')
                                                    ->label(__('Card Title'))
                                                    ->required(),
                                            ])
                                            ->visible(fn (Get $get) => $get('../../exercise_type') === ExerciseTypeEnum::AUDIO_FLASHCARDS->value
                                            ),
                                        SpatieMediaLibraryFileUpload::make('image')
                                            ->disk('public')
                                            ->collection('image')
                                            ->label(__('Card Image'))
                                            ->image()
                                            ->maxSize(5120)
                                            ->required(),
                                        SpatieMediaLibraryFileUpload::make('audio')
                                            ->disk('public')
                                            ->collection('audio')
                                            ->label(__('Card Audio File'))
                                            ->acceptedFileTypes(['audio/mpeg', 'audio/wav'])
                                            ->visible(fn (Get $get) => $get('../../exercise_type') === ExerciseTypeEnum::AUDIO_FLASHCARDS->value)
                                            ->required(fn (Get $get) => $get('../../exercise_type') === ExerciseTypeEnum::AUDIO_FLASHCARDS->value),
                                        Toggle::make('is_correct')
                                            ->label(__('Is Correct Answer'))
                                            ->default(false),
                                    ])
                                    ->minItems(2)
                                    ->columns(2)
                                    ->visible(fn (Get $get) => in_array($get('exercise_type'), [ExerciseTypeEnum::AUDIO_FLASHCARDS->value, ExerciseTypeEnum::IMAGE_SELECTION->value, ExerciseTypeEnum::DISTINGUISHING->value]))
                                    ->required(fn (Get $get) => in_array($get('exercise_type'), [ExerciseTypeEnum::AUDIO_FLASHCARDS->value, ExerciseTypeEnum::IMAGE_SELECTION->value, ExerciseTypeEnum::DISTINGUISHING->value])),

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
                    ])->columnSpanFull(),
            ]);
    }
}
