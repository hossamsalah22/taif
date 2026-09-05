<?php

namespace App\Services;

use App\Enums\SubscriptionStatusEnum;
use App\Models\Child;
use App\Models\ExerciseInteractionLog;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;

class AnalyticsService
{
    public static function getDashboardKpis(string $period = 'monthly'): array
    {
        $startDate = match ($period) {
            'daily' => Carbon::today(),
            'yearly' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth(),
        };

        $totalParents = User::where('created_at', '>=', $startDate)->count();

        $activeChildren = Child::where('created_at', '>=', $startDate)
            ->count();

        $activeSubscriptions = Subscription::where('status', SubscriptionStatusEnum::ACTIVE)
            ->where('start_date', '<=', now())
            ->where('expiry_date', '>=', now())
            ->count();

        $executedSessions = ExerciseInteractionLog::where('created_at', '>=', $startDate)->count();

        $totalLogs = $executedSessions;
        $successfulLogs = ExerciseInteractionLog::where('created_at', '>=', $startDate)->where('is_successful', true)->count();
        $averageProgress = $totalLogs > 0 ? round(($successfulLogs / $totalLogs) * 100, 2) : 0;

        $totalRevenue = Subscription::where('subscriptions.created_at', '>=', $startDate)
            ->join('subscription_packages', 'subscriptions.subscription_package_id', '=', 'subscription_packages.id')
            ->sum('subscription_packages.price');

        return [
            'period' => $period,
            'total_registered_parents' => $totalParents,
            'active_children_profiles' => $activeChildren,
            'active_subscription_plans' => $activeSubscriptions,
            'executed_therapy_sessions' => $executedSessions,
            'average_progress_percentage' => $averageProgress.'%',
            'total_revenue_sar' => $totalRevenue,
        ];
    }
}
