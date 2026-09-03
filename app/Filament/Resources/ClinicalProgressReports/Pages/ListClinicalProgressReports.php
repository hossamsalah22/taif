<?php

namespace App\Filament\Resources\ClinicalProgressReports\Pages;

use App\Filament\Resources\ClinicalProgressReports\ClinicalProgressReportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClinicalProgressReports extends ListRecords
{
    protected static string $resource = ClinicalProgressReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
