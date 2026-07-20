<?php

namespace App\Filament\Resources\AssessmentSubmissions;

use App\Filament\Resources\AssessmentSubmissions\Pages\CreateAssessmentSubmission;
use App\Filament\Resources\AssessmentSubmissions\Pages\EditAssessmentSubmission;
use App\Filament\Resources\AssessmentSubmissions\Pages\ListAssessmentSubmissions;
use App\Filament\Resources\AssessmentSubmissions\Schemas\AssessmentSubmissionForm;
use App\Filament\Resources\AssessmentSubmissions\Tables\AssessmentSubmissionsTable;
use App\Filament\Resources\MainResource;
use App\Models\AssessmentSubmission;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AssessmentSubmissionResource extends MainResource
{
    protected static ?string $model = AssessmentSubmission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return AssessmentSubmissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssessmentSubmissionsTable::configure($table);
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
            'index' => ListAssessmentSubmissions::route('/'),
            'create' => CreateAssessmentSubmission::route('/create'),
            'edit' => EditAssessmentSubmission::route('/{record}/edit'),
        ];
    }
}
