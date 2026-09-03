<?php

namespace App\Filament\Resources\ClinicalProgressReports\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClinicalProgressReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('child.name')
                    ->label(__('Child'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('plan.name')
                    ->label(__('Learning Plan'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('title')
                    ->label(__('Report Title'))
                    ->searchable(),
                IconColumn::make('is_visible_to_parent')
                    ->label(__('Visible to Parent'))
                    ->boolean(),
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
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
