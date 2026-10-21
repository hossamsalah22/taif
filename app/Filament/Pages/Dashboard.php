<?php

namespace App\Filament\Pages;

use App\Exports\DashboardAnalyticsExport;
use App\Services\AnalyticsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Maatwebsite\Excel\Facades\Excel;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function persistsFiltersInSession(): bool
    {
        return false;
    }

    /**
     * @return array{startDate: string, endDate: string}
     */
    public function getDefaultFilters(): array
    {
        return [
            'startDate' => now()->subMonth()->format('Y-m-d'),
            'endDate' => now()->format('Y-m-d'),
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }

    /**
     * Same as Filament's filters form but not live: the widgets refresh only when "Filter" is pressed.
     */
    public function getFiltersForm(): Schema
    {
        if ((! $this->isCachingSchemas) && $this->hasCachedSchema('filtersForm')) {
            return $this->getSchema('filtersForm');
        }

        return $this->filtersForm(
            $this->makeSchema()
                ->extraAttributes(['wire:partial' => 'table-filters-form'])
                ->statePath('filters'),
        );
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->description(__('Pick a quick range, or choose dates and press Filter. Every card and chart below follows these dates.'))
                    ->schema([
                        Actions::make(collect($this->rangePresets())
                            ->map(fn (array $preset, string $key): Action => Action::make($key)
                                ->label($preset['label'])
                                ->size('sm')
                                ->color(fn (): string => $this->isActivePreset($key) ? 'primary' : 'gray')
                                ->outlined(fn (): bool => ! $this->isActivePreset($key))
                                ->action(fn () => $this->applyPreset($key)))
                            ->values()
                            ->all()),
                        Grid::make(['default' => 12])
                            // Buttons sit on the same line as the inputs, not next to their labels.
                            ->extraAttributes(['style' => 'align-items: end;'])
                            ->schema([
                                DatePicker::make('startDate')
                                    ->label(__('Start Date'))
                                    ->default(now()->subMonth()->format('Y-m-d'))
                                    ->maxDate(now())
                                    ->columnSpan(['default' => 12, 'md' => 5]),
                                DatePicker::make('endDate')
                                    ->label(__('End Date'))
                                    ->default(now()->format('Y-m-d'))
                                    ->maxDate(now())
                                    ->afterOrEqual('startDate')
                                    ->validationMessages([
                                        'after_or_equal' => __('The end date cannot be before the start date.'),
                                    ])
                                    ->columnSpan(['default' => 12, 'md' => 4]),
                                Actions::make([
                                    Action::make('filter')
                                        ->label(__('Filter'))
                                        ->icon('heroicon-m-funnel')
                                        ->action(fn () => $this->applyFilters()),
                                    Action::make('reset')
                                        ->label(__('Reset'))
                                        ->icon('heroicon-m-x-mark')
                                        ->color('gray')
                                        ->outlined()
                                        ->action(function (): void {
                                            $this->filters = $this->getDefaultFilters();
                                            $this->getFiltersForm()->fill($this->filters);
                                        }),
                                ])
                                    ->columnSpan(['default' => 12, 'md' => 3])
                                    ->alignStart(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Validates the picked dates and refreshes every widget with them.
     */
    public function applyFilters(): void
    {
        $this->filters = $this->getFiltersForm()->getState();
    }

    public function applyPreset(string $preset): void
    {
        $range = $this->rangePresets()[$preset] ?? null;

        if ($range === null) {
            return;
        }

        $this->filters = ['startDate' => $range['start'], 'endDate' => $range['end']];
        $this->getFiltersForm()->fill($this->filters);
    }

    /**
     * One-click ranges, all ending today.
     *
     * @return array<string, array{label: string, start: string, end: string}>
     */
    private function rangePresets(): array
    {
        $today = now()->format('Y-m-d');

        return [
            'last7Days' => ['label' => __('Last 7 days'), 'start' => now()->subDays(6)->format('Y-m-d'), 'end' => $today],
            'last30Days' => ['label' => __('Last 30 days'), 'start' => now()->subDays(29)->format('Y-m-d'), 'end' => $today],
            'thisMonth' => ['label' => __('This month'), 'start' => now()->startOfMonth()->format('Y-m-d'), 'end' => $today],
            'thisYear' => ['label' => __('This year'), 'start' => now()->startOfYear()->format('Y-m-d'), 'end' => $today],
        ];
    }

    private function isActivePreset(string $preset): bool
    {
        $range = $this->rangePresets()[$preset];

        return ($this->filters['startDate'] ?? null) === $range['start']
            && ($this->filters['endDate'] ?? null) === $range['end'];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportPdf')
                ->label(__('Export PDF'))
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action(function () {
                    $data = $this->dashboardKpis();

                    $pdf = Pdf::loadView('pdf.analytics', ['data' => $data]);

                    return response()->streamDownload(fn () => print ($pdf->output()), $this->exportFileName('pdf'));
                }),
            Action::make('exportExcel')
                ->label(__('Export Excel'))
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->action(function () {
                    return Excel::download(
                        new DashboardAnalyticsExport($this->dashboardKpis()),
                        $this->exportFileName('xlsx')
                    );
                }),
        ];
    }

    /**
     * e.g. analytics_2026-09-21_to_2026-10-20.pdf
     */
    private function exportFileName(string $extension): string
    {
        $filters = array_filter($this->filters ?? []) + $this->getDefaultFilters();

        return "analytics_{$filters['startDate']}_to_{$filters['endDate']}.{$extension}";
    }

    /**
     * KPIs for the date range currently applied on the dashboard.
     */
    private function dashboardKpis(): array
    {
        $filters = array_filter($this->filters ?? []) + $this->getDefaultFilters();

        return AnalyticsService::getDashboardKpis(Carbon::parse($filters['startDate']), Carbon::parse($filters['endDate']));
    }
}
