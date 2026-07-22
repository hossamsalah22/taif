<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\MainRelationManager;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubscriptionsRelationManager extends MainRelationManager
{
    protected static string $relationship = 'subscriptions';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // Readonly or manage subscriptions forms if needed
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('id')->label(__('Subscription ID')),
                TextColumn::make('subscriptionPackage.name')
                    ->label(__('Package Name')),
                TextColumn::make('amount_paid')
                    ->label(__('Amount Paid'))
                    ->money('SAR'),
                TextColumn::make('status')
                    ->label(__('Payment Status'))
                    ->badge(),
                TextColumn::make('start_date')
                    ->label(__('Subscription Date'))
                    ->dateTime(),
                TextColumn::make('expiry_date')
                    ->label(__('Expiry Date'))
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
