<?php

namespace App\Filament\Resources\ClinicalProgressReports\Pages;

use App\Filament\Resources\ClinicalProgressReports\ClinicalProgressReportResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClinicalProgressReport extends EditRecord
{
    protected static string $resource = ClinicalProgressReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
