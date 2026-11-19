<?php

namespace App\Filament\Resources\LearningPlans;

use App\Enums\NavigationGroupEnum;
use App\Filament\Resources\LearningPlans\Pages\CreateLearningPlan;
use App\Filament\Resources\LearningPlans\Pages\EditLearningPlan;
use App\Filament\Resources\LearningPlans\Pages\ListLearningPlans;
use App\Filament\Resources\LearningPlans\Pages\ViewLearningPlan;
use App\Filament\Resources\LearningPlans\Schemas\LearningPlanForm;
use App\Filament\Resources\LearningPlans\Schemas\LearningPlanInfolist;
use App\Filament\Resources\LearningPlans\Tables\LearningPlansTable;
use App\Filament\Resources\MainResource;
use App\Models\LearningPlan;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LearningPlanResource extends MainResource
{
    protected static ?string $model = LearningPlan::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroupEnum::CONTENT;

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    /**
     * One sidebar entry for the whole learning content workspace; goals, lessons and rewards are tabs inside it.
     */
    public static function getNavigationLabel(): string
    {
        return __('Learning content');
    }

    /**
     * @return array<int, string>
     */
    public static function getNavigationItemActiveRoutePattern(): array
    {
        return array_map(
            fn (string $resource): string => $resource::getRouteBaseName().'.*',
            ListLearningPlans::learningContentResources(),
        );
    }

    public static function form(Schema $schema): Schema
    {
        return LearningPlanForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LearningPlanInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LearningPlansTable::configure($table);
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
            'index' => ListLearningPlans::route('/'),
            'create' => CreateLearningPlan::route('/create'),
            'view' => ViewLearningPlan::route('/{record}'),
            'edit' => EditLearningPlan::route('/{record}/edit'),
        ];
    }
}
