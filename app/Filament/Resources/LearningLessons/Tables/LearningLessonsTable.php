<?php

namespace App\Filament\Resources\LearningLessons\Tables;

use App\Models\LearningGoal;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LearningLessonsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Lesson Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('goal.name')
                    ->label(__('Learning Goal'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('exercises_count')
                    ->label(__('Exercises'))
                    ->counts('exercises')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'info' : 'danger')
                    ->tooltip(fn (int $state): ?string => $state > 0 ? null : __('This lesson has no exercises yet. Add them from its goal.'))
                    ->sortable(),
                TextColumn::make('reward.name')
                    ->label(__('Reward'))
                    ->placeholder('-')
                    ->searchable(),
                IconColumn::make('is_locked')
                    ->label(__('Locked'))
                    ->tooltip(__('Locked lessons open only after the previous lesson is completed.'))
                    ->boolean(),
                TextColumn::make('display_priority')
                    ->label(__('Display priority'))
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
            ->filters([
                SelectFilter::make('learning_goal_id')
                    ->label(__('Learning Goal'))
                    ->relationship('goal', 'name')
                    ->getOptionLabelFromRecordUsing(fn (LearningGoal $record): string => $record->name)
                    ->preload(),
            ])
            ->emptyStateHeading(__('No lessons yet'))
            ->emptyStateDescription(__('Lessons belong to a goal. The easiest way to add them with their exercises is from the goal form.'))
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
