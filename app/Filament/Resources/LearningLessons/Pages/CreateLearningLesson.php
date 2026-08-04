<?php

namespace App\Filament\Resources\LearningLessons\Pages;

use App\Filament\Resources\LearningLessons\LearningLessonResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLearningLesson extends CreateRecord
{
    protected static string $resource = LearningLessonResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
