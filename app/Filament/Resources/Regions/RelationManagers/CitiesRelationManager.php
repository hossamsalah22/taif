<?php

namespace App\Filament\Resources\Regions\RelationManagers;

use App\Filament\Resources\MainRelationManager;
use App\Models\City;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CitiesRelationManager extends MainRelationManager
{
    protected static string $relationship = 'cities';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('name'))
                    ->required()
                    ->rule(function (Get $get, ?City $record) {
                        return function (string $attribute, $value, $fail) use ($record) {
                            if (! filled($value)) {
                                return;
                            }

                            $query = City::query();
                            if ($record?->getKey()) {
                                $query->whereKeyNot($record->getKey());
                            }

                            $exists = $query
                                ->where(function ($q) use ($value) {
                                    $q->where('name->en', $value)
                                        ->orWhere('name->ar', $value);
                                })
                                ->exists();

                            if ($exists) {
                                $fail(__('validation.unique', ['attribute' => __('name')]));
                            }
                        };
                    })
                    ->translatableTabs(),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('name'))
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
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
