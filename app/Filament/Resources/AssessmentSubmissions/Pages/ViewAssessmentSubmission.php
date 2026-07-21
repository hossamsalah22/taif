<?php

namespace App\Filament\Resources\AssessmentSubmissions\Pages;

use App\Filament\Resources\AssessmentSubmissions\AssessmentSubmissionResource;
use Filament\Resources\Pages\ViewRecord;

class ViewAssessmentSubmission extends ViewRecord
{
    protected static string $resource = AssessmentSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\EditAction::make(),
            \Filament\Actions\Action::make('publish')
                ->label(__('Publish Report'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (\App\Models\AssessmentSubmission $record) => $record->status !== 'published')
                ->action(function (\App\Models\AssessmentSubmission $record) {
                    $record->update([
                        'status' => 'published',
                        'report_published_at' => now(),
                    ]);
                    $record->child->parent->notify(new \App\Notifications\ReportPublishedNotification($record));
                    \Filament\Notifications\Notification::make()->title(__('Report Published Successfully'))->success()->send();
                }),
        ];
    }
}
