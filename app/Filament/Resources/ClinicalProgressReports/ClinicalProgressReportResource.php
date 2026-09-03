<?php

namespace App\Filament\Resources\ClinicalProgressReports;

use App\Filament\Resources\ClinicalProgressReports\Pages\CreateClinicalProgressReport;
use App\Filament\Resources\ClinicalProgressReports\Pages\EditClinicalProgressReport;
use App\Filament\Resources\ClinicalProgressReports\Pages\ListClinicalProgressReports;
use App\Filament\Resources\ClinicalProgressReports\Schemas\ClinicalProgressReportForm;
use App\Filament\Resources\ClinicalProgressReports\Tables\ClinicalProgressReportsTable;
use App\Models\ClinicalProgressReport;
use BackedEnum;
use App\Filament\Resources\MainResource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ClinicalProgressReportResource extends MainResource
{
    protected static ?string $model = ClinicalProgressReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationGroup(): ?string
    {
        return __('Reports & Follow-ups');
    }
    
    public static function getModelLabel(): string
    {
        return __('Progress Report');
    }
    
    public static function getPluralModelLabel(): string
    {
        return __('Progress Reports');
    }

    public static function form(Schema $schema): Schema
    {
        return ClinicalProgressReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClinicalProgressReportsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClinicalProgressReports::route('/'),
            'create' => CreateClinicalProgressReport::route('/create'),
            'edit' => EditClinicalProgressReport::route('/{record}/edit'),
        ];
    }
}
