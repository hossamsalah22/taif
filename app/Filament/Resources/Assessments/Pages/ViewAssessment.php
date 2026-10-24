<?php

namespace App\Filament\Resources\Assessments\Pages;

use App\Enums\AssessmentStatusEnum;
use App\Filament\Resources\Assessments\AssessmentResource;
use App\Models\Assessment;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAssessment extends ViewRecord
{
    protected static string $resource = AssessmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Only drafts are edited in place; an active assessment gets a new version from the list instead.
            EditAction::make()
                ->label(__('Continue Editing'))
                ->visible(fn (Assessment $record): bool => $record->status === AssessmentStatusEnum::DRAFT),
        ];
    }
}
