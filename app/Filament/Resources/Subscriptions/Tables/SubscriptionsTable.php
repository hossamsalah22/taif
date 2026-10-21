<?php

namespace App\Filament\Resources\Subscriptions\Tables;

use App\Enums\SubscriptionStatusEnum;
use App\Models\Subscription;
use App\Models\SubscriptionPackage;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubscriptionsTable
{
    /** Days before the end of a subscription that count as "ending soon". */
    public const ENDING_SOON_DAYS = 7;

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('parent.name')
                    ->label(__('Parent'))
                    ->description(fn (Subscription $record): ?string => $record->parent?->phone ? ltrim($record->parent->phone, '+') : null)
                    ->searchable(),
                TextColumn::make('subscriptionPackage.name')
                    ->label(__('Package Name'))
                    ->placeholder(__('Free access')),
                TextColumn::make('amount_paid')
                    ->label(__('Amount Paid'))
                    ->money('SAR')
                    ->description(fn (Subscription $record): ?string => $record->is_free ? __('Free') : null)
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->formatStateUsing(fn (SubscriptionStatusEnum $state): string => SubscriptionStatusEnum::label($state))
                    ->color(fn (SubscriptionStatusEnum $state): string => SubscriptionStatusEnum::color($state))
                    ->badge(),
                TextColumn::make('start_date')
                    ->label(__('Subscription Date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('expiry_date')
                    ->label(__('Expiry Date'))
                    ->date()
                    ->sortable()
                    ->description(fn (Subscription $record): ?string => self::remainingTime($record))
                    ->color(fn (Subscription $record): ?string => self::isEndingSoon($record) ? 'danger' : null),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('running_now')
                    ->label(__('Running now'))
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->runningNow()),
                Filter::make('ending_soon')
                    ->label(__('Ending within :days days', ['days' => self::ENDING_SOON_DAYS]))
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->expiringWithin(self::ENDING_SOON_DAYS)),
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(SubscriptionStatusEnum::options()),
                SelectFilter::make('subscription_package_id')
                    ->label(__('Package Name'))
                    ->relationship('subscriptionPackage', 'name')
                    ->getOptionLabelFromRecordUsing(fn (SubscriptionPackage $record): string => $record->name)
                    ->preload(),
                TernaryFilter::make('is_free')
                    ->label(__('Free'))
                    ->trueLabel(__('Free subscriptions only'))
                    ->falseLabel(__('Paid subscriptions only')),
            ]);
    }

    private static function isEndingSoon(Subscription $record): bool
    {
        if ($record->status !== SubscriptionStatusEnum::ACTIVE || blank($record->expiry_date)) {
            return false;
        }

        $expiry = Carbon::parse($record->expiry_date);

        return $expiry->isFuture() && $expiry->lte(now()->addDays(self::ENDING_SOON_DAYS));
    }

    private static function remainingTime(Subscription $record): ?string
    {
        if ($record->status !== SubscriptionStatusEnum::ACTIVE || blank($record->expiry_date)) {
            return null;
        }

        $expiry = Carbon::parse($record->expiry_date);

        return $expiry->isPast()
            ? __('Ended :time', ['time' => $expiry->diffForHumans()])
            : __('Ends :time', ['time' => $expiry->diffForHumans()]);
    }
}
