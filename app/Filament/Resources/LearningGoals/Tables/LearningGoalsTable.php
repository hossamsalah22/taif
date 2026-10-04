<?php

namespace App\Filament\Resources\LearningGoals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
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
                TextColumn::make('questions_count')
                    ->label(__('Questions'))
                    ->counts('questions')
                    ->sortable(),
                IconColumn::make('is_locked')
                    ->label(__('Locked'))
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
            ->filters([
                //
            ])
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
