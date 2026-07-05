<?php

namespace App\Filament\Resources\Countries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CountriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('name'))
                    ->searchable(),
                TextColumn::make('iso')
                    ->label(__('iso'))
                    ->searchable(),
                TextColumn::make('iso3')
                    ->label(__('iso3')),
                TextColumn::make('numcode')
                    ->label(__('numcode'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('phonecode')
                    ->label(__('phonecode'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('currency_code')
                    ->label(__('currency_code'))
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(__('created_at'))
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('updated_at'))
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
