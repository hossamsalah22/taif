<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

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
                            ->default(__('Not Assigned'))
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('free_subscription_granted_by')
                            ->label(__('Granted By'))
                            ->default('-'),
                        TextEntry::make('free_subscription_granted_date')
                            ->label(__('Granted Date'))
                            ->default('-'),
                        TextEntry::make('free_subscription_duration')
                            ->label(__('Free Subscription Duration (Days)'))
                            ->default('-'),
                        TextEntry::make('free_subscription_remaining')
                            ->label(__('Remaining Days'))
                            ->default('-'),
                        TextEntry::make('free_subscription_expiry')
                            ->label(__('Expiry Date'))
                            ->default('-'),
                    ])->columns(3),
            ]);
    }
}
