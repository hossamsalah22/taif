<?php

namespace App\Filament\Resources\SubscriptionPackages\Schemas;

use App\Enums\BillingCycleEnum;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubscriptionPackageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Package Information'))
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('Package Name')),
                        TextEntry::make('is_active')
                            ->badge()
                            ->label(__('Status'))
                            ->color(fn (bool $state): string => match ($state) {
                                true => 'success',
                                false => 'danger',
                            })
                            ->formatStateUsing(fn (bool $state) => $state ? __('Active') : __('Inactive')),
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->label(__('Created Timestamp')),
                    ])->columns(2),

                Section::make(__('Billing & Structural Timeframes'))
                    ->schema([
                        TextEntry::make('billing_cycle')
                            ->label(__('Billing Cycle'))
                            ->formatStateUsing(fn ($state) => BillingCycleEnum::label($state))
                            ->color(fn ($state) => BillingCycleEnum::color(($state)))
                            ->badge(),
                        TextEntry::make('duration_value')
                            ->label(__('Duration'))
                            ->numeric(),
                        TextEntry::make('price')
                            ->money('SAR')
                            ->label(__('Unit Price')),
                    ])->columns(3),

                Section::make(__('Included Features'))
                    ->schema([
                        RepeatableEntry::make('features')
                            ->label(__('Included Features'))
                            ->schema([
                                TextEntry::make('feature.'.app()->getLocale())
                                    ->hiddenLabel(),
                            ])
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make(__('Metrics Summary'))
                    ->schema([
                        TextEntry::make('subscriptions_count')
                            ->label(__('Total Subscribers'))
                            ->counts('subscriptions'),
                        TextEntry::make('active_subscribers_count')
                            ->label(__('Active Subscribers'))
                            ->state(function ($record) {
                                return $record->subscriptions()->where('status', 'active')->count();
                            }),
                    ])->columns(2),
            ]);
    }
}
