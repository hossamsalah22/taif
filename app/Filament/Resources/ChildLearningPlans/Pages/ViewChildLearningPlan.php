<?php

namespace App\Filament\Resources\ChildLearningPlans\Pages;

use App\Filament\Resources\ChildLearningPlans\ChildLearningPlanResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewChildLearningPlan extends ViewRecord
{
    protected static string $resource = ChildLearningPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
