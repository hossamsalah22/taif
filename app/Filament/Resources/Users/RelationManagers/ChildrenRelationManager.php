<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Enums\GenderEnum;
use App\Filament\Resources\Children\ChildResource;
use App\Filament\Resources\MainRelationManager;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChildrenRelationManager extends MainRelationManager
{
    protected static string $relationship = 'children';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
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
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Action::make('view_child')
                    ->label(__('View Details'))
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record): string => ChildResource::getUrl('view', ['record' => $record])),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
