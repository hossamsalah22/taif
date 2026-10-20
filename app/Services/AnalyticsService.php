<?php

namespace App\Services;

use App\Enums\SubscriptionStatusEnum;
use App\Models\Child;
use App\Models\ExerciseInteractionLog;
use App\Models\Subscription;
use App\Models\User;
use Carbon\CarbonInterface;

class AnalyticsService
{
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

        $activeSubscriptions = Subscription::where('status', SubscriptionStatusEnum::ACTIVE)
            ->where('start_date', '<=', now())
            ->where('expiry_date', '>=', now())
            ->count();

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
