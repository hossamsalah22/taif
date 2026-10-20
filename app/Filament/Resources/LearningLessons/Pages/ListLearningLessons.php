<?php

namespace App\Filament\Resources\LearningLessons\Pages;

use App\Filament\Resources\LearningLessons\LearningLessonResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLearningLessons extends ListRecords
{
    protected static string $resource = LearningLessonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getSubheading(): ?string
    {
        return __('A lesson is one step of a goal. To add or edit its exercises, open the goal it belongs to.');
    }
}
