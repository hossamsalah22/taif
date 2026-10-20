<?php

namespace App\Filament\Resources\LearningGoals\Tables;

use App\Models\LearningPlan;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LearningGoalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Goal Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('plans.name')
                    ->label(__('Learning Plans'))
                    ->badge()
                    ->placeholder(__('No plan')),
                TextColumn::make('reward.name')
                    ->label(__('Reward'))
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('lessons_count')
                    ->label(__('Lessons'))
                    ->counts('lessons')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'info' : 'danger')
                    ->tooltip(fn (int $state): ?string => $state > 0 ? null : __('This goal has no lessons yet.'))
                    ->sortable(),
                TextColumn::make('questions_count')
                    ->label(__('Questions'))
                    ->counts('questions')
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_locked')
                    ->label(__('Locked'))
                    ->tooltip(__('Locked goals open only after the previous goal is completed.'))
                    ->boolean(),
                TextColumn::make('display_priority')
                    ->label(__('Display Priority'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('display_priority')
            ->filters([
                SelectFilter::make('plans')
                    ->label(__('Learning Plans'))
                    ->relationship('plans', 'name')
                    ->getOptionLabelFromRecordUsing(fn (LearningPlan $record): string => $record->name)
                    ->preload(),
            ])
            ->emptyStateHeading(__('No learning goals yet'))
            ->emptyStateDescription(__('Create a goal, add its lessons and exercises, then add it to one or more plans.'))
            ->recordActions([
                // ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
