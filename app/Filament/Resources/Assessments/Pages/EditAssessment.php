<?php

namespace App\Filament\Resources\Assessments\Pages;

use App\Enums\AssessmentStatusEnum;
use App\Filament\Resources\Assessments\AssessmentResource;
use App\Models\Assessment;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditAssessment extends EditRecord
{
    protected static string $resource = AssessmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getSaveFormAction(): \Filament\Actions\Action
    {
        return parent::getSaveFormAction()
            ->label(__('Publish Updated Package'));
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // First deprecate the old active version with the same autism level
        if ($record->status === AssessmentStatusEnum::DRAFT) {
            Assessment::where('autism_level', $record->autism_level)
                ->where('status', AssessmentStatusEnum::ACTIVE)
                ->where('id', '!=', $record->id)
                ->update(['status' => AssessmentStatusEnum::DEPRECATED->value]);

            $data['status'] = AssessmentStatusEnum::ACTIVE->value;
        }

        $record->update($data);

        return $record;
    }
}
