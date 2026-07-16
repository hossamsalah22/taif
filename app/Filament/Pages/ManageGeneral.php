<?php

namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageGeneral extends SettingsPage
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string $settings = GeneralSettings::class;

    protected static string|UnitEnum|null $navigationGroup = 'system_settings';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('manage_general');
    }

    public function getTitle(): string
    {
        return __('manage_general');
    }

    public function getHeading(): string
    {
        return __('manage_general');
    }

    public function getSubheading(): ?string
    {
        return __('general_settings_description');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('system_settings');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('contact_information'))
                    ->description(__('contact_information_description'))
                    ->icon('heroicon-o-phone')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('phone')
                                    ->label(__('phone'))
                                    ->tel()
                                    ->nullable()
                                    ->placeholder(__('phone_placeholder'))
                                    ->helperText(__('general_phone_help'))
                                    ->prefixIcon('heroicon-o-phone')
                                    ->rules(function (Get $get): array {
                                        $rules = ['nullable'];
                                        if ($get('phone')) {
                                            $rules[] = 'phone_number:SA';
                                        }

                                        return $rules;
                                    })
                                    ->dehydrateStateUsing(
                                        fn ($state, Get $get) => $get('phone') ? prepare_phone($state, 'SA') : $state
                                    ),

                                TextInput::make('whatsapp')
                                    ->label(__('whatsapp'))
                                    ->tel()
                                    ->nullable()
                                    ->placeholder(__('whatsapp_placeholder'))
                                    ->helperText(__('whatsapp_help'))
                                    ->prefixIcon('heroicon-o-chat-bubble-oval-left')
                                    ->rules(function (Get $get): array {
                                        $rules = ['nullable'];
                                        if ($get('whatsapp')) {
                                            $rules[] = 'phone_number:SA';
                                        }

                                        return $rules;
                                    })
                                    ->dehydrateStateUsing(
                                        fn ($state, Get $get) => $get('whatsapp') ? prepare_phone($state, 'SA') : $state
                                    ),
                            ]),

                        TextInput::make('email')
                            ->label(__('email'))
                            ->email()
                            ->nullable()
                            ->placeholder(__('email_placeholder'))
                            ->helperText(__('general_email_help'))
                            ->prefixIcon('heroicon-o-envelope')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->columns(1),

                Section::make(__('mobile_app_links'))
                    ->description(__('mobile_app_links_description'))
                    ->icon('heroicon-o-device-phone-mobile')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('app_store_link')
                                    ->label(__('app_store_link'))
                                    ->url()
                                    ->nullable()
                                    ->placeholder(__('app_store_placeholder'))
                                    ->helperText(__('app_store_help'))
                                    ->prefixIcon('heroicon-o-device-phone-mobile')
                                    ->suffixAction(
                                        Action::make('test_app_store')
                                            ->icon('heroicon-o-arrow-top-right-on-square')
                                            ->url(fn (Get $get): ?string => $get('app_store_link'))
                                            ->openUrlInNewTab()
                                            ->visible(fn (Get $get): bool => filled($get('app_store_link')))
                                    ),

                                TextInput::make('play_store_link')
                                    ->label(__('play_store_link'))
                                    ->url()
                                    ->nullable()
                                    ->placeholder(__('play_store_placeholder'))
                                    ->helperText(__('play_store_help'))
                                    ->prefixIcon('heroicon-o-device-phone-mobile')
                                    ->suffixAction(
                                        Action::make('test_play_store')
                                            ->icon('heroicon-o-arrow-top-right-on-square')
                                            ->url(fn (Get $get): ?string => $get('play_store_link'))
                                            ->openUrlInNewTab()
                                            ->visible(fn (Get $get): bool => filled($get('play_store_link')))
                                    ),
                            ]),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->columns(1),

                Section::make(__('shipping_and_tax'))
                    ->description(__('shipping_and_tax_description'))
                    ->icon('heroicon-o-truck')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('tax')
                                    ->label(__('tax'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->rule(['required', 'min:0', 'max:100'])
                                    ->suffix('%')
                                    ->helperText(__('tax_help')),
                                TextInput::make('commission')
                                    ->label(__('commission'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->rule(['required', 'min:0', 'max:100'])
                                    ->suffix('%')
                                    ->helperText(__('commission_help')),
                            ]),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->columns(1),

                Section::make(__('social_links'))
                    ->description(__('social_links_description'))
                    ->icon('heroicon-o-share')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('facebook')
                                    ->label(__('facebook'))
                                    ->url()
                                    ->nullable()
                                    ->placeholder('https://facebook.com/...')
                                    ->prefixIcon('heroicon-o-link'),
                                TextInput::make('instagram')
                                    ->label(__('instagram'))
                                    ->url()
                                    ->nullable()
                                    ->placeholder('https://instagram.com/...')
                                    ->prefixIcon('heroicon-o-link'),
                                TextInput::make('snapchat')
                                    ->label(__('snapchat'))
                                    ->url()
                                    ->nullable()
                                    ->placeholder('https://snapchat.com/...')
                                    ->prefixIcon('heroicon-o-link'),
                                TextInput::make('linkedin')
                                    ->label(__('linkedin'))
                                    ->url()
                                    ->nullable()
                                    ->placeholder('https://linkedin.com/...')
                                    ->prefixIcon('heroicon-o-link'),
                                TextInput::make('tiktok')
                                    ->label(__('tiktok'))
                                    ->url()
                                    ->nullable()
                                    ->placeholder('https://tiktok.com/...')
                                    ->prefixIcon('heroicon-o-link'),
                            ]),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->columns(1),

                Section::make(__('plan_access_configuration'))
                    ->description(__('plan_access_configuration_description'))
                    ->icon('heroicon-o-calendar')
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                TextInput::make('plan_grace_period_days')
                                    ->label(__('plan_grace_period_days'))
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(365)
                                    ->rule(['required', 'integer', 'min:1', 'max:365'])
                                    ->suffix(__('days'))
                                    ->helperText(__('plan_grace_period_days_help')),
                            ]),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->columns(1),

                Section::make(__('system_information'))
                    ->description(__('system_information_description'))
                    ->icon('heroicon-o-information-circle')
                    ->schema([

                        TextEntry::make('environment')
                            ->label(__('environment'))
                            ->state(app()->environment())
                            ->helperText(__('current_environment')),

                        TextEntry::make('last_updated')
                            ->label(__('last_updated'))
                            ->state(function () {
                                return cache()->remember('general_settings_last_updated', 60, function () {
                                    return now()->diffForHumans();
                                });
                            })
                            ->helperText(__('when_settings_last_changed')),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->columns(3),
            ])
            ->columns(1);
    }
}
