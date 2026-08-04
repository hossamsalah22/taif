<?php

namespace App\Filament\Resources\LearningGoals\Pages;

use App\Filament\Resources\LearningGoals\LearningGoalResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLearningGoal extends CreateRecord
{
    protected static string $resource = LearningGoalResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
