<?php

namespace App\Filament\Widgets;

use App\Models\ExerciseInteractionLog;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class PerformanceLineChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Performance & Executed Sessions';

    protected static ?int $sort = 2;

    public function getHeading(): string
    {
        return __('Performance & Executed Sessions');
    }

    protected function getData(): array
    {
        $period = $this->filters['period'] ?? 'monthly';
        $startDate = match ($period) {
            'daily' => Carbon::now()->subDays(7), // Show last 7 days for daily
            'yearly' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth(),
        };

        $trend = Trend::model(ExerciseInteractionLog::class)
            ->between(
                start: $startDate,
                end: now(),
            );

        $data = match ($period) {
            'daily' => $trend->perDay()->count(),
            'yearly' => $trend->perMonth()->count(),
            default => $trend->perDay()->count(),
        };

        $totalCount = $data->sum('aggregate');

        if ($totalCount === 0) {
            return []; // Return empty array to trigger empty state if we wanted, or we just render 0s. 
            // Wait, we need to show explicitly "No data available for the selected period."
            // We can handle this by returning an empty datasets array.
        }

        return [
            'datasets' => [
                [
                    'label' => __('Executed Therapy Sessions'),
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
                    'fill' => true,
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
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
