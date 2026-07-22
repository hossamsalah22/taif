<?php

namespace App\Filament\Resources\Children\Tables;

use App\Models\Child;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;

class ChildrenTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make(),
                Action::make('force_retest')
                    ->label(__('Force Re-test'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading(__('Override Assessment Lock'))
                    ->modalDescription(__('Are you sure you want to allow this child to take the assessment again? This will bypass the maximum attempts limit.'))
                    ->action(fn (Child $record) => $record->update(['override_assessment_lock' => true]))
                    ->visible(fn (Child $record) => ! $record->override_assessment_lock),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
