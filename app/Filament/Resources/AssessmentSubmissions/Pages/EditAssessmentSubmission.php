<?php

namespace App\Filament\Resources\AssessmentSubmissions\Pages;

use App\Filament\Resources\AssessmentSubmissions\AssessmentSubmissionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAssessmentSubmission extends EditRecord
{
    protected static string $resource = AssessmentSubmissionResource::class;

    public function getTitle(): string
    {
        return __('Evaluate');
    }

    public function getBreadcrumb(): string
    {
        return __('Evaluate');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * Same status change as "Evaluate" on the list.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['status'] = $this->getRecord()->statusAfterEvaluation();

        return $data;
    }

    /**
     * Back to the submission that was just edited, not the panel-wide default of the list.
     */
    protected function getRedirectUrl(): string
    {
        return AssessmentSubmissionResource::getUrl('view', ['record' => $this->getRecord()]);
    }
}
