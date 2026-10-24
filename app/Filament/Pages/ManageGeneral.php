<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroupEnum;
use App\Services\SubscriptionPricing;
use App\Settings\GeneralSettings;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\UnorderedList;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageGeneral extends SettingsPage
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string $settings = GeneralSettings::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroupEnum::SETTINGS;

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

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('settings')
                    ->persistTabInQueryString('tab')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make(__('General'))
                            ->id('general')
                            ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                            ->schema([
                                $this->contactSection(),
                                $this->mobileAppLinksSection(),
                            ]),

                        Tab::make(__('Tax & Commission'))
                            ->id('pricing')
                            ->icon(Heroicon::OutlinedBanknotes)
                            ->schema([
                                $this->pricingSection(),
                                $this->pricingExplanation(),
                            ]),

                        Tab::make(__('Subscriptions'))
                            ->id('subscriptions')
                            ->icon(Heroicon::OutlinedCalendar)
                            ->schema([
                                $this->planAccessSection(),
                            ]),

                        Tab::make(__('Social Media'))
                            ->id('social')
                            ->icon(Heroicon::OutlinedShare)
                            ->schema([
                                $this->socialLinksSection(),
                            ]),

                        Tab::make(__('System'))
                            ->id('system')
                            ->icon(Heroicon::OutlinedInformationCircle)
                            ->schema([
                                $this->systemInformationSection(),
                            ]),
                    ]),
            ])
            ->columns(1);
    }

    private function contactSection(): Section
    {
        return Section::make(__('contact_information'))
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
                    ->rule('email:rfc,filter')
                    ->nullable()
                    ->placeholder(__('email_placeholder'))
                    ->helperText(__('general_email_help'))
                    ->prefixIcon('heroicon-o-envelope')
                    ->columnSpanFull(),
            ])
            ->columns(1);
    }

    private function mobileAppLinksSection(): Section
    {
        return Section::make(__('mobile_app_links'))
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
            ->columns(1);
    }

    private function pricingSection(): Section
    {
        return Section::make(__('Tax & Commission'))
            ->description(__('Package prices already include the tax. The tax is taken out of the price first, then the platform commission is taken from what is left; the rest is the service provider\'s share.'))
            ->icon(Heroicon::OutlinedBanknotes)
            ->schema([
                Grid::make(2)
                    ->schema([
                        TextInput::make('tax')
                            ->label(__('Tax Rate (%)'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.5)
                            ->rule(['required', 'min:0', 'max:100'])
                            ->suffix('%')
                            ->live(onBlur: true)
                            ->helperText(__('VAT percentage already included in every package price (e.g., 15 for 15%). It is not added on top: a 115 SAR package with 15% tax is 100 SAR + 15 SAR tax.')),

                        TextInput::make('commission')
                            ->label(__('Platform Commission (%)'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.5)
                            ->rule(['required', 'min:0', 'max:100'])
                            ->suffix('%')
                            ->live(onBlur: true)
                            ->helperText(__('Share of the price before tax that the platform keeps from every paid subscription (e.g., 15 for 15%). The rest goes to the service provider.')),
                    ]),
            ])
            ->columns(1);
    }

    /**
     * "How it works" card: splits an example package price with the tax and commission typed above, so the
     * admin sees what the parent pays, the tax, the platform's share and the service provider's share.
     */
    private function pricingExplanation(): Section
    {
        $breakdown = fn (Get $get): array => SubscriptionPricing::calculateWith(
            (float) $get('example_price'),
            (float) $get('tax'),
            (float) $get('commission'),
        );
        $money = fn (float $value): string => number_format($value, 2).' '.__('SAR');
        $percent = fn ($value): string => rtrim(rtrim(number_format((float) $value, 2), '0'), '.').'%';

        return Section::make(__('How the package price is split'))
            ->description(__('Type any package price to preview the split with the rates above. This preview is not saved.'))
            ->icon(Heroicon::OutlinedCalculator)
            ->iconColor('info')
            ->schema([
                TextInput::make('example_price')
                    ->label(__('Example package price'))
                    ->numeric()
                    ->minValue(0)
                    ->default(115)
                    ->suffix(__('SAR'))
                    ->live(onBlur: true)
                    ->dehydrated(false),

                Grid::make(['default' => 1, 'lg' => 2])
                    ->schema([
                        Section::make(__('What the parent pays'))
                            ->icon(Heroicon::OutlinedUser)
                            ->iconColor('primary')
                            ->compact()
                            ->schema([
                                UnorderedList::make([
                                    Text::make(fn (Get $get): string => __('The parent pays :price (the package price, tax included).', ['price' => $money($breakdown($get)['price'])])),
                                    Text::make(fn (Get $get): string => __('Price before tax: :net.', ['net' => $money($breakdown($get)['net_amount'])])),
                                    Text::make(fn (Get $get): string => __('Tax (:rate): :tax.', ['rate' => $percent($get('tax')), 'tax' => $money($breakdown($get)['tax_amount'])])),
                                ]),
                            ]),

                        Section::make(__('How the price before tax is shared'))
                            ->icon(Heroicon::OutlinedScale)
                            ->iconColor('success')
                            ->compact()
                            ->schema([
                                UnorderedList::make([
                                    Text::make(fn (Get $get): string => __('Platform commission (:rate of :net): :commission.', [
                                        'rate' => $percent($get('commission')),
                                        'net' => $money($breakdown($get)['net_amount']),
                                        'commission' => $money($breakdown($get)['platform_commission']),
                                    ])),
                                    Text::make(fn (Get $get): string => __('Service provider share: :provider.', ['provider' => $money($breakdown($get)['provider_amount'])])),
                                ]),
                            ]),
                    ]),

                Callout::make(__('Good to know'))
                    ->description(__('Free packages (price 0) have no tax and no commission. Changing these rates does not change subscriptions that were already paid.'))
                    ->info(),
            ])
            ->columns(1);
    }

    private function planAccessSection(): Section
    {
        return Section::make(__('plan_access_configuration'))
            ->description(__('plan_access_configuration_description'))
            ->icon('heroicon-o-calendar')
            ->schema([
                TextInput::make('plan_grace_period_days')
                    ->label(__('plan_grace_period_days'))
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(365)
                    ->rule(['required', 'integer', 'min:1', 'max:365'])
                    ->suffix(__('days'))
                    ->helperText(__('plan_grace_period_days_help')),
            ])
            ->columns(1);
    }

    private function socialLinksSection(): Section
    {
        $link = fn (string $name, string $placeholder): TextInput => TextInput::make($name)
            ->label(__($name))
            ->url()
            ->nullable()
            ->placeholder($placeholder)
            ->prefixIcon('heroicon-o-link');

        return Section::make(__('social_links'))
            ->description(__('social_links_description'))
            ->icon('heroicon-o-share')
            ->schema([
                Grid::make(2)
                    ->schema([
                        $link('facebook', 'https://facebook.com/...'),
                        $link('instagram', 'https://instagram.com/...'),
                        $link('snapchat', 'https://snapchat.com/...'),
                        $link('linkedin', 'https://linkedin.com/...'),
                        $link('tiktok', 'https://tiktok.com/...'),
                    ]),
            ])
            ->columns(1);
    }

    private function systemInformationSection(): Section
    {
        return Section::make(__('system_information'))
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
            ->columns(3);
    }
}
