<?php

namespace App\Filament\Widgets\Concerns;

use App\Services\AnalyticsService;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget\Stat;
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

    /**
     * KPIs for the selected range and for the same number of days just before it.
     *
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    protected function kpisWithPreviousPeriod(): array
    {
        [$previousStart, $previousEnd] = AnalyticsService::previousPeriod($this->rangeStart(), $this->rangeEnd());

        return [
            AnalyticsService::cachedDashboardKpis($this->rangeStart(), $this->rangeEnd()),
            AnalyticsService::cachedDashboardKpis($previousStart, $previousEnd),
        ];
    }

    /**
     * Adds "+12% vs previous 30 days" with a matching arrow and colour to a stat card.
     */
    protected function compareWithPreviousPeriod(Stat $stat, float $current, float $previous): Stat
    {
        $days = (int) $this->rangeStart()->diffInDays($this->rangeEnd()->copy()->startOfDay()) + 1;

        if ($current == $previous) {
            return $stat->description(__('Same as the previous :days days', ['days' => $days]))
                ->descriptionIcon('heroicon-m-minus');
        }

        if ($previous == 0) {
            return $stat->description(__('Up from none in the previous :days days', ['days' => $days]))
                ->descriptionIcon('heroicon-m-arrow-trending-up');
        }

        $change = (int) round(($current - $previous) / $previous * 100);

        return $stat->description(__(':change% vs the previous :days days', [
            'change' => ($change > 0 ? '+' : '').$change,
            'days' => $days,
        ]))
            ->descriptionIcon($current > $previous ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->descriptionColor($current > $previous ? 'success' : 'danger');
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
