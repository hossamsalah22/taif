<?php

namespace App\Filament\Resources\LearningPlans\Pages;

use App\Filament\Resources\LearningPlans\LearningPlanResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLearningPlan extends ViewRecord
{
    protected static string $resource = LearningPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // EditAction::make(),
        ];
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);
        $this->record->loadMissing('goals.lessons.exercises');
    }
}
