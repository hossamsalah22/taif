<?php

namespace App\Filament\Resources\LearningLessons\Pages;

use App\Filament\Resources\LearningLessons\LearningLessonResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditLearningLesson extends EditRecord
{
    protected static string $resource = LearningLessonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
