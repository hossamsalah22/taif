<?php

namespace App\Filament\Resources\LearningPlans\Pages;

use App\Filament\Resources\LearningPlans\LearningPlanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLearningPlan extends CreateRecord
{
    protected static string $resource = LearningPlanResource::class;

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
