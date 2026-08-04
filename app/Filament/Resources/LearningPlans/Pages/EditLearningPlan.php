<?php

namespace App\Filament\Resources\LearningPlans\Pages;

use App\Filament\Resources\LearningPlans\LearningPlanResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditLearningPlan extends EditRecord
{
    protected static string $resource = LearningPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ViewAction::make(),
            // DeleteAction::make(),
        ];
    }

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function mount(int | string $record): void
    {
        parent::mount($record);
        $this->record->loadMissing('goals.lessons.exercises');
    }
}
