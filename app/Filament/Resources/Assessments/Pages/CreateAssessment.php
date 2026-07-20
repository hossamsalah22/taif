<?php

namespace App\Filament\Resources\Assessments\Pages;

use App\Enums\AssessmentStatusEnum;
use App\Filament\Resources\Assessments\AssessmentResource;
use App\Models\Assessment;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAssessment extends CreateRecord
{
    protected static string $resource = AssessmentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $existingActive = Assessment::where('autism_level', $data['autism_level'])
            ->where('status', AssessmentStatusEnum::ACTIVE)
            ->first();

        if ($existingActive) {
            $data['version'] = $existingActive->version + 1;
            $existingActive->update(['status' => AssessmentStatusEnum::DEPRECATED->value]);
        } else {
            $data['version'] = 1;
        }

        $data['status'] = AssessmentStatusEnum::ACTIVE->value;

        return parent::handleRecordCreation($data);
    }
}
