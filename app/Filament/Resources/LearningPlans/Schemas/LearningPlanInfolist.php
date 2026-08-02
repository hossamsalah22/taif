<?php

namespace App\Filament\Resources\LearningPlans\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Schemas\Schema;

class LearningPlanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('target_severity_level')
                    ->badge(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('weekly_sessions_count')
                    ->numeric(),
                TextEntry::make('phase_duration')
                    ->placeholder('-'),
                TextEntry::make('max_daily_goals')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('max_daily_lessons')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('max_daily_exercises')
                    ->numeric()
                    ->placeholder('-'),
                RepeatableEntry::make('goals')
                    ->label(__('Goals'))
                    ->schema([
                        TextEntry::make('name')->label(__('Goal Name')),
                        TextEntry::make('description')->label(__('Description')),
                        TextEntry::make('acquired_skills')->label(__('Acquired Skills')),
                        TextEntry::make('is_locked')
                            ->label(__('Access Lock'))
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state ? 'Locked' : 'Unlocked')
                            ->color(fn ($state) => $state ? 'danger' : 'success'),
                        TextEntry::make('display_priority')->label(__('Priority')),
                        RepeatableEntry::make('lessons')
                            ->label(__('Lessons'))
                            ->schema([
                                TextEntry::make('name')->label(__('Lesson Name')),
                                TextEntry::make('is_locked')
                                    ->label(__('Access Lock'))
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => $state ? 'Locked' : 'Unlocked')
                                    ->color(fn ($state) => $state ? 'danger' : 'success'),
                                TextEntry::make('display_priority')->label(__('Priority')),
                                RepeatableEntry::make('exercises')
                                    ->label(__('Exercises'))
                                    ->schema([
                                        TextEntry::make('difficulty_level')->label(__('Difficulty')),
                                        TextEntry::make('type')->label(__('Type'))->badge(),
                                        TextEntry::make('max_attempts')->label(__('Max Attempts')),
                                        TextEntry::make('is_locked')
                                            ->label(__('Access Lock'))
                                            ->badge()
                                            ->formatStateUsing(fn ($state) => $state ? 'Locked' : 'Unlocked')
                                            ->color(fn ($state) => $state ? 'danger' : 'success'),
                                        TextEntry::make('display_priority')->label(__('Priority')),
                                        TextEntry::make('video_url')
                                            ->label(__('Video URL'))
                                            ->visible(fn ($record) => $record->type === \App\Enums\ExerciseType::InstructionalVideo)
                                            ->url(fn ($record) => $record->video_url),
                                    ])->columns(3),
                            ])->columns(3),
                    ])->columns(2),
            ]);
    }
}
