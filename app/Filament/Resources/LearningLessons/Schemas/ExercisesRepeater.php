<?php

namespace App\Filament\Resources\LearningLessons\Schemas;

use App\Enums\DifficultyLevel;
use App\Enums\ExerciseTypeEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;

/**
 * Exercises editor of the learning lesson form. Exercises are ordered by dragging, which sets their display priority.
 */
class ExercisesRepeater
{
    /**
     * Exercise types whose content is a set of answer cards.
     *
     * @var array<int, string>
     */
    private const CARD_TYPES = [
        ExerciseTypeEnum::AUDIO_FLASHCARDS->value,
        ExerciseTypeEnum::IMAGE_SELECTION->value,
        ExerciseTypeEnum::DISTINGUISHING->value,
    ];

    public static function make(): Repeater
    {
        return Repeater::make('exercises')
            ->hiddenLabel()
            ->relationship('exercises')
            ->orderColumn('display_priority')
            ->reorderableWithButtons()
            ->schema([
                Grid::make(2)->schema([
                    Select::make('type')
                        ->label(__('Exercise Type'))
                        ->options(ExerciseTypeEnum::options())
                        ->required()
                        ->live()
                        ->helperText(fn (Get $get): string => match (self::typeOf($get('type'))) {
                            ExerciseTypeEnum::IMAGE_SELECTION->value, ExerciseTypeEnum::DISTINGUISHING->value => __('Add at least 2 picture cards and switch on "Is Correct Answer" for the right one(s).'),
                            ExerciseTypeEnum::AUDIO_FLASHCARDS->value => __('Add at least 2 cards, each with a picture and an audio file; mark the correct one(s).'),
                            ExerciseTypeEnum::MATCHING->value => __('Add at least 3 rows; the child matches each left picture with the right picture on the same row.'),
                            ExerciseTypeEnum::ORDERING->value => __('Add at least 2 pictures in the correct order; the app shuffles them for the child.'),
                            ExerciseTypeEnum::INSTRUCTIONAL_VIDEO->value => __('Add the video link and a thumbnail picture.'),
                            default => __('Choose a type to see which content the exercise needs.'),
                        }),
                    TextInput::make('prompt')
                        ->label(__('Instructional Prompt'))
                        ->required()
                        ->translatableTabs(),
                ]),
                Grid::make(3)->schema([
                    Select::make('difficulty_level')
                        ->label(__('Difficulty Level'))
                        ->options(DifficultyLevel::options())
                        ->default(DifficultyLevel::MEDIUM->value)
                        ->required(),
                    TextInput::make('max_attempts')
                        ->label(__('Max Attempts Bound'))
                        ->numeric()
                        ->default(3)
                        ->minValue(1)
                        ->maxValue(10)
                        ->helperText(__('How many times the child can try this exercise (1 to 10).')),
                    Toggle::make('is_locked')
                        ->label(__('Locked'))
                        ->helperText(__('Locked exercises open only after the previous exercise is completed.'))
                        ->default(true)
                        ->inline(false),
                ]),
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
                        ->defaultItems(3)
                        ->visible(fn (Get $get): bool => self::typeOf($get('../type')) === ExerciseTypeEnum::MATCHING->value)
                        ->required(fn (Get $get): bool => self::typeOf($get('../type')) === ExerciseTypeEnum::MATCHING->value),

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
                        ->grid(3)
                        ->minItems(2)
                        ->defaultItems(2)
                        ->visible(fn (Get $get): bool => self::typeOf($get('../type')) === ExerciseTypeEnum::ORDERING->value)
                        ->required(fn (Get $get): bool => self::typeOf($get('../type')) === ExerciseTypeEnum::ORDERING->value),

                    Repeater::make('options')
                        ->label(__('Options'))
                        ->schema([
                            TextInput::make('title')
                                ->label(__('Card Title'))
                                ->required(),
                            Toggle::make('is_correct')
                                ->label(__('Is Correct Answer'))
                                ->default(false)
                                ->inline(false),
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
                                ->visible(fn (Get $get): bool => self::typeOf($get('../../type')) === ExerciseTypeEnum::AUDIO_FLASHCARDS->value)
                                ->required(fn (Get $get): bool => self::typeOf($get('../../type')) === ExerciseTypeEnum::AUDIO_FLASHCARDS->value),
                        ])
                        ->columns(2)
                        ->grid(2)
                        ->minItems(2)
                        ->defaultItems(2)
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                        ->visible(fn (Get $get): bool => in_array(self::typeOf($get('../type')), self::CARD_TYPES, true))
                        ->required(fn (Get $get): bool => in_array(self::typeOf($get('../type')), self::CARD_TYPES, true)),

                    TextInput::make('video_url')
                        ->label(__('Video URL'))
                        ->url()
                        ->maxLength(500)
                        ->visible(fn (Get $get): bool => self::typeOf($get('../type')) === ExerciseTypeEnum::INSTRUCTIONAL_VIDEO->value)
                        ->required(fn (Get $get): bool => self::typeOf($get('../type')) === ExerciseTypeEnum::INSTRUCTIONAL_VIDEO->value),
                ])->columnSpanFull(),

                SpatieMediaLibraryFileUpload::make('video_thumbnail')
                    ->disk('public')
                    ->collection('video_thumbnail')
                    ->label(__('Video Thumbnail'))
                    ->image()
                    ->helperText(__('Recommended size: 1920x1080'))
                    ->maxSize(5120)
                    ->visible(fn (Get $get): bool => self::typeOf($get('type')) === ExerciseTypeEnum::INSTRUCTIONAL_VIDEO->value)
                    ->required(fn (Get $get): bool => self::typeOf($get('type')) === ExerciseTypeEnum::INSTRUCTIONAL_VIDEO->value),
            ])
            ->columnSpanFull()
            ->addActionLabel(__('Add exercise'))
            ->deleteAction(fn (Action $action): Action => $action->requiresConfirmation())
            ->collapsible()
            ->collapsed(fn (string $operation): bool => $operation === 'edit')
            ->itemLabel(fn (array $state): string => self::itemLabel($state));
    }

    /**
     * The type field holds an enum once loaded from the database and a plain string once picked in the form.
     */
    private static function typeOf(mixed $type): ?string
    {
        return $type instanceof ExerciseTypeEnum ? $type->value : $type;
    }

    /**
     * @param  array{type?: ExerciseTypeEnum|string|null, prompt?: array<string, string>|string|null}  $state
     */
    private static function itemLabel(array $state): string
    {
        $type = ExerciseTypeEnum::tryFrom((string) self::typeOf($state['type'] ?? null));

        if (! $type) {
            return __('New Exercise');
        }

        $prompt = is_array($state['prompt'] ?? null)
            ? ($state['prompt'][app()->getLocale()] ?? reset($state['prompt']))
            : ($state['prompt'] ?? null);

        return filled($prompt)
            ? ExerciseTypeEnum::label($type).' — '.$prompt
            : ExerciseTypeEnum::label($type);
    }
}
