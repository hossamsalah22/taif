<?php

namespace App\Filament\Resources\SubscriptionPackages\Schemas;

use App\Enums\BillingCycleEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Enum;

class SubscriptionPackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Package Details'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Package Name'))
                            ->rules(['required', 'max:255', 'min:1'])
                            ->translatableTabs(),
                        TextInput::make('duration_value')
                            ->label(__('Duration'))
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->helperText(__('Number of months/days defined by the billing cycle.')),
                        Select::make('billing_cycle')
                            ->label(__('Billing Cycle'))
                            ->options(BillingCycleEnum::options())
                            ->rules(['required', new Enum(BillingCycleEnum::class)]),
                        TextInput::make('price')
                            ->label(__('Price (SAR)'))
                            ->numeric()
                            ->minValue(0)
                            ->step('0.01')
                            ->required(),
                    ])
                    ->columns(2),
                Section::make(__('Package Features'))
                    ->schema([
                        Repeater::make('features')
                            ->label(__('Features List'))
                            ->schema([
                                TextInput::make('feature')
                                    ->label(__('Feature'))
                                    ->required()
                                    ->maxLength(255)
                                    ->translatableTabs(),
                            ])
                            ->default([
                                ['feature' => ''],
                            ])
                            ->minItems(1)
                            ->reorderable()
                            ->collapsible()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
