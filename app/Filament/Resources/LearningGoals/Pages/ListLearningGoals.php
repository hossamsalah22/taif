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

    public function getSubheading(): ?string
    {
        return __('A goal is a skill the child learns. Its lessons are added inside the goal form, and one goal can be shared by several plans.');
    }
}
