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
                    ->description(__('Pick a date range and press Filter. Every card and chart below follows these dates.'))
                    ->schema([
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
                                    ->columnSpan(['default' => 12, 'md' => 4]),
                                Actions::make([
                                    Action::make('filter')
                                        ->label(__('Filter'))
                                        ->icon('heroicon-m-funnel')
                                        ->action(function (): void {
                                            $this->filters = $this->getFiltersForm()->getState();
                                        }),
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

                    return response()->streamDownload(fn () => print ($pdf->output()), 'analytics_'.now()->format('Y_m_d').'.pdf');
                }),
            Action::make('exportExcel')
                ->label(__('Export Excel'))
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->action(function () {
                    return Excel::download(
                        new DashboardAnalyticsExport($this->dashboardKpis()),
                        'analytics_'.now()->format('Y_m_d').'.xlsx'
                    );
                }),
        ];
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
