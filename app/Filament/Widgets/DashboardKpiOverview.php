<?php

namespace App\Filament\Widgets;

use App\Models\Child;
use App\Models\ExerciseInteractionLog;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class DashboardKpiOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $period = $this->filters['period'] ?? 'monthly';
        $kpis = \App\Services\AnalyticsService::getDashboardKpis($period);

        return [
            Stat::make(__('Total Registered Parents'), $kpis['total_registered_parents'])
                ->description(__('For selected period'))
                ->descriptionIcon('heroicon-m-users')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('primary'),

            Stat::make(__('Active Children Profiles'), $kpis['active_children_profiles'])
                ->description(__('Active profiles in period'))
                ->descriptionIcon('heroicon-m-face-smile')
                ->chart([1, 4, 2, 5, 8, 3, 12])
                ->color('success'),

            Stat::make(__('Active Subscriptions'), $kpis['active_subscription_plans'])
                ->description(__('Currently active'))
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('warning'),

            Stat::make(__('Executed Therapy Sessions'), $kpis['executed_therapy_sessions'])
                ->description(__('Completed sessions'))
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->chart([4, 5, 12, 10, 20, 15, 25])
                ->color('info'),

            Stat::make(__('Average Progress Percentage'), $kpis['average_progress_percentage'])
                ->description(__('Overall baseline development'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make(__('Total Revenue (SAR)'), Number::format($kpis['total_revenue_sar']))
                ->description(__('Gross ingestion'))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
        ];
    }
}
