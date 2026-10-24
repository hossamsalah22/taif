<?php

namespace App\Filament\Resources\ChildLearningPlans;

use App\Enums\NavigationGroupEnum;
use App\Filament\Resources\ChildLearningPlans\Pages\CreateChildLearningPlan;
use App\Filament\Resources\ChildLearningPlans\Pages\EditChildLearningPlan;
use App\Filament\Resources\ChildLearningPlans\Pages\ListChildLearningPlans;
use App\Filament\Resources\ChildLearningPlans\Pages\ViewChildLearningPlan;
use App\Filament\Resources\ChildLearningPlans\Pages\ViewChildLearningPlanProgress;
use App\Filament\Resources\ChildLearningPlans\Schemas\ChildLearningPlanForm;
use App\Filament\Resources\ChildLearningPlans\Schemas\ChildLearningPlanInfolist;
use App\Filament\Resources\ChildLearningPlans\Tables\ChildLearningPlansTable;
use App\Filament\Resources\MainResource;
use App\Models\ChildLearningPlan;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ChildLearningPlanResource extends MainResource
{
    protected static ?string $model = ChildLearningPlan::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroupEnum::FAMILIES;

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $recordTitleAttribute = 'id';

    /**
     * "Salma — Communication basics" in titles and breadcrumbs instead of the bare row id.
     */
    public static function getRecordTitle(?Model $record): string|Htmlable|null
    {
        if (! $record instanceof ChildLearningPlan) {
            return parent::getRecordTitle($record);
        }

        return "{$record->child?->name} — {$record->learningPlan?->name}";
    }

    public static function form(Schema $schema): Schema
    {
        return ChildLearningPlanForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ChildLearningPlanInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChildLearningPlansTable::configure($table);
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
            'index' => ListChildLearningPlans::route('/'),
            'create' => CreateChildLearningPlan::route('/create'),
            'view' => ViewChildLearningPlan::route('/{record}'),
            'edit' => EditChildLearningPlan::route('/{record}/edit'),
            'progress' => ViewChildLearningPlanProgress::route('/{record}/progress'),
        ];
    }
}
