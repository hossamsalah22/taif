<?php

namespace App\Filament\Pages;

use App\Exports\DashboardAnalyticsExport;
use App\Services\AnalyticsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;
use Maatwebsite\Excel\Facades\Excel;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function filtersForm(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('period')
                    ->label(__('Time Period'))
                    ->options([
                        'daily' => __('Daily'),
                        'monthly' => __('Monthly'),
                        'yearly' => __('Yearly'),
                    ])
                    ->default('monthly')
                    ->selectablePlaceholder(false),
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
                    $period = $this->filters['period'] ?? 'monthly';
                    $data = AnalyticsService::getDashboardKpis($period);

                    $pdf = Pdf::loadView('pdf.analytics', ['data' => $data]);

                    return response()->streamDownload(fn () => print ($pdf->output()), 'analytics_'.$period.'_'.now()->format('Y_m_d').'.pdf');
                }),
            Action::make('exportExcel')
                ->label(__('Export Excel'))
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->action(function () {
                    $period = $this->filters['period'] ?? 'monthly';
                    $data = AnalyticsService::getDashboardKpis($period);

                    return Excel::download(
                        new DashboardAnalyticsExport($data),
                        'analytics_'.$period.'_'.now()->format('Y_m_d').'.xlsx'
                    );
                }),
        ];
    }
}
