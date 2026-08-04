<?php

namespace App\Filament\Resources\LearningLessons\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
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
                TextColumn::make('reward.name')
                    ->label(__('Reward'))
                    ->searchable(),
                IconColumn::make('is_locked')
                    ->label(__('Locked'))
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
