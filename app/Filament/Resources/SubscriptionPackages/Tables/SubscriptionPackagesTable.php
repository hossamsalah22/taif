<?php

namespace App\Filament\Resources\SubscriptionPackages\Tables;

use App\Enums\BillingCycleEnum;
use App\Models\SubscriptionPackage;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubscriptionPackagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('name'))->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function ($query) use ($search) {
                            $query->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.en"))) LIKE ?', ['%'.strtolower($search).'%'])
                                ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ar"))) LIKE ?', ['%'.strtolower($search).'%']);
                        });
                    }),
                TextColumn::make('billing_cycle')
                    ->label(__('Billing Cycle'))
                    ->badge()
                    ->color(fn ($state) => BillingCycleEnum::color($state))
                    ->formatStateUsing(fn ($state) => BillingCycleEnum::label($state))
                    ->sortable(),
                TextColumn::make('duration_value')
                    ->label(__('Duration Value'))
                    ->sortable(),
                TextColumn::make('price')
                    ->label(__('Unit Price'))
                    ->money('SAR')
                    ->sortable(),
                TextColumn::make('active_subscribers_count')
                    ->label(__('Active Subscribers'))
                    ->state(fn ($record) => $record->subscriptions()->where('status', 'active')->count())
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label(__('Status'))
                    ->afterStateUpdated(function (SubscriptionPackage $record, $state) {
                        $record->update(['is_active' => $state]);

                        Notification::make()
                            ->title(__('Status Updated'))
                            ->body(__('Status updated successfully'))
                            ->success()
                            ->send();
                    }),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->before(function ($record, DeleteAction $action) {
                        $activeCount = $record->subscriptions()->where('status', 'active')->count();
                        if ($activeCount > 0) {
                            Notification::make()
                                ->warning()
                                ->title('Action Blocked')
                                ->body('This package is linked to active subscribers and cannot be deleted.')
                                ->send();

                            $action->halt();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
