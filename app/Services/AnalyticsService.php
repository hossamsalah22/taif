<?php

namespace App\Services;

use App\Models\Child;
use App\Models\ExerciseInteractionLog;
use App\Models\Subscription;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;

class AnalyticsService
{
    /**
     * Same as getDashboardKpis(), kept for a minute so the dashboard widgets (each loaded in its own request)
     * and the previous-period comparison do not run the same queries again.
     *
     * @return array<string, mixed>
     */
    public static function cachedDashboardKpis(CarbonInterface $startDate, CarbonInterface $endDate): array
    {
        $key = 'dashboard-kpis:'.$startDate->format('Y-m-d').':'.$endDate->format('Y-m-d');

        return Cache::remember($key, now()->addMinute(), fn (): array => static::getDashboardKpis($startDate, $endDate));
    }

    /**
     * The period of the same length that ends the day before the given one starts.
     *
     * @return array{0: CarbonInterface, 1: CarbonInterface}
     */
    public static function previousPeriod(CarbonInterface $startDate, CarbonInterface $endDate): array
    {
        $days = (int) $startDate->copy()->startOfDay()->diffInDays($endDate->copy()->startOfDay()) + 1;
        $previousEnd = $startDate->copy()->subDay()->endOfDay();

        return [$previousEnd->copy()->subDays($days - 1)->startOfDay(), $previousEnd];
    }

    /**
     * Dashboard KPIs for the selected date range (both ends inclusive).
     *
     * Revenue is what parents actually paid (tax included); it is split into tax, platform commission
     * and service provider share with the current rates from the settings.
     *
     * @return array{period: string, total_registered_parents: int, active_children_profiles: int, active_subscription_plans: int, executed_therapy_sessions: int, average_progress_percentage: string, total_revenue_sar: float, revenue_tax_sar: float, revenue_net_sar: float, platform_commission_sar: float, provider_share_sar: float}
     */
    public static function getDashboardKpis(CarbonInterface $startDate, CarbonInterface $endDate): array
    {
        $range = [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()];

        $totalParents = User::whereBetween('created_at', $range)->count();

        $activeChildren = Child::whereBetween('created_at', $range)->count();

        $activeSubscriptions = Subscription::query()->runningNow()->count();

        $executedSessions = ExerciseInteractionLog::whereBetween('created_at', $range)->count();
        $successfulLogs = ExerciseInteractionLog::whereBetween('created_at', $range)->where('is_successful', true)->count();
        $averageProgress = $executedSessions > 0 ? round(($successfulLogs / $executedSessions) * 100, 2) : 0;

        $totalRevenue = (float) Subscription::whereBetween('created_at', $range)->sum('amount_paid');
        $revenue = SubscriptionPricing::calculate($totalRevenue);

        return [
            'period' => $range[0]->format('Y-m-d').' - '.$range[1]->format('Y-m-d'),
            'total_registered_parents' => $totalParents,
            'active_children_profiles' => $activeChildren,
            'active_subscription_plans' => $activeSubscriptions,
            'executed_therapy_sessions' => $executedSessions,
            'average_progress_percentage' => $averageProgress.'%',
            'total_revenue_sar' => $revenue['price'],
            'revenue_tax_sar' => $revenue['tax_amount'],
            'revenue_net_sar' => $revenue['net_amount'],
            'platform_commission_sar' => $revenue['platform_commission'],
            'provider_share_sar' => $revenue['provider_amount'],
        ];
    }
}
