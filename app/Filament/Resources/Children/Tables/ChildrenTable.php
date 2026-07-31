<?php

namespace App\Filament\Resources\Children\Tables;

use App\Enums\GenderEnum;
use App\Models\Child;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChildrenTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label(__('Child ID')),
                TextColumn::make('name')->label(__('Child Name')),
                TextColumn::make('age')
                    ->label(__('Age')),
                TextColumn::make('gender')
                    ->label(__('Gender'))
                    ->formatStateUsing(fn ($state) => GenderEnum::label($state))
                    ->color(fn ($state) => GenderEnum::color($state))
                    ->badge(),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('force_retest')
                    ->label(__('Force Re-test'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading(__('Override Assessment Lock'))
                    ->modalDescription(__('Are you sure you want to allow this child to take the assessment again? This will bypass the maximum attempts limit.'))
                    ->action(fn (Child $record) => $record->update(['force_re_test' => true]))
                    ->visible(fn (Child $record) => ! $record->force_re_test),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
