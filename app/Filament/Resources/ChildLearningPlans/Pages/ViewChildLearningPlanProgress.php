<?php

namespace App\Filament\Resources\ChildLearningPlans\Pages;

use App\Filament\Resources\ChildLearningPlans\ChildLearningPlanResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class ViewChildLearningPlanProgress extends Page
{
    use InteractsWithRecord;

    protected static string $resource = ChildLearningPlanResource::class;

    public function getTitle(): string | \Illuminate\Contracts\Support\Htmlable
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
                    return Notification::make()
                        ->title(__('Logs exported successfully.'))
                        ->success()
                        ->send();
                    // Note: Here you can integrate Maatwebsite/Laravel-Excel or similar for actual download.
                    // return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ExerciseInteractionLogExport($this->record->child_id), 'logs.xlsx');
                }),
        ];
    }
}
