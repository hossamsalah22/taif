<?php

namespace App\Filament\Resources\LearningGoals\Pages;

use App\Filament\Resources\LearningGoals\LearningGoalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLearningGoals extends ListRecords
{
    protected static string $resource = LearningGoalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
