<?php

namespace App\Filament\Resources\LearningGoals\Pages;

use App\Filament\Resources\LearningGoals\LearningGoalResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditLearningGoal extends EditRecord
{
    protected static string $resource = LearningGoalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
