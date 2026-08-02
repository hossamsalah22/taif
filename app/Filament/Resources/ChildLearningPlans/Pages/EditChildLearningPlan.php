<?php

namespace App\Filament\Resources\ChildLearningPlans\Pages;

use App\Filament\Resources\ChildLearningPlans\ChildLearningPlanResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditChildLearningPlan extends EditRecord
{
    protected static string $resource = ChildLearningPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
