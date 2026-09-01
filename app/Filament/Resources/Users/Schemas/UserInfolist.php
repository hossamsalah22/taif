<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\SubscriptionStatusEnum;
use App\Models\User;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Basic Parent Information'))
                    ->schema([
                        TextEntry::make('id')->label(__('Parent ID')),
                        TextEntry::make('name')->label(__('Full Name')),
                        TextEntry::make('email')->label(__('Email Address')),
                        TextEntry::make('phone')->label(__('Mobile Number')),
                        TextEntry::make('is_active')
                            ->label(__('Account Status'))
                            ->badge()
                            ->color(fn ($state) => $state ? 'success' : 'danger')
                            ->formatStateUsing(fn ($state) => $state ? __('Active') : __('Suspended')),
                        TextEntry::make('created_at')
                            ->label(__('Registration Date'))
                            ->dateTime(),
                    ])->columns(2),

                Section::make(__('Free Subscription Information'))
                    ->schema([
                        TextEntry::make('free_subscription_status')
                            ->label(__('Free Subscription Status'))
                            ->state(function (User $record) {
                                return $record->subscriptions()
                                    ->where('is_free', true)
                                    ->first()?->status;
                            })
                            ->formatStateUsing(
                                fn (?SubscriptionStatusEnum $state) => $state?->value
                                        ? SubscriptionStatusEnum::label($state)
                                        : __('Not Assigned')
                            )
                            ->badge()
                            ->color(function (?SubscriptionStatusEnum $state) {
                                return $state
                                    ? SubscriptionStatusEnum::color($state)
                                    : 'gray';
                            }),
                        TextEntry::make('free_subscription_granted_date')
                            ->label(__('Granted Date'))
                            ->state(function (User $record) {
                                $subscription = $record->subscriptions()->where('is_free', true)->first();

                                return $subscription ? Carbon::parse($subscription->start_date)->format('Y-m-d') : '-';
                            }),
                        TextEntry::make('free_subscription_duration')
                            ->label(__('Free Subscription Duration (Days)'))
                            ->state(function (User $record) {
                                $subscription = $record->subscriptions()->where('is_free', true)->first();

                                return $subscription ? Carbon::parse($subscription->start_date)->diffInDays(Carbon::parse($subscription->expiry_date)) : '-';
                            }),
                        TextEntry::make('free_subscription_remaining')
                            ->label(__('Remaining Days'))
                            ->state(function (User $record) {
                                $subscription = $record->subscriptions()->where('is_free', true)->first();
                                if (! $subscription) {
                                    return '-';
                                }
                                $remaining = now()->diffInDays(Carbon::parse($subscription->expiry_date), false);

                                return $remaining > 0 ? (int) $remaining : 0;
                            }),
                        TextEntry::make('free_subscription_expiry')
                            ->label(__('Expiry Date'))
                            ->state(function (User $record) {
                                $subscription = $record->subscriptions()->where('is_free', true)->first();

                                return $subscription ? Carbon::parse($subscription->expiry_date)->format('Y-m-d') : '-';
                            }),
                    ])->columns(3),
            ]);
    }
}
