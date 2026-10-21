<?php

namespace App\Filament\Widgets;

use App\Enums\SubscriptionStatusEnum;
use App\Filament\Widgets\Concerns\InteractsWithDashboardRange;
use App\Models\Subscription;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SkillMappingPieChart extends ChartWidget
{
    use InteractsWithDashboardRange;

    protected static ?int $sort = 4;

    protected ?string $pollingInterval = null;

    protected ?string $maxHeight = '300px';

    protected int|string|array $columnSpan = ['default' => 'full', 'lg' => 1];

    public function getHeading(): string
    {
        return __('Active Plans Breakdown');
    }

    public function getDescription(): ?string
    {
        return $this->getData()['datasets'][0]['data'] === []
            ? __('No data available for the selected period.')
            : __('Active subscriptions started in the selected period, by package.');
    }

    protected function getData(): array
    {
        $plans = Subscription::select('subscription_packages.name', DB::raw('count(subscriptions.id) as total'))
            ->join('subscription_packages', 'subscriptions.subscription_package_id', '=', 'subscription_packages.id')
            ->where('subscriptions.status', SubscriptionStatusEnum::ACTIVE)
            ->whereBetween('subscriptions.created_at', [$this->rangeStart(), $this->rangeEnd()])
            ->groupBy('subscription_packages.name')
            ->get();

        $palette = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899', '#84cc16', '#f97316', '#64748b'];
        // Repeat the palette so every package gets a colour, however many there are.
        $colors = $plans->keys()->map(fn (int $index): string => $palette[$index % count($palette)])->all();

        $labels = $plans->map(function (Subscription $plan): string {
            $name = json_decode($plan->name, true);

            return is_array($name) ? ($name[app()->getLocale()] ?? collect($name)->first()) : $plan->name;
        })->all();

        return [
            'datasets' => [
                [
                    'label' => __('Active Plans'),
                    'data' => $plans->pluck('total')->all(),
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => ['x' => ['display' => false], 'y' => ['display' => false]],
            'plugins' => ['legend' => ['position' => 'bottom']],
            'cutout' => '62%',
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
