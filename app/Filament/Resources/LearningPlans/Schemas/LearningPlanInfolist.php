<?php

namespace App\Filament\Resources\LearningPlans\Schemas;

use App\Enums\DifficultyLevel;
use App\Enums\ExerciseTypeEnum;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LearningPlanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Plan Details'))
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')->label(__('Name')),
                        TextEntry::make('autism_level')->label(__('Severity Level'))->badge(),
                        TextEntry::make('status')->label(__('Status'))->badge(),
                        TextEntry::make('weekly_sessions_count')->label(__('Weekly sessions count'))->numeric(),
                        TextEntry::make('phase_duration')->label(__('Phase duration'))->placeholder('-'),
                        TextEntry::make('created_at')->label(__('Created at'))->dateTime()->placeholder('-'),
                    ]),

                Section::make(__('Daily Limits'))
                    ->columns(3)
                    ->schema([
                        TextEntry::make('max_daily_goals')->label(__('Max daily goals'))->numeric()->placeholder('-'),
                        TextEntry::make('max_daily_lessons')->label(__('Max daily lessons'))->numeric()->placeholder('-'),
                        TextEntry::make('max_daily_exercises')->label(__('Max daily exercises'))->numeric()->placeholder('-'),
                    ]),

                Section::make(__('Goals Hierarchy'))
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('goals')
                            ->label(__('Goals'))
                            ->columnSpanFull()
                            ->schema([
                                TextEntry::make('name')->label(__('Goal Name')),
                                TextEntry::make('display_priority')->label(__('Priority')),
                                TextEntry::make('is_locked')
                                    ->label(__('Access Lock'))
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => $state ? __('Locked') : __('Unlocked'))
                                    ->color(fn ($state) => $state ? 'danger' : 'success'),
                                TextEntry::make('acquired_skills')->label(__('Acquired Skills')),
                                TextEntry::make('description')->label(__('Description'))->columnSpanFull(),

                                RepeatableEntry::make('lessons')
                                    ->label(__('Lessons'))
                                    ->columnSpanFull()
                                    ->schema([
                                        TextEntry::make('name')->label(__('Lesson Name')),
                                        TextEntry::make('display_priority')->label(__('Priority')),
                                        TextEntry::make('is_locked')
                                            ->label(__('Access Lock'))
                                            ->badge()
                                            ->formatStateUsing(fn ($state) => $state ? __('Locked') : __('Unlocked'))
                                            ->color(fn ($state) => $state ? 'danger' : 'success'),

                                        RepeatableEntry::make('exercises')
                                            ->label(__('Exercises'))
                                            ->columnSpanFull()
                                            ->grid(2)
                                            ->schema([
                                                TextEntry::make('type')->label(__('Type'))->formatStateUsing(fn ($state) => ExerciseTypeEnum::label($state))->badge()->color(fn ($state) => ExerciseTypeEnum::color($state)),
                                                TextEntry::make('difficulty_level')->label(__('Difficulty'))->formatStateUsing(fn ($state) => DifficultyLevel::label($state))->badge()->color(fn ($state) => DifficultyLevel::color($state)),
                                                TextEntry::make('max_attempts')->label(__('Max Attempts')),
                                                TextEntry::make('is_locked')
                                                    ->label(__('Access Lock'))
                                                    ->badge()
                                                    ->formatStateUsing(fn ($state) => $state ? __('Locked') : __('Unlocked'))
                                                    ->color(fn ($state) => $state ? 'danger' : 'success'),
                                                TextEntry::make('display_priority')->label(__('Priority')),
                                                TextEntry::make('video_url')
                                                    ->label(__('Video URL'))
                                                    ->visible(fn ($record) => $record->type === ExerciseTypeEnum::INSTRUCTIONAL_VIDEO)
                                                    ->url(fn ($record) => $record->video_url),
                                            ])->columns(3),
                                    ])->columns(3),
                            ])->columns(4),
                    ]),
            ]);
    }
}
