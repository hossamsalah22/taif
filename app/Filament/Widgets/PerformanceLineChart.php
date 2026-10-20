<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithDashboardRange;
use App\Models\ExerciseInteractionLog;
use Filament\Widgets\ChartWidget;

class PerformanceLineChart extends ChartWidget
{
    use InteractsWithDashboardRange;

    protected static ?int $sort = 3;

    protected ?string $pollingInterval = null;

    protected ?string $maxHeight = '300px';

    protected int|string|array $columnSpan = ['default' => 'full', 'lg' => 1];

    public function getHeading(): string
    {
        return __('Performance & Executed Sessions');
    }

    public function getDescription(): ?string
    {
        return __('All exercise attempts vs. the successful ones, per day (per month for ranges over two months).');
    }

    protected function getData(): array
    {
        [$labels, $sessions] = $this->series(ExerciseInteractionLog::query());
        [, $successful] = $this->series(ExerciseInteractionLog::query()->where('is_successful', true));

        return [
            'datasets' => [
                [
                    'label' => __('Executed Therapy Sessions'),
                    'data' => $sessions,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.15)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => __('Successful'),
                    'data' => $successful,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
            'plugins' => ['legend' => ['position' => 'bottom']],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
