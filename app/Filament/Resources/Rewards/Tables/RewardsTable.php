<?php

namespace App\Filament\Resources\Rewards\Tables;

use App\Enums\RewardTypeEnum;
use App\Models\Reward;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RewardsTable
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
                    ->label(__('Reward Name'))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function ($query) use ($search) {
                            $query->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.en"))) LIKE ?', ['%'.strtolower($search).'%'])
                                ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ar"))) LIKE ?', ['%'.strtolower($search).'%']);
                        });
                    }),
                TextColumn::make('type')
                    ->label(__('Reinforcement Type'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => RewardTypeEnum::label($state))
                    ->color(fn ($state) => RewardTypeEnum::colors($state))
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('updated_at')
                    ->label(__('Updated At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (Reward $record, DeleteAction $action) {
                        $linkedCount = $record->lessons()->count();
                        if ($linkedCount > 0) {
                            Notification::make()
                                ->warning()
                                ->title(__('Action Blocked'))
                                ->body(__('This reward is linked to active lessons and cannot be deleted.'))
                                ->send();

                            $action->halt();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
