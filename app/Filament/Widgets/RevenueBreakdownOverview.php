<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\ManageGeneral;
use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Filament\Widgets\Concerns\InteractsWithDashboardRange;
use App\Models\Subscription;
use App\Settings\GeneralSettings;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Where the subscription money goes: collected (tax included) → tax → platform commission → service provider share.
 */
class RevenueBreakdownOverview extends StatsOverviewWidget
{
    use InteractsWithDashboardRange;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    public function getHeading(): ?string
    {
        return __('Revenue & how it is split');
    }

    public function getDescription(): ?string
    {
        $settings = app(GeneralSettings::class);

        return __('Package prices include :tax% tax. The tax is taken out first, the platform keeps :commission% of the rest and the remainder is the service provider\'s share. Split with the current rates.', [
            'tax' => (float) $settings->tax + 0,
            'commission' => (float) $settings->commission + 0,
        ]);
    }

    protected function getColumns(): int|array
    {
        return ['default' => 1, 'sm' => 2, 'xl' => 4];
    }

    protected function getStats(): array
    {
        [$kpis, $previous] = $this->kpisWithPreviousPeriod();
        $settings = app(GeneralSettings::class);
        $settingsUrl = ManageGeneral::canAccess() ? ManageGeneral::getUrl(['tab' => 'pricing']) : null;
        $money = fn (float $value): string => number_format($value, 2).' '.__('SAR');

        [, $revenue] = $this->series(Subscription::query(), 'created_at', 'amount_paid');

        return [
            $this->compareWithPreviousPeriod(
                Stat::make(__('Collected from parents'), $money($kpis['total_revenue_sar'])),
                $kpis['total_revenue_sar'],
                $previous['total_revenue_sar'],
            )
                ->chart($revenue)
                ->url(SubscriptionResource::canViewAny() ? SubscriptionResource::getUrl('index') : null)
                ->color('success'),

            Stat::make(__('Tax (:rate%)', ['rate' => (float) $settings->tax + 0]), $money($kpis['revenue_tax_sar']))
                ->description(__('Included in the price, owed to the tax authority'))
                ->descriptionIcon('heroicon-m-building-library')
                ->url($settingsUrl)
                ->color('gray'),

            Stat::make(__('Platform commission (:rate%)', ['rate' => (float) $settings->commission + 0]), $money($kpis['platform_commission_sar']))
                ->description(__(':rate% of :net before tax', ['rate' => (float) $settings->commission + 0, 'net' => $money($kpis['revenue_net_sar'])]))
                ->descriptionIcon('heroicon-m-building-office')
                ->url($settingsUrl)
                ->color('primary'),

            Stat::make(__('Service provider share'), $money($kpis['provider_share_sar']))
                ->description(__('What is left before tax after the commission'))
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
        ];
    }
}
