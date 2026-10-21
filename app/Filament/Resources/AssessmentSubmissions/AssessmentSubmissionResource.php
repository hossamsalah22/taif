<?php

namespace App\Filament\Resources\AssessmentSubmissions;

use App\Enums\NavigationGroupEnum;
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
use UnitEnum;

class AssessmentSubmissionResource extends MainResource
{
    protected static ?string $model = AssessmentSubmission::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroupEnum::OPERATIONS;

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    /**
     * Number of submissions waiting for a specialist review; hidden when there are none.
     */
    public static function getNavigationBadge(): ?string
    {
        $pendingReview = AssessmentSubmission::where('status', 'pending_review')->count();

        return $pendingReview > 0 ? (string) $pendingReview : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return __('Submissions waiting for review');
    }

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
