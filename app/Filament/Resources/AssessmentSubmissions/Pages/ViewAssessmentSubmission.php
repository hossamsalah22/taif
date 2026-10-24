<?php

namespace App\Filament\Resources\AssessmentSubmissions\Pages;

use App\Enums\AssessmentSubmissionStatusEnum;
use App\Filament\Resources\AssessmentSubmissions\AssessmentSubmissionResource;
use App\Models\AssessmentSubmission;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewAssessmentSubmission extends ViewRecord
{
    protected static string $resource = AssessmentSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('publish')
                ->label(__('Publish Report'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (AssessmentSubmission $record) => $record->status !== AssessmentSubmissionStatusEnum::PUBLISHED)
                ->action(function (AssessmentSubmission $record) {
                    $record->publish();
                    Notification::make()->title(__('Report Published Successfully'))->success()->send();
                }),
        ];
    }
}
