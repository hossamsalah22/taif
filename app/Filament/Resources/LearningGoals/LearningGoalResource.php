<?php

namespace App\Filament\Resources\LearningGoals;

use App\Filament\Resources\LearningGoals\Pages\CreateLearningGoal;
use App\Filament\Resources\LearningGoals\Pages\EditLearningGoal;
use App\Filament\Resources\LearningGoals\Pages\ListLearningGoals;
use App\Filament\Resources\LearningGoals\Pages\ViewLearningGoal;
use App\Filament\Resources\LearningGoals\Schemas\LearningGoalForm;
use App\Filament\Resources\LearningGoals\Schemas\LearningGoalInfolist;
use App\Filament\Resources\LearningGoals\Tables\LearningGoalsTable;
use App\Filament\Resources\MainResource;
use App\Models\LearningGoal;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LearningGoalResource extends MainResource
{
    protected static ?string $model = LearningGoal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationGroup(): ?string
    {
        return __('Learning Plans Management');
    }

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return LearningGoalForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LearningGoalInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LearningGoalsTable::configure($table);
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
            'index' => ListLearningGoals::route('/'),
            'create' => CreateLearningGoal::route('/create'),
            'view' => ViewLearningGoal::route('/{record}'),
            'edit' => EditLearningGoal::route('/{record}/edit'),
        ];
    }
}
