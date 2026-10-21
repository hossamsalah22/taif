<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ChildLearningPlans\ChildLearningPlanResource;
use App\Filament\Resources\Children\ChildResource;
use App\Filament\Resources\Subscriptions\SubscriptionResource;
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

    public function getDescription(): ?string
    {
        return __('New parents, new child profiles and exercise attempts between the selected dates, compared with the same number of days just before. Success rate of the attempts: :rate.', [
            'rate' => AnalyticsService::cachedDashboardKpis($this->rangeStart(), $this->rangeEnd())['average_progress_percentage'],
        ]);
    }

    protected function getColumns(): int|array
    {
        return ['default' => 1, 'sm' => 2, 'xl' => 4];
    }

    protected function getStats(): array
    {
        [$kpis, $previous] = $this->kpisWithPreviousPeriod();

        [, $parents] = $this->series(User::query());
        [, $children] = $this->series(Child::query());
        [, $sessions] = $this->series(ExerciseInteractionLog::query());

        return [
            $this->compareWithPreviousPeriod(
                Stat::make(__('New Parents'), Number::format($kpis['total_registered_parents'])),
                $kpis['total_registered_parents'],
                $previous['total_registered_parents'],
            )
                ->chart($parents)
                ->url(UserResource::canViewAny() ? UserResource::getUrl('index') : null)
                ->color('primary'),

            $this->compareWithPreviousPeriod(
                Stat::make(__('New Children Profiles'), Number::format($kpis['active_children_profiles'])),
                $kpis['active_children_profiles'],
                $previous['active_children_profiles'],
            )
                ->chart($children)
                ->url(ChildResource::canViewAny() ? ChildResource::getUrl('index') : null)
                ->color('success'),

            Stat::make(__('Active Subscriptions'), Number::format($kpis['active_subscription_plans']))
                ->description(__('Subscriptions running today (not affected by the dates)'))
                ->descriptionIcon('heroicon-m-check-badge')
                ->url(SubscriptionResource::canViewAny() ? SubscriptionResource::getUrl('index', [
                    'filters' => ['running_now' => ['isActive' => true]],
                ]) : null)
                ->color('warning'),

            $this->compareWithPreviousPeriod(
                Stat::make(__('Executed Therapy Sessions'), Number::format($kpis['executed_therapy_sessions'])),
                $kpis['executed_therapy_sessions'],
                $previous['executed_therapy_sessions'],
            )
                ->chart($sessions)
                ->url(ChildLearningPlanResource::canViewAny() ? ChildLearningPlanResource::getUrl('index') : null)
                ->color('info'),
        ];
    }
}
