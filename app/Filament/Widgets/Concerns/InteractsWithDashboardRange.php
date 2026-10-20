<?php

namespace App\Filament\Widgets\Concerns;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Database\Eloquent\Builder;

/**
 * Shared by every dashboard widget: reads the page date filter (defaults to the last month up to today)
 * and builds per-day / per-month series for charts and sparklines.
 */
trait InteractsWithDashboardRange
{
    use InteractsWithPageFilters;

    protected function rangeStart(): CarbonInterface
    {
        return Carbon::parse($this->pageFilters['startDate'] ?? now()->subMonth())->startOfDay();
    }

    protected function rangeEnd(): CarbonInterface
    {
        return Carbon::parse($this->pageFilters['endDate'] ?? now())->endOfDay();
    }

    /** Daily buckets for ranges up to ~2 months, monthly buckets beyond that. */
    protected function groupsByMonth(): bool
    {
        return $this->rangeStart()->diffInDays($this->rangeEnd()) > 62;
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, float>} labels and values (zero-filled)
     */
    protected function series(Builder $query, string $dateColumn = 'created_at', ?string $sumColumn = null): array
    {
        $byMonth = $this->groupsByMonth();

        $trend = Trend::query($query)
            ->dateColumn($dateColumn)
            ->between(start: $this->rangeStart(), end: $this->rangeEnd());
        $trend = $byMonth ? $trend->perMonth() : $trend->perDay();
        $data = $sumColumn ? $trend->sum($sumColumn) : $trend->count();

        return [
            $data->map(fn (TrendValue $value): string => $byMonth
                ? Carbon::createFromFormat('!Y-m', $value->date)->translatedFormat('M Y')
                : Carbon::parse($value->date)->translatedFormat('d M'))->all(),
            $data->map(fn (TrendValue $value): float => round((float) $value->aggregate, 2))->all(),
        ];
    }
}
