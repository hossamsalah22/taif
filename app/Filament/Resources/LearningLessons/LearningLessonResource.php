<?php

namespace App\Filament\Resources\LearningLessons;

use App\Enums\NavigationGroupEnum;
use App\Filament\Resources\LearningLessons\Pages\CreateLearningLesson;
use App\Filament\Resources\LearningLessons\Pages\EditLearningLesson;
use App\Filament\Resources\LearningLessons\Pages\ListLearningLessons;
use App\Filament\Resources\LearningLessons\Pages\ViewLearningLesson;
use App\Filament\Resources\LearningLessons\Schemas\LearningLessonForm;
use App\Filament\Resources\LearningLessons\Schemas\LearningLessonInfolist;
use App\Filament\Resources\LearningLessons\Tables\LearningLessonsTable;
use App\Filament\Resources\MainResource;
use App\Models\LearningLesson;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LearningLessonResource extends MainResource
{
    protected static ?string $model = LearningLesson::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroupEnum::LEARNING_CONTENT;

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPlayCircle;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return LearningLessonForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LearningLessonInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LearningLessonsTable::configure($table);
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
            'index' => ListLearningLessons::route('/'),
            'create' => CreateLearningLesson::route('/create'),
            'view' => ViewLearningLesson::route('/{record}'),
            'edit' => EditLearningLesson::route('/{record}/edit'),
        ];
    }
}
