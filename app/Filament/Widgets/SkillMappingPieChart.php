<?php

namespace App\Filament\Widgets;

use App\Models\Subscription;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class SkillMappingPieChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Active Plans Breakdown';

    protected static ?int $sort = 3;

    public function getHeading(): string
    {
        return __('Active Plans Breakdown');
    }

    protected function getData(): array
    {
        $period = $this->filters['period'] ?? 'monthly';
        $startDate = match ($period) {
            'daily' => Carbon::today(),
            'yearly' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth(),
        };

        $plans = Subscription::select('subscription_packages.name', DB::raw('count(subscriptions.id) as total'))
            ->join('subscription_packages', 'subscriptions.subscription_package_id', '=', 'subscription_packages.id')
            ->where('subscriptions.status', \App\Enums\SubscriptionStatusEnum::ACTIVE)
            ->where('subscriptions.created_at', '>=', $startDate)
            ->groupBy('subscription_packages.name')
            ->get();

        if ($plans->isEmpty()) {
            return [];
        }

        $labels = [];
        $data = [];
        $colors = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'];

        foreach ($plans as $index => $plan) {
            $nameData = json_decode($plan->name, true);
            $labels[] = is_array($nameData) ? ($nameData[app()->getLocale()] ?? collect($nameData)->first()) : $plan->name;
            $data[] = $plan->total;
        }

        return [
            'datasets' => [
                [
                    'label' => __('Active Plans'),
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    public function getDescription(): ?string
    {
        $data = $this->getData();
        if (empty($data) || empty($data['datasets'])) {
            return __('No data available for the selected period.');
        }

        return null;
    }
}
