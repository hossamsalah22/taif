<?php

namespace App\Filament\Resources\AssessmentSubmissions;

use App\Filament\Resources\AssessmentSubmissions\Pages\EditAssessmentSubmission;
use App\Filament\Resources\AssessmentSubmissions\Pages\ListAssessmentSubmissions;
use App\Filament\Resources\AssessmentSubmissions\Pages\ViewAssessmentSubmission;
use App\Filament\Resources\AssessmentSubmissions\Schemas\AssessmentSubmissionForm;
use App\Filament\Resources\AssessmentSubmissions\Schemas\AssessmentSubmissionInfolist;
use App\Filament\Resources\AssessmentSubmissions\Tables\AssessmentSubmissionsTable;
use App\Filament\Resources\MainResource;
use App\Models\AssessmentSubmission;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssessmentSubmissionResource extends MainResource
{
    protected static ?string $model = AssessmentSubmission::class;

    public static function getNavigationGroup(): ?string
    {
        return __('Assessments Management');
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return AssessmentSubmissionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AssessmentSubmissionInfolist::configure($schema);
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

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssessmentSubmissions::route('/'),
            'view' => ViewAssessmentSubmission::route('/{record}'),
            'edit' => EditAssessmentSubmission::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'answers.question.options.media',
                'answers.question.matchingPairs.media',
                'answers.question.orderingSteps.media',
            ]);
    }
}
