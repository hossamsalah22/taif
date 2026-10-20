<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Children\ChildResource;
use App\Filament\Resources\SubscriptionPackages\SubscriptionPackageResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Widgets\Concerns\InteractsWithDashboardRange;
use App\Models\Child;
use App\Models\ExerciseInteractionLog;
use App\Models\User;
use App\Services\AnalyticsService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class DashboardKpiOverview extends StatsOverviewWidget
{
    use InteractsWithDashboardRange;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    public function getHeading(): ?string
    {
        return __('Activity in the selected period');
    }

    protected function getColumns(): int|array
    {
        return ['default' => 1, 'sm' => 2, 'xl' => 4];
    }

    protected function getStats(): array
    {
        $kpis = AnalyticsService::getDashboardKpis($this->rangeStart(), $this->rangeEnd());

        [, $parents] = $this->series(User::query());
        [, $children] = $this->series(Child::query());
        [, $sessions] = $this->series(ExerciseInteractionLog::query());

        return [
            Stat::make(__('New Parents'), Number::format($kpis['total_registered_parents']))
                ->description(__('Parents who registered in the selected period'))
                ->descriptionIcon('heroicon-m-user-plus')
                ->chart($parents)
                ->url(UserResource::canViewAny() ? UserResource::getUrl('index') : null)
                ->color('primary'),

            Stat::make(__('New Children Profiles'), Number::format($kpis['active_children_profiles']))
                ->description(__('Child profiles added in the selected period'))
                ->descriptionIcon('heroicon-m-face-smile')
                ->chart($children)
                ->url(ChildResource::canViewAny() ? ChildResource::getUrl('index') : null)
                ->color('success'),

            Stat::make(__('Active Subscriptions'), Number::format($kpis['active_subscription_plans']))
                ->description(__('Subscriptions running today (not affected by the dates)'))
                ->descriptionIcon('heroicon-m-check-badge')
                ->url(SubscriptionPackageResource::canViewAny() ? SubscriptionPackageResource::getUrl('index') : null)
                ->color('warning'),

            Stat::make(__('Executed Therapy Sessions'), Number::format($kpis['executed_therapy_sessions']))
                ->description(__('Success rate :rate of all attempts', ['rate' => $kpis['average_progress_percentage']]))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($sessions)
                ->color('info'),
        ];
    }
}
