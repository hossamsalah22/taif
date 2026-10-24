<?php

namespace App\Filament\Resources\ChildLearningPlans\Pages;

use App\Exports\ChildPlanInteractionLogsExport;
use App\Filament\Resources\ChildLearningPlans\ChildLearningPlanResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Maatwebsite\Excel\Facades\Excel;

class ViewChildLearningPlanProgress extends Page
{
    use InteractsWithRecord;

    protected static string $resource = ChildLearningPlanResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('View Child Learning Plan Progress');
    }

    protected string $view = 'filament.resources.child-learning-plans.pages.view-child-learning-plan-progress';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->record->loadMissing('learningPlan.goals.lessons.exercises');
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('export_logs')
                ->label(__('Export Logs'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->action(function () {
                    $export = new ChildPlanInteractionLogsExport($this->record);

                    if (! $export->query()->exists()) {
                        Notification::make()
                            ->title(__('No logs to export yet.'))
                            ->body(__('Logs appear once the child plays exercises from this plan in the app.'))
                            ->warning()
                            ->send();

                        return null;
                    }

                    return Excel::download($export, "interaction-logs_{$this->record->getKey()}_".now()->format('Y-m-d').'.xlsx');
                }),
        ];
    }
}
